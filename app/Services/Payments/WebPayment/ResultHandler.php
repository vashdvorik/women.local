<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;

/**
 * Оповещение банка об итоге оплаты — вызов нашего ResultURL (раздел 4 документа). Банк вызывает его сам, поэтому
 * это единственная точка, куда можно прислать поддельный запрос. Защита в три слоя:
 *
 *  1. подпись MD5 с паролем торговца — без MerchantPass правильную подпись не составить;
 *  2. счёт должен существовать у нас, а сумма, валюта и признак теста — совпасть с ним;
 *  3. оплата подтверждается отдельным запросом GetState к банку (PaymentReconciler), а не словами этого запроса.
 *
 * Метод ничего не возвращает «наружу» кроме итога для ответа банку: подробности остаются в журнале и в payload платежа.
 */
final class ResultHandler
{
    public const OK = 'ok';

    public const BAD_REQUEST = 'bad_request';

    public const BAD_SIGNATURE = 'bad_signature';

    public const UNKNOWN_INVOICE = 'unknown_invoice';

    public function __construct(private readonly PaymentReconciler $reconciler) {}

    /**
     * @param  array<string, mixed>  $input  параметры запроса банка (POST или GET)
     * @return self::OK|self::BAD_REQUEST|self::BAD_SIGNATURE|self::UNKNOWN_INVOICE
     */
    public function handle(array $input): string
    {
        $p = array_change_key_case(array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $input), CASE_LOWER);

        $invoice = $p['invoiceid'] ?? '';
        $status = strtolower($p['status'] ?? '');
        $date = $p['date'] ?? '';
        $signature = $p['signature'] ?? '';
        $pass = (string) config('webpayment.merchant_pass');

        if ($invoice === '' || $status === '' || $date === '' || $signature === '' || $pass === '') {
            return self::BAD_REQUEST;
        }

        $expected = match ($status) {
            'paid' => Signature::paid($invoice, $status, $p['paymentsum'] ?? '', $p['paymentcurrency'] ?? '', $date, $pass),
            'fail' => Signature::failed($invoice, $status, $date, $pass),
            default => null,
        };

        if ($expected === null || ! Signature::equals($expected, $signature)) {
            Log::warning('Web-платёж: ResultURL с неверной подписью', ['invoice' => $invoice, 'status' => $status]);

            return self::BAD_SIGNATURE;
        }

        /** @var Payment|null $payment */
        $payment = Payment::query()->where('invoice_id', $invoice)->first();

        if ($payment === null) {
            Log::warning('Web-платёж: ResultURL по неизвестному счёту', ['invoice' => $invoice]);

            return self::UNKNOWN_INVOICE;
        }

        // Повторный вызов по уже закрытому счёту безвреден: сообщаем «принято» и ничего не меняем.
        if ($payment->isFinal()) {
            return self::OK;
        }

        if ($status === 'fail') {
            $payment->forceFill([
                'status' => Payment::STATUS_FAILED,
                'checked_at' => now(),
                'payload' => array_merge($payment->payload ?? [], ['result' => $this->safe($p)]),
            ])->save();

            return self::OK;
        }

        // Оплата: сумма, валюта и признак теста из запроса должны совпасть со счётом.
        $istest = ($p['istest'] ?? '') === '1';
        $problem = null;

        if (($p['paymentsum'] ?? '') !== (string) $payment->amount) {
            $problem = "сумма в оповещении {$p['paymentsum']}, в счёте {$payment->amount}";
        } elseif (($p['paymentcurrency'] ?? '') !== $payment->currency) {
            $problem = "валюта в оповещении {$p['paymentcurrency']}, в счёте {$payment->currency}";
        } elseif ($istest !== $payment->is_test) {
            $problem = 'признак теста в оповещении не совпал со счётом';
        }

        $payload = array_merge($payment->payload ?? [], ['result' => $this->safe($p)]);

        if ($problem !== null) {
            $payload['anomaly'] = $problem;
            $payment->forceFill(['status' => Payment::STATUS_VERIFYING, 'payload' => $payload, 'checked_at' => now()])->save();
            Log::critical('Web-платёж: оповещение об оплате не совпало со счётом', ['invoice' => $invoice, 'problem' => $problem]);

            return self::OK;
        }

        $payment->forceFill([
            'status' => Payment::STATUS_VERIFYING,
            'rrn' => ($p['rrn'] ?? '') !== '' ? $p['rrn'] : $payment->rrn,
            'last_digits' => ($p['lastdgt'] ?? '') !== '' ? substr($p['lastdgt'], -4) : $payment->last_digits,
            'payload' => $payload,
        ])->save();

        // Решающий шаг: тариф включится, только если банк сам подтвердит оплату через GetState.
        $this->reconciler->check($payment);

        return self::OK;
    }

    /**
     * Что из оповещения банка сохраняем: ничего секретного там нет, но токен рекуррентных платежей
     * (token) мы не используем и в базе не держим.
     *
     * @param  array<string, string>  $p
     * @return array<string, string>
     */
    private function safe(array $p): array
    {
        unset($p['token'], $p['signature']);

        return array_slice($p, 0, 20, true);
    }
}
