<?php

namespace Tests\Feature\Admin;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Nutgram\Laravel\Facades\Telegram;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * «Подписки»: администратор меняет цену подписки и дарит тариф на год. Подарок включается молча.
 */
class SubscriptionPriceAndGiftTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Telegram::fake();
    }

    private function savePrices(mixed $community, mixed $private)
    {
        return $this->actingAsAdmin()->put(route('admin.subscriptions.prices'), ['community' => $community, 'private' => $private]);
    }

    // ---------------------------------------------------------------- цена

    public function test_without_admin_prices_the_configured_ones_are_used_and_the_form_shows_them(): void
    {
        $this->assertSame(600, Plan::Community->price());
        $this->assertSame(20000, Plan::Private->price());

        $this->actingAsAdmin()->get(route('admin.subscriptions.index'))
            ->assertOk()
            ->assertSee('Цена подписки на год')
            ->assertSee('name="community"', false)
            ->assertSee('value="600"', false)
            ->assertSee('value="20000"', false);
    }

    public function test_the_administrator_changes_the_prices_and_everything_follows(): void
    {
        $this->savePrices(750, 25000)->assertRedirect(route('admin.subscriptions.index'))->assertSessionHas('success');

        $this->assertSame(['community' => 750, 'private' => 25000], SiteSetting::subscriptionPrices());
        $this->assertSame(750, Plan::Community->price());
        $this->assertSame(75000, Plan::Community->priceKopecks());
        $this->assertSame(25000, Plan::Private->price());
        $this->assertSame("750\u{00A0}руб.", Plan::Community->priceLabel('ru'));

        // Участница видит новые цены на странице тарифов и во вкладке Private, форма админки — тоже.
        $user = BotUser::factory()->community()->create();
        $plans = $this->asParticipant($user)->get(route('account.subscription'))->assertOk()->getContent();
        $this->assertStringContainsString("750\u{00A0}руб.", $plans);
        $this->assertStringContainsString("25\u{00A0}000\u{00A0}руб.", $plans);
        $this->assertStringNotContainsString("20\u{00A0}000\u{00A0}руб.", $plans);
        $this->asParticipant($user)->get(route('account.private'))->assertSee("25\u{00A0}000\u{00A0}руб.", false);

        $this->actingAsAdmin()->get(route('admin.subscriptions.index'))->assertSee('value="750"', false)->assertSee('value="25000"', false);
    }

    public function test_a_new_payment_is_issued_for_the_new_price_and_an_old_invoice_stays_valid_at_the_old_one(): void
    {
        $user = BotUser::factory()->approved()->create();
        $old = $this->checkoutFor($user, Plan::Community);
        $this->assertSame(60000, $old->amount);

        $this->savePrices(750, 20000);

        // Цена изменилась: повторное нажатие «Оформить» даёт новый счёт на новую сумму, а не старый.
        $new = $this->checkoutFor($user, Plan::Community);
        $this->assertNotSame($old->id, $new->id);
        $this->assertSame(75000, $new->amount);

        // Пока цена не меняется, счёт переиспользуется, как и раньше.
        $this->assertSame($new->id, $this->checkoutFor($user, Plan::Community)->id);

        // Старый счёт не изменился и оплачивается по той сумме, на которую был выставлен.
        $this->assertSame(60000, $old->fresh()->amount);
        $this->assertSame(Payment::STATUS_PENDING, $old->fresh()->status);
    }

    public function test_a_price_change_does_not_touch_plans_already_paid_for(): void
    {
        $user = BotUser::factory()->community()->create();
        $endsAt = $user->fresh()->plan_ends_at->copy();

        $this->savePrices(9999, 99999);

        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
        $this->assertTrue($user->fresh()->plan_ends_at->equalTo($endsAt));
    }

    public function test_invalid_prices_are_refused_and_change_nothing(): void
    {
        foreach ([
            [0, 20000],
            [-5, 20000],
            [600, 0],
            ['', 20000],
            [600, ''],
            ['шестьсот', 20000],
            ['600.5', 20000],
            [SiteSetting::SUBSCRIPTION_PRICE_MAX + 1, SiteSetting::SUBSCRIPTION_PRICE_MAX + 1],
            [600, SiteSetting::SUBSCRIPTION_PRICE_MAX + 1],
            [20000, 600], // Private дешевле Community — скорее всего опечатка
        ] as [$community, $private]) {
            $response = $this->savePrices($community, $private);
            $this->assertTrue($response->getSession()->has('errors'), 'принято: '.json_encode([$community, $private], JSON_UNESCAPED_UNICODE).' → '.$response->getStatusCode());
        }

        $this->assertSame([], SiteSetting::subscriptionPrices());
        $this->assertSame(600, Plan::Community->price());
        $this->assertSame(20000, Plan::Private->price());
    }

    public function test_equal_prices_are_allowed(): void
    {
        $this->savePrices(1000, 1000)->assertSessionHasNoErrors();

        $this->assertSame(1000, Plan::Private->price());
    }

    public function test_the_cached_prices_are_refreshed_on_save_and_a_broken_stored_value_falls_back_to_the_config(): void
    {
        $this->savePrices(700, 21000);
        $this->assertSame(700, Plan::Community->price());

        // Испорченные данные в настройках (кто-то правил базу руками) не ломают тарифы: берётся значение из конфига.
        SiteSetting::query()->where('key', SiteSetting::SUBSCRIPTION_PRICES_KEY)->update(['value' => json_encode(['community' => 'много', 'private' => -4])]);
        Cache::forget(SiteSetting::SUBSCRIPTION_PRICES_KEY);

        $this->assertSame(600, Plan::Community->price());
        $this->assertSame(20000, Plan::Private->price());
    }

    // ---------------------------------------------------------------- подарок

    public function test_a_gift_turns_the_plan_on_for_a_year_and_the_participant_is_not_told(): void
    {
        $user = BotUser::factory()->approved()->create(['locale' => 'ru']);
        $this->travelTo(now()->startOfSecond());

        $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $user), ['plan' => 'community'])
            ->assertRedirect(route('admin.subscriptions.edit', $user))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame(Plan::Community, $user->currentPlan());
        $this->assertTrue($user->plan_ends_at->equalTo(now()->addYear()), 'ровно год');

        $subscription = $user->subscriptions()->first();
        $this->assertSame(Subscription::SOURCE_ADMIN, $subscription->source);
        $this->assertSame('Подарок', $subscription->note);
        $this->assertNull($subscription->payment_id);
        $this->assertSame(0, Payment::count(), 'подарок — не платёж');

        $this->assertSame([], Telegram::getRequestHistory(), 'ни одного сообщения участнице');
    }

    public function test_a_gift_of_private_opens_the_private_tab_and_a_second_gift_adds_another_year(): void
    {
        $user = BotUser::factory()->approved()->create();
        $this->travelTo(now()->startOfSecond());

        $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $user), ['plan' => 'private', 'note' => 'за выступление на встрече'])->assertSessionHas('success');
        $this->assertSame('за выступление на встрече', $user->subscriptions()->first()->note);

        $this->asParticipant($user->fresh())->get(route('account.private'))->assertOk();
        $this->asParticipant($user->fresh())->get(route('account.people'))->assertOk();

        $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $user), ['plan' => 'private'])->assertSessionHas('success');
        $this->assertTrue($user->fresh()->plan_ends_at->equalTo(now()->addYears(2)), 'второй год добавлен к концу первого');
        $this->assertSame([], Telegram::getRequestHistory());
    }

    public function test_a_gift_follows_the_same_term_as_a_purchase(): void
    {
        config(['subscription.plans.community.months' => 6]);
        $user = BotUser::factory()->approved()->create();
        $this->travelTo(now()->startOfSecond());

        $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $user), ['plan' => 'community']);

        $this->assertTrue($user->fresh()->plan_ends_at->equalTo(now()->addMonthsNoOverflow(6)));
    }

    public function test_invalid_gifts_are_refused_and_change_nothing(): void
    {
        $user = BotUser::factory()->approved()->create();
        $pending = BotUser::factory()->pending()->create();

        foreach (['open', 'gold', ''] as $plan) {
            $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $user), ['plan' => $plan])->assertSessionHasErrors('plan');
        }

        $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $pending), ['plan' => 'community'])->assertStatus(422);

        $this->assertSame(0, Subscription::count());
        $this->assertSame(Plan::Open, $user->fresh()->currentPlan());
    }

    public function test_the_manage_page_offers_a_gift_first_and_the_manual_grant_is_quiet_by_default(): void
    {
        $user = BotUser::factory()->approved()->create();

        $html = $this->actingAsAdmin()->get(route('admin.subscriptions.edit', $user))->assertOk()
            ->assertSee('Подарить подписку')
            ->assertSee('Подарить на год')
            ->assertSee('участница ничего не получит', false)
            ->getContent();

        $this->assertStringContainsString(route('admin.subscriptions.gift', $user), $html);
        $this->assertDoesNotMatchRegularExpression('~name="notify"[^>]*\bchecked\b~', $html, 'галочка «сообщить» по умолчанию снята');

        // Ручная выдача без галочки тоже молчит.
        $this->actingAsAdmin()->post(route('admin.subscriptions.grant', $user), ['plan' => 'community', 'mode' => 'months', 'months' => 3])->assertSessionHas('success');
        $this->assertSame([], Telegram::getRequestHistory());
    }

    public function test_a_gifted_plan_still_gets_the_usual_expiry_reminders(): void
    {
        $user = BotUser::factory()->approved()->create(['telegram_id' => 7777, 'locale' => 'ru']);
        $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $user), ['plan' => 'community']);
        $this->assertSame([], Telegram::getRequestHistory());

        $this->travelTo($user->fresh()->plan_ends_at->copy()->subDays(14)->addHour());
        $this->artisan('subscriptions:notify')->assertSuccessful();

        $this->assertCount(1, Telegram::getRequestHistory(), 'о подарке молчим, но о конце срока предупреждаем как обычно');
    }
}
