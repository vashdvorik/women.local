<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Telegram\TelegramKeyboards;
use Illuminate\Support\Facades\Log;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

/**
 * Модерация профилей участниц из Telegram-бота: смена статуса и уведомление
 * участницы в боте. Раньше жила внутри Filament-ресурса BotUserResource.
 *
 * Сбой Telegram не откатывает решение модератора: статус уже сохранён, а
 * неудачная отправка пишется в лог.
 */
class ProfileModeration
{
    public function approve(BotUser $profile): void
    {
        $profile->update([
            'status' => BotUser::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $this->sendApproval($profile);
    }

    public function reject(BotUser $profile): void
    {
        $wasApproved = $profile->isApproved();

        $profile->update(['status' => BotUser::STATUS_REJECTED]);

        $wasApproved ? $this->sendAccessRevoked($profile) : $this->sendRejection($profile);
    }

    private function sendApproval(BotUser $profile): void
    {
        $firstName = explode(' ', (string) $profile->full_name)[0];

        $inlineKeyboard = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('С чего начать? →', callback_data: 'start_guide'));

        try {
            Telegram::sendMessage(
                chat_id: $profile->telegram_id,
                text: "🎉 {$firstName}, ваша заявка одобрена.\n\nДобро пожаловать в Women Entrepreneurs Platform of the Two Banks. Теперь вам доступен личный кабинет, каталог участниц, поиск контактов, рекомендации и публикация возможностей.\n\nЗаполните профиль подробнее, чтобы другие участницы лучше понимали ваш бизнес, запросы и возможные форматы сотрудничества.",
                reply_markup: TelegramKeyboards::mainMenu(),
            );

            Telegram::sendMessage(
                chat_id: $profile->telegram_id,
                text: 'С чего начать?',
                reply_markup: $inlineKeyboard,
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об одобрении профиля участницы', [
                'telegram_id' => $profile->telegram_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendRejection(BotUser $profile): void
    {
        $firstName = explode(' ', (string) $profile->full_name)[0];

        try {
            Telegram::sendMessage(
                chat_id: $profile->telegram_id,
                text: "{$firstName}, спасибо за интерес к Women Entrepreneurs Platform of the Two Banks.\n\nСейчас ваша заявка не была одобрена. Если хотите уточнить детали или задать вопрос команде проекта, напишите: @lesnichenkoP",
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об отклонении профиля участницы', [
                'telegram_id' => $profile->telegram_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendAccessRevoked(BotUser $profile): void
    {
        try {
            Telegram::sendMessage(
                chat_id: $profile->telegram_id,
                text: "Доступ к Women Entrepreneurs Platform of the Two Banks закрыт.\n\nЕсли у вас есть вопросы по участию, напишите команде проекта: @lesnichenkoP",
                reply_markup: ReplyKeyboardRemove::make(remove_keyboard: true),
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об отзыве доступа', [
                'telegram_id' => $profile->telegram_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
