<?php

namespace Tests\Feature\Subscription;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Payment;
use App\Services\Payments\WebPayment\PaymentReconciler;
use App\Services\Payments\WebPayment\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Nutgram\Laravel\Facades\Telegram;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Весь путь оплаты подписки: «Оплатить» в кабинете → страница банка → оповещение ResultURL → подтверждение через
 * GetState → тариф включён. И защита: поддельные оповещения, чужая сумма, повторы, недоступный банк.
 */
class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Telegram::fake();
        config(['webpayment.driver' => 'fake', 'webpayment.is_test' => true]);
        $this->registerFakeBank();
    }

    private function community(): BotUser
    {
        return BotUser::factory()->approved()->create(['full_name' => 'Анна Иванова', 'locale' => 'ru']);
    }

    // ---------------------------------------------------------------- «Оплатить»

    public function test_checkout_creates_an_invoice_with_the_price_from_settings_and_a_correct_signature(): void
    {
        $user = $this->community();

        $response = $this->asParticipant($user)
            ->post(route('account.subscription.checkout'), ['plan' => 'community', 'amount' => 1, 'RequestSum' => 1])
            ->assertOk();

        /** @var Payment $payment */
        $payment = Payment::firstOrFail();

        // Сумма — из настроек (600 руб. = 60000 коп.), а не из запроса браузера.
        $this->assertSame(60000, $payment->amount);
        $this->assertSame('000', $payment->currency);
        $this->assertSame('community', $payment->plan);
        $this->assertSame(12, $payment->months);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertTrue($payment->is_test, 'пока WEBPAYMENT_TEST не выключен, платежи тестовые');
        $this->assertSame('fake', $payment->driver);
        $this->assertSame($user->id, $payment->bot_user_id);
        $this->assertMatchesRegularExpression('/^WHT\d{6}[0-9A-Z]{6}$/', $payment->invoice_id);
        $this->assertLessThanOrEqual(20, strlen($payment->invoice_id), 'банк принимает номер счёта до 20 символов');

        // Форма для банка: названия полей и подпись — как в разделе 3 документа.
        $html = $response->getContent();
        $this->assertStringContainsString('action="'.url('/dev/fake-bank').'"', $html);

        $fields = [
            'MerchantLogin' => '000123',
            'RequestSum' => '60000',
            'RequestCurrCode' => '000',
            'nivid' => $payment->invoice_id,
            'IsTest' => '1',
            'LifeTime' => '30',
        ];
        foreach ($fields as $name => $value) {
            $this->assertStringContainsString('name="'.$name.'" value="'.$value.'"', $html, $name);
        }

        $desc = 'Womens Hub Community membership '.$payment->invoice_id.' tg'.$user->telegram_id;
        $this->assertStringContainsString('name="Desc" value="'.$desc.'"', $html);
        $this->assertStringContainsString(
            'name="SignatureValue" value="'.md5('000123:'.$payment->invoice_id.':1:60000:000:'.$desc.':local-fake-merchant-pass').'"',
            $html,
        );

        // Пароль торговца на страницу не попадает.
        $this->assertStringNotContainsString('local-fake-merchant-pass', $html);
    }

    public function test_the_description_in_the_signature_is_plain_ascii(): void
    {
        // Кодировка строки подписи в документе не указана, поэтому описание счёта нарочно только из ASCII.
        $payment = $this->checkoutFor($this->community(), Plan::Private);

        $this->assertMatchesRegularExpression('/^[\x20-\x7E]+$/', app(\App\Services\Payments\WebPayment\Checkout::class)->description($payment));
        $this->assertSame(2000000, $payment->amount);
    }

    public function test_a_second_click_reuses_the_unexpired_invoice(): void
    {
        $user = $this->community();

        $first = $this->checkoutFor($user, Plan::Community);
        $second = $this->checkoutFor($user, Plan::Community);
        $other = $this->checkoutFor($user, Plan::Private);

        $this->assertTrue($first->is($second));
        $this->assertFalse($first->is($other));
        $this->assertSame(2, Payment::count());
    }

    public function test_an_expired_invoice_is_not_reused(): void
    {
        $user = $this->community();
        $first = $this->checkoutFor($user, Plan::Community);
        $first->update(['expires_at' => now()->subMinute()]);

        $this->assertFalse($first->is($this->checkoutFor($user, Plan::Community)));
    }

    public function test_only_paid_plans_can_be_bought_and_garbage_is_rejected(): void
    {
        $user = $this->community();

        foreach (['open', 'enterprise', '', '../../etc'] as $plan) {
            $this->asParticipant($user)
                ->post(route('account.subscription.checkout'), ['plan' => $plan])
                ->assertSessionHasErrors('plan');
        }

        $this->assertSame(0, Payment::count());
    }

    public function test_paying_for_a_lower_plan_than_the_current_one_is_refused(): void
    {
        $user = BotUser::factory()->private()->create();

        $this->asParticipant($user)
            ->post(route('account.subscription.checkout'), ['plan' => 'community'])
            ->assertRedirect(route('account.subscription'))
            ->assertSessionHas('error');

        $this->assertSame(0, Payment::count());
    }

    public function test_checkout_requires_a_logged_in_participant(): void
    {
        $this->post(route('account.subscription.checkout'), ['plan' => 'community'])
            ->assertRedirect(route('account.login'));

        $this->assertSame(0, Payment::count());
    }

    public function test_the_imitator_can_never_take_payments_on_a_production_server(): void
    {
        $this->app['env'] = 'production';
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->asParticipant($this->community())
            ->post(route('account.subscription.checkout'), ['plan' => 'community'])
            ->assertRedirect(route('account.subscription'))
            ->assertSessionHas('error');

        $this->assertSame(0, Payment::count());
    }

    public function test_the_real_bank_without_credentials_does_not_create_invoices(): void
    {
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => null, 'webpayment.merchant_pass' => null]);
        Log::spy();

        $this->asParticipant($this->community())
            ->post(route('account.subscription.checkout'), ['plan' => 'community'])
            ->assertRedirect(route('account.subscription'))
            ->assertSessionHas('error');

        $this->assertSame(0, Payment::count());
    }

    public function test_the_real_bank_gets_the_real_start_url_and_the_flag_from_the_settings(): void
    {
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => '000555', 'webpayment.merchant_pass' => 'prod-secret', 'webpayment.is_test' => false]);

        $html = $this->asParticipant($this->community())
            ->post(route('account.subscription.checkout'), ['plan' => 'community'])
            ->assertOk()
            ->getContent();

        $payment = Payment::firstOrFail();

        $this->assertStringContainsString('action="https://epay.apb.online/PaymentStart"', $html);
        $this->assertStringContainsString('name="IsTest" value="0"', $html);
        $this->assertMatchesRegularExpression('/^WH\d{6}[0-9A-Z]{6}$/', $payment->invoice_id, 'боевой счёт без буквы T');
        $this->assertSame('bank', $payment->driver);
        $this->assertFalse($payment->is_test);
        $this->assertStringNotContainsString('prod-secret', $html);
    }

    // ---------------------------------------------------------------- «банк» и оплата

    public function test_the_imitator_checks_the_signature_like_the_real_bank_would(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        $fields = app(\App\Services\Payments\WebPayment\Checkout::class)->fields($payment);

        $this->post('/dev/fake-bank', $fields)
            ->assertOk()
            ->assertSee('✓ верна', false)
            ->assertSee($payment->invoice_id);

        // Любое изменение (например, суммы) ломает подпись, и «банк» отказывается принимать оплату.
        $fields['RequestSum'] = '100';
        $this->post('/dev/fake-bank', $fields)
            ->assertOk()
            ->assertSee('НЕ СОВПАЛА', false);
    }

    public function test_a_paid_invoice_switches_the_plan_on_notifies_the_participant_and_shows_the_result(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);

        $this->asParticipant($user)
            ->post(route('dev.fakebank.complete'), ['nivid' => $payment->invoice_id, 'decision' => 'pay'])
            ->assertRedirect(route('account.subscription.success', ['invoiceid' => $payment->invoice_id]));

        $payment->refresh();
        $user->refresh();

        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame('4242', $payment->last_digits);
        $this->assertNotNull($payment->rrn);
        $this->assertSame(Plan::Community, $user->currentPlan());
        $this->assertTrue($user->plan_ends_at->isFuture());
        $this->assertSame($payment->id, $user->subscriptions()->first()->payment_id);

        // Страница «Успешно» показывает итог из нашей базы.
        $this->asParticipant($user)
            ->get(route('account.subscription.success', ['invoiceid' => $payment->invoice_id]))
            ->assertOk()
            ->assertSee(__('subscription.result.paid_title'));

        // Сообщение в Telegram о включённой подписке.
        $texts = collect(Telegram::getRequestHistory())
            ->map(fn ($item) => json_decode((string) ($item['request'] ?? array_values($item)[0])->getBody(), true)['text'] ?? '')
            ->implode("\n");
        $this->assertStringContainsString('Подписка <b>WOMEN’S HUB COMMUNITY</b> включена', $texts);
    }

    public function test_a_declined_payment_changes_nothing(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);

        $this->asParticipant($user)
            ->post(route('dev.fakebank.complete'), ['nivid' => $payment->invoice_id, 'decision' => 'decline'])
            ->assertRedirect(route('account.subscription.fail', ['invoiceid' => $payment->invoice_id]));

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertSame(Plan::Open, $user->fresh()->currentPlan());

        $this->asParticipant($user)
            ->get(route('account.subscription.fail', ['invoiceid' => $payment->invoice_id]))
            ->assertOk()
            ->assertSee(__('subscription.result.failed_title'));
    }

    public function test_a_second_payment_for_the_same_plan_extends_the_period_once_per_payment(): void
    {
        $user = $this->community();

        $first = $this->checkoutFor($user, Plan::Community);
        $this->post(route('dev.fakebank.complete'), ['nivid' => $first->invoice_id, 'decision' => 'pay']);
        $endAfterFirst = $user->fresh()->plan_ends_at->copy();

        $second = $this->checkoutFor($user->fresh(), Plan::Community);
        $this->assertNotSame($first->invoice_id, $second->invoice_id);
        $this->post(route('dev.fakebank.complete'), ['nivid' => $second->invoice_id, 'decision' => 'pay']);

        $this->assertTrue($user->fresh()->plan_ends_at->equalTo($endAfterFirst->copy()->addMonthsNoOverflow(12)));
        $this->assertSame(2, $user->subscriptions()->count());
    }

    // ---------------------------------------------------------------- защита ResultURL

    public function test_a_valid_result_notification_is_confirmed_with_the_bank_before_the_plan_is_switched_on(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        // Имитатор «банка» знает об оплате.
        $payment->update(['payload' => ['fake' => ['state' => 1, 'sum' => 60000, 'currency' => '000', 'istest' => true, 'rrn' => '1', 'lastdgt' => '4242']]]);

        $this->post('/payment/result', $this->paidNotification($payment))->assertOk()->assertSee('OK');

        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
    }

    public function test_a_notification_alone_does_not_switch_the_plan_on_when_the_bank_does_not_confirm_it(): void
    {
        // Документ банка требует проверять оплату через GetState. Оповещение с верной подписью, но без подтверждения банка — не оплата.
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);

        $this->post('/payment/result', $this->paidNotification($payment))->assertOk();

        $this->assertSame(Plan::Open, $user->fresh()->currentPlan());
        $this->assertSame(Payment::STATUS_VERIFYING, $payment->fresh()->status);
    }

    public function test_a_forged_notification_with_a_wrong_signature_is_rejected(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);

        $forged = $this->paidNotification($payment, ['signature' => md5('guess')]);
        $this->post('/payment/result', $forged)->assertStatus(400)->assertSee('ERROR');

        // Подпись, составленная с чужим паролем.
        $date = now()->format('dmY');
        $wrongPass = $this->paidNotification($payment, ['signature' => Signature::paid($payment->invoice_id, 'paid', (string) $payment->amount, '000', $date, 'not-the-merchant-pass')]);
        $this->post('/payment/result', $wrongPass)->assertStatus(400);

        // Подпись есть, но сумму подменили после подписи.
        $tampered = $this->paidNotification($payment);
        $tampered['paymentsum'] = '100';
        $this->post('/payment/result', $tampered)->assertStatus(400);

        $this->assertSame(Plan::Open, $user->fresh()->currentPlan());
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_incomplete_or_unknown_notifications_are_rejected(): void
    {
        $payment = $this->checkoutFor($this->community(), Plan::Community);

        $this->post('/payment/result', [])->assertStatus(400);
        $this->post('/payment/result', ['invoiceid' => $payment->invoice_id])->assertStatus(400);
        $this->post('/payment/result', ['status' => 'refunded', 'invoiceid' => $payment->invoice_id, 'date' => '01012025', 'signature' => md5('x')])->assertStatus(400);

        // Верная подпись, но такого счёта у нас нет.
        $ghost = new Payment(['invoice_id' => 'WHT99999999', 'amount' => 60000, 'currency' => '000', 'is_test' => true]);
        $this->post('/payment/result', $this->paidNotification($ghost))->assertStatus(400);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_a_notification_that_does_not_match_the_invoice_is_never_trusted(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        $payment->update(['payload' => ['fake' => ['state' => 1, 'sum' => 100, 'currency' => '000', 'istest' => true]]]);

        // Банк (с верной подписью) прислал сумму 1 рубль вместо 600.
        $cheap = $this->paidNotification($payment, ['paymentsum' => '100']);
        $cheap['signature'] = Signature::paid($payment->invoice_id, 'paid', '100', '000', $cheap['date'], 'local-fake-merchant-pass');
        $this->post('/payment/result', $cheap)->assertOk();

        $payment->refresh();
        $this->assertSame(Payment::STATUS_VERIFYING, $payment->status);
        $this->assertStringContainsString('сумма', $payment->payload['anomaly']);
        $this->assertSame(Plan::Open, $user->fresh()->currentPlan(), 'тариф за 1 рубль не включается');

        // Нестыковка по признаку теста: боевое оповещение по тестовому счёту.
        $other = $this->checkoutFor($this->community(), Plan::Community);
        $live = $this->paidNotification($other, ['istest' => '0']);
        $this->post('/payment/result', $live)->assertOk();
        $this->assertSame(Payment::STATUS_VERIFYING, $other->fresh()->status);
        $this->assertStringContainsString('тест', $other->fresh()->payload['anomaly']);
    }

    public function test_a_replayed_notification_does_not_extend_the_plan_twice(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        $payment->update(['payload' => ['fake' => ['state' => 1, 'sum' => 60000, 'currency' => '000', 'istest' => true]]]);
        $notification = $this->paidNotification($payment);

        $this->post('/payment/result', $notification)->assertOk();
        $end = $user->fresh()->plan_ends_at->copy();

        $this->post('/payment/result', $notification)->assertOk();
        $this->get(route('account.subscription.success', ['invoiceid' => $payment->invoice_id]));
        app(PaymentReconciler::class)->check($payment->fresh());

        $this->assertTrue($user->fresh()->plan_ends_at->equalTo($end));
        $this->assertSame(1, $user->subscriptions()->count());
    }

    public function test_the_notification_also_works_with_get_and_ignores_header_case_of_names(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        $payment->update(['payload' => ['fake' => ['state' => 1, 'sum' => 60000, 'currency' => '000', 'istest' => true]]]);

        $this->get('/payment/result?'.http_build_query($this->paidNotification($payment)))->assertOk();

        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
    }

    public function test_the_failure_notification_closes_the_invoice(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);

        $this->post('/payment/result', $this->failedNotification($payment))->assertOk();

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertSame(Plan::Open, $user->fresh()->currentPlan());

        // Позднее «оплачено» по уже закрытому счёту ничего не меняет.
        $this->post('/payment/result', $this->paidNotification($payment))->assertOk();
        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
    }

    public function test_the_notification_endpoint_needs_no_csrf_token_and_no_login(): void
    {
        // Банк присылает запрос с чужого сайта: ни сессии, ни CSRF-токена у него нет.
        $this->withMiddleware();

        $this->post('/payment/result', [])->assertStatus(400);
    }

    // ---------------------------------------------------------------- страница результата

    public function test_the_result_page_trusts_the_database_not_the_address_it_was_opened_with(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);

        // Открыли «Успешно» руками, а банк оплату не подтвердил: тариф не включается, страница говорит «проверяем».
        $this->asParticipant($user)
            ->get(route('account.subscription.success', ['invoiceid' => $payment->invoice_id]))
            ->assertOk()
            ->assertSee(__('subscription.result.processing_title'))
            ->assertSee('http-equiv="refresh"', false);

        $this->assertSame(Plan::Open, $user->fresh()->currentPlan());
    }

    public function test_the_result_page_never_shows_someone_elses_invoice(): void
    {
        $owner = $this->community();
        $payment = $this->checkoutFor($owner, Plan::Community);
        $payment->update(['status' => Payment::STATUS_PAID]);

        $stranger = $this->community();

        $this->asParticipant($stranger)
            ->get(route('account.subscription.success', ['invoiceid' => $payment->invoice_id]))
            ->assertOk()
            ->assertSee(__('subscription.result.unknown_title'));
    }

    public function test_the_bank_may_return_the_participant_with_post_and_without_an_invoice_number(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        $payment->update(['status' => Payment::STATUS_FAILED]);

        // Номер счёта запоминается в сессии при нажатии «Оплатить»: если банк его не вернул, находим счёт по ней.
        $this->withSession([...$this->sessionFor($user), 'payment_invoice' => $payment->invoice_id])
            ->post(route('account.subscription.fail'))
            ->assertOk()
            ->assertSee(__('subscription.result.failed_title'));
    }

    // ---------------------------------------------------------------- сбой банка и фоновая сверка

    public function test_when_the_bank_is_unreachable_the_payment_waits_and_the_reconciler_finishes_it_later(): void
    {
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => '000555', 'webpayment.merchant_pass' => 'prod-secret', 'webpayment.is_test' => false]);

        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);

        // Банк оповестил об оплате, но веб-сервис недоступен: подтвердить нельзя, тариф не включается.
        $bankUp = false;
        $reply = $this->paidSoapReply($payment);
        Http::fake(function () use (&$bankUp, $reply) {
            if (! $bankUp) {
                throw new ConnectionException('timeout');
            }

            return Http::response($reply, 200);
        });
        $date = now()->format('dmY');
        $this->post('/payment/result', [
            'status' => 'paid', 'paymentsum' => '60000', 'paymentcurrency' => '000', 'invoiceid' => $payment->invoice_id,
            'date' => $date, 'istest' => '0', 'rrn' => '77',
            'signature' => Signature::paid($payment->invoice_id, 'paid', '60000', '000', $date, 'prod-secret'),
        ])->assertOk();

        $payment->refresh();
        $this->assertSame(Payment::STATUS_VERIFYING, $payment->status);
        $this->assertNotEmpty($payment->payload['last_error']);
        $this->assertSame(Plan::Open, $user->fresh()->currentPlan());

        // Через несколько минут банк снова доступен: фоновая сверка подтверждает оплату и включает тариф.
        $bankUp = true;
        $this->travel(10)->minutes();

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
    }

    public function test_the_reconciler_finds_a_payment_whose_notification_was_lost(): void
    {
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => '000555', 'webpayment.merchant_pass' => 'prod-secret', 'webpayment.is_test' => false]);

        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Private);
        Http::fake(['*' => Http::response($this->paidSoapReply($payment), 200)]);

        // Свежий счёт не трогаем (человек может ещё вводить карту), а старый проверяем.
        $this->assertSame(0, app(PaymentReconciler::class)->reconcileOpen());
        $this->travel(3)->minutes();
        $this->assertSame(1, app(PaymentReconciler::class)->reconcileOpen());

        $this->assertSame(Plan::Private, $user->fresh()->currentPlan());
    }

    public function test_the_reconciler_closes_stale_invoices_and_leaves_paid_ones_alone(): void
    {
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => '000555', 'webpayment.merchant_pass' => 'prod-secret', 'webpayment.is_test' => false]);

        $payment = $this->checkoutFor($this->community(), Plan::Community);
        Http::fake(['*' => Http::response($this->bankSoapReply(['state' => '0', 'sum' => '60000', 'currency' => '000', 'istest' => '0', 'invoiceid' => $payment->invoice_id]), 200)]);

        $this->travel(5)->minutes();
        app(PaymentReconciler::class)->reconcileOpen();
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status, 'ещё в пределах времени жизни счёта');

        $this->travel(45)->minutes();
        app(PaymentReconciler::class)->reconcileOpen();
        $this->assertSame(Payment::STATUS_EXPIRED, $payment->fresh()->status);

        // Закрытый счёт больше не спрашивает банк.
        Http::fake();
        $this->assertSame(0, app(PaymentReconciler::class)->reconcileOpen());
        Http::assertNothingSent();
    }

    public function test_bank_states_cancelled_and_error_close_the_invoice(): void
    {
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => '000555', 'webpayment.merchant_pass' => 'prod-secret', 'webpayment.is_test' => false]);

        $code = 0;
        Http::fake(function () use (&$code) {
            return Http::response($this->bankSoapReply(['state' => (string) $code, 'sum' => '60000', 'currency' => '000', 'istest' => '0']), 200);
        });

        foreach ([2 => Payment::STATUS_CANCELLED, 3 => Payment::STATUS_FAILED, 4 => Payment::STATUS_EXPIRED] as $code => $expected) {
            $payment = $this->checkoutFor($this->community(), Plan::Community);

            app(PaymentReconciler::class)->check($payment);

            $this->assertSame($expected, $payment->fresh()->status, "state {$code}");
        }
    }

    public function test_a_paid_state_that_does_not_match_the_invoice_is_held_for_a_human(): void
    {
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => '000555', 'webpayment.merchant_pass' => 'prod-secret', 'webpayment.is_test' => false]);

        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        Http::fake(['*' => Http::response($this->paidSoapReply($payment, ['sum' => '100']), 200)]);

        app(PaymentReconciler::class)->check($payment);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_VERIFYING, $payment->status);
        $this->assertStringContainsString('сумма', $payment->payload['anomaly']);
        $this->assertSame(Plan::Open, $user->fresh()->currentPlan());
    }

    public function test_a_payment_after_the_participant_deleted_the_profile_is_kept_for_a_refund(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        $user->delete();

        $payment->refresh();
        $this->assertNull($payment->bot_user_id, 'платёж остаётся в истории, даже если профиль удалён');

        $payment->update(['payload' => ['fake' => ['state' => 1, 'sum' => 60000, 'currency' => '000', 'istest' => true]]]);
        $this->post('/payment/result', $this->paidNotification($payment))->assertOk();

        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        $this->assertSame(0, \App\Models\Subscription::count());
    }

    public function test_pending_invoices_survive_without_a_participant_and_subscriptions_follow_the_profile(): void
    {
        $user = $this->community();
        $payment = $this->checkoutFor($user, Plan::Community);
        app(\App\Services\Subscriptions\SubscriptionService::class)->grant($user, Plan::Community, 1);

        $user->delete();

        $this->assertSame(1, Payment::count());
        $this->assertSame(0, \App\Models\Subscription::count(), 'периоды подписки удаляются вместе с профилем');
        $this->assertSame($payment->telegram_id, $payment->fresh()->telegram_id);
    }

    // ---------------------------------------------------------------- номера счетов и восстановление из копии

    public function test_invoice_numbers_do_not_come_from_the_database_row_counter(): void
    {
        $user = $this->community();
        $first = $this->checkoutFor($user, Plan::Community);
        $first->update(['status' => Payment::STATUS_EXPIRED]);

        $second = $this->checkoutFor($user, Plan::Community);

        $this->assertNotSame($first->invoice_id, $second->invoice_id);
        $this->assertStringNotContainsString(str_pad((string) $first->id, 8, '0', STR_PAD_LEFT), $first->invoice_id);
        $this->assertStringStartsWith('WHT'.now()->format('ymd'), $first->invoice_id, 'приставка, T тестового и сегодняшняя дата');
    }

    public function test_restoring_an_old_backup_does_not_repeat_invoice_numbers_the_bank_has_already_seen(): void
    {
        $user = $this->community();
        $before = [];

        // До аварии: счета создавались и уходили в банк.
        for ($i = 0; $i < 5; $i++) {
            $payment = $this->checkoutFor($user, Plan::Community);
            $before[] = $payment->invoice_id;
            $payment->update(['status' => Payment::STATUS_EXPIRED]);
        }

        // Восстановление копии базы: таблица платежей откатилась в пустое состояние, счётчик строк начался с единицы.
        Payment::query()->delete();
        \Illuminate\Support\Facades\DB::statement("DELETE FROM sqlite_sequence WHERE name = 'payments'");

        $after = [];
        for ($i = 0; $i < 5; $i++) {
            $payment = $this->checkoutFor($user, Plan::Community);
            $after[] = $payment->invoice_id;
            $payment->update(['status' => Payment::STATUS_EXPIRED]);
        }

        $this->assertSame(1, Payment::query()->orderBy('id')->value('id'), 'счётчик строк действительно начался заново');
        $this->assertSame([], array_intersect($before, $after), 'новые номера не повторяют те, что банк уже видел');
    }

    public function test_a_taken_invoice_number_is_replaced_by_another_one(): void
    {
        $user = $this->community();
        $taken = 'WHT'.now()->format('ymd').'AAAAAA';
        Payment::create([
            'telegram_id' => 1, 'plan' => 'community', 'months' => 12, 'amount' => 60000, 'currency' => '000',
            'invoice_id' => $taken, 'status' => Payment::STATUS_PAID, 'is_test' => true, 'driver' => 'fake', 'expires_at' => now(),
        ]);

        // Случайная часть дважды выпала такой же, как у уже занятого номера, и только с третьего раза — другая.
        \Illuminate\Support\Str::createRandomStringsUsingSequence(['aaaaaa', 'aaaaaa', 'bbbbbb']);

        try {
            $payment = $this->checkoutFor($user, Plan::Community);
        } finally {
            \Illuminate\Support\Str::createRandomStringsNormally();
        }

        $this->assertSame('WHT'.now()->format('ymd').'BBBBBB', $payment->invoice_id);
        $this->assertSame(2, Payment::count());
    }

    public function test_giving_up_after_five_taken_numbers_raises_the_database_error_instead_of_looping(): void
    {
        $user = $this->community();
        Payment::create([
            'telegram_id' => 1, 'plan' => 'community', 'months' => 12, 'amount' => 60000, 'currency' => '000',
            'invoice_id' => 'WHT'.now()->format('ymd').'AAAAAA', 'status' => Payment::STATUS_PAID, 'is_test' => true, 'driver' => 'fake', 'expires_at' => now(),
        ]);
        \Illuminate\Support\Str::createRandomStringsUsing(fn (): string => 'aaaaaa');

        try {
            $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
            $this->checkoutFor($user, Plan::Community);
        } finally {
            \Illuminate\Support\Str::createRandomStringsNormally();
        }
    }
}
