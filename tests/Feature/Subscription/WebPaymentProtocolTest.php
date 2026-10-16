<?php

namespace Tests\Feature\Subscription;

use App\Models\Payment;
use App\Services\Payments\WebPayment\BankGateway;
use App\Services\Payments\WebPayment\BankSignature;
use App\Services\Payments\WebPayment\BankState;
use App\Services\Payments\WebPayment\GatewayException;
use App\Services\Payments\WebPayment\GetStateResponse;
use App\Services\Payments\WebPayment\Signature;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Протокол Web-платежа Агропромбанка (docs/WebPayment-Документация.md): формулы подписей, разбор ответа GetState и
 * сам запрос к веб-сервису. Строки подписей в тестах собраны вручную по документу, а не вызовом тех же функций.
 */
class WebPaymentProtocolTest extends TestCase
{
    use SubscriptionHelpers;

    private const PASS = 'secret-pass';

    // ---------------------------------------------------------------- подписи

    public function test_request_signature_follows_the_documented_field_order(): void
    {
        // Раздел 3: MD5(MerchantLogin:nivid:istest:RequestSum:RequestCurrCode:Desc:MerchantPass)
        $expected = md5('000123:123456:0:1524:000:Account 123456:'.self::PASS);

        $this->assertSame($expected, Signature::request('000123', '123456', false, 1524, '000', 'Account 123456', self::PASS));
        $this->assertSame(md5('000123:123456:1:1524:000:Account 123456:'.self::PASS), Signature::request('000123', '123456', true, 1524, '000', 'Account 123456', self::PASS));
        $this->assertNotSame($expected, Signature::request('000123', '123456', false, 1525, '000', 'Account 123456', self::PASS), 'сумма входит в подпись');
    }

    public function test_notification_and_service_signatures_follow_the_documented_field_order(): void
    {
        // Разделы 4 и 5: успех — invoiceid:status:paymentsum:paymentcurrency:date:MerchantPass, отказ — invoiceid:status:date:MerchantPass.
        $this->assertSame(md5('123456:paid:1524:000:01012025:'.self::PASS), Signature::paid('123456', 'paid', '1524', '000', '01012025', self::PASS));
        $this->assertSame(md5('123456:fail:01012025:'.self::PASS), Signature::failed('123456', 'fail', '01012025', self::PASS));
        // Раздел 6.1: MD5(MerchantId:InvoiceId:MerchantPass)
        $this->assertSame(md5('000123:123456:'.self::PASS), Signature::service('000123', '123456', self::PASS));
    }

    public function test_signature_comparison_ignores_case_but_rejects_everything_else(): void
    {
        $sig = md5('x');

        $this->assertTrue(Signature::equals($sig, $sig));
        $this->assertTrue(Signature::equals($sig, strtoupper($sig)));
        $this->assertTrue(Signature::equals($sig, "  {$sig}  "));
        $this->assertFalse(Signature::equals($sig, md5('y')));
        $this->assertFalse(Signature::equals($sig, ''));
        $this->assertFalse(Signature::equals($sig, null));
    }

    // ---------------------------------------------------------------- разбор ответа GetState

    public function test_a_paid_state_is_parsed_from_the_documented_envelope(): void
    {
        $state = GetStateResponse::parse($this->bankSoapReply([
            'state' => '1', 'statedescription' => 'Оплачен', 'istest' => '0', 'sum' => '60000', 'currency' => '000',
            'invoiceid' => 'WH00000012', 'rrn' => '12345678910', 'lastdgt' => '0123', 'date' => '01.01.2025',
        ]));

        $this->assertTrue($state->found);
        $this->assertTrue($state->isPaid());
        $this->assertSame(BankState::PAID, $state->state);
        $this->assertSame(60000, $state->sumKopecks);
        $this->assertSame('000', $state->currency);
        $this->assertFalse($state->isTest);
        $this->assertSame('WH00000012', $state->invoiceId);
        $this->assertSame('12345678910', $state->rrn);
        $this->assertSame('0123', $state->lastDigits);
    }

