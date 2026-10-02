<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

/**
 * Проверка электронной подписи банка в ответах веб-сервиса (раздел 6 документа: «Все ответы банка подписываются
 * электронной подписью», сертификат — на сайте банка).
 *
 * Документ не называет алгоритм и то, что именно подписывается (строка Base64 узла response или расшифрованный
 * XML). Поэтому алгоритм настраивается (webpayment.bank_signature_algo), а проверка принимает любой из двух
 * вариантов данных. Пока сертификат не задан (webpayment.bank_cert), проверка пропускается, и подлинность ответа
 * держится на HTTPS-соединении с узлом банка.
 */
final class BankSignature
{
    public function __construct(
        private readonly ?string $certPath = null,
        private readonly string $algorithm = 'sha256',
    ) {}

    public static function fromConfig(): self
    {
        $path = config('webpayment.bank_cert');

        return new self(is_string($path) && $path !== '' ? $path : null, (string) config('webpayment.bank_signature_algo', 'sha256'));
    }

    public function isConfigured(): bool
    {
        return $this->certPath !== null;
    }

    public function verify(string $responseBase64, string $responseXml, string $signature): bool
    {
        if ($this->certPath === null || ! is_file($this->certPath)) {
            return false;
        }

        $key = openssl_pkey_get_public((string) file_get_contents($this->certPath));
        if ($key === false) {
            return false;
        }

        foreach ([$responseXml, $responseBase64] as $data) {
            if (openssl_verify($data, $signature, $key, $this->algorithm) === 1) {
                return true;
            }
        }

        return false;
    }
}
