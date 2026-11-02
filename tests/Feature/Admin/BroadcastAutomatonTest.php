<?php

namespace Tests\Feature\Admin;

use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Models\BotUser;
use App\Services\Broadcasts\BroadcastLauncher;
use App\Services\Broadcasts\BroadcastSender;
use App\Support\SchedulerHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Рассылка как «автомат» для общего хостинга: доводит себя до конца без очереди, не теряет сообщения при сбоях Telegram и
 * сети, не расходует получательниц на ошибки, когда Telegram недоступен, и сообщает админу, если не работает cron.
 */
class BroadcastAutomatonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['nutgram.token' => 'TEST-TOKEN']);
    }

    private function sending(int $recipients = 1): Broadcast
    {
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'sending', 'started_at' => now()]);

        foreach (BotUser::factory()->active()->count($recipients)->create() as $user) {
            BroadcastDelivery::create(['broadcast_id' => $broadcast->id, 'bot_user_id' => $user->id, 'telegram_id' => $user->telegram_id, 'locale' => 'ru', 'status' => 'pending']);
        }

        return $broadcast;
    }

    private function pending(Broadcast $broadcast): int
    {
        return $broadcast->deliveries()->where('status', 'pending')->count();
    }

    public function test_the_scheduler_alone_delivers_a_broadcast_without_any_queue_worker(): void
    {
        Queue::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        BotUser::factory()->active()->count(3)->create();
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'draft']);

        app(BroadcastLauncher::class)->start($broadcast);
        // Очередь не работает: задание лежит и не выполняется.
        $this->assertSame('sending', $broadcast->fresh()->status);

        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();

        $this->assertSame('sent', $broadcast->fresh()->status);
        $this->assertSame([3, 3], [$broadcast->fresh()->total, $broadcast->fresh()->sent]);
    }

    public function test_a_scheduled_broadcast_starts_and_finishes_in_one_scheduler_run(): void
    {
        Queue::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        BotUser::factory()->active()->create();
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute()]);

        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();

        $this->assertSame('sent', $broadcast->fresh()->status);
    }

    public function test_a_temporary_failure_is_retried_and_the_message_is_not_lost(): void
    {
        $broadcast = $this->sending();
        Http::fake(['api.telegram.org/*' => Http::sequence()
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502)
            ->push(['ok' => true, 'result' => []])]);

        app(BroadcastSender::class)->run($broadcast->id);

        $delivery = $broadcast->deliveries()->firstOrFail();
        $this->assertSame('sent', $delivery->status);
        $this->assertSame(2, $delivery->attempts);
        $this->assertSame('sent', $broadcast->fresh()->status);
    }

    public function test_a_delivery_that_keeps_failing_gives_up_after_three_attempts(): void
    {
        $broadcast = $this->sending();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502)]);

        app(BroadcastSender::class)->run($broadcast->id);

        $delivery = $broadcast->deliveries()->firstOrFail();
        $this->assertSame('failed', $delivery->status);
        $this->assertSame(BroadcastSender::MAX_ATTEMPTS, $delivery->attempts);
        $this->assertCount(BroadcastSender::MAX_ATTEMPTS, Http::recorded());
    }

    public function test_when_telegram_is_down_the_broadcast_pauses_instead_of_burning_through_the_recipients(): void
    {
        $broadcast = $this->sending(20);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502)]);

        $wait = app(BroadcastSender::class)->run($broadcast->id);

        $this->assertSame(BroadcastSender::BREAKER_PAUSE, $wait);
        $this->assertCount(BroadcastSender::BREAKER_STREAK, Http::recorded(), 'после серии неудач запросы прекращаются');
        $this->assertSame(20, $this->pending($broadcast), 'никто не списан в ошибки: все ещё ждут');
        $this->assertSame(0, $broadcast->deliveries()->where('status', 'failed')->count());
        $this->assertStringContainsString('Bad Gateway', (string) $broadcast->fresh()->last_error);
        $this->assertSame('sending', $broadcast->fresh()->status);

        // Пока пауза, следующий запуск ничего не отправляет.
        $before = count(Http::recorded());
        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();
        $this->assertCount($before, Http::recorded());
    }

    public function test_after_the_pause_the_broadcast_continues_by_itself_and_the_warning_goes_away(): void
    {
        $broadcast = $this->sending(10);
        Http::fake(['api.telegram.org/*' => Http::sequence()
            ->whenEmpty(Http::response(['ok' => true, 'result' => []]))
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502, [])
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502, [])
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502, [])
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502, [])
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502, [])
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502, [])
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502, [])
            ->push(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502, [])]);

        app(BroadcastSender::class)->run($broadcast->id);
        $this->assertNotNull($broadcast->fresh()->last_error);

        // Telegram снова отвечает, пауза прошла.
        Cache::forget(BroadcastSender::pauseKey($broadcast->id));
        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();

        $fresh = $broadcast->fresh();
        $this->assertSame('sent', $fresh->status);
        $this->assertNull($fresh->last_error);
        $this->assertSame(10, $fresh->sent, 'все получили сообщение, потерянных нет');
    }

    public function test_a_rejected_bot_token_pauses_the_broadcast_with_a_clear_reason(): void
    {
        $broadcast = $this->sending(5);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 401, 'description' => 'Unauthorized'], 401)]);

        $wait = app(BroadcastSender::class)->run($broadcast->id);

        $this->assertSame(BroadcastSender::FATAL_PAUSE, $wait);
        $this->assertCount(1, Http::recorded(), 'одного отказа достаточно, чтобы не бить по Telegram впустую');
        $this->assertSame(5, $this->pending($broadcast));
        $this->assertStringContainsString('Unauthorized', (string) $broadcast->fresh()->last_error);
    }

    public function test_one_broken_broadcast_does_not_stop_the_others(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $first = $this->sending();
        $second = $this->sending();
        Cache::put(BroadcastSender::pauseKey($first->id), time() + 600, 700);

        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();

        $this->assertSame('sending', $first->fresh()->status, 'эта на паузе');
        $this->assertSame('sent', $second->fresh()->status, 'соседняя ушла');
    }

    public function test_the_admin_is_warned_when_cron_is_not_running(): void
    {
        Cache::forget(SchedulerHealth::KEY);
        $this->actingAsAdmin()->get(route('admin.broadcasts.index'))->assertOk()->assertSee('Планировщик (cron) на хостинге не запускается');

        SchedulerHealth::beat();
        $this->actingAsAdmin()->get(route('admin.broadcasts.index'))->assertOk()->assertDontSee('Планировщик (cron) на хостинге не запускается');

        // Пульс старше пяти минут: cron встал.
        Cache::put(SchedulerHealth::KEY, now()->subMinutes(6)->getTimestamp(), 3600);
        $this->actingAsAdmin()->get(route('admin.broadcasts.index'))->assertOk()->assertSee('Планировщик (cron) на хостинге не запускается');
    }

    public function test_the_scheduler_leaves_a_heartbeat_every_minute(): void
    {
        Cache::forget(SchedulerHealth::KEY);

        $this->artisan('schedule:run')->assertSuccessful();

        $this->assertFalse(SchedulerHealth::isStale());
    }

    public function test_the_broadcast_page_explains_a_pause(): void
    {
        SchedulerHealth::beat();
        $broadcast = $this->sending(3);
        $broadcast->forceFill(['last_error' => 'Telegram не отвечает или отклоняет сообщения (Bad Gateway). Рассылка на паузе и продолжится сама.'])->save();
        Cache::put(BroadcastSender::pauseKey($broadcast->id), time() + 240, 300);

        $this->actingAsAdmin()->get(route('admin.broadcasts.show', $broadcast))->assertOk()
            ->assertSee('Рассылка на паузе')
            ->assertSee('Bad Gateway')
            ->assertSee('Продолжится сама примерно через 4 мин.');
    }

    public function test_the_scheduled_tasks_cannot_stay_locked_for_a_day(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());

        foreach (['payments:reconcile', 'subscriptions:notify', 'broadcasts:dispatch-due'] as $command) {
            $event = $events->first(fn ($event) => str_contains((string) $event->command, $command));
            $this->assertNotNull($event, $command);
            $this->assertTrue($event->withoutOverlapping);
            $this->assertLessThan(1440, $event->expiresAt, $command.': блокировка сама снимается, если процесс убили');
        }
    }
}