    public function test_every_state_code_from_the_document_is_understood(): void
    {
        foreach ([0 => [false, false], 1 => [true, false], 2 => [false, true], 3 => [false, true], 4 => [false, true]] as $code => [$paid, $dead]) {
            $state = GetStateResponse::parse($this->bankSoapReply(['state' => (string) $code, 'sum' => '100', 'currency' => '000', 'istest' => '1']));

            $this->assertSame($paid, $state->isPaid(), "state {$code}: оплачен");
            $this->assertSame($dead, $state->isDead(), "state {$code}: закончился неудачей");
            $this->assertTrue($state->isTest);
        }
    }

    public function test_code_zero_means_the_bank_does_not_know_the_invoice(): void
    {
        $state = GetStateResponse::parse($this->bankSoapReply([], code: 0));

        $this->assertFalse($state->found);
        $this->assertFalse($state->isPaid());
        $this->assertSame('Счёт не найден', $state->message);
    }

    #[DataProvider('brokenReplies')]
    public function test_a_reply_that_cannot_be_trusted_is_an_error_not_a_payment(string $body): void
    {
        $this->expectException(GatewayException::class);

        GetStateResponse::parse($body);
    }

    public static function brokenReplies(): array
    {
        $soap = fn (string $result) => '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            .'<GetStateResponse xmlns="http://webpayment.services.agroprombank.com/"><GetStateResult>'.$result.'</GetStateResult></GetStateResponse></soap:Body></soap:Envelope>';

        return [
            'не XML' => ['<html>502 Bad Gateway</html'],
            'SOAP Fault' => ['<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><soap:Fault><faultcode>soap:Server</faultcode></soap:Fault></soap:Body></soap:Envelope>'],
            'пустой результат' => [$soap('')],
            'результат не Base64' => [$soap('%%% not base64 %%%')],
            'внутри не XML' => [$soap(base64_encode('plain text'))],
            'нет узла response' => [$soap(base64_encode('<envelope><signature>eA==</signature></envelope>'))],
            'response не Base64' => [$soap(base64_encode('<envelope><response>%%%</response></envelope>'))],
            'успех без StateInfo' => [$soap(base64_encode('<envelope><response>'.base64_encode('<R><Result><Code>1</Code></Result></R>').'</response></envelope>'))],
            'DOCTYPE (атака XXE)' => [$soap(base64_encode('<envelope><response>'.base64_encode('<!DOCTYPE r [<!ENTITY x SYSTEM "file:///etc/passwd">]><R/>').'</response></envelope>'))],
            'DOCTYPE в самом конверте' => ['<?xml version="1.0"?><!DOCTYPE s [<!ENTITY a "aaaa">]><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"/>'],
        ];
    }

    // ---------------------------------------------------------------- электронная подпись банка

