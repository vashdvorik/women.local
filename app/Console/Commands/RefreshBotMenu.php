<?php

namespace App\Console\Commands;

use App\Models\BotUser;
use App\Support\BotMessages;
use App\Telegram\TelegramKeyboards;
use Illuminate\Console\Command;
use Nutgram\Laravel\Facades\Telegram;
use Throwable;

/**
 * Присылает всем одобренным участницам сообщение «меню бота обновилось» вместе с новой клавиатурой («Кабинет» и «AI
 * помощник»). Запускается вручную один раз после выкладки: пока участница не получит сообщение с клавиатурой, у неё
 * на телефоне остаётся прежнее меню («Найти контакты», «Чат сообщества»).
 *
 *   php artisan bot:refresh-menu --dry-run   — только посчитать, кому уйдёт
 *   php artisan bot:refresh-menu              — отправить
 */
class RefreshBotMenu extends Command
{
    protected $signature = 'bot:refresh-menu {--dry-run : не отправлять, только показать, сколько получательниц}';

    protected $description = 'Присылает одобренным участницам сообщение с обновлённым меню бота (Кабинет, AI помощник)';

    public function handle(): int
    {
        $users = BotUser::approved()->get();

        if ($this->option('dry-run')) {
            $this->info("Получат сообщение: {$users->count()}.");

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($users as $user) {
            $locale = $user->messageLocale();

            try {
                Telegram::sendMessage(
                    chat_id: $user->telegram_id,
                    text: BotMessages::text('menu_updated', $locale, ['name' => BotMessages::firstName($user->full_name)]),
                    parse_mode: 'HTML',
                    reply_markup: TelegramKeyboards::mainMenu($locale),
                );
                $sent++;
            } catch (Throwable $e) {
                // Заблокировала бота или удалила аккаунт: пропускаем, остальным пишем дальше.
                logger()->info('Menu refresh was not delivered', ['telegram_id' => $user->telegram_id, 'error' => $e->getMessage()]);
            }

            usleep(50_000);
        }

        $this->info("Отправлено: {$sent} из {$users->count()}.");

        return self::SUCCESS;
    }
}
