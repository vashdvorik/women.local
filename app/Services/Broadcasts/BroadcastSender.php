<?php

declare(strict_types=1);

namespace App\Services\Broadcasts;

use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Автомат отправки рассылки в Telegram, рассчитанный на общий хостинг, где нет постоянных процессов, а любой запуск
 * ограничен по времени и может оборваться.
 *
 * Как он устроен:
 *  - Отправка идёт короткими запусками (срез ~50 с): после среза рассылка просто продолжается со следующего запуска.
 *    Запускать срез могут и очередь (SendBroadcast, сразу после нажатия «Отправить»), и планировщик (раз в минуту,
 *    artisan broadcasts:dispatch-due). Достаточно любого из них: если очередь на хостинге не настроена, рассылку
 *    всё равно доведёт планировщик.
 *  - По одной рассылке работает один запуск (замок в кеше): два воркера не напишут одной участнице дважды. Замок сам
 *    истекает, если запуск оборвали.
 *  - Участница, которой уже отправлено, повторно не получает: итог каждой отправки пишется в строку получательницы.
 *  - Скорость около 20 сообщений в секунду при пределе Telegram в 30. Если Telegram просит подождать (429), рассылка
 *    встаёт на указанную им паузу и продолжается сама.
 *  - Временный сбой (нет ответа, таймаут, 5xx) не теряет сообщение: до трёх попыток, неудачные идут в конец очереди.
 *  - Автомат безопасности: подряд несколько неудач (Telegram недоступен, токен не принят) ставят рассылку на паузу с
 *    пометкой причины на её странице. Пока пауза, нетронутым получательницам ничего не уходит и «ошибок» не копится;
 *    когда причина исчезла, рассылка продолжается сама.
 */
class BroadcastSender
{
    /** Сколько секунд работает один запуск до передачи остатка следующему (меньше retry_after очереди, 90 с). */
    public const SLICE_SECONDS = 50;

    /** Пауза между сообщениями, микросекунды (около 20 в секунду при пределе Telegram 30). */
    private const PAUSE = 50_000;

    private const BATCH = 20;

    /** Сколько раз пробуем отправить при временном сбое. */
    public const MAX_ATTEMPTS = 3;

    /** Сколько неудач подряд считается «Telegram не работает» и ставит рассылку на паузу. */
    public const BREAKER_STREAK = 8;

    public const BREAKER_PAUSE = 300;

    /** Пауза, когда Telegram не принял самого бота (токен): даём время исправить настройку. */
    public const FATAL_PAUSE = 900;

    public function __construct(private readonly TelegramBroadcaster $telegram) {}

    public static function pauseKey(int $broadcastId): string
    {
        return 'broadcast-pause-'.$broadcastId;
    }

    /** Сколько секунд рассылка ещё на паузе (0 — идёт). */
    public function pausedFor(int $broadcastId): int
    {
        $until = (int) Cache::get(self::pauseKey($broadcastId), 0);

        return max(0, $until - time());
    }

    private function pause(int $broadcastId, int $seconds): void
    {
        Cache::put(self::pauseKey($broadcastId), time() + $seconds, $seconds + 60);
    }

    /**
     * Один срез отправки по рассылке.
     *
     * @return int|null через сколько секунд продолжить (0 — сразу, срез закончился по времени); null — рассылка закончена,
     *                  остановлена или её сейчас ведёт другой запуск
     */
    public function run(int $broadcastId, float $seconds = self::SLICE_SECONDS): ?int
    {
        $lock = Cache::lock('broadcast-send-'.$broadcastId, (int) ceil($seconds) + 70);

        if (! $lock->get()) {
            return null;
        }

        try {
            return $this->slice($broadcastId, $seconds);
        } finally {
            $lock->release();
        }
    }

    /**
     * Планировщик, раз в минуту: ведёт все идущие рассылки по очереди в пределах $seconds.
     *
     * @return int сколько рассылок обработано
     */
    public function pump(float $seconds): int
    {
        $deadline = microtime(true) + $seconds;
        $count = 0;

        $ids = Broadcast::query()->where('status', Broadcast::STATUS_SENDING)->orderBy('started_at')->orderBy('id')->pluck('id');

        foreach ($ids as $id) {
            $left = $deadline - microtime(true);

            if ($left < 2) {
                break;
            }

            try {
                $this->run((int) $id, $left);
                $count++;
            } catch (Throwable $e) {
                // Одна сломанная рассылка не должна останавливать остальные.
                report($e);
            }
        }

        return $count;
    }

