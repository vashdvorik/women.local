<?php

namespace Tests\Feature\Subscription;

use App\Enums\Plan;
use App\Mail\PlanPurchaseOpenedMail;
use App\Mail\PlanRequestMail;
use App\Models\BotUser;
use App\Models\Payment;
use App\Models\PlanRequest;
use App\Models\SiteSetting;
use App\Services\Subscriptions\PlanPurchase;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Закрытая покупка: вместо «Купить» стоит «Оставить заявку», менеджер звонит и открывает покупку участнице лично.
 */
class ClosedPurchaseTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Mail::fake();
        SiteSetting::setClosedPlans([Plan::Community->value, Plan::Private->value]);
        SiteSetting::setPlanRequestEmail('manager@example.test');
    }

    private function newcomer(): BotUser
    {
        return BotUser::factory()->approved()->create(['phone' => '+373 69 111 222']);
    }

    public function test_by_default_every_plan_can_be_bought_and_closing_is_per_plan(): void
    {
        SiteSetting::setClosedPlans([]);
        $user = $this->newcomer();
        $purchase = app(PlanPurchase::class);

        foreach (Plan::paid() as $plan) {
            $this->assertTrue($purchase->canBuy($user, $plan), $plan->value);
        }

        SiteSetting::setClosedPlans([Plan::Community->value]);

        $this->assertTrue($purchase->canBuy($user, Plan::Open));
        $this->assertFalse($purchase->canBuy($user, Plan::Community));
        $this->assertTrue($purchase->canBuy($user, Plan::Private));
    }

    public function test_the_plans_page_shows_buy_for_open_and_leave_a_request_for_the_closed_plans(): void
    {
        $user = $this->newcomer();

        $html = $this->withSession($this->participantSession($user))->get(route('account.subscription'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'name="comment"'), 'две формы заявки: Community и Private');
        $this->assertSame(2, substr_count($html, __('participant.plans.request')), 'кнопка «Оставить заявку» у двух закрытых тарифов');
        $this->assertStringContainsString(route('account.subscription.checkout'), $html, 'у Open обычная кнопка покупки');
    }

    public function test_a_closed_plan_cannot_be_checked_out_even_with_a_forged_form(): void
    {
        $user = $this->newcomer();

        $this->withSession($this->participantSession($user))
            ->post(route('account.subscription.checkout'), ['plan' => 'community'])
            ->assertRedirect(route('account.subscription'))
            ->assertSessionHas('error');

        $this->assertSame(0, Payment::count());
    }

    public function test_a_request_is_saved_the_manager_gets_the_letter_and_a_second_request_is_not_created(): void
    {
        $user = $this->newcomer();

        $this->withSession($this->participantSession($user))
            ->post(route('account.plan-request.store'), ['plan' => 'community', 'comment' => 'Позвоните после 15:00'])
            ->assertRedirect(route('account.subscription'))
            ->assertSessionHas('success');

        $request = PlanRequest::firstOrFail();
        $this->assertSame($user->id, $request->bot_user_id);
        $this->assertSame('community', $request->plan);
        $this->assertSame(PlanRequest::STATUS_NEW, $request->status);
        $this->assertSame('Позвоните после 15:00', $request->comment);

        Mail::assertSent(PlanRequestMail::class, fn (PlanRequestMail $mail) => $mail->hasTo('manager@example.test'));

        $this->withSession($this->participantSession($user))->post(route('account.plan-request.store'), ['plan' => 'community']);

        $this->assertSame(1, PlanRequest::count());
        Mail::assertSent(PlanRequestMail::class, 1);
    }

    public function test_a_plan_that_is_open_for_purchase_does_not_take_requests(): void
    {
        $user = $this->newcomer();

        $this->withSession($this->participantSession($user))
            ->post(route('account.plan-request.store'), ['plan' => 'open'])
            ->assertRedirect(route('account.subscription'))
            ->assertSessionHas('error');

        $this->assertSame(0, PlanRequest::count());
    }

    public function test_a_request_needs_a_phone(): void
    {
        $user = BotUser::factory()->approved()->create(['phone' => null]);

        $this->withSession($this->participantSession($user))
            ->post(route('account.plan-request.store'), ['plan' => 'community'])
            ->assertRedirect(route('account.profile.edit'));

        $this->assertSame(0, PlanRequest::count());
    }

    public function test_opening_the_purchase_lets_only_that_participant_buy_the_plan(): void
    {
        $user = $this->newcomer();
        $other = $this->newcomer();
        $purchase = app(PlanPurchase::class);

        $request = $purchase->request($user, Plan::Community);
        $purchase->unlock($request, notify: true);

        $this->assertTrue($purchase->canBuy($user, Plan::Community));
        $this->assertFalse($purchase->canBuy($other, Plan::Community));
        $this->assertFalse($purchase->canBuy($user, Plan::Private), 'другой закрытый тариф по-прежнему закрыт');

        Mail::assertSent(PlanPurchaseOpenedMail::class, fn (PlanPurchaseOpenedMail $mail) => $mail->hasTo($user->email));

        $this->registerFakeBank();

        $this->withSession($this->participantSession($user))
            ->post(route('account.subscription.checkout'), ['plan' => 'community'])
            ->assertOk();

        $this->assertSame(1, Payment::where('bot_user_id', $user->id)->count());
    }

    public function test_the_request_closes_when_the_plan_is_granted(): void
    {
        $user = $this->newcomer();
        $purchase = app(PlanPurchase::class);

        $purchase->unlock($purchase->request($user, Plan::Community));
        app(SubscriptionService::class)->grant($user, Plan::Community, 12, note: 'Оплата');

        $this->assertSame(PlanRequest::STATUS_PURCHASED, PlanRequest::firstOrFail()->status);
        $this->assertTrue($user->fresh()->hasPlan(Plan::Community));
    }

    public function test_the_admin_can_close_plans_set_the_manager_email_and_open_a_purchase(): void
    {
        $user = $this->newcomer();

        $this->actingAsAdmin()->put(route('admin.subscriptions.purchase'), ['closed' => ['private'], 'manager_email' => 'boss@example.test'])
            ->assertRedirect(route('admin.subscriptions.index'));

        $this->assertSame(['private'], SiteSetting::closedPlans());
        $this->assertSame('boss@example.test', SiteSetting::planRequestEmail());

        $this->actingAsAdmin()->post(route('admin.subscriptions.unlock', $user), ['plan' => 'private'])
            ->assertRedirect(route('admin.subscriptions.edit', $user));

        $this->assertTrue(app(PlanPurchase::class)->canBuy($user, Plan::Private));
    }

    public function test_the_admin_list_of_requests_opens_a_purchase_and_closes_a_request(): void
    {
        $user = $this->newcomer();
        $purchase = app(PlanPurchase::class);
        $request = $purchase->request($user, Plan::Private);

        $this->actingAsAdmin()->get(route('admin.plan-requests.index'))->assertOk()->assertSee($user->phone);

        $this->actingAsAdmin()->post(route('admin.plan-requests.contacted', $request))->assertRedirect();
        $this->assertSame(PlanRequest::STATUS_CONTACTED, $request->fresh()->status);

        $this->actingAsAdmin()->post(route('admin.plan-requests.unlock', $request))->assertRedirect();
        $this->assertSame(PlanRequest::STATUS_UNLOCKED, $request->fresh()->status);
        $this->assertTrue($purchase->canBuy($user, Plan::Private));

        $this->actingAsAdmin()->post(route('admin.plan-requests.decline', $request))->assertRedirect();
        $this->assertSame(PlanRequest::STATUS_DECLINED, $request->fresh()->status);
        $this->assertFalse($purchase->canBuy($user, Plan::Private), 'закрытая заявка снова закрывает покупку');
    }

    public function test_only_the_admin_reaches_the_requests(): void
    {
        $user = $this->newcomer();

        $this->withSession($this->participantSession($user))->get(route('admin.plan-requests.index'))->assertRedirect();
    }

    public function test_prices_default_to_600_5000_and_19000_and_must_not_decrease(): void
    {
        $this->assertSame([600, 5000, 19000], [Plan::Open->price(), Plan::Community->price(), Plan::Private->price()]);

        $this->actingAsAdmin()->put(route('admin.subscriptions.prices'), ['open' => 6000, 'community' => 5000, 'private' => 19000])
            ->assertSessionHasErrors('community');

        $this->actingAsAdmin()->put(route('admin.subscriptions.prices'), ['open' => 700, 'community' => 5500, 'private' => 20000])
            ->assertRedirect(route('admin.subscriptions.index'));

        $this->assertSame([700, 5500, 20000], [Plan::Open->price(), Plan::Community->price(), Plan::Private->price()]);
    }
}