    /** Новый ключ RSA. На Windows у PHP может не быть openssl.cnf, поэтому конфигурация передаётся явно. */
    private function newKey(): \OpenSSLAsymmetricKey
    {
        $config = tempnam(sys_get_temp_dir(), 'openssl-cnf-');
        file_put_contents($config, "[req]\ndistinguished_name=req_dn\n[req_dn]\n");

        $key = openssl_pkey_new(['config' => $config, 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        @unlink($config);

        $this->assertNotFalse($key, 'OpenSSL не смог создать тестовый ключ: '.openssl_error_string());

        return $key;
    }

    private function keyPair(): array
    {
        $key = $this->newKey();
        $public = openssl_pkey_get_details($key)['key'];
        $file = tempnam(sys_get_temp_dir(), 'bank-cert-');
        file_put_contents($file, $public);

        return [$key, $public, $file];
    }

    public function test_the_bank_signature_is_checked_when_a_certificate_is_configured(): void
    {
        [$key, , $cert] = $this->keyPair();
        $sign = function (string $xml) use ($key): string {
            openssl_sign($xml, $signature, $key, 'sha256');

            return $signature;
        };

        $reply = $this->bankSoapReply(['state' => '1', 'sum' => '100', 'currency' => '000'], sign: $sign);
        $verifier = new BankSignature($cert, 'sha256');

        $this->assertTrue(GetStateResponse::parse($reply, $verifier)->isPaid());

        // Подмена содержимого (сумма) при прежней подписи: ответ отвергается.
        $tampered = $this->bankSoapReply(['state' => '1', 'sum' => '1', 'currency' => '000'], sign: fn () => $sign($this->xmlWith(['state' => '1', 'sum' => '100', 'currency' => '000'])));
        $this->expectException(GatewayException::class);
        GetStateResponse::parse($tampered, $verifier);

        @unlink($cert);
    }

    public function test_a_signature_from_a_different_key_is_rejected(): void
    {
        [, , $cert] = $this->keyPair();
        $other = $this->newKey();

        $reply = $this->bankSoapReply(['state' => '1', 'sum' => '100', 'currency' => '000'], sign: function (string $xml) use ($other): string {
            openssl_sign($xml, $signature, $other, 'sha256');

            return $signature;
        });

        $this->expectException(GatewayException::class);

        try {
            GetStateResponse::parse($reply, new BankSignature($cert, 'sha256'));
        } finally {
            @unlink($cert);
        }
    }

    public function test_without_a_certificate_the_signature_check_is_skipped_and_the_unsigned_reply_is_still_parsed(): void
    {
        $this->assertFalse((new BankSignature(null))->isConfigured());
        $this->assertTrue(GetStateResponse::parse($this->bankSoapReply(['state' => '1', 'sum' => '100', 'currency' => '000']), new BankSignature(null))->isPaid());
    }

    // ---------------------------------------------------------------- другие виды ответа и текст ошибки

    /** Конверт SOAP с произвольной строкой в GetStateResult (XML внутри экранируется, как это делает ASMX). */
    private function soapWith(string $result): string
    {
        return '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            .'<GetStateResponse xmlns="http://webpayment.services.agroprombank.com/"><GetStateResult>'.htmlspecialchars($result, ENT_XML1).'</GetStateResult></GetStateResponse></soap:Body></soap:Envelope>';
    }

    private function paidResponseXml(): string
    {
        return $this->xmlWith(['state' => '1', 'statedescription' => 'Оплачен', 'sum' => '100000', 'currency' => '000', 'istest' => '1', 'invoiceid' => 'WHT261016LONDXZ']);
    }

    #[DataProvider('equivalentReplies')]
    public function test_the_same_answer_is_understood_in_every_shape_the_bank_may_use(callable $shape): void
    {
        $xml = $this->paidResponseXml();
        $envelope = fn (string $response): string => '<envelope><response>'.$response.'</response><signature>'.base64_encode('sig').'</signature></envelope>';

        $state = GetStateResponse::parse($this->soapWith($shape($xml, $envelope)));

        $this->assertTrue($state->isPaid());
        $this->assertSame(100000, $state->sumKopecks);
        $this->assertSame('WHT261016LONDXZ', $state->invoiceId);
    }

    public static function equivalentReplies(): array
    {
        return [
            'как в документе: Base64 от конверта с Base64' => [fn ($xml, $env) => base64_encode($env(base64_encode($xml)))],
            'конверт сразу XML, response в Base64' => [fn ($xml, $env) => $env(base64_encode($xml))],
            'конверт сразу XML, response тоже XML' => [fn ($xml, $env) => $env(htmlspecialchars($xml, ENT_XML1))],
            'без конверта, сразу XML ответа' => [fn ($xml, $env) => $xml],
            'без конверта, XML ответа в Base64' => [fn ($xml, $env) => base64_encode($xml)],
            'Base64 с переносами строк' => [fn ($xml, $env) => chunk_split(base64_encode($env(base64_encode($xml))), 64, "\r\n")],
            'Base64 без завершающих «=»' => [fn ($xml, $env) => rtrim(base64_encode($env(base64_encode($xml))), '=')],
            'Base64 с алфавитом URL (- и _)' => [fn ($xml, $env) => strtr(base64_encode($env(base64_encode($xml))), '+/', '-_')],
            'XML с BOM' => [fn ($xml, $env) => "\xEF\xBB\xBF".$xml],
        ];
    }

    public function test_with_a_certificate_an_answer_without_the_signed_envelope_is_not_trusted(): void
    {
        [, , $cert] = $this->keyPair();

        try {
            $this->expectException(GatewayException::class);
            $this->expectExceptionMessage('без электронной подписи');

            GetStateResponse::parse($this->soapWith($this->paidResponseXml()), new BankSignature($cert, 'sha256'));
        } finally {
            @unlink($cert);
        }
    }

    public function test_an_answer_without_a_result_block_is_an_error_and_never_means_invoice_not_found(): void
    {
        // «Счёт не найден» со временем превращается в «просрочен», поэтому мусорный XML не должен читаться как Code=0.
        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('нет блока Result');

        GetStateResponse::parse($this->soapWith('<hello>world</hello>'));
    }

    #[DataProvider('unreadableReplies')]
    public function test_the_error_shows_what_the_bank_actually_sent(string $body, string $expected): void
    {
        try {
            GetStateResponse::parse($body);
            $this->fail('Ожидалось исключение');
        } catch (GatewayException $e) {
            $this->assertStringContainsString($expected, $e->getMessage());
        }
    }

    public static function unreadableReplies(): array
    {
        $soap = fn (string $result) => '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            .'<GetStateResponse xmlns="http://webpayment.services.agroprombank.com/"><GetStateResult>'.htmlspecialchars($result, ENT_XML1).'</GetStateResult></GetStateResponse></soap:Body></soap:Envelope>';

        return [
            'текст вместо Base64' => [$soap('Merchant not found: 000123'), 'ни строкой Base64, ни XML'],
            'ответ банка словами' => [$soap('Merchant not found: 000123'), 'Merchant not found: 000123'],
            'после Base64 не XML' => [$soap(base64_encode('Invalid signature')), 'не XML'],
            'причина SOAP Fault' => ['<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><soap:Fault><faultcode>soap:Server</faultcode><faultstring>Merchant is blocked</faultstring></soap:Fault></soap:Body></soap:Envelope>', 'Merchant is blocked'],
            'не SOAP' => ['<html><body>502 Bad Gateway nginx</body></html>', '502 Bad Gateway nginx'],
            'битый XML' => ['<GetStateResult>oops', 'oops'],
        ];
    }

    public function test_the_error_text_is_short_single_line_and_hides_signatures_and_tokens(): void
    {
        $noise = "line one\n\n   line two ".str_repeat('long ', 200);
        $reply = $this->soapWith('<r><signature>TOP-SECRET-SIGNATURE</signature><token>TOP-SECRET-TOKEN</token>'.$noise.'</r>');

        try {
            GetStateResponse::parse($reply);
            $this->fail('Ожидалось исключение');
        } catch (GatewayException $e) {
            $this->assertStringNotContainsString('TOP-SECRET', $e->getMessage());
            $this->assertStringNotContainsString("\n", $e->getMessage());
            $this->assertStringContainsString('line one line two', $e->getMessage());
            $this->assertLessThan(500, mb_strlen($e->getMessage()));
        }
    }

    public function test_excerpt_cleans_control_characters_and_hides_secrets_in_plain_and_escaped_xml(): void
    {
        $this->assertSame('a b c d', GetStateResponse::excerpt("a\x07b\x00c\n d"));
        $this->assertSame('', GetStateResponse::excerpt(''));
        $this->assertStringNotContainsString("\xFF", GetStateResponse::excerpt("ok \xFF\xFE broken utf-8"));

        foreach (['<signature>SECRET</signature>', '&lt;signature&gt;SECRET&lt;/signature&gt;', '<Token id="1">SECRET</Token>', '&lt;token&gt;SECRET&lt;/token&gt;'] as $text) {
            $excerpt = GetStateResponse::excerpt('before '.$text.' after');

            $this->assertStringNotContainsString('SECRET', $excerpt, $text);
            $this->assertStringContainsString('before', $excerpt);
            $this->assertStringContainsString('after', $excerpt);
        }
    }

    /** XML ответа ровно в том виде, в каком его собирает bankSoapReply (нужен, чтобы подписать «честное» содержимое). */
    private function xmlWith(array $stateInfo): string
    {
        $info = '';
        foreach ($stateInfo as $name => $value) {
            $info .= "<{$name}>{$value}</{$name}>";
        }

        return '<?xml version="1.0" encoding="utf-8"?><OperationStateResponse><Result><Code>1</Code><Description>OK</Description></Result>'
            ."<StateInfo>{$info}</StateInfo></OperationStateResponse>";
    }

    // ---------------------------------------------------------------- запрос к веб-сервису

    private function bankPayment(): Payment
    {
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => '000123', 'webpayment.merchant_pass' => self::PASS]);

        return new Payment(['invoice_id' => 'WHT00000012', 'amount' => 60000, 'currency' => '000', 'is_test' => true]);
    }

