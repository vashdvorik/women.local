<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Настоящий Агропромбанк. Проверка счёта — вызов GetState веб-сервиса (SOAP 1.1, раздел 6.1 документа).
 *
 * SOAP собирается вручную, без расширения php-soap: на хостинге его может не быть, а вызов один. Имя операции,
 * пространство имён и SOAPAction взяты из WSDL банка (…AgentService.asmx?WSDL).
 *
 * Адрес запроса содержит пароль торговца только внутри MD5-подписи; сами MerchantPass и токен бота в журналы
 * не попадают: исключения клиента HTTP здесь перехватываются и заменяются безопасным текстом.
 */
final class BankGateway implements Gateway
{
    public function __construct(private readonly BankSignature $bankSignature) {}

    public function driver(): string
    {
        return 'bank';
    }

    public function startUrl(Payment $payment): string
    {
        return (string) config('webpayment.start_url');
    }

    public function getState(Payment $payment): BankState
    {
        $login = (string) config('webpayment.merchant_login');
        $pass = (string) config('webpayment.merchant_pass');

        if ($login === '' || $pass === '') {
            throw new GatewayException('Не заданы WEBPAYMENT_MERCHANT_LOGIN и WEBPAYMENT_MERCHANT_PASS.');
        }

        $namespace = (string) config('webpayment.service_namespace');
        $signature = Signature::service($login, $payment->invoice_id, $pass);

        $envelope = '<?xml version="1.0" encoding="utf-8"?>'
            .'<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
            .'<soap:Body>'
            .'<GetState xmlns="'.e($namespace).'">'
            .'<merchantId>'.e($login).'</merchantId>'
            .'<invoiceId>'.e($payment->invoice_id).'</invoiceId>'
            .'<signature>'.e($signature).'</signature>'
            .'</GetState>'
            .'</soap:Body>'
            .'</soap:Envelope>';

        try {
            $response = Http::timeout((int) config('webpayment.timeout', 15))
                ->withHeaders(['SOAPAction' => '"'.$namespace.'GetState"'])
                ->withBody($envelope, 'text/xml; charset=utf-8')
                ->post((string) config('webpayment.service_url'));
        } catch (ConnectionException) {
            throw new GatewayException('Банк не ответил на запрос состояния счёта.');
        }

        // ASMX отдаёт SOAP Fault с кодом 500: тело всё равно разбираем, там причина.
        if ($response->status() !== 200 && $response->status() !== 500) {
            throw new GatewayException("Банк ответил HTTP {$response->status()} на запрос состояния счёта.");
        }

        return GetStateResponse::parse($response->body(), $this->bankSignature);
    }
}
