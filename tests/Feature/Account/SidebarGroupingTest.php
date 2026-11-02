<?php

namespace Tests\Feature\Account;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use App\Support\CabinetNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Боковое меню кабинета: на ACTIVE и PRIVATE редкие разделы лежат в группе «Ещё», разделы пакетов ACTIVE+ помечены PREMIUM.
 */
class SidebarGroupingTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'ru');
    }

    private function member(Plan $plan): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT);

        return $user->fresh();
    }

    public function test_active_and_private_get_a_more_group_with_the_rare_sections(): void
    {
        foreach ([Plan::Active, Plan::Private] as $plan) {
            $entries = CabinetNav::sidebar($this->member($plan));
            $groups = array_values(array_filter($entries, fn ($e) => ! empty($e['group'])));

            $this->assertCount(1, $groups);
            $this->assertSame(['manager', 'opportunities', 'subscription'], array_column($groups[0]['items'], 'key'));

            // «Главная», затем разделы PREMIUM, затем «Возможности» и «Обучение», группа «Ещё» и «Профиль»
            $this->assertSame(
                ['home', 'assistant', 'matches', 'search', 'people', 'possibilities', 'knowledge', 'more', 'profile'],
                array_column($entries, 'key'),
            );
        }
    }

    public function test_community_keeps_a_flat_menu(): void
    {
        $entries = CabinetNav::sidebar($this->member(Plan::Community));

        $this->assertSame([], array_filter($entries, fn ($e) => ! empty($e['group'])));
        $this->assertSame(CabinetNav::items($this->member(Plan::Community), includeLocked: false), $entries);
    }

    public function test_only_the_active_plus_sections_are_marked_premium(): void
    {
        $items = CabinetNav::items($this->member(Plan::Private), includeLocked: false);
        $premium = array_column(array_filter($items, fn ($i) => $i['premium']), 'key');

        $this->assertSame(['assistant', 'matches', 'search', 'people'], $premium);
    }

    public function test_the_page_shows_premium_tags_and_the_group_and_opens_it_on_a_group_page(): void
    {
        $member = $this->member(Plan::Active);

        $html = $this->asParticipant($member)->get(route('account.index'))->assertOk()->getContent();
        $this->assertSame(4, substr_count($html, 'account-premium-tag" title'));
        $this->assertStringContainsString('account-nav-group', $html);
        $this->assertDoesNotMatchRegularExpression('/<details class="account-nav-group[^>]*\sopen/', $html);

        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $html = $this->asParticipant($member)->get(route('account.subscription'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<details class="account-nav-group[^>]*\sopen/', $html);
    }

    public function test_community_page_has_no_group_and_no_premium_tag(): void
    {
        $html = $this->asParticipant($this->member(Plan::Community))->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<details class="account-nav-group', $html);
        $this->assertStringNotContainsString('account-premium-tag" title', $html);
    }
}
