<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BotCommandSync;
use Illuminate\Console\Command;

class SyncBotCommands extends Command
{
    protected $signature = 'bot:sync-commands';

    protected $description = 'Отправить в Telegram меню команд бота (описания из resources/data/bot_messages.php и правок админки)';

    public function handle(BotCommandSync $sync): int
    {
        if (! $sync->isConfigured()) {
            $this->warn('TELEGRAM_TOKEN не задан: меню команд отправлять некуда.');

            return self::FAILURE;
        }

        try {
            $sync->sync();
        } catch (\Throwable $e) {
            $this->error('Telegram не принял меню команд: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Меню команд обновлено (ru, en, ro).');

        return self::SUCCESS;
    }
}
