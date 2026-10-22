<?php

namespace App\Console\Commands;

use App\Services\Broadcasts\BroadcastLauncher;
use Illuminate\Console\Command;

/** Раз в минуту (schedule:run): запускает рассылки, время которых пришло, и возобновляет зависшие. */
class DispatchDueBroadcasts extends Command
{
    protected $signature = 'broadcasts:dispatch-due';

    protected $description = 'Запускает запланированные рассылки сообщений участницам в Telegram';

    public function handle(BroadcastLauncher $launcher): int
    {
        $count = $launcher->dispatchDue();

        if ($count > 0) {
            $this->info("Рассылок запущено: {$count}.");
        }

        return self::SUCCESS;
    }
}
