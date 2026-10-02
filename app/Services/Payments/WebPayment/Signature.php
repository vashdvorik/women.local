<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

/**
 * Подписи Web-платежа Агропромбанка: MD5 от строки параметров через двоеточие, в конце — пароль торговца
 * (MerchantPass). Порядок и формат параметров строго такой, как в документе банка
 * (docs/WebPayment-Документация.md, разделы 3–6): любое отличие даёт неверную подпись.
 */
final class Signature
{
    /** Запрос на оплату (раздел 3): MerchantLogin:nivid:istest:RequestSum:RequestCurrCode:Desc:MerchantPass. */
    public static function request(
        string $merchantLogin,
        string $invoiceId,
        bool $isTest,
        int $sumKopecks,
        string $currencyCode,
        string $description,
        string $merchantPass,
    ): string {
        return self::md5([$merchantLogin, $invoiceId, $isTest ? '1' : '0', (string) $sumKopecks, $currencyCode, $description, $merchantPass]);
    }

    /** Оповещение об успешной оплате (разделы 4 и 5): invoiceid:status:paymentsum:paymentcurrency:date:MerchantPass. */
    public static function paid(string $invoiceId, string $status, string $sum, string $currency, string $date, string $merchantPass): string
    {
        return self::md5([$invoiceId, $status, $sum, $currency, $date, $merchantPass]);
    }

    /** Оповещение о неуспешной оплате (разделы 4 и 5): invoiceid:status:date:MerchantPass. */
    public static function failed(string $invoiceId, string $status, string $date, string $merchantPass): string
    {
        return self::md5([$invoiceId, $status, $date, $merchantPass]);
    }

    /** Запросы к веб-сервису, например GetState (раздел 6.1): MerchantId:InvoiceId:MerchantPass. */
    public static function service(string $merchantId, string $invoiceId, string $merchantPass): string
    {
        return self::md5([$merchantId, $invoiceId, $merchantPass]);
    }

    /** Сравнение без утечки по времени и без учёта регистра букв (md5 бывает записан и заглавными). */
    public static function equals(string $expected, ?string $given): bool
    {
        return $given !== null && $given !== '' && hash_equals(strtolower($expected), strtolower(trim($given)));
    }

    /** @param list<string> $parts */
    private static function md5(array $parts): string
    {
        return md5(implode(':', $parts));
    }
}
