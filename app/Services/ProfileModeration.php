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
 * Модерация анкет участниц: видимость другим (approve / unapprove), блокировка и разблокировка. Видимость участнице
 * не сообщается ни в Telegram, ни письмом (она просто начинает или перестаёт отображаться другим участницам, если у неё
 * действует тариф). О блокировке уведомление уходит в Telegram, если она его подключила, иначе письмом на почту; о
 * разблокировке не сообщается.
 *
 * Сбой отправки не откатывает решение модератора: статус уже сохранён, а неудача пишется в лог.
 *
 * Тексты в Telegram — в resources/data/bot_messages.php (раздел «Решение по анкете»), в письме — lang/{ru,en,ro}/participant.php;
 * язык — участницы (BotUser::messageLocale()).
 */
class ProfileModeration
{
    /** «Видима другим»: профиль попадает в каталог, поиск и подбор других участниц, пока у неё действует тариф. Без уведомления. */
    public function approve(BotUser $profile): void
    {
        $profile->update([
            'status' => BotUser::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    /** «Скрыта от других»: профиль уходит из каталога, поиска и подбора. Кабинет, тариф и рассылки остаются. Без уведомления. */
    public function unapprove(BotUser $profile): void
    {
        $profile->update(['status' => BotUser::STATUS_PENDING]);
    }

    /**
     * Блокировка: вход в кабинет закрыт, профиль не виден другим. Можно заблокировать кого угодно и когда угодно.
     * Прежняя отметка «видима / скрыта» запоминается, чтобы разблокировка вернула именно её.
     */
    public function block(BotUser $profile): void
    {
        if ($profile->isRejected()) {
            return;
        }

        $wasApproved = $profile->isApproved();

        $profile->forceFill([
            'status' => BotUser::STATUS_REJECTED,
            'status_before_block' => $profile->status,
        ])->save();

        $wasApproved
            ? $this->notify($profile, 'moderation_access_revoked', ModerationNoticeMail::REVOKED)
            : $this->notify($profile, 'moderation_rejected', ModerationNoticeMail::REJECTED, ['name' => BotMessages::firstName($profile->full_name)]);
    }

    /** Разблокировка: вход открыт, отметка «видима / скрыта» такая, какой была до блокировки. Участнице ничего не отправляется. */
    public function unblock(BotUser $profile): void
    {
        if (! $profile->isRejected()) {
            return;
        }

        $before = $profile->status_before_block;

        $profile->forceFill([
            'status' => $before === BotUser::STATUS_APPROVED ? BotUser::STATUS_APPROVED : BotUser::STATUS_PENDING,
            'status_before_block' => null,
        ])->save();
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
