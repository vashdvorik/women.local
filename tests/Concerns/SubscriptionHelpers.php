<?php

namespace Tests\Concerns;

use App\Enums\Plan;
use App\Http\Controllers\Dev\FakeBankController;
use App\Models\BotUser;
use App\Models\Payment;
use App\Services\Payments\WebPayment\Checkout;
use App\Services\Payments\WebPayment\Signature;
use Illuminate\Support\Facades\Route;

/**
 * Общие приёмы для тестов подписок и оплаты: вход участницы, регистрация маршрутов «имитатора банка» и разбор формы
 * на платёжный сайт банка.
 */
trait SubscriptionHelpers
{
    protected function sessionFor(BotUser $user): array
    {
        return [
            'account_telegram_id' => $user->telegram_id,
            '_account_expires' => now()->addDays(7)->timestamp,
        ];
    }

    protected function asParticipant(BotUser $user): static
    {
        return $this->withSession($this->sessionFor($user));
    }

    /** Подключает страницу «банка» для разработки: в обычной работе она есть только при APP_ENV=local. */
    protected function registerFakeBank(): void
    {
        Route::middleware('web')->group(function (): void {
            Route::match(['GET', 'POST'], '/dev/fake-bank', [FakeBankController::class, 'start'])->name('dev.fakebank.start');
            Route::post('/dev/fake-bank/complete', [FakeBankController::class, 'complete'])->name('dev.fakebank.complete');
        });

        app('router')->getRoutes()->refreshNameLookups();
    }

    /** Счёт, созданный так же, как при нажатии «Оплатить». */
    protected function checkoutFor(BotUser $user, Plan $plan): Payment
    {
        return app(Checkout::class)->begin($user, $plan);
    }

    /**
     * Оповещение банка (ResultURL) о принятой оплате: ровно те параметры и подпись, что описаны в разделе 4 документа.
     *
     * @return array<string, string>
     */
    protected function paidNotification(Payment $payment, array $override = []): array
    {
        $date = now()->format('dmY');
        $pass = (string) config('webpayment.merchant_pass');
        $sum = (string) ($override['paymentsum'] ?? $payment->amount);
        $currency = (string) ($override['paymentcurrency'] ?? $payment->currency);

        return array_merge([
            'status' => 'paid',
            'paymentsum' => $sum,
            'paymentcurrency' => $currency,
            'invoiceid' => $payment->invoice_id,
            'date' => $date,
            'signature' => Signature::paid($payment->invoice_id, 'paid', $sum, $currency, $date, $pass),
            'istest' => $payment->is_test ? '1' : '0',
            'rrn' => '0007458712',
            'lastdgt' => '0123',
        ], $override);
    }

    /**
     * Ответ веб-сервиса банка на GetState в том виде, как его описывает документ (раздел 6): SOAP-конверт со строкой
     * Base64, внутри — <envelope> с response (XML, Base64) и signature (Base64).
     *
     * @param  array<string, string>  $stateInfo  поля StateInfo
     * @param  callable(string): string|null  $sign  как подписать XML ответа (по умолчанию подпись-заглушка)
     */
    protected function bankSoapReply(array $stateInfo, int $code = 1, ?callable $sign = null): string
    {
        $info = '';
        foreach ($stateInfo as $name => $value) {
            $info .= "<{$name}>{$value}</{$name}>";
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?><OperationStateResponse><Result><Code>'.$code.'</Code><Description>'
            .($code === 1 ? 'OK' : 'Счёт не найден').'</Description></Result>'
            .($code === 1 ? "<StateInfo>{$info}</StateInfo>" : '').'</OperationStateResponse>';

        $signature = $sign ? $sign($xml) : 'unsigned';
        $envelope = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><envelope><response>'.base64_encode($xml)
            .'</response><signature>'.base64_encode($signature).'</signature></envelope>';

        return '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
            .'<soap:Body><GetStateResponse xmlns="http://webpayment.services.agroprombank.com/"><GetStateResult>'
            .base64_encode($envelope).'</GetStateResult></GetStateResponse></soap:Body></soap:Envelope>';
    }

    /** Ответ GetState «оплачен» для счёта (совпадающие сумма, валюта, признак теста). */
    protected function paidSoapReply(Payment $payment, array $override = []): string
    {
        return $this->bankSoapReply(array_merge([
            'state' => '1',
            'statedescription' => 'Оплачен',
            'istest' => $payment->is_test ? '1' : '0',
            'sum' => (string) $payment->amount,
            'currency' => $payment->currency,
            'invoiceid' => $payment->invoice_id,
            'rrn' => '12345678910',
            'lastdgt' => '0123',
        ], $override));
    }

    /** Оповещение банка об отказе (раздел 4, «При неуспешном завершении»). */
    protected function failedNotification(Payment $payment): array
    {
        $date = now()->format('dmY');

        return [
            'status' => 'fail',
            'invoiceid' => $payment->invoice_id,
            'date' => $date,
            'signature' => Signature::failed($payment->invoice_id, 'fail', $date, (string) config('webpayment.merchant_pass')),
            'istest' => $payment->is_test ? '1' : '0',
        ];
    }
}
