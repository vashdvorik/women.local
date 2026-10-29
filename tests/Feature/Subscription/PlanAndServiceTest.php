<?php

namespace Tests\Feature\Subscription;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Тарифы кабинета и единственное место, где они меняются (SubscriptionService): продление, повышение, окончание.
 */
class PlanAndServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SubscriptionService::class);
        $this->travelTo(now()->startOfDay()->setHour(12));
    }

    public function test_plans_are_ordered_and_nested(): void
    {
        $this->assertSame([0, 1, 2], [Plan::Community->rank(), Plan::Active->rank(), Plan::Private->rank()]);
        $this->assertTrue(Plan::Private->includes(Plan::Active));
        $this->assertTrue(Plan::Active->includes(Plan::Community));
        $this->assertFalse(Plan::Community->includes(Plan::Active));
        $this->assertSame([Plan::Active, Plan::Private], Plan::atLeast(Plan::Active));
        $this->assertSame([Plan::Active, Plan::Private], Plan::paid());
    }

    public function test_prices_come_from_the_settings_in_rubles_and_go_to_the_bank_in_kopecks(): void
    {
        $this->assertSame(600, Plan::Active->price());
        $this->assertSame(60000, Plan::Active->priceKopecks());
        $this->assertSame(20000, Plan::Private->price());
        $this->assertSame(2000000, Plan::Private->priceKopecks());
        $this->assertSame(0, Plan::Community->priceKopecks());
        $this->assertSame(12, Plan::Active->months());

        config(['subscription.plans.community.price' => 750]);
        $this->assertSame(75000, Plan::Active->priceKopecks());
    }

    public function test_price_labels_and_titles_follow_the_language(): void
    {
        $this->assertSame("600\u{00A0}руб.", Plan::Active->priceLabel('ru'));
        $this->assertSame("20\u{00A0}000\u{00A0}руб.", Plan::Private->priceLabel('ru'));
        $this->assertSame("600\u{00A0}rub.", Plan::Active->priceLabel('en'));
        $this->assertSame('Бесплатно', Plan::Community->priceLabel('ru'));
        $this->assertSame('Free', Plan::Community->priceLabel('en'));
        $this->assertSame('WOMEN’S HUB COMMUNITY', Plan::Active->title('ru'));
        $this->assertSame('WOMEN’S HUB PRIVATE', Plan::Private->title('ro'));
    }

    public function test_every_plan_description_is_present_in_all_three_languages(): void
    {
        foreach (['ru' => 7, 'en' => 7, 'ro' => 7] as $locale => $openCount) {
            $this->assertCount($openCount, __('subscription.plans.open.features', [], $locale), $locale);
            $this->assertCount(10, __('subscription.plans.community.features', [], $locale), $locale);
            $this->assertCount(10, __('subscription.plans.private.features', [], $locale), $locale);

            foreach (Plan::cases() as $plan) {
                $this->assertNotSame("subscription.plans.{$plan->value}.lead", __("subscription.plans.{$plan->value}.lead", [], $locale), "$locale/{$plan->value}");
            }
        }
    }

    public function test_a_new_participant_is_on_open_and_the_plan_cannot_be_mass_assigned(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->assertSame(Plan::Community, $user->currentPlan());

        // Тариф нельзя выдать через массовое заполнение (форма профиля, бот): только через сервис.
        $user->update(['plan' => 'private', 'plan_ends_at' => now()->addYear()]);

        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
    }

    public function test_grant_turns_the_plan_on_for_the_period(): void
    {
        $user = BotUser::factory()->approved()->create();

        $subscription = $this->service->grant($user, Plan::Active, 12, note: 'тест');

        $user->refresh();
        $this->assertSame(Plan::Active, $user->currentPlan());
        $this->assertTrue($user->hasPlan(Plan::Active));
        $this->assertFalse($user->hasPlan(Plan::Private));
        $this->assertTrue($user->plan_ends_at->equalTo(now()->addMonthsNoOverflow(12)));
        $this->assertSame(Subscription::SOURCE_ADMIN, $subscription->source);
        $this->assertSame('тест', $subscription->note);
    }

    public function test_renewal_continues_from_the_end_of_the_paid_period_instead_of_losing_days(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->service->grant($user, Plan::Active, 12);
        $firstEnd = $user->fresh()->plan_ends_at->copy();

        $this->travelTo(now()->addMonths(10));
        $this->service->grant($user, Plan::Active, 12);

        $user->refresh();
        $this->assertTrue($user->plan_ends_at->equalTo($firstEnd->copy()->addMonthsNoOverflow(12)), 'продление добавляется к концу, а не к дате оплаты');
        $this->assertSame(2, $user->subscriptions()->count());
    }

    public function test_upgrade_to_private_starts_immediately_and_the_community_remainder_returns_if_it_is_longer(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->service->grant($user, Plan::Active, 12);
        $communityEnd = $user->fresh()->plan_ends_at->copy();

        $this->travelTo(now()->addMonths(2));
        $this->service->grant($user, Plan::Private, 1);

        $user->refresh();
        $this->assertSame(Plan::Private, $user->currentPlan(), 'Private включается сразу');
        $this->assertTrue($user->plan_ends_at->equalTo(now()->addMonthNoOverflow()));

        // Private закончился, а Community ещё оплачен: после пересчёта возвращается Community до его срока.
        $this->travelTo(now()->addMonths(1)->addDay());
        $this->service->refresh($user);
        $user->refresh();

        $this->assertSame(Plan::Active, $user->currentPlan());
        $this->assertTrue($user->plan_ends_at->equalTo($communityEnd));
    }

    public function test_access_closes_at_the_moment_the_subscription_ends_even_before_any_job_runs(): void
    {
        $user = BotUser::factory()->approved()->create();
        $this->service->grant($user, Plan::Active, 1);

        $this->assertTrue($user->fresh()->hasPlan(Plan::Active));

        $this->travelTo(now()->addMonth()->addMinute());

        // В базе ещё Community, но по дате доступа уже нет.
        $this->assertSame('community', $user->fresh()->plan);
        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
        $this->assertFalse(BotUser::members()->whereKey($user->id)->exists());
    }

    public function test_set_until_replaces_all_current_periods_with_one(): void
    {
        $user = BotUser::factory()->approved()->create();
        $this->service->grant($user, Plan::Active, 12);
        $this->service->grant($user, Plan::Active, 12);

        $this->service->setUntil($user, Plan::Private, now()->addDays(40), 'договорились');

        $user->refresh();
        $this->assertSame(Plan::Private, $user->currentPlan());
        $this->assertTrue($user->plan_ends_at->equalTo(now()->addDays(40)));
        $this->assertSame(1, $user->subscriptions()->active()->count(), 'идёт один период');
        $this->assertSame(0, $user->subscriptions()->where('plan', 'community')->where('ends_at', '>', now())->count(), 'прежние закрыты');
    }

    public function test_revoke_returns_the_participant_to_open_at_once(): void
    {
        $user = BotUser::factory()->approved()->create();
        $this->service->grant($user, Plan::Private, 12);
        $this->service->grant($user, Plan::Active, 12);

        $this->service->revoke($user);

        $user->refresh();
        $this->assertSame(Plan::Community, $user->currentPlan());
        $this->assertSame('open', $user->plan);
        $this->assertNull($user->plan_ends_at);
    }

    public function test_invalid_grants_are_rejected(): void
    {
        $user = BotUser::factory()->approved()->create();

        foreach ([
            fn () => $this->service->grant($user, Plan::Community, 12),
            fn () => $this->service->grant($user, Plan::Active, 0),
            fn () => $this->service->setUntil($user, Plan::Active, now()->subDay()),
            fn () => $this->service->setUntil($user, Plan::Community, now()->addDay()),
        ] as $attempt) {
            try {
                $attempt();
                $this->fail('Ожидалось исключение');
            } catch (InvalidArgumentException) {
                $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
            }
        }
    }

    public function test_members_are_approved_participants_with_a_live_paid_plan(): void
    {
        $open = BotUser::factory()->approved()->create();
        $community = BotUser::factory()->active()->create();
        $private = BotUser::factory()->private()->create();
        $pendingPaid = BotUser::factory()->pending()->create();
        $this->service->grant($pendingPaid, Plan::Active, 12);
        $expired = BotUser::factory()->active()->create();
        $expired->subscriptions()->update(['ends_at' => now()->subDay()]);
        $expired->forceFill(['plan_ends_at' => now()->subDay()])->save();

        $ids = BotUser::members()->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$community->id, $private->id], $ids);
        $this->assertNotContains($open->id, $ids);
        $this->assertNotContains($pendingPaid->id, $ids, 'неодобренная не попадает в каталог, даже если тариф выдан');
        $this->assertNotContains($expired->id, $ids);
        $this->assertSame([$private->id], BotUser::withPlan(Plan::Private)->pluck('id')->all());
    }
}
