<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Subscription;
use App\Support\BotMessages;
use Illuminate\Support\Facades\Log;
use Nutgram\Laravel\Facades\Telegram;
use Throwable;

/**
 * Сообщения о подписке в Telegram: «тариф включён», напоминания об окончании и «подписка закончилась».
 * Тексты — в resources/data/bot_messages.php (раздел «Подписка»), на языке участницы. Сбой Telegram
 * никогда не отменяет само действие: пишется в журнал.
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

        $this->send($user, 'subscription_activated', [
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
        return $this->send($user, 'subscription_reminder', [
            'plan' => $user->currentPlan()->title($user->messageLocale()),
            'until' => $user->plan_ends_at?->format('d.m.Y') ?? '',
            'days' => $daysLeft,
            'url' => route('account.subscription'),
        ]);
    }

    /** Подписка закончилась: $ended — тариф, который был. */
    public function expired(BotUser $user, Plan $ended): bool
    {
        return $this->send($user, 'subscription_expired', [
            'plan' => $ended->title($user->messageLocale()),
            'url' => route('account.subscription'),
        ]);
    }

    /** @param array<string, scalar|null> $vars */
    private function send(BotUser $user, string $key, array $vars): bool
    {
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
}
