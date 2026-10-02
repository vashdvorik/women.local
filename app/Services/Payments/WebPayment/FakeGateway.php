<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

use App\Models\Payment;

/**
 * Имитатор банка для разработки и тестов. «Банк» живёт на нашем сайте (Dev\FakeBankController): страница оплаты с
 * кнопками «Оплатить» и «Отказаться». Решение запоминается в payment.payload['fake'], а GetState возвращает его так,
 * как ответил бы банк.
 *
 * На боевом сервере (APP_ENV=production) не используется: Checkout не создаст платёж.
 */
final class FakeGateway implements Gateway
{
    public function driver(): string
    {
        return 'fake';
    }

    public function startUrl(Payment $payment): string
    {
        return url('/dev/fake-bank');
    }

    public function getState(Payment $payment): BankState
    {
        $fake = $payment->payload['fake'] ?? null;

        if (! is_array($fake)) {
            // Решения ещё нет: «банк» знает счёт, но он не оплачен.
            return new BankState(
                found: true,
                state: BankState::NOT_PAID,
                sumKopecks: $payment->amount,
                currency: $payment->currency,
                isTest: $payment->is_test,
                invoiceId: $payment->invoice_id,
            );
        }

        return new BankState(
            found: true,
            state: (int) ($fake['state'] ?? BankState::NOT_PAID),
            sumKopecks: (int) ($fake['sum'] ?? $payment->amount),
            currency: (string) ($fake['currency'] ?? $payment->currency),
            isTest: (bool) ($fake['istest'] ?? $payment->is_test),
            invoiceId: $payment->invoice_id,
            rrn: $fake['rrn'] ?? null,
            lastDigits: $fake['lastdgt'] ?? null,
            message: 'Имитатор банка',
        );
    }
}