    public function test_get_state_is_sent_as_the_soap_call_described_in_the_banks_wsdl(): void
    {
        $payment = $this->bankPayment();
        Http::fake(['*' => Http::response($this->paidSoapReply($payment), 200, ['Content-Type' => 'text/xml'])]);

        $state = (new BankGateway(new BankSignature(null)))->getState($payment);

        $this->assertTrue($state->isPaid());

        Http::assertSent(function (Request $request): bool {
            $body = $request->body();

            return $request->url() === 'https://ws.agroprombank.com/merchant/APB.SV.WebPayment.AgentService.asmx'
                && $request->method() === 'POST'
                && $request->header('SOAPAction') === ['"http://webpayment.services.agroprombank.com/GetState"']
                && str_contains($request->header('Content-Type')[0], 'text/xml')
                && str_contains($body, '<GetState xmlns="http://webpayment.services.agroprombank.com/">')
                && str_contains($body, '<merchantId>000123</merchantId>')
                && str_contains($body, '<invoiceId>WHT00000012</invoiceId>')
                // Подпись GetState: MD5(MerchantId:InvoiceId:MerchantPass); сам пароль в запрос не попадает.
                && str_contains($body, '<signature>'.md5('000123:WHT00000012:'.self::PASS).'</signature>')
                && ! str_contains($body, self::PASS);
        });
    }

