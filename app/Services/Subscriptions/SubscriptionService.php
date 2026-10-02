<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Payment;
use App\Models\Subscription;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Единственное место, где меняется тариф участницы. Оплата в банке и ручная выдача в админке приходят сюда.
 *
 * Правила:
 *  - продление того же тарифа начинается в день окончания текущего, поэтому ранняя оплата не сгорает;
 *  - более высокий тариф (Community → Private) включается сразу, а остаток прежнего вернётся, если он дольше;
 *  - bot_users.plan / plan_ends_at — срез «что действует сейчас», его пересчитывает refresh() из таблицы subscriptions.
 */
final class SubscriptionService
{
    /**
     * Включает тариф на $months месяцев.
     */
    public function grant(
        BotUser $user,
        Plan $plan,
        int $months,
        string $source = Subscription::SOURCE_ADMIN,
        ?Payment $payment = null,
        ?string $note = null,
    ): Subscription {
        if (! $plan->isPaid()) {
            throw new InvalidArgumentException('Open — бесплатный уровень, выдавать его подпиской не нужно.');
        }

        if ($months < 1) {
            throw new InvalidArgumentException('Срок подписки — не меньше одного месяца.');
        }

        return DB::transaction(function () use ($user, $plan, $months, $source, $payment, $note): Subscription {
            $now = now();

            // Продление того же тарифа продолжается с конца уже оплаченного периода.
            $lastEnd = $user->subscriptions()
                ->where('plan', $plan->value)
                ->where('ends_at', '>', $now)
                ->max('ends_at');

            $startsAt = $lastEnd !== null ? Carbon::parse($lastEnd) : $now;

            $subscription = $user->subscriptions()->create([
                'plan' => $plan->value,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMonthsNoOverflow($months),
                'source' => $source,
                'payment_id' => $payment?->id,
                'note' => $note,
            ]);

            // Новая оплата — новая серия напоминаний.
            $user->forceFill(['plan_notified_stage' => 0])->save();
            $this->refresh($user);

            return $subscription;
        });
    }

    /**
     * Задаёт точную дату окончания тарифа (ручная правка в админке): вместо истории создаётся одна запись
     * «с сейчас по $endsAt», а все прежние периоды этой же участницы закрываются.
     */
    public function setUntil(BotUser $user, Plan $plan, CarbonInterface $endsAt, ?string $note = null): Subscription
    {
        if (! $plan->isPaid()) {
            throw new InvalidArgumentException('Для Open срок не задаётся.');
        }

        if ($endsAt->lte(now())) {
            throw new InvalidArgumentException('Дата окончания должна быть в будущем. Чтобы отключить тариф, используйте «Закрыть доступ».');
        }

        return DB::transaction(function () use ($user, $plan, $endsAt, $note): Subscription {
            $this->closeActive($user);

            $subscription = $user->subscriptions()->create([
                'plan' => $plan->value,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'source' => Subscription::SOURCE_ADMIN,
                'note' => $note,
            ]);

            $user->forceFill(['plan_notified_stage' => 0])->save();
            $this->refresh($user);

            return $subscription;
        });
    }

    /** Закрывает все действующие периоды: участница возвращается на Open немедленно. */
    public function revoke(BotUser $user): void
    {
        DB::transaction(function () use ($user): void {
            $this->closeActive($user);
            $this->refresh($user);
        });
    }

    /**
     * Пересчитывает bot_users.plan / plan_ends_at по таблице subscriptions: действует самый высокий тариф
     * из идущих сейчас, а срок — конец последнего периода этого тарифа.
     */
    public function refresh(BotUser $user): void
    {
        $active = $user->subscriptions()->active()->get();

        if ($active->isEmpty()) {
            $user->forceFill(['plan' => Plan::Open->value, 'plan_ends_at' => null])->save();

            return;
        }

        $best = $active->sortByDesc(fn (Subscription $s): int => $s->planEnum()->rank())->first()->planEnum();

        // Периоды этого тарифа, включая уже оплаченные вперёд (начинаются в день окончания предыдущего).
        $endsAt = $user->subscriptions()
            ->where('plan', $best->value)
            ->where('ends_at', '>', now())
            ->max('ends_at');

        $user->forceFill([
            'plan' => $best->value,
            'plan_ends_at' => Carbon::parse($endsAt),
        ])->save();
    }

    private function closeActive(BotUser $user): void
    {
        $user->subscriptions()
            ->where('ends_at', '>', now())
            ->get()
            ->each(function (Subscription $subscription): void {
                // Идущий период обрывается сейчас; тот, что ещё не начался (оплачен вперёд), сводится к нулевой длине
                // «сейчас — сейчас»: в истории он остаётся, но уже ничего не даёт и не сдвигает следующие продления.
                $subscription->update([
                    'starts_at' => $subscription->starts_at->gt(now()) ? now() : $subscription->starts_at,
                    'ends_at' => now(),
                ]);
            });
    }
}
