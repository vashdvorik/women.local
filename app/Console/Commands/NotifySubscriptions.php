<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Services\Subscriptions\SubscriptionNotifier;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Раз в сутки (расписание — routes/console.php): напоминания об окончании подписки в Telegram и перевод участниц,
 * у которых подписка закончилась, на Open.
 *
 * Напоминаний два (config subscription.reminder_days, по умолчанию за 14 и за 3 дня) и одно сообщение «закончилась».
 * Какое уже отправлено, помнит bot_users.plan_notified_stage; оплата сбрасывает счётчик. Доступ закрывается и без
 * этой команды (BotUser::currentPlan() сверяет дату), здесь только уведомления и приведение базы в порядок.
 */
class NotifySubscriptions extends Command
{
    protected $signature = 'subscriptions:notify';

    protected $description = 'Напоминания об окончании подписки в Telegram и перевод закончившихся подписок на Open';

    public function handle(SubscriptionNotifier $notifier, SubscriptionService $subscriptions): int
    {
        [$first, $last] = array_pad(array_map('intval', (array) config('subscription.reminder_days', [14, 3])), 2, 0);

        $reminded = 0;
        $expired = 0;

        BotUser::query()
            ->approved()
            ->where('plan', '!=', Plan::Open->value)
            ->whereNotNull('plan_ends_at')
            ->where('plan_ends_at', '<=', now()->addDays(max($first, $last)))
            ->orderBy('id')
            ->each(function (BotUser $user) use ($notifier, $subscriptions, $first, $last, &$reminded, &$expired): void {
                $ended = Plan::fromStored($user->plan);

                // Подписка закончилась: пересчитываем (вдруг остался оплаченный период пониже) и сообщаем один раз.
                if ($user->plan_ends_at->lte(now())) {
                    $stage = $user->plan_notified_stage;
                    $subscriptions->refresh($user);
                    $user->refresh();

                    if ($user->plan === Plan::Open->value) {
                        if ($stage < 3) {
                            $notifier->expired($user, $ended);
                            $expired++;
                        }
                    } else {
                        $user->forceFill(['plan_notified_stage' => 0])->save();
                    }

                    return;
                }

                $daysLeft = (int) ceil(($user->plan_ends_at->timestamp - now()->timestamp) / 86400);

                // Этап отмечается только после доставки: если Telegram не ответил, напоминание повторится завтра.
                $stage = match (true) {
                    $daysLeft <= $last && $user->plan_notified_stage < 2 => 2,
                    $daysLeft <= $first && $user->plan_notified_stage < 1 => 1,
                    default => null,
                };

                if ($stage !== null && $notifier->reminder($user, $daysLeft)) {
                    $user->forceFill(['plan_notified_stage' => $stage])->save();
                    $reminded++;
                }
            });

        $this->info("Напоминаний отправлено: {$reminded}, подписок закончилось: {$expired}.");

        return self::SUCCESS;
    }
}
