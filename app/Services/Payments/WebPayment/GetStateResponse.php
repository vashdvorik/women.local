<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

/**
 * Разбор ответа веб-сервиса банка (раздел 6 документа).
 *
 * Веб-сервис отвечает строкой Base64 — это XML вида
 *     <envelope><response>XML ответа (Base64)</response><signature>подпись содержимого (Base64)</signature></envelope>
 * а внутри response — XML с блоками Result (Code: 1 — успех, 0 — ошибка; Description) и, при успехе,
 * StateInfo (state, sum, currency, istest, ...).
 *
 * Ответу банка не доверяем вслепую: любой сбой формата — GatewayException, а при заданном сертификате банка
 * дополнительно проверяется электронная подпись (BankSignature).
 */
final class GetStateResponse
{
    /**
     * @param  string  $soapBody  тело ответа SOAP (XML-конверт SOAP 1.1)
     *
     * @throws GatewayException
     */
    public static function parse(string $soapBody, ?BankSignature $signature = null): BankState
    {
        $result = self::soapResult($soapBody);

        $envelopeXml = base64_decode($result, true);
        if ($envelopeXml === false) {
            throw new GatewayException('Ответ банка не является строкой Base64.');
        }

        $envelope = self::xml($envelopeXml);
        $responseB64 = trim((string) ($envelope->response ?? ''));
        $signatureB64 = trim((string) ($envelope->signature ?? ''));

        $responseXml = $responseB64 === '' ? false : base64_decode($responseB64, true);
        if ($responseXml === false) {
            throw new GatewayException('В ответе банка нет узла response в формате Base64.');
        }

        if ($signature !== null && $signature->isConfigured()) {
            $given = base64_decode($signatureB64, true);

            if ($given === false || ! $signature->verify($responseB64, $responseXml, $given)) {
                throw new GatewayException('Электронная подпись банка в ответе не прошла проверку.');
            }
        }

        $response = self::xml($responseXml);
        $code = trim((string) ($response->Result->Code ?? ''));
        $description = trim((string) ($response->Result->Description ?? ''));

        if ($code !== '1') {
            return BankState::missing($description !== '' ? $description : 'Банк не вернул данные по счёту.');
        }

        $info = $response->StateInfo ?? null;
        if ($info === null) {
            throw new GatewayException('В успешном ответе банка нет блока StateInfo.');
        }

        $raw = [];
        foreach ($info->children() as $child) {
            if (count($child->children()) === 0) {
                $raw[strtolower($child->getName())] = trim((string) $child);
            }
        }

        return new BankState(
            found: true,
            state: isset($raw['state']) && ctype_digit($raw['state']) ? (int) $raw['state'] : null,
            sumKopecks: isset($raw['sum']) && ctype_digit($raw['sum']) ? (int) $raw['sum'] : null,
            currency: $raw['currency'] ?? null,
            isTest: isset($raw['istest']) ? in_array(strtolower($raw['istest']), ['1', 'true'], true) : null,
            invoiceId: $raw['invoiceid'] ?? null,
            rrn: ($raw['rrn'] ?? '') !== '' ? $raw['rrn'] : null,
            lastDigits: ($raw['lastdgt'] ?? '') !== '' ? $raw['lastdgt'] : null,
            message: $raw['statedescription'] ?? null,
            raw: $raw,
        );
    }

    /** Достаёт строку GetStateResult из конверта SOAP; SOAP Fault превращает в исключение. */
    private static function soapResult(string $soapBody): string
    {
        $xml = self::xml($soapBody);
        $body = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body ?? null;

        if ($body === null) {
            throw new GatewayException('Ответ банка не похож на SOAP-конверт.');
        }

        if (isset($body->children('http://schemas.xmlsoap.org/soap/envelope/')->Fault)) {
            throw new GatewayException('Веб-сервис банка вернул ошибку SOAP.');
        }

        $response = $body->children(config('webpayment.service_namespace'))->GetStateResponse ?? null;
        $result = $response !== null ? trim((string) $response->GetStateResult) : '';

        if ($result === '') {
            throw new GatewayException('В ответе банка нет GetStateResult.');
        }

        return $result;
    }

    /** XML из внешнего источника: без DOCTYPE (защита от XXE и «взрыва» сущностей) и без сетевых запросов. */
    private static function xml(string $text): \SimpleXMLElement
    {
        if (stripos($text, '<!DOCTYPE') !== false || stripos($text, '<!ENTITY') !== false) {
            throw new GatewayException('В ответе банка недопустимое объявление DOCTYPE.');
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($text, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($xml === false) {
            throw new GatewayException('Ответ банка — некорректный XML.');
        }

        return $xml;
    }
}
