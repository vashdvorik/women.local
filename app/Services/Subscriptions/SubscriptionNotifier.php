<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Enums\Plan;
use App\Mail\SubscriptionNoticeMail;
use App\Models\BotUser;
use App\Models\Subscription;
use App\Support\BotMessages;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Nutgram\Laravel\Facades\Telegram;
use Throwable;

/**
 * Сообщения о подписке: «тариф включён», напоминания об окончании и «подписка закончилась». Уходят в Telegram, если
 * участница его подключила, иначе письмом на почту. Тексты в Telegram — в resources/data/bot_messages.php (раздел
 * «Подписка»), в письме — lang/{ru,en,ro}/participant.php; язык — участницы. Сбой отправки никогда не отменяет само действие:
 * пишется в журнал.
 */
class SubscriptionNotifier
{
    /** Тариф включён (после оплаты или вручную в админке). */
    public function activated(Subscription $subscription): void
    {
        $user = $subscription->botUser;

        if ($user === null) {
            return;
        }

        $locale = $user->messageLocale();

        $this->send($user, 'subscription_activated', 'activated', [
            'plan' => $subscription->planEnum()->title($locale),
            'until' => $subscription->ends_at->format('d.m.Y'),
            'url' => route('account.subscription'),
        ]);
    }

    /**
     * Напоминание об окончании: $daysLeft — сколько дней осталось.
     *
     * @return bool доставлено ли: команда подписок отмечает напоминание отправленным только после успеха и повторит завтра
     */
    public function reminder(BotUser $user, int $daysLeft): bool
    {
        return $this->send($user, 'subscription_reminder', 'reminder', [
            'plan' => $user->currentPlan()->title($user->messageLocale()),
            'until' => $user->plan_ends_at?->format('d.m.Y') ?? '',
            'days' => $daysLeft,
            'url' => route('account.subscription'),
        ]);
    }

    /** Подписка закончилась: $ended — тариф, который был. */
    public function expired(BotUser $user, Plan $ended): bool
    {
        return $this->send($user, 'subscription_expired', 'expired', [
            'plan' => $ended->title($user->messageLocale()),
            'url' => route('account.subscription'),
        ]);
    }

    /** @param array<string, scalar|null> $vars */
    private function send(BotUser $user, string $key, string $mailKind, array $vars): bool
    {
        if (! $user->hasTelegram()) {
            return $this->mail($user, $mailKind, $vars);
        }

        try {
            Telegram::sendMessage(
                chat_id: $user->telegram_id,
                text: BotMessages::text($key, $user->messageLocale(), $vars),
                parse_mode: 'HTML',
            );

            return true;
        } catch (Throwable $e) {
            Log::warning('Telegram: не удалось отправить сообщение о подписке', [
                'key' => $key,
                'telegram_id' => $user->telegram_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /** @param array<string, scalar|null> $vars */
    private function mail(BotUser $user, string $kind, array $vars): bool
    {
        if (blank($user->email)) {
            // Некуда писать: считаем сообщение доставленным, иначе напоминание повторялось бы каждый день.
            return true;
        }

        try {
            Mail::to($user->email)->send(new SubscriptionNoticeMail($user, $kind, $vars));

            return true;
        } catch (Throwable $e) {
            Log::warning('Не удалось отправить письмо о подписке', ['kind' => $kind, 'user' => $user->id, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
