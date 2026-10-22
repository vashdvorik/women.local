<?php

namespace App\Services\Broadcasts;

use App\Jobs\SendBroadcast;
use App\Models\Broadcast;

/**
 * Запуск, отмена и «подталкивание» рассылок. Запуск атомарный: из двух одновременных нажатий «Отправить» (или кнопки и
 * расписания) сработает одно, поэтому участницы не получат сообщение дважды.
 */
class BroadcastLauncher
{
    /** Сколько минут рассылка может не двигаться, прежде чем её запустят заново (упал воркер очереди). */
    private const STALLED_MINUTES = 10;

    public function start(Broadcast $broadcast): bool
    {
        $started = Broadcast::query()
            ->whereKey($broadcast->id)
            ->whereIn('status', [Broadcast::STATUS_DRAFT, Broadcast::STATUS_SCHEDULED])
            ->update(['status' => Broadcast::STATUS_SENDING, 'started_at' => now(), 'updated_at' => now()]);

        if ($started === 0) {
            return false;
        }

        SendBroadcast::dispatch($broadcast->id);

        return true;
    }

    /** Отмена: запланированная не уйдёт, идущая остановится (кому уже отправлено, тем не вернуть). */
    public function cancel(Broadcast $broadcast): bool
    {
        return Broadcast::query()
            ->whereKey($broadcast->id)
            ->whereIn('status', [Broadcast::STATUS_DRAFT, Broadcast::STATUS_SCHEDULED, Broadcast::STATUS_SENDING])
            ->update(['status' => Broadcast::STATUS_CANCELLED, 'finished_at' => now(), 'updated_at' => now()]) > 0;
    }

    /**
     * Раз в минуту (artisan broadcasts:dispatch-due): наступило время запланированной рассылки — запускаем; рассылка
     * зависла (воркер упал посреди отправки) — запускаем заново, отправленным она повторно не пишет.
     *
     * @return int сколько рассылок запущено или возобновлено
     */
    public function dispatchDue(): int
    {
        $count = 0;

        Broadcast::query()
            ->where('status', Broadcast::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now())
            ->each(function (Broadcast $broadcast) use (&$count) {
                $count += $this->start($broadcast) ? 1 : 0;
            });

        Broadcast::query()
            ->where('status', Broadcast::STATUS_SENDING)
            ->where('updated_at', '<', now()->subMinutes(self::STALLED_MINUTES))
            ->each(function (Broadcast $broadcast) use (&$count) {
                $broadcast->touch();
                SendBroadcast::dispatch($broadcast->id);
                $count++;
            });

        return $count;
    }
}
