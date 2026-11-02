<?php

namespace App\Console\Commands;

use App\Services\Broadcasts\BroadcastLauncher;
use App\Services\Broadcasts\BroadcastSender;
use Illuminate\Console\Command;

/**
 * Раз в минуту (schedule:run): запускает запланированные рассылки, время которых пришло, и ведёт все идущие (срез ~45 с).
 * Это страховка и основной «автомат» на общем хостинге: рассылка уходит, даже если очередь не настроена.
 */
class DispatchDueBroadcasts extends Command
{
    protected $signature = 'broadcasts:dispatch-due';

    protected $description = 'Запускает запланированные рассылки в Telegram и ведёт идущие';

    public function handle(BroadcastLauncher $launcher, BroadcastSender $sender): int
    {
        $started = $launcher->dispatchDue();
        $worked = $sender->pump(45);

        if ($started > 0 || $worked > 0) {
            $this->info("Рассылок запущено: {$started}, обработано: {$worked}.");
        }

        return self::SUCCESS;
    }
}
