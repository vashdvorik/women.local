<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\WebPayment\BankState;
use App\Services\Payments\WebPayment\ResultHandler;
use App\Services\Payments\WebPayment\Signature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * «Банк» для разработки: страница оплаты, на которую форма из кабинета попадает вместо epay.apb.online.
 *
 * Ведёт себя как настоящий платёжный сайт: проверяет подпись запроса так же, как это сделал бы банк (так мы ловим
 * ошибки в нашей подписи до выхода в бой), после решения вызывает наш ResultURL с правильной подписью и возвращает
 * участницу на страницу результата. Работает только локально и в тестах, и только при webpayment.driver = fake.
 */
class FakeBankController extends Controller
{
    /** Страница оплаты: сводка по счёту и кнопки «Оплатить» / «Отказаться». */
    public function start(Request $request): View
    {
        $this->ensureAvailable();

        $f = $request->all();
        $login = (string) config('webpayment.merchant_login');
        $pass = (string) config('webpayment.merchant_pass');

        $expected = Signature::request(
            (string) ($f['MerchantLogin'] ?? ''),
            (string) ($f['nivid'] ?? ''),
            ($f['IsTest'] ?? '') === '1',
            (int) ($f['RequestSum'] ?? 0),
            (string) ($f['RequestCurrCode'] ?? ''),
            (string) ($f['Desc'] ?? ''),
            $pass,
        );

        $signatureOk = Signature::equals($expected, (string) ($f['SignatureValue'] ?? ''));
        $loginOk = $login !== '' && ($f['MerchantLogin'] ?? '') === $login;
        $payment = Payment::query()->where('invoice_id', (string) ($f['nivid'] ?? ''))->first();

        return view('dev.fake-bank', [
            'fields' => $f,
            'payment' => $payment,
            'signatureOk' => $signatureOk,
            'loginOk' => $loginOk,
        ]);
    }

    /** Решение на «банковской» странице. */
    public function complete(Request $request, ResultHandler $handler): RedirectResponse
    {
        $this->ensureAvailable();

        $payment = Payment::query()->where('invoice_id', (string) $request->input('nivid'))->firstOrFail();
        $pay = $request->input('decision') === 'pay';

        $payment->forceFill(['payload' => array_merge($payment->payload ?? [], [
            'fake' => [
                'state' => $pay ? BankState::PAID : BankState::CANCELLED,
                'sum' => $payment->amount,
                'currency' => $payment->currency,
                'istest' => $payment->is_test,
                'rrn' => $pay ? (string) random_int(1_000_000_000, 9_999_999_999) : null,
                'lastdgt' => $pay ? '4242' : null,
            ],
        ])])->save();

        $pass = (string) config('webpayment.merchant_pass');
        $date = now()->format('dmY');
        $status = $pay ? 'paid' : 'fail';

        // Оповещение ResultURL — ровно с теми параметрами и подписью, что присылает настоящий банк (раздел 4).
        $params = $pay
            ? [
                'status' => 'paid',
                'paymentsum' => (string) $payment->amount,
                'paymentcurrency' => $payment->currency,
                'invoiceid' => $payment->invoice_id,
                'date' => $date,
                'signature' => Signature::paid($payment->invoice_id, $status, (string) $payment->amount, $payment->currency, $date, $pass),
                'istest' => $payment->is_test ? '1' : '0',
                'rrn' => $payment->payload['fake']['rrn'],
                'lastdgt' => $payment->payload['fake']['lastdgt'],
            ]
            : [
                'status' => 'fail',
                'invoiceid' => $payment->invoice_id,
                'date' => $date,
                'signature' => Signature::failed($payment->invoice_id, $status, $date, $pass),
                'istest' => $payment->is_test ? '1' : '0',
            ];

        // Вызываем обработчик напрямую, а не по HTTP: встроенный сервер разработки однопоточный и завис бы на запросе к самому себе.
        $handler->handle($params);

        return redirect()->route($pay ? 'account.subscription.success' : 'account.subscription.fail', ['invoiceid' => $payment->invoice_id]);
    }

    private function ensureAvailable(): void
    {
        abort_unless(
            app()->environment(['local', 'testing']) && config('webpayment.driver') === 'fake' && ! app()->isProduction(),
            404,
        );
    }
}
