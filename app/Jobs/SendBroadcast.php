<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Broadcasts\BroadcastSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Запуск отправки рассылки сразу после нажатия «Отправить» (очередь). Вся логика — в App\Services\Broadcasts\BroadcastSender:
 * один срез отправки до ~50 секунд (короче retry_after очереди, 90 с, поэтому другой воркер срез не перехватит), под замком.
 * Остаток передаётся следующему срезу этим же заданием. Если очередь на хостинге не работает, рассылку ведёт планировщик
 * (artisan broadcasts:dispatch-due, раз в минуту), так что это задание только ускоряет начало.
 */
class SendBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 80;

    public function __construct(private readonly int $broadcastId) {}

    public function handle(BroadcastSender $sender): void
    {
        $wait = $sender->run($this->broadcastId, BroadcastSender::SLICE_SECONDS);

        // Срез закончился по времени: сразу продолжаем. Если рассылка на паузе (Telegram просил подождать, сбой), её
        // продолжит планировщик после паузы; задание не крутится вхолостую.
        if ($wait === 0) {
            self::dispatch($this->broadcastId);
        }
    }
}
