<?php

namespace Tests\Feature\Account;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use App\Support\CabinetTour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Подсказки кабинета: порядок как в меню, шаги про шапку и ленты главной, на повышении пакета только новые разделы.
 */
class CabinetTourTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('ru');
    }

    private function member(Plan $plan): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT);

        return $user->fresh();
    }

    /** @return list<string> */
    private function targets(array $steps): array
    {
        return array_map(fn (array $step): string => (string) $step['target'], $steps);
    }

    public function test_the_full_tour_follows_the_menu_order_and_explains_the_home_blocks(): void
    {
        $targets = $this->targets(CabinetTour::steps($this->member(Plan::Active), replay: true));

        $this->assertSame([
            '[data-tour-nav]',
            '[data-tour-home="plan"]',
            '[data-tour-home="feed"]',
            '[data-tour="assistant"]',
            '[data-tour="matches"]',
            '[data-tour="search"]',
            '[data-tour="people"]',
            '[data-tour="possibilities"]',
            '[data-tour="knowledge"]',
            '[data-tour="manager"]',
            '[data-tour="opportunities"]',
            '[data-tour="profile"]',
        ], $targets);
    }

    public function test_the_menu_step_tells_about_premium_and_the_more_group(): void
    {
        $steps = CabinetTour::steps($this->member(Plan::Active), replay: true);

        $this->assertStringContainsString('PREMIUM', $steps[0]['text']);
        $this->assertStringContainsString('«Ещё»', $steps[0]['text']);
    }

    public function test_community_tour_has_no_premium_sections_but_has_learning(): void
    {
        $targets = $this->targets(CabinetTour::steps($this->member(Plan::Community), replay: true));

        $this->assertContains('[data-tour="knowledge"]', $targets);
        $this->assertContains('[data-tour="subscription"]', $targets);
        $this->assertNotContains('[data-tour="assistant"]', $targets);
        $this->assertNotContains('[data-tour="people"]', $targets);
    }

    public function test_upgrading_shows_only_the_sections_the_previous_plan_did_not_have(): void
    {
        $user = $this->member(Plan::Active);
        $user->markSeen(CabinetTour::seenKey(Plan::Community));

        $targets = $this->targets(CabinetTour::steps($user));

        $this->assertSame('', $targets[0], 'сначала поздравление');
        $this->assertSame(['[data-tour="assistant"]', '[data-tour="matches"]', '[data-tour="search"]', '[data-tour="people"]'], array_slice($targets, 1));
    }

    public function test_the_step_texts_exist_in_every_language(): void
    {
        foreach (['ru', 'en', 'ro'] as $locale) {
            app()->setLocale($locale);

            foreach (CabinetTour::steps($this->member(Plan::Active), replay: true) as $step) {
                $this->assertStringNotContainsString('account.', $step['title'].$step['text'].$step['how'], "{$locale}: нет перевода");
                $this->assertNotSame('', $step['title']);
            }
        }
    }
}
