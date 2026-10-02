<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Support\BotMessages;
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
 *
 * Тексты уведомлений — в resources/data/bot_messages.php (раздел «Решение по заявке»), на языке
 * участницы (BotUser::messageLocale()).
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
        $locale = $profile->messageLocale();

        $inlineKeyboard = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(BotMessages::text('moderation_approved_guide_button', $locale), callback_data: 'start_guide'));

        try {
            Telegram::sendMessage(
                chat_id: $profile->telegram_id,
                text: BotMessages::text('moderation_approved', $locale, ['name' => BotMessages::firstName($profile->full_name)]),
                parse_mode: 'HTML',
                reply_markup: TelegramKeyboards::mainMenu($locale),
            );

            Telegram::sendMessage(
                chat_id: $profile->telegram_id,
                text: BotMessages::text('moderation_approved_guide_prompt', $locale),
                parse_mode: 'HTML',
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
        try {
            Telegram::sendMessage(
                chat_id: $profile->telegram_id,
                text: BotMessages::text('moderation_rejected', $profile->messageLocale(), ['name' => BotMessages::firstName($profile->full_name)]),
                parse_mode: 'HTML',
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
                text: BotMessages::text('moderation_access_revoked', $profile->messageLocale()),
                parse_mode: 'HTML',
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
