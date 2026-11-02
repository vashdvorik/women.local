<?php

namespace Tests\Feature\Subscription;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\PlanRequest;
use App\Models\SiteSetting;
use App\Models\Subscription;
use App\Services\Subscriptions\PlanPurchase;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Логика тарифов и доступа к оплате во всех сочетаниях: что у участницы есть или было, какие тарифы закрыты для продажи и что
 * ей при этом можно купить. Главное правило: закрытие продаж касается новых покупателей. Тариф, который у участницы есть или
 * был (подарен, выдан вручную, оплачен), продлевается всегда; более высокий закрытый тариф покупается только после того,
 * как менеджер откроет покупку лично.
 */
class PlanAccessLogicTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Mail::fake();
        $this->withHeader('Accept-Language', 'ru');
    }

    private function user(): BotUser
    {
        return BotUser::factory()->approved()->create(['phone' => '+373 69 111 222']);
    }

    private function gift(BotUser $user, Plan $plan, string $source = Subscription::SOURCE_ADMIN): void
    {
        app(SubscriptionService::class)->grant($user, $plan, 12, $source, note: 'Подарок');
    }

    private function expire(BotUser $user): void
    {
        Subscription::where('bot_user_id', $user->id)->update(['starts_at' => now()->subYear()->subDay(), 'ends_at' => now()->subDay()]);
        app(SubscriptionService::class)->refresh($user->fresh());
    }

    private function can(BotUser $user, Plan $plan): bool
    {
        return app(PlanPurchase::class)->canBuy($user->fresh(), $plan);
    }

    /** @return array<string, list<string>> какие тарифы закрыты */
    public static function closedSets(): array
    {
        return [
            'всё открыто' => [[]],
            'закрыт Active' => [['active']],
            'закрыт Private' => [['private']],
            'закрыты Active и Private' => [['active', 'private']],
            'закрыто всё' => [['community', 'active', 'private']],
        ];
    }

    // ------------------------------------------------------------------ ваш случай: подарок и закрытая оплата

    public function test_a_gifted_closed_plan_can_be_renewed_by_the_participant(): void
    {
        SiteSetting::setClosedPlans(['active', 'private']);
        $user = $this->user();
        $this->gift($user, Plan::Active);

        $this->assertTrue($this->can($user, Plan::Active), 'подаренный Active можно продлить, хотя продажа Active закрыта');

        $html = $this->asParticipant($user)->get(route('account.subscription'))->assertOk()->getContent();
        $this->assertStringContainsString('Продлить за', $html);
        $this->assertSame(1, substr_count($html, route('account.plan-request.store')), 'заявка осталась только у Private, а у Active её нет');
    }

    public function test_the_renewal_goes_through_checkout_for_a_gifted_closed_plan(): void
    {
        SiteSetting::setClosedPlans(['active']);
        config(['webpayment.driver' => 'fake', 'webpayment.enabled' => true]);
        $user = $this->user();
        $this->gift($user, Plan::Active);

        $response = $this->asParticipant($user)->post(route('account.subscription.checkout'), ['plan' => 'active']);

        $response->assertOk();
        $this->assertDatabaseHas('payments', ['bot_user_id' => $user->id, 'plan' => 'active']);
    }

    public function test_after_the_gift_expires_the_plan_can_still_be_renewed_but_a_higher_closed_one_cannot(): void
    {
        SiteSetting::setClosedPlans(['active', 'private']);
        $user = $this->user();
        $this->gift($user, Plan::Active);
        $this->expire($user);

        $this->assertSame(Plan::None, $user->fresh()->currentPlan());
        $this->assertTrue($this->can($user, Plan::Active));
        $this->assertFalse($this->can($user, Plan::Private));
    }

    public function test_a_bought_closed_plan_stays_renewable_after_the_managers_unlock_was_used(): void
    {
        SiteSetting::setClosedPlans(['private']);
        $user = $this->user();
        app(PlanPurchase::class)->unlockFor($user, Plan::Private);
        $this->assertTrue($this->can($user, Plan::Private));

        $this->gift($user, Plan::Private, Subscription::SOURCE_PAYMENT);

        // Заявка закрыта оплатой, но продление остаётся: раньше его пришлось бы «открывать» заново каждый год.
        $this->assertSame(PlanRequest::STATUS_PURCHASED, $user->planRequests()->first()->status);
        $this->assertTrue($this->can($user, Plan::Private));
    }

    public function test_setting_an_exact_date_also_closes_the_requests_for_that_plan(): void
    {
        SiteSetting::setClosedPlans(['active']);
        $user = $this->user();
        $request = app(PlanPurchase::class)->unlockFor($user, Plan::Active);

        app(SubscriptionService::class)->setUntil($user, Plan::Active, now()->addMonths(6), 'Исправление');

        $this->assertSame(PlanRequest::STATUS_PURCHASED, $request->fresh()->status);
        $this->assertTrue($this->can($user, Plan::Active), 'и продлить тариф она по-прежнему может');
    }

    // ------------------------------------------------------------------ повышение тарифа

    public function test_upgrading_to_a_closed_higher_plan_still_needs_the_manager(): void
    {
        SiteSetting::setClosedPlans(['private']);
        $user = $this->user();
        $this->gift($user, Plan::Active);

        $this->assertFalse($this->can($user, Plan::Private));
        $html = $this->asParticipant($user)->get(route('account.subscription'))->assertOk()->getContent();
        $this->assertStringContainsString(route('account.plan-request.store'), $html);

        app(PlanPurchase::class)->unlockFor($user, Plan::Private);
        $this->assertTrue($this->can($user, Plan::Private), 'менеджер открыл покупку лично');
    }

    public function test_a_gift_of_a_lower_plan_does_not_consume_the_unlock_of_a_higher_one(): void
    {
        SiteSetting::setClosedPlans(['private']);
        $user = $this->user();
        app(PlanPurchase::class)->unlockFor($user, Plan::Private);

        $this->gift($user, Plan::Active);

        $this->assertTrue($this->can($user, Plan::Private), 'подарок Active не отнимает открытую покупку Private');
    }

    public function test_a_higher_plan_makes_the_lower_ones_unnecessary(): void
    {
        SiteSetting::setClosedPlans([]);
        $user = $this->user();
        $this->gift($user, Plan::Private);

        $html = $this->asParticipant($user)->get(route('account.subscription'))->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, __('subscription.page.lower_included')), 'Community и Active уже входят в Private');

        // Сервер тоже не даст оплатить меньший тариф, даже поддельной формой.
        config(['webpayment.driver' => 'fake', 'webpayment.enabled' => true]);
        $this->asParticipant($user)->post(route('account.subscription.checkout'), ['plan' => 'community'])->assertRedirect(route('account.subscription'));
        $this->assertDatabaseCount('payments', 0);
    }

    // ------------------------------------------------------------------ полная матрица

    /**
     * Что у участницы → какие тарифы ей можно купить при каждом наборе закрытых.
     * Правило: открытый тариф — да; закрытый — только если он у неё есть или был (продление) или менеджер открыл покупку.
     *
     * @dataProvider closedSets
     */
    public function test_the_matrix_of_what_can_be_bought(array $closed): void
    {
        SiteSetting::setClosedPlans($closed);
        $purchase = app(PlanPurchase::class);

        $states = [
            'ничего нет' => fn (BotUser $u) => null,
            'подарен Community' => fn (BotUser $u) => $this->gift($u, Plan::Community),
            'подарен Active' => fn (BotUser $u) => $this->gift($u, Plan::Active),
            'подарен Private' => fn (BotUser $u) => $this->gift($u, Plan::Private),
            'оплачен Active' => fn (BotUser $u) => $this->gift($u, Plan::Active, Subscription::SOURCE_PAYMENT),
            'Active закончился' => function (BotUser $u): void { $this->gift($u, Plan::Active); $this->expire($u); },
            'Active отозван' => fn (BotUser $u) => tap($this->gift($u, Plan::Active), fn () => app(SubscriptionService::class)->revoke($u->fresh())),
        ];

        // Какие тарифы у каждой участницы «были»: по ним продление всегда открыто.
        $had = [
            'ничего нет' => [], 'подарен Community' => ['community'], 'подарен Active' => ['active'], 'подарен Private' => ['private'],
            'оплачен Active' => ['active'], 'Active закончился' => ['active'], 'Active отозван' => ['active'],
        ];

        foreach ($states as $label => $setup) {
            $user = $this->user();
            $setup($user);

            foreach (Plan::paid() as $plan) {
                $expected = ! in_array($plan->value, $closed, true) || in_array($plan->value, $had[$label], true);

                $this->assertSame($expected, $purchase->canBuy($user->fresh(), $plan), "«{$label}», тариф {$plan->value}, закрыто: ".implode(',', $closed));
            }
        }
    }

    // ------------------------------------------------------------------ заявка на тариф, который и так можно купить

    public function test_a_request_for_a_plan_that_can_be_renewed_is_refused_with_an_explanation(): void
    {
        SiteSetting::setClosedPlans(['active']);
        $user = $this->user();
        $this->gift($user, Plan::Active);

        $this->asParticipant($user)->post(route('account.plan-request.store'), ['plan' => 'active'])
            ->assertRedirect(route('account.subscription'))->assertSessionHas('error');
        $this->assertDatabaseCount('plan_requests', 0);
    }

    // ------------------------------------------------------------------ выдача тарифа и одобрение анкеты

    public function test_a_plan_can_be_granted_to_a_participant_whose_profile_is_not_yet_approved(): void
    {
        $pending = BotUser::factory()->pending()->create();

        $this->actingAsAdmin()->post(route('admin.subscriptions.grant', $pending), ['plan' => 'community', 'mode' => 'months', 'months' => 12])
            ->assertSessionHas('success');

        $this->assertSame(Plan::Community, $pending->fresh()->currentPlan());
    }

    public function test_a_plan_cannot_be_granted_to_a_rejected_profile(): void
    {
        $rejected = BotUser::factory()->create(['status' => BotUser::STATUS_REJECTED]);

        $this->actingAsAdmin()->post(route('admin.subscriptions.grant', $rejected), ['plan' => 'community', 'mode' => 'months', 'months' => 12])
            ->assertStatus(422);

        $this->assertSame(Plan::None, $rejected->fresh()->currentPlan());
    }

    public function test_the_admin_page_shows_that_a_closed_plan_is_renewable_for_its_holder(): void
    {
        SiteSetting::setClosedPlans(['active', 'private']);
        $user = $this->user();
        $this->gift($user, Plan::Active);

        $this->actingAsAdmin()->get(route('admin.subscriptions.edit', $user))->assertOk()
            ->assertSee('продление открыто')
            ->assertSee('Открыть покупку');
    }

    public function test_the_ai_manager_is_told_that_a_gifted_closed_plan_can_be_renewed(): void
    {
        SiteSetting::setClosedPlans(['active', 'private']);
        $user = $this->user();
        $this->gift($user, Plan::Active);

        $facts = (new \ReflectionClass(\App\Services\AiManagerService::class))->getMethod('facts');
        $facts->setAccessible(true);
        $text = $facts->invoke(app(\App\Services\AiManagerService::class), $user->fresh(), 'ru');

        $this->assertMatchesRegularExpression('/WOMEN.S HUB ACTIVE[^\n]*HER CURRENT PLAN[^\n]*she can buy or renew it right away/u', $text);
        $this->assertMatchesRegularExpression('/WOMEN.S HUB PRIVATE[^\n]*purchase by request/u', $text);
    }
}
