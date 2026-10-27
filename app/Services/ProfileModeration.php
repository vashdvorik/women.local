<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ModerationNoticeMail;
use App\Models\BotUser;
use App\Support\BotMessages;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Nutgram\Laravel\Facades\Telegram;

/**
 * Модерация анкет участниц: смена статуса и уведомление участницы. Уведомление уходит в Telegram, если она его
 * подключила, иначе письмом на почту.
 *
 * Сбой отправки не откатывает решение модератора: статус уже сохранён, а неудача пишется в лог.
 *
 * Тексты в Telegram — в resources/data/bot_messages.php (раздел «Решение по анкете»), в письме — lang/{ru,en,ro}/participant.php;
 * язык — участницы (BotUser::messageLocale()).
 */
class ProfileModeration
{
    public function approve(BotUser $profile): void
    {
        $profile->update([
            'status' => BotUser::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $this->notify($profile, 'moderation_approved', ModerationNoticeMail::APPROVED, [
            'name' => BotMessages::firstName($profile->full_name),
            'url' => route('account.index'),
        ]);
    }

    public function reject(BotUser $profile): void
    {
        $wasApproved = $profile->isApproved();

        $profile->update(['status' => BotUser::STATUS_REJECTED]);

        $wasApproved
            ? $this->notify($profile, 'moderation_access_revoked', ModerationNoticeMail::REVOKED)
            : $this->notify($profile, 'moderation_rejected', ModerationNoticeMail::REJECTED, ['name' => BotMessages::firstName($profile->full_name)]);
    }

    /** @param array<string, scalar|null> $vars */
    private function notify(BotUser $profile, string $botKey, string $mailKind, array $vars = []): void
    {
        if ($profile->hasTelegram()) {
            try {
                Telegram::sendMessage(
                    chat_id: $profile->telegram_id,
                    text: BotMessages::text($botKey, $profile->messageLocale(), $vars),
                    parse_mode: 'HTML',
                );
            } catch (\Throwable $e) {
                Log::error('Telegram: не удалось отправить сообщение о решении по анкете', [
                    'key' => $botKey,
                    'telegram_id' => $profile->telegram_id,
                    'error' => $e->getMessage(),
                ]);
            }

            return;
        }

        if (blank($profile->email)) {
            return;
        }

        try {
            Mail::to($profile->email)->send(new ModerationNoticeMail($profile, $mailKind));
        } catch (\Throwable $e) {
            Log::error('Не удалось отправить письмо о решении по анкете', ['kind' => $mailKind, 'user' => $profile->id, 'error' => $e->getMessage()]);
        }
    }
}
