<?php

namespace Tests\Feature\Subscription;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Services\Subscriptions\SubscriptionService;
use App\Support\BotMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ChatType;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\User\User;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Подписка в Telegram-боте: закрытый поиск для Open, сообщения о тарифе, напоминания об окончании.
 */
class BotAndRemindersTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    private const ID = 7001;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Queue::fake();
    }

    private function bot(string $lang = 'ru', int $id = self::ID): Nutgram
    {
        return app(Nutgram::class)
            ->setCommonUser(User::make(id: $id, is_bot: false, first_name: 'Ann', language_code: $lang))
            ->setCommonChat(Chat::make(id: $id, type: ChatType::PRIVATE));
    }

    /** @return list<string> тексты сообщений бота за последний шаг диалога */
    private function texts(Nutgram $bot): array
    {
        return collect($bot->getRequestHistory())
            ->map(fn ($item) => json_decode((string) ($item['request'] ?? array_values($item)[0])->getBody(), true))
            ->filter(fn ($body) => isset($body['text']))
            ->pluck('text')
            ->all();
    }

    // ---------------------------------------------------------------- бот

    public function test_contact_search_in_the_bot_is_closed_for_open_and_points_to_the_subscription(): void
    {
        BotUser::factory()->approved()->create(['telegram_id' => self::ID]);

        $bot = $this->bot('en');
        $bot->hearText(BotMessages::default('menu_matches_button', 'en'))->reply();

        [$text] = $this->texts($bot);
        $this->assertSame(BotMessages::text('plan_required', 'en', ['url' => route('account.subscription')]), $text);
        $this->assertStringContainsString(route('account.subscription'), $text);
        $this->assertStringNotContainsString(BotMessages::default('search_intro', 'en'), $text);
    }

    public function test_contact_search_in_the_bot_works_for_community_and_private(): void
    {
        foreach ([self::ID => BotUser::factory()->community(), self::ID + 1 => BotUser::factory()->private()] as $id => $factory) {
            $factory->create(['telegram_id' => $id]);

            $bot = $this->bot('ru', $id);
            $bot->hearText(BotMessages::default('menu_matches_button', 'ru'))->reply();

            $this->assertSame(BotMessages::default('search_intro', 'ru'), $this->texts($bot)[0], "telegram {$id}");
        }
    }

    public function test_a_search_dialog_that_outlives_the_subscription_stops_politely(): void
    {
        $user = BotUser::factory()->community()->create(['telegram_id' => self::ID]);

        $bot = $this->bot();
        $bot->hearText(BotMessages::default('menu_matches_button', 'ru'))->reply();

        // Пока участница думала над запросом, подписка закончилась.
        app(SubscriptionService::class)->revoke($user);

        $bot->hearText('ищу партнёра для экспорта')->reply();

        $this->assertSame(BotMessages::text('plan_required', 'ru', ['url' => route('account.subscription')]), $this->texts($bot)[0]);
    }

    // ---------------------------------------------------------------- тексты о подписке

    public function test_the_subscription_messages_are_in_the_single_messages_file_in_three_languages(): void
    {
        foreach (['plan_required', 'subscription_activated', 'subscription_reminder', 'subscription_expired'] as $key) {
            $definition = BotMessages::definition($key);
            $this->assertSame('subscription', $definition['group']);

            foreach (BotMessages::LOCALES as $locale) {
                [$text, $errors] = BotMessages::check($key, BotMessages::default($key, $locale));
                $this->assertSame([], $errors, "{$key}/{$locale}");
                $this->assertStringContainsString('{url}', $text, "{$key}/{$locale}: ссылка на оплату");
            }
        }
    }

    public function test_granting_a_plan_tells_the_participant_in_her_language(): void
    {
        Telegram::fake();
        $user = BotUser::factory()->approved()->create(['telegram_id' => self::ID, 'locale' => 'ro']);

        $subscription = app(SubscriptionService::class)->grant($user, Plan::Community, 12);
        app(\App\Services\Subscriptions\SubscriptionNotifier::class)->activated($subscription);

        $texts = collect(Telegram::getRequestHistory())
            ->map(fn ($item) => json_decode((string) ($item['request'] ?? array_values($item)[0])->getBody(), true)['text'] ?? '')
            ->implode("\n");

        $this->assertStringContainsString('Abonamentul <b>WOMEN’S HUB COMMUNITY</b> este activ până la '.$subscription->ends_at->format('d.m.Y'), $texts);
    }

    // ---------------------------------------------------------------- напоминания

    private function remindersSent(): array
    {
        return collect(Telegram::getRequestHistory())
            ->map(fn ($item) => json_decode((string) ($item['request'] ?? array_values($item)[0])->getBody(), true))
            ->filter(fn ($body) => isset($body['text']))
            ->values()
            ->all();
    }

    public function test_reminders_come_14_and_3_days_before_the_end_once_each(): void
    {
        $user = BotUser::factory()->approved()->create(['telegram_id' => self::ID, 'locale' => 'ru']);
        app(SubscriptionService::class)->grant($user, Plan::Community, 12);
        $end = $user->fresh()->plan_ends_at->copy();

        Telegram::fake();

        // Далеко до конца: тишина.
        $this->travelTo($end->copy()->subDays(30));
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->assertCount(0, $this->remindersSent());

        // За 14 дней: первое напоминание, повторный запуск в тот же день ничего не добавляет.
        $this->travelTo($end->copy()->subDays(14)->addHour());
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->assertCount(1, $this->remindersSent());
        $this->assertStringContainsString('осталось дней: 14', $this->remindersSent()[0]['text']);
        $this->assertStringContainsString($end->format('d.m.Y'), $this->remindersSent()[0]['text']);
        $this->assertStringContainsString(route('account.subscription'), $this->remindersSent()[0]['text']);

        // Между напоминаниями — тихо.
        $this->travelTo($end->copy()->subDays(8));
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->assertCount(1, $this->remindersSent());

        // За 3 дня: второе.
        $this->travelTo($end->copy()->subDays(3)->addHour());
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->assertCount(2, $this->remindersSent());
        $this->assertStringContainsString('осталось дней: 3', $this->remindersSent()[1]['text']);
    }

    public function test_a_late_first_run_sends_one_urgent_reminder_not_two(): void
    {
        $user = BotUser::factory()->approved()->create(['telegram_id' => self::ID]);
        app(SubscriptionService::class)->grant($user, Plan::Community, 12);
        Telegram::fake();

        // Команду не запускали, и она впервые сработала за 2 дня до конца.
        $this->travelTo($user->fresh()->plan_ends_at->copy()->subDays(2));
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->artisan('subscriptions:notify')->assertSuccessful();

        $this->assertCount(1, $this->remindersSent());
    }

    public function test_when_the_plan_ends_the_participant_goes_to_open_and_is_told_once(): void
    {
        $user = BotUser::factory()->approved()->create(['telegram_id' => self::ID]);
        app(SubscriptionService::class)->grant($user, Plan::Private, 12);
        Telegram::fake();

        $this->travelTo($user->fresh()->plan_ends_at->copy()->addHour());
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->artisan('subscriptions:notify')->assertSuccessful();

        $user->refresh();
        $this->assertSame('open', $user->plan, 'в базе тоже Open, а не только по дате');
        $this->assertNull($user->plan_ends_at);
        $this->assertSame(Plan::Open, $user->currentPlan());

        $sent = $this->remindersSent();
        $this->assertCount(1, $sent);
        $this->assertStringContainsString('закончилась', $sent[0]['text']);
        $this->assertStringContainsString('WOMEN’S HUB PRIVATE', $sent[0]['text']);
    }

    public function test_when_a_higher_plan_ends_the_remaining_lower_plan_takes_over_without_a_goodbye(): void
    {
        $user = BotUser::factory()->approved()->create(['telegram_id' => self::ID]);
        $service = app(SubscriptionService::class);
        $service->grant($user, Plan::Community, 12);
        $service->grant($user, Plan::Private, 1);
        Telegram::fake();

        $this->travelTo(now()->addMonth()->addDay());
        $this->artisan('subscriptions:notify')->assertSuccessful();

        $user->refresh();
        $this->assertSame('community', $user->plan);
        $this->assertCount(0, $this->remindersSent(), 'подписка не прервалась, прощаться не с чем');
    }

    public function test_renewing_starts_a_new_series_of_reminders(): void
    {
        $user = BotUser::factory()->approved()->create(['telegram_id' => self::ID]);
        $service = app(SubscriptionService::class);
        $service->grant($user, Plan::Community, 1);
        Telegram::fake();

        $this->travelTo(now()->addDays(20));
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->assertCount(1, $this->remindersSent());
        $this->assertSame(1, $user->fresh()->plan_notified_stage);

        $service->grant($user->fresh(), Plan::Community, 1);
        $this->assertSame(0, $user->fresh()->plan_notified_stage, 'оплата сбросила счётчик напоминаний');
    }

    public function test_open_participants_and_unapproved_ones_get_no_reminders(): void
    {
        BotUser::factory()->approved()->create();
        $pending = BotUser::factory()->pending()->create();
        app(SubscriptionService::class)->grant($pending, Plan::Community, 1);
        Telegram::fake();

        $this->travel(40)->days();
        $this->artisan('subscriptions:notify')->assertSuccessful();

        $this->assertCount(0, $this->remindersSent());
    }

    public function test_a_telegram_failure_does_not_break_the_run_and_the_reminder_is_retried_next_day(): void
    {
        $user = BotUser::factory()->approved()->create(['telegram_id' => self::ID]);
        app(SubscriptionService::class)->grant($user, Plan::Community, 1);
        $this->travel(20)->days();

        // Telegram не отвечает: команда не падает, напоминание не считается отправленным.
        Telegram::shouldReceive('sendMessage')->andThrow(new \RuntimeException('Telegram is down'));
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->assertSame(0, $user->fresh()->plan_notified_stage);

        // На следующий день Telegram снова работает: напоминание доходит.
        Telegram::swap(app(Nutgram::class)::fake());
        $this->travel(1)->day();
        $this->artisan('subscriptions:notify')->assertSuccessful();
        $this->assertSame(1, $user->fresh()->plan_notified_stage);
        $this->assertCount(1, $this->remindersSent());
    }

    public function test_the_schedule_runs_the_reconciler_and_the_reminders(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->map(fn ($e) => $e->command)->implode("\n");

        $this->assertStringContainsString('payments:reconcile', $events);
        $this->assertStringContainsString('subscriptions:notify', $events);
    }
}