    public function test_transport_failures_are_errors_and_never_leak_the_merchant_password(): void
    {
        $payment = $this->bankPayment();
        $gateway = new BankGateway(new BankSignature(null));

        foreach ([
            'нет связи' => fn () => Http::fake(fn () => throw new ConnectionException('cURL error 28: timeout for https://ws.agroprombank.com?pass='.self::PASS)),
            'HTTP 502' => fn () => Http::fake(['*' => Http::response('Bad Gateway', 502)]),
            'SOAP Fault 500' => fn () => Http::fake(['*' => Http::response('<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><soap:Fault><faultcode>x</faultcode></soap:Fault></soap:Body></soap:Envelope>', 500)]),
        ] as $name => $arrange) {
            $arrange();

            try {
                $gateway->getState($payment);
                $this->fail("{$name}: ожидалось исключение");
            } catch (GatewayException $e) {
                $this->assertStringNotContainsString(self::PASS, $e->getMessage(), $name);
            }
        }
    }

    public function test_without_credentials_the_bank_is_not_called_at_all(): void
    {
        Http::fake();
        config(['webpayment.merchant_login' => null, 'webpayment.merchant_pass' => null]);

        try {
            (new BankGateway(new BankSignature(null)))->getState(new Payment(['invoice_id' => 'X']));
            $this->fail('Ожидалось исключение');
        } catch (GatewayException) {
            Http::assertNothingSent();
        }
    }
}
