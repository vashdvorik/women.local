<?php

namespace App\Console\Commands;

use App\Models\BotUser;
use App\Support\BotMessages;
use App\Telegram\TelegramKeyboards;
use Illuminate\Console\Command;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use Throwable;

/**
 * Напоминание о кабинете: участнице, которую одобрили два дня назад и больше (но не больше двух недель назад), а она так и
 * не открыла кабинет, бот один раз мягко напоминает и предлагает помощь. Тем, кто одобрен до появления напоминаний,
 * оно не уходит (миграция отметила их как уже напомненных). Запускается раз в сутки (routes/console.php).
 *
 *   php artisan bot:remind-cabinet --dry-run   — только показать, кому бы ушло
 */
class RemindCabinet extends Command
{
    /** Через сколько дней после одобрения напоминаем и до какого дня ещё имеет смысл. */
    private const AFTER_DAYS = 2;
    private const UNTIL_DAYS = 14;

    protected $signature = 'bot:remind-cabinet {--dry-run : не отправлять, только показать, кому уйдёт}';

    protected $description = 'Один раз напоминает одобренным участницам, которые так и не открыли кабинет';

    public function handle(): int
    {
        $users = BotUser::query()
            ->approved()
            ->whereNull('cabinet_opened_at')
            ->whereNull('cabinet_reminded_at')
            ->where('approved_at', '<=', now()->subDays(self::AFTER_DAYS))
            ->where('approved_at', '>=', now()->subDays(self::UNTIL_DAYS))
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            if ($this->option('dry-run')) {
                $this->line("{$user->full_name} (telegram_id {$user->telegram_id}), одобрена {$user->approved_at->format('d.m.Y')}");

                continue;
            }

            $sent += $this->remind($user) ? 1 : 0;
            usleep(50_000);
        }

        $this->info($this->option('dry-run') ? "Напомнили бы: {$users->count()}." : "Напоминаний отправлено: {$sent} из {$users->count()}.");

        return self::SUCCESS;
    }

    private function remind(BotUser $user): bool
    {
        $locale = $user->messageLocale();
        $keyboard = InlineKeyboardMarkup::make();

        if ($cabinet = TelegramKeyboards::cabinetButton($locale)) {
            $keyboard->addRow($cabinet);
        }
        $keyboard->addRow(TelegramKeyboards::helpAskButton($locale));

        try {
            Telegram::sendMessage(
                chat_id: $user->telegram_id,
                text: BotMessages::text('cabinet_reminder', $locale, ['name' => BotMessages::firstName($user->full_name)]),
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );

            return true;
        } catch (Throwable $e) {
            logger()->warning('Cabinet reminder was not delivered', ['telegram_id' => $user->telegram_id, 'error' => $e->getMessage()]);

            return false;
        } finally {
            // Одна попытка: заблокировавшей бота снова не пишем и не повторяем каждые сутки.
            $user->forceFill(['cabinet_reminded_at' => now()])->save();
        }
    }
}
