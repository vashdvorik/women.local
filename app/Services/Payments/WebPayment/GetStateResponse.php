<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

/**
 * Разбор ответа веб-сервиса банка (раздел 6 документа).
 *
 * По документу веб-сервис отвечает строкой Base64 — это XML вида
 *     <envelope><response>XML ответа (Base64)</response><signature>подпись содержимого (Base64)</signature></envelope>
 * а внутри response — XML с блоками Result (Code: 1 — успех, 0 — ошибка; Description) и, при успехе,
 * StateInfo (state, sum, currency, istest, ...).
 *
 * Документ описывает формат неоднозначно, поэтому каждый слой принимается в двух видах: Base64 (как в документе)
 * или сразу XML. Принять можно и ответ без конверта: сразу XML с Result. Ответу банка не доверяем вслепую: любой сбой
 * формата — GatewayException, причём в тексте ошибки есть начало ответа банка, чтобы администратор видел, что именно
 * пришло. Если задан сертификат банка (BankSignature), ответ обязан быть в конверте с подписью, и подпись проверяется.
 */
final class GetStateResponse
{
    /** Сколько символов ответа банка показывать в тексте ошибки. */
    private const EXCERPT_LENGTH = 300;

    /**
     * @param  string  $soapBody  тело ответа SOAP (XML-конверт SOAP 1.1)
     *
     * @throws GatewayException
     */
    public static function parse(string $soapBody, ?BankSignature $signature = null): BankState
    {
        $result = self::soapResult($soapBody);
        $checkSignature = $signature !== null && $signature->isConfigured();

        $document = self::xml(self::xmlText($result, 'Ответ банка'));
        $enveloped = strtolower($document->getName()) === 'envelope';

        if ($enveloped) {
            $responseText = trim((string) ($document->response ?? ''));

            if ($responseText === '') {
                throw new GatewayException('В ответе банка нет узла response. Начало ответа: «'.self::excerpt($result).'».');
            }

            $responseXml = self::xmlText($responseText, 'Узел response');

            if ($checkSignature) {
                $given = self::base64(trim((string) ($document->signature ?? '')));

                if ($given === null || ! $signature->verify($responseText, $responseXml, $given)) {
                    throw new GatewayException('Электронная подпись банка в ответе не прошла проверка.');
                }
            }

            $response = self::xml($responseXml);
        } else {
            // Без конверта подписи нет, а раз сертификат задан, значит, подпись обязательна: такому ответу не верим.
            if ($checkSignature) {
                throw new GatewayException('Ответ банка пришёл без электронной подписи, а сертификат банка задан.');
            }

            $response = $document;
        }

        $code = trim((string) ($response->Result->Code ?? ''));
        $description = trim((string) ($response->Result->Description ?? ''));

        if ($code === '') {
            throw new GatewayException('В ответе банка нет блока Result. Начало ответа: «'.self::excerpt($result).'».');
        }

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
            throw new GatewayException('Ответ банка не похож на SOAP-конверт. Начало ответа: «'.self::excerpt($soapBody).'».');
        }

        if (isset($body->children('http://schemas.xmlsoap.org/soap/envelope/')->Fault)) {
            // Узлы внутри Fault (faultcode, faultstring) в SOAP 1.1 пишутся без пространства имён.
            $fault = trim((string) $body->children('http://schemas.xmlsoap.org/soap/envelope/')->Fault->children('')->faultstring);

            throw new GatewayException('Веб-сервис банка вернул ошибку SOAP'.($fault !== '' ? ': «'.self::excerpt($fault).'»' : '').'.');
        }

        $response = $body->children(config('webpayment.service_namespace'))->GetStateResponse ?? null;
        $result = $response !== null ? trim((string) $response->GetStateResult) : '';

        if ($result === '') {
            throw new GatewayException('В ответе банка нет GetStateResult. Начало ответа: «'.self::excerpt($soapBody).'».');
        }

        return $result;
    }

    /**
     * Текст XML из значения, которое банк прислал либо сразу XML, либо строкой Base64.
     *
     * @throws GatewayException
     */
    private static function xmlText(string $value, string $what): string
    {
        $value = self::withoutBom(trim($value));

        if (str_starts_with($value, '<')) {
            return $value;
        }

        $decoded = self::base64($value);

        if ($decoded === null) {
            throw new GatewayException("{$what} не является ни строкой Base64, ни XML. Начало ответа: «".self::excerpt($value).'».');
        }

        $decoded = self::withoutBom(trim($decoded));

        if (! str_starts_with($decoded, '<')) {
            throw new GatewayException("{$what} после раскодирования Base64 не XML. Начало исходного ответа: «".self::excerpt($value).'».');
        }

        return $decoded;
    }

    /** Base64 в строгом разборе; принимает и «URL-безопасный» алфавит (- и _) и строку без завершающих «=». */
    private static function base64(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $value = strtr($value, '-_', '+/');
        $padding = strlen(preg_replace('/\s+/', '', $value) ?? '') % 4;

        if ($padding > 1) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($value, true);

        return $decoded === false ? null : $decoded;
    }

    private static function withoutBom(string $text): string
    {
        return str_starts_with($text, "\xEF\xBB\xBF") ? substr($text, 3) : $text;
    }

    /**
     * Начало ответа банка для текста ошибки: одной строкой, без управляющих символов и без подписи и токенов.
     * Видит только администратор; чужой текст при выводе экранируется.
     */
    public static function excerpt(string $text): string
    {
        $text = mb_scrub($text, 'UTF-8');
        // Узлы подписи и токена прячем и в обычном виде, и в экранированном (ASMX экранирует XML внутри GetStateResult).
        $text = preg_replace('#(<|&lt;)(signature|token|rectoken|recurrenttoken)\b([^>]*?)(>|&gt;).*?(<|&lt;)/\2(>|&gt;)#is', '$1$2$4…$5/$2$6', $text) ?? '';
        $text = preg_replace('/[\p{Cc}\p{Cf}\s]+/u', ' ', $text) ?? '';

        return mb_strimwidth(trim($text), 0, self::EXCERPT_LENGTH, '…', 'UTF-8');
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
            throw new GatewayException('Ответ банка — некорректный XML. Начало ответа: «'.self::excerpt($text).'».');
        }

        return $xml;
    }
}
