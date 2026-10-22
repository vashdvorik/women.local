<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Services\Broadcasts\DeliveryOutcome;
use App\Services\Broadcasts\TelegramBroadcaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Рассылка сообщения участницам из админки. Получательницы фиксируются один раз (по строке на каждую), дальше задание
 * идёт по тем, кому ещё не отправлено: упало и запущено снова — повторно никому не пишет. Тело задания ограничено по
 * времени и при нехватке времени ставит себя в очередь заново, поэтому длинная рассылка не упирается в таймаут.
 * Telegram разрешает около 30 сообщений в секунду; здесь — около 25.
 */
class SendBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Сколько секунд задание работает, прежде чем передать остаток следующему запуску. */
    private const BUDGET_SECONDS = 200;

    /** Пауза между сообщениями, микросекунды (около 25 в секунду). */
    private const PAUSE = 40_000;

    public int $tries = 1;
    public int $timeout = 280;

    public function __construct(private readonly int $broadcastId) {}

    public function handle(TelegramBroadcaster $telegram): void
    {
        $broadcast = Broadcast::find($this->broadcastId);

        if ($broadcast === null || $broadcast->status !== Broadcast::STATUS_SENDING) {
            return;
        }

        $this->snapshotRecipients($broadcast);

        $deadline = microtime(true) + self::BUDGET_SECONDS;

        while (true) {
            $broadcast->refresh();

            // Отменили, пока шла отправка: оставшимся не пишем.
            if ($broadcast->status !== Broadcast::STATUS_SENDING) {
                return;
            }

            $batch = $broadcast->deliveries()->where('status', BroadcastDelivery::PENDING)->orderBy('id')->limit(20)->get();

            if ($batch->isEmpty()) {
                $this->finish($broadcast);

                return;
            }

            foreach ($batch as $delivery) {
                if (microtime(true) > $deadline) {
                    $this->recount($broadcast);
                    self::dispatch($broadcast->id);

                    return;
                }

                $outcome = $telegram->send($broadcast, $delivery->telegram_id, $delivery->locale);

                if ($outcome->status === DeliveryOutcome::RETRY) {
                    // Telegram просит подождать: ждём (не дольше 30 с) и берём эту же получательницу снова.
                    sleep(min($outcome->retryAfter, 30));

                    continue 2;
                }

                $delivery->update([
                    'status' => match ($outcome->status) {
                        DeliveryOutcome::SENT => BroadcastDelivery::SENT,
                        DeliveryOutcome::BLOCKED => BroadcastDelivery::BLOCKED,
                        default => BroadcastDelivery::FAILED,
                    },
                    'error' => $outcome->error,
                    'sent_at' => $outcome->isSent() ? now() : null,
                ]);

                usleep(self::PAUSE);
            }

            $this->recount($broadcast);
        }
    }

    /** Получательницы на момент начала отправки; повторный запуск задания ничего не добавляет. */
    private function snapshotRecipients(Broadcast $broadcast): void
    {
        if ($broadcast->deliveries()->exists()) {
            return;
        }

        $broadcast->recipients()->select(['bot_users.id', 'bot_users.telegram_id', 'bot_users.locale'])->orderBy('bot_users.id')
            ->chunkById(500, function ($users) use ($broadcast) {
                BroadcastDelivery::insertOrIgnore($users->map(fn ($user) => [
                    'broadcast_id' => $broadcast->id,
                    'bot_user_id' => $user->id,
                    'telegram_id' => $user->telegram_id,
                    'locale' => $user->messageLocale(),
                    'status' => BroadcastDelivery::PENDING,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all());
            }, 'bot_users.id', 'id');

        $this->recount($broadcast);
    }

    private function finish(Broadcast $broadcast): void
    {
        $this->recount($broadcast);
        $broadcast->forceFill(['status' => Broadcast::STATUS_SENT, 'finished_at' => now()])->save();
    }

    /** Счётчики рассылки из строк отправок (источник правды — они). */
    private function recount(Broadcast $broadcast): void
    {
        $counts = $broadcast->deliveries()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $broadcast->forceFill([
            'total' => (int) $counts->sum(),
            'sent' => (int) ($counts[BroadcastDelivery::SENT] ?? 0),
            'failed' => (int) ($counts[BroadcastDelivery::FAILED] ?? 0),
            'blocked' => (int) ($counts[BroadcastDelivery::BLOCKED] ?? 0),
        ])->save();
    }
}
