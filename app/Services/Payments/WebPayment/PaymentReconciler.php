<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionNotifier;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Сверяет счёт с банком и, когда банк подтвердил оплату, включает тариф.
 *
 * Документ банка требует: оповещению ResultURL и перенаправлению на SuccessURL нельзя верить само по себе,
 * оплату нужно подтвердить запросом GetState. Поэтому тариф включается только здесь, и только если банк ответил
 * «оплачен», а сумма, валюта и признак теста совпали с нашим счётом.
 *
 * Метод apply() безопасно вызывать сколько угодно раз и одновременно (ResultURL, страница «Успешно», фоновая
 * сверка): строка платежа блокируется, второй вызов видит уже оплаченный счёт и ничего не делает.
 */
final class PaymentReconciler
{
    /** Через сколько минут после конца жизни счёта «ждёт оплаты» превращается в «истёк». */
    private const EXPIRY_GRACE_MINUTES = 10;

    public function __construct(
        private readonly Gateway $gateway,
        private readonly SubscriptionService $subscriptions,
        private readonly SubscriptionNotifier $notifier,
    ) {}

    /** Спрашивает банк и применяет ответ. Сбой связи оставляет счёт как есть: его перепроверит фоновая сверка. */
    public function check(Payment $payment): Payment
    {
        if ($payment->isFinal()) {
            return $payment;
        }

        try {
            $state = $this->gateway->getState($payment);
        } catch (GatewayException $e) {
            $this->remember($payment, ['last_error' => $e->getMessage()]);
            Log::warning('Web-платёж: не удалось проверить счёт в банке', ['invoice' => $payment->invoice_id, 'error' => $e->getMessage()]);

            return $payment->refresh();
        }

        return $this->apply($payment, $state);
    }

    public function apply(Payment $payment, BankState $state): Payment
    {
        $activated = null;

        $payment = DB::transaction(function () use ($payment, $state, &$activated): Payment {
            /** @var Payment $locked */
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->isFinal()) {
                return $locked;
            }

            $locked->checked_at = now();
            $payload = $locked->payload ?? [];
            $payload['bank_state'] = $state->raw !== [] ? $state->raw : ['found' => $state->found, 'message' => $state->message];
            unset($payload['last_error']);
            $locked->payload = $payload;

            if (! $state->found) {
                $this->expireIfStale($locked);
                $locked->save();

                return $locked;
            }

            $locked->bank_state = $state->state;

            if ($state->isPaid()) {
                $problem = $this->mismatch($locked, $state);

                if ($problem !== null) {
                    // Банк говорит «оплачен», но данные не сходятся с нашим счётом: тариф не включаем, разбирается человек.
                    $payload['anomaly'] = $problem;
                    $locked->payload = $payload;
                    $locked->status = Payment::STATUS_VERIFYING;
                    $locked->save();
                    Log::critical('Web-платёж: банк подтвердил оплату, но данные счёта не совпали', ['invoice' => $locked->invoice_id, 'problem' => $problem]);

                    return $locked;
                }

                $locked->fill([
                    'status' => Payment::STATUS_PAID,
                    'paid_at' => now(),
                    'rrn' => $state->rrn ?? $locked->rrn,
                    'last_digits' => $state->lastDigits ?? $locked->last_digits,
                ])->save();

                $activated = $this->activate($locked);

                return $locked;
            }

            if ($state->isDead()) {
                $locked->status = match ($state->state) {
                    BankState::CANCELLED => Payment::STATUS_CANCELLED,
                    BankState::EXPIRED => Payment::STATUS_EXPIRED,
                    default => Payment::STATUS_FAILED,
                };
            } else {
                $this->expireIfStale($locked);
            }

            $locked->save();

            return $locked;
        });

        // Сообщение в Telegram — уже после фиксации в базе: сбой бота не должен откатывать оплату.
        if ($activated !== null) {
            $this->notifier->activated($activated);
        }

        return $payment;
    }

    /**
     * Фоновая сверка: спрашивает банк по всем неоплаченным счетам, которые уже могли быть оплачены.
     *
     * @return int сколько счетов проверено
     */
    public function reconcileOpen(int $limit = 100): int
    {
        $payments = Payment::query()
            ->open()
            ->where('created_at', '<=', now()->subMinutes(2))
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($payments as $payment) {
            try {
                $this->check($payment);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $payments->count();
    }

    /** Включает тариф; возвращает подписку или null, если участницы уже нет (профиль удалён после оплаты). */
    private function activate(Payment $payment): ?Subscription
    {
        $user = $payment->botUser;

        if ($user === null) {
            Log::critical('Web-платёж оплачен, но участницы больше нет: нужен возврат', ['invoice' => $payment->invoice_id, 'telegram_id' => $payment->telegram_id]);

            return null;
        }

        return $this->subscriptions->grant(
            $user,
            $payment->planEnum(),
            $payment->months,
            Subscription::SOURCE_PAYMENT,
            $payment,
            'Оплата '.$payment->invoice_id,
        );
    }

    /** @return string|null описание расхождения или null, если всё сходится */
    private function mismatch(Payment $payment, BankState $state): ?string
    {
        if ($state->sumKopecks !== $payment->amount) {
            return "сумма: у нас {$payment->amount}, у банка ".var_export($state->sumKopecks, true);
        }

        if ($state->currency !== null && $state->currency !== $payment->currency) {
            return "валюта: у нас {$payment->currency}, у банка {$state->currency}";
        }

        if ($state->isTest !== null && $state->isTest !== $payment->is_test) {
            return 'признак теста не совпал: у нас '.($payment->is_test ? 'тест' : 'боевой').', у банка '.($state->isTest ? 'тест' : 'боевой');
        }

        if ($state->invoiceId !== null && $state->invoiceId !== $payment->invoice_id) {
            return "номер счёта: у нас {$payment->invoice_id}, у банка {$state->invoiceId}";
        }

        return null;
    }

    private function expireIfStale(Payment $payment): void
    {
        if ($payment->isOpen() && $payment->expires_at->copy()->addMinutes(self::EXPIRY_GRACE_MINUTES)->isPast()) {
            $payment->status = Payment::STATUS_EXPIRED;
        }
    }

    /** @param array<string, mixed> $extra */
    private function remember(Payment $payment, array $extra): void
    {
        $payment->forceFill([
            'checked_at' => now(),
            'payload' => array_merge($payment->payload ?? [], $extra),
        ])->save();
    }
}
