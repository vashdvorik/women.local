<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Payment;
use App\Models\Subscription;

/**
 * Цена перехода на тариф выше. Участница, которая уже оплатила тариф ниже (Community) и переходит на высший (Active),
 * не платит за оставшиеся месяцы дважды: неиспользованная часть оплаты вычитается из цены нового тарифа.
 *
 * Правила:
 *  - зачёт идёт только за оплаченное через банк: сумма платежа делится на длину периода, берётся доля оставшегося срока
 *    (период, оплаченный вперёд и ещё не начавшийся, засчитывается целиком), по всем неистёкшим оплаченным периодам
 *    ниже стоящих тарифов;
 *  - если действующий тариф выдан или подарен администратором, пересчёта нет: цена полная (платить нечем, нечего засчитывать);
 *  - сумма зачёта округляется вниз до целых рублей, к оплате остаётся не меньше одного рубля;
 *  - после оплаты засчитанные периоды закрываются (SubscriptionService::spend): остаток уже оплачен новым тарифом, а не
 *    возвращается позже сверх него.
 *
 * Цена всегда считается на сервере (при создании счёта), а не берётся из браузера.
 */
final class UpgradePricing
{
    /** Не меньше этой суммы (копейки) участница платит за переход: банк не принимает нулевой счёт. */
    private const MIN_KOPECKS = 100;

    /**
     * @return array{full: int, credit: int, payable: int, from: ?Plan, gift: bool, subscriptions: list<int>}  суммы в копейках;
     *         gift — действующий тариф подарен администратором (пересчёта нет); subscriptions — id засчитанных периодов
     */
    public function quote(BotUser $user, Plan $target): array
    {
        $full = $target->priceKopecks();
        $none = ['full' => $full, 'credit' => 0, 'payable' => $full, 'from' => null, 'gift' => false, 'subscriptions' => []];

        $current = $user->currentPlan();

        if (! $current->isPaid() || $current->rank() >= $target->rank()) {
            return $none;
        }

        // Действующий период текущего тарифа: если его выдала не оплата, а администратор, пересчёта нет
        $active = $user->subscriptions()->active()->where('plan', $current->value)->orderByDesc('ends_at')->first();

        if ($active === null || $active->source !== Subscription::SOURCE_PAYMENT) {
            return ['gift' => $active !== null] + $none;
        }

        $credit = 0.0;
        $ids = [];

        $periods = $user->subscriptions()
            ->where('source', Subscription::SOURCE_PAYMENT)
            ->whereNotNull('payment_id')
            ->where('ends_at', '>', now())
            ->with('payment')
            ->get()
            ->filter(fn (Subscription $period): bool => $period->planEnum()->rank() < $target->rank());

        foreach ($periods as $period) {
            $payment = $period->payment;

            if ($payment === null || $payment->status !== Payment::STATUS_PAID) {
                continue;
            }

            $total = $period->starts_at->diffInSeconds($period->ends_at, true);
            $left = max($period->starts_at, now())->diffInSeconds($period->ends_at, true);

            if ($total <= 0 || $left <= 0) {
                continue;
            }

            $credit += $payment->amount * min(1.0, $left / $total);
            $ids[] = $period->id;
        }

        $credit = (int) (floor($credit / 100) * 100);
        $credit = max(0, min($credit, $full - self::MIN_KOPECKS));

        if ($credit === 0) {
            return $none;
        }

        return [
            'full' => $full,
            'credit' => $credit,
            'payable' => $full - $credit,
            'from' => $current,
            'gift' => false,
            'subscriptions' => $ids,
        ];
    }

    /** Сумма в копейках словами для интерфейса: «4 700 руб.» (неразрывные пробелы). */
    public static function label(int $kopecks, ?string $locale = null): string
    {
        return number_format($kopecks / 100, 0, ',', "\u{00A0}")."\u{00A0}".str_replace(' ', "\u{00A0}", __('subscription.currency', [], $locale));
    }
}