    private function slice(int $broadcastId, float $seconds): ?int
    {
        $broadcast = Broadcast::find($broadcastId);

        if ($broadcast === null || $broadcast->status !== Broadcast::STATUS_SENDING) {
            return null;
        }

        $wait = $this->pausedFor($broadcastId);

        if ($wait > 0) {
            return $wait;
        }

        $this->snapshotRecipients($broadcast);

        $deadline = microtime(true) + $seconds;
        $streak = 0;
        $lastError = '';

        while (true) {
            $broadcast->refresh();

            // Отменили, пока шла отправка: оставшимся не пишем.
            if ($broadcast->status !== Broadcast::STATUS_SENDING) {
                return null;
            }

            // Сначала те, кого ещё не пробовали: неудачные попытки уходят в конец очереди.
            $batch = $broadcast->deliveries()->where('status', BroadcastDelivery::PENDING)->orderBy('attempts')->orderBy('id')->limit(self::BATCH)->get();

            if ($batch->isEmpty()) {
                $this->finish($broadcast);

                return null;
            }

            foreach ($batch as $delivery) {
                if (microtime(true) > $deadline) {
                    $this->recount($broadcast);

                    return 0;
                }

                $outcome = $this->telegram->send($broadcast, $delivery->telegram_id, $delivery->locale);

                if ($outcome->status === DeliveryOutcome::RETRY) {
                    // Telegram просит подождать: эта участница остаётся «ждёт», рассылка встаёт на указанную им паузу.
                    $wait = $outcome->retryAfter + 1;
                    $this->pause($broadcastId, $wait);
                    $this->recount($broadcast);
                    $broadcast->touch();

                    return $wait;
                }

                if ($outcome->status === DeliveryOutcome::FATAL) {
                    $this->stop($broadcast, self::FATAL_PAUSE, __('Telegram не принял бота (:error). Проверьте токен бота в настройках сервера: рассылка продолжится сама, когда он заработает.', ['error' => (string) $outcome->error]));

                    return self::FATAL_PAUSE;
                }

                if ($outcome->status === DeliveryOutcome::TRANSIENT) {
                    $attempts = $delivery->attempts + 1;
                    $delivery->update($attempts >= self::MAX_ATTEMPTS
                        ? ['status' => BroadcastDelivery::FAILED, 'attempts' => $attempts, 'error' => $outcome->error]
                        : ['attempts' => $attempts, 'error' => $outcome->error]);
                    $streak++;
                    $lastError = (string) $outcome->error;
                } elseif ($outcome->status === DeliveryOutcome::FAILED) {
                    $delivery->update(['status' => BroadcastDelivery::FAILED, 'attempts' => $delivery->attempts + 1, 'error' => $outcome->error]);
                    $streak++;
                    $lastError = (string) $outcome->error;
                } else {
                    $delivery->update([
                        'status' => $outcome->status === DeliveryOutcome::SENT ? BroadcastDelivery::SENT : BroadcastDelivery::BLOCKED,
                        'attempts' => $delivery->attempts + 1,
                        'error' => $outcome->error,
                        'sent_at' => $outcome->isSent() ? now() : null,
                    ]);
                    $streak = 0;

                    if ($broadcast->last_error !== null) {
                        $broadcast->forceFill(['last_error' => null])->save();
                    }
                }

                // Несколько неудач подряд: это не «плохие получательницы», а Telegram или сеть. Пауза, а не расход очереди на ошибки.
                if ($streak >= self::BREAKER_STREAK) {
                    $this->recount($broadcast);
                    $this->stop($broadcast, self::BREAKER_PAUSE, __('Telegram не отвечает или отклоняет сообщения (:error). Рассылка на паузе и продолжится сама.', ['error' => $lastError]));

                    return self::BREAKER_PAUSE;
                }

                usleep(self::PAUSE);
            }

            $this->recount($broadcast);
        }
    }

    /** Ставит рассылку на паузу и пишет причину на её страницу. */
    private function stop(Broadcast $broadcast, int $seconds, string $reason): void
    {
        $this->pause($broadcast->id, $seconds);
        $broadcast->forceFill(['last_error' => mb_substr($reason, 0, 500)])->save();
        $broadcast->touch();
    }

    /** Получательницы на момент начала отправки; повторный запуск ничего не добавляет. */
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
        $broadcast->forceFill(['status' => Broadcast::STATUS_SENT, 'finished_at' => now(), 'last_error' => null])->save();
        Cache::forget(self::pauseKey($broadcast->id));
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
