<?php

namespace Tests\Feature\Admin;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Broadcast;
use App\Models\BroadcastClick;
use App\Models\BroadcastDelivery;
use App\Models\ManagerMessage;
use App\Models\Subscription;
use App\Services\ActivityReport;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Страница «Статистика»: вкладки «Сообщество», «Активность» (заходы по месяцам, инструменты, ушедшие участницы) и «Рассылки»
 * (доставлено, блок, клики), а также карточка активности в профиле участницы.
 */
class StatisticsActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('ru');
    }

    private function member(Plan $plan = Plan::Active): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT);

        return $user->fresh();
    }

    private function visit(BotUser $user, string $key, string $day, int $hits = 1): void
    {
        DB::table('cabinet_activity')->insert(['bot_user_id' => $user->id, 'day' => $day, 'key' => $key, 'hits' => $hits, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_the_page_has_three_tabs_and_community_is_the_default(): void
    {
        $this->actingAsAdmin()->get(route('admin.statistics.index'))->assertOk()
            ->assertSee(route('admin.statistics.index', ['tab' => 'activity']), false)
            ->assertSee(route('admin.statistics.index', ['tab' => 'broadcasts']), false)
            ->assertSee('Индекс готовности сообщества')
            ->assertSee('Скачать PDF')
            ->assertDontSee('Посещения по месяцам');

        $this->actingAsAdmin()->get(route('admin.statistics.index', ['tab' => 'broken']))->assertOk()->assertSee('Индекс готовности сообщества');
    }

    public function test_the_activity_tab_explains_when_nothing_has_been_recorded_yet(): void
    {
        $this->actingAsAdmin()->get(route('admin.statistics.index', ['tab' => 'activity']))->assertOk()
            ->assertSee('Данных об активности пока нет')
            ->assertSee('Посещения по месяцам')
            ->assertDontSee('Скачать PDF')
            ->assertSee('Учёт идёт меньше');
    }

    public function test_visits_are_counted_by_month_in_unique_participants_and_days(): void
    {
        $a = $this->member();
        $b = $this->member();
        $thisMonth = now()->startOfMonth();

        $this->visit($a, 'home', $thisMonth->copy()->addDay()->toDateString());
        $this->visit($a, 'matches', $thisMonth->copy()->addDay()->toDateString());   // тот же день: один «день с заходом»
        $this->visit($a, 'home', $thisMonth->copy()->addDays(2)->toDateString());
        $this->visit($b, 'home', $thisMonth->copy()->addDays(2)->toDateString());
        $this->visit($a, 'home', $thisMonth->copy()->subMonth()->addDay()->toDateString());

        $report = app(ActivityReport::class)->activity('30');
        $months = collect($report['months'])->keyBy('key');

        $this->assertSame(2, $months[$thisMonth->format('Y-m')]['users']);
        $this->assertSame(3, $months[$thisMonth->format('Y-m')]['visits'], 'a: два дня, b: один день');
        $this->assertSame(1, $months[$thisMonth->copy()->subMonth()->format('Y-m')]['users']);
        $this->assertCount(12, $report['months']);
    }

    public function test_key_numbers_and_the_share_of_paid_members_who_visited(): void
    {
        $a = $this->member();
        $b = $this->member(Plan::Community);
        $this->member();    // платная, но не заходила

        $this->visit($a, 'home', now()->toDateString());
        $this->visit($b, 'home', now()->subDays(20)->toDateString());

        $report = app(ActivityReport::class)->activity('30');

        $this->assertSame(1, $report['active7']);
        $this->assertSame(2, $report['active30']);
        $this->assertSame(2, $report['visits30']);
        $this->assertSame(3, $report['paidMembers']);
        $this->assertSame(2, $report['paidActive30']);
        $this->assertSame(67, $report['activeShare']);
        $this->assertEquals(['active' => 1, 'community' => 1, 'private' => 0], collect($report['plans'])->pluck('users', 'plan')->all());
    }

    public function test_tools_are_ranked_with_people_hits_and_share_for_the_chosen_period(): void
    {
        $a = $this->member();
        $b = $this->member();

        $this->visit($a, 'matches', now()->toDateString(), 5);
        $this->visit($b, 'matches', now()->toDateString(), 2);
        $this->visit($a, 'search', now()->toDateString(), 1);
        $this->visit($a, 'manager_question', now()->toDateString(), 3);
        $this->visit($a, 'home', now()->subDays(100)->toDateString(), 9);   // вне 30 дней

        $month = app(ActivityReport::class)->activity('30');
        $matches = collect($month['sections'])->firstWhere('key', 'matches');

        $this->assertSame('Рекомендации', $matches['label']);
        $this->assertSame([2, 7, 100], [$matches['users'], $matches['hits'], $matches['share']]);
        $this->assertSame('matches', $month['sections'][0]['key'], 'сверху самый используемый');
        $this->assertSame(0, collect($month['sections'])->firstWhere('key', 'home')['hits'], 'старое не входит в 30 дней');
        $this->assertSame(3, collect($month['actions'])->firstWhere('key', 'manager_question')['hits']);

        $all = app(ActivityReport::class)->activity('all');
        $this->assertSame(9, collect($all['sections'])->firstWhere('key', 'home')['hits']);

        $this->assertSame('30', app(ActivityReport::class)->activity('hacker')['period'], 'неизвестный период заменяется на 30 дней');
    }

    public function test_members_who_stopped_visiting_are_listed_only_when_tracking_is_old_enough(): void
    {
        $gone = $this->member();
        $here = $this->member();

        // учёт идёт 3 дня: список был бы неточным
        $this->visit($here, 'home', now()->subDays(2)->toDateString());
        $this->assertNull(app(ActivityReport::class)->activity('30')['inactive']);

        // учёт идёт 45 дней, «here» заходила вчера, «gone» не заходила никогда
        $this->visit($here, 'home', now()->subDays(45)->toDateString());
        $this->visit($here, 'home', now()->subDay()->toDateString());

        $inactive = app(ActivityReport::class)->activity('30')['inactive'];

        $this->assertSame(1, $inactive['total']);
        $this->assertSame($gone->id, $inactive['rows'][0]['id']);
        $this->assertSame('Active', $inactive['rows'][0]['plan'], 'пакет показан названием, а не «None»');
    }

    public function test_the_history_comes_from_existing_tables(): void
    {
        $user = $this->member();
        ManagerMessage::create(['bot_user_id' => $user->id, 'role' => 'user', 'content' => 'Как оплатить?', 'question' => 'Как оплатить?', 'plan' => 'active']);
        ManagerMessage::create(['bot_user_id' => $user->id, 'role' => 'assistant', 'content' => 'Ответ']);

        $current = collect(app(ActivityReport::class)->activity('30')['history']['rows'])->last();

        $this->assertSame(1, $current['questions'], 'считаются вопросы участницы, а не ответы бота');
        $this->assertGreaterThanOrEqual(1, $current['registrations']);
    }

    public function test_the_activity_tab_renders_with_data(): void
    {
        $a = $this->member();
        $this->visit($a, 'matches', now()->toDateString(), 4);
        $this->visit($a, 'matches', now()->subDays(40)->toDateString(), 1);

        $this->actingAsAdmin()->get(route('admin.statistics.index', ['tab' => 'activity']))->assertOk()
            ->assertSee('Заходили за 7 дней')
            ->assertSee('Какими инструментами пользуются')
            ->assertSee('Рекомендации')
            ->assertSee('Вопросы AI-менеджеру')
            ->assertSee('История по месяцам')
            ->assertSee(e(route('admin.statistics.index', ['tab' => 'activity', 'period' => 'all'])), false);
    }

    public function test_the_broadcasts_tab_shows_delivery_and_clicks_per_button(): void
    {
        $user = BotUser::factory()->active()->create();
        $other = BotUser::factory()->active()->create();
        $broadcast = Broadcast::create([
            'texts' => ['ru' => "Приходите на форум\nВторая строка"],
            'buttons' => [
                ['url' => 'https://example.test/a', 'label' => ['ru' => 'Регистрация', 'en' => '', 'ro' => '']],
                ['url' => 'https://example.test/b', 'label' => ['ru' => 'Программа', 'en' => '', 'ro' => '']],
            ],
            'audience' => 'members', 'status' => 'sent', 'total' => 2, 'sent' => 2, 'blocked' => 0, 'failed' => 0, 'started_at' => now(),
        ]);
        $d1 = BroadcastDelivery::create(['broadcast_id' => $broadcast->id, 'bot_user_id' => $user->id, 'telegram_id' => 1, 'token' => 'tokenaaaaaaaaaaaa1', 'locale' => 'ru', 'status' => 'sent', 'clicks' => 3]);
        BroadcastDelivery::create(['broadcast_id' => $broadcast->id, 'bot_user_id' => $other->id, 'telegram_id' => 2, 'token' => 'tokenbbbbbbbbbbbb2', 'locale' => 'ru', 'status' => 'sent']);
        foreach ([0, 0, 1] as $button) {
            BroadcastClick::create(['broadcast_id' => $broadcast->id, 'broadcast_delivery_id' => $d1->id, 'bot_user_id' => $user->id, 'button' => $button, 'clicked_at' => now()]);
        }

        $report = app(ActivityReport::class)->broadcasts();
        $row = $report['rows'][0];

        $this->assertSame('Приходите на форум', $row['title']);
        $this->assertSame([2, 2, 1, 50.0], [$row['total'], $row['delivered'], $row['clickers'], (float) $row['ctr']]);
        $this->assertSame(['Программа', 'Регистрация'], collect($row['buttons'])->pluck('label')->sort()->values()->all());
        $this->assertSame(3, $report['clicks30']);

        $this->actingAsAdmin()->get(route('admin.statistics.index', ['tab' => 'broadcasts']))->assertOk()
            ->assertSee('Приходите на форум')
            ->assertSee('Регистрация: 2 нажатий, 1 чел.')
            ->assertSee('Прочитано» Telegram боту не сообщает');
    }

    public function test_the_broadcasts_tab_is_empty_without_sent_broadcasts(): void
    {
        $this->actingAsAdmin()->get(route('admin.statistics.index', ['tab' => 'broadcasts']))->assertOk()->assertSee('Отправленных рассылок пока нет.');
    }

    public function test_the_profile_page_shows_the_members_activity(): void
    {
        $user = $this->member();

        $this->actingAsAdmin()->get(route('admin.profiles.show', $user))->assertOk()
            ->assertSee('Активность в кабинете')
            ->assertSee('Заходов в кабинет после включения учёта пока нет.');

        $this->visit($user, 'matches', now()->toDateString(), 3);
        $this->visit($user, 'manager_question', now()->subDay()->toDateString(), 2);
        $user->forceFill(['last_seen_at' => now()])->save();

        $this->actingAsAdmin()->get(route('admin.profiles.show', $user))->assertOk()
            ->assertSee('Последний вход')
            ->assertSee('Рекомендации')
            ->assertSee('3 раз, 1 дн.')
            ->assertSee('Вопросы AI-менеджеру');
    }

    public function test_the_statistics_pages_are_closed_to_non_admins(): void
    {
        config(['admin.email' => 'someone-else@example.test']);
        $intruder = \App\Models\User::factory()->create(['email' => 'intruder@example.test']);

        foreach (['activity', 'broadcasts', 'community'] as $tab) {
            $this->actingAs($intruder)->get(route('admin.statistics.index', ['tab' => $tab]))->assertForbidden();
        }
    }
}
