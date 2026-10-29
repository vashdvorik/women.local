<?php

namespace Tests\Feature\Admin;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Nutgram\Laravel\Facades\Telegram;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * «Кабинеты участниц → Подписки» и «Платежи»: список, ручная выдача и закрытие тарифа, проверка и подтверждение платежей.
 */
class SubscriptionAdminTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Telegram::fake();
    }

    public function test_guests_and_non_admins_cannot_reach_subscriptions_or_payments(): void
    {
        $user = BotUser::factory()->approved()->create();
        $payment = $this->checkoutFor($user, Plan::Active);

        $targets = [
            ['get', route('admin.subscriptions.index')],
            ['get', route('admin.subscriptions.edit', $user)],
            ['post', route('admin.subscriptions.grant', $user)],
            ['post', route('admin.subscriptions.gift', $user)],
            ['put', route('admin.subscriptions.prices')],
            ['post', route('admin.subscriptions.revoke', $user)],
            ['get', route('admin.payments.index')],
            ['get', route('admin.payments.show', $payment)],
            ['post', route('admin.payments.recheck', $payment)],
            ['post', route('admin.payments.confirm', $payment)],
            ['delete', route('admin.payments.destroy', $payment)],
        ];

        foreach ($targets as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('login'));
        }

        // Участница с работающей сессией кабинета — не администратор.
        foreach ($targets as [$method, $url]) {
            $this->asParticipant($user)->{$method}($url)->assertRedirect(route('login'));
        }

        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_the_menu_of_the_participants_camp_has_subscriptions_and_payments(): void
    {
        $html = $this->actingAsAdmin()->get(route('admin.cabinets.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(route('admin.subscriptions.index'), $html);
        $this->assertStringContainsString(route('admin.payments.index'), $html);
        $this->assertStringContainsString('Подписки', $html);
        $this->assertStringContainsString('Платежи', $html);
    }

    public function test_the_list_shows_plans_and_filters_by_them(): void
    {
        BotUser::factory()->approved()->create(['full_name' => 'Анна Открытая']);
        BotUser::factory()->active()->create(['full_name' => 'Белла Комьюнити']);
        BotUser::factory()->private()->create(['full_name' => 'Вера Приват']);
        BotUser::factory()->pending()->create(['full_name' => 'Галина Новая']);

        $all = $this->actingAsAdmin()->get(route('admin.subscriptions.index'))->assertOk();
        $all->assertSee('Анна Открытая')->assertSee('Белла Комьюнити')->assertSee('Вера Приват')->assertDontSee('Галина Новая');

        $this->actingAsAdmin()->get(route('admin.subscriptions.index', ['filter' => 'community']))
            ->assertSee('Белла Комьюнити')->assertDontSee('Вера Приват')->assertDontSee('Анна Открытая');
        $this->actingAsAdmin()->get(route('admin.subscriptions.index', ['filter' => 'private']))
            ->assertSee('Вера Приват')->assertDontSee('Белла Комьюнити');
        $this->actingAsAdmin()->get(route('admin.subscriptions.index', ['filter' => 'open']))
            ->assertSee('Анна Открытая')->assertDontSee('Белла Комьюнити')->assertDontSee('Вера Приват');
        $this->actingAsAdmin()->get(route('admin.subscriptions.index', ['q' => 'Вера']))
            ->assertSee('Вера Приват')->assertDontSee('Анна Открытая');
        $this->actingAsAdmin()->get(route('admin.subscriptions.index', ['filter' => 'bogus', 'q' => "'; drop table bot_users;--"]))->assertOk();
    }

    public function test_the_expiring_filter_lists_plans_ending_within_30_days(): void
    {
        $soon = BotUser::factory()->active()->create(['full_name' => 'Скоро Закончится']);
        $later = BotUser::factory()->active()->create(['full_name' => 'Нескоро Закончится']);
        $soon->forceFill(['plan_ends_at' => now()->addDays(10)])->save();

        $this->actingAsAdmin()->get(route('admin.subscriptions.index', ['filter' => 'expiring']))
            ->assertSee('Скоро Закончится')->assertDontSee('Нескоро Закончится');
    }

    public function test_the_manage_page_shows_the_state_the_history_and_the_payments(): void
    {
        $user = BotUser::factory()->active()->create(['full_name' => 'Дарья Платная']);
        $this->checkoutFor($user, Plan::Private);

        $this->actingAsAdmin()->get(route('admin.subscriptions.edit', $user))
            ->assertOk()
            ->assertSee('Дарья Платная')
            ->assertSee('WOMEN’S HUB COMMUNITY')
            ->assertSee($user->fresh()->plan_ends_at->format('d.m.Y'))
            ->assertSee('Выдано вручную')
            ->assertSee('Закрыть доступ')
            ->assertSee('WHT');
    }

    public function test_the_administrator_grants_a_plan_for_months_and_the_participant_is_told(): void
    {
        $user = BotUser::factory()->approved()->create(['locale' => 'ru']);
        $this->travelTo(now()->startOfSecond());

        $this->actingAsAdmin()->post(route('admin.subscriptions.grant', $user), [
            'plan' => 'community', 'mode' => 'months', 'months' => 6, 'note' => 'перевод на счёт', 'notify' => '1',
        ])->assertRedirect(route('admin.subscriptions.edit', $user))->assertSessionHas('success');

        $user->refresh();
        $this->assertSame(Plan::Active, $user->currentPlan());
        $this->assertTrue($user->plan_ends_at->equalTo(now()->addMonthsNoOverflow(6)));
        $this->assertSame('перевод на счёт', $user->subscriptions()->first()->note);
        $this->assertSame(Subscription::SOURCE_ADMIN, $user->subscriptions()->first()->source);

        $texts = collect(Telegram::getRequestHistory())->map(fn ($i) => json_decode((string) ($i['request'] ?? array_values($i)[0])->getBody(), true)['text'] ?? '')->implode("\n");
        $this->assertStringContainsString('включена до '.$user->plan_ends_at->format('d.m.Y'), $texts);
    }

    public function test_the_administrator_can_grant_quietly_and_until_an_exact_date(): void
    {
        $user = BotUser::factory()->approved()->create();
        $until = now()->addDays(100)->format('Y-m-d');

        $this->actingAsAdmin()->post(route('admin.subscriptions.grant', $user), [
            'plan' => 'private', 'mode' => 'until', 'until' => $until, 'notify' => '0',
        ])->assertSessionHas('success');

        $this->assertSame(Plan::Private, $user->fresh()->currentPlan());
        $this->assertSame($until, $user->fresh()->plan_ends_at->format('Y-m-d'));
        $this->assertSame([], Telegram::getRequestHistory(), 'без галочки сообщение не уходит');
    }

    public function test_invalid_grants_are_refused_and_change_nothing(): void
    {
        $user = BotUser::factory()->approved()->create();
        $pending = BotUser::factory()->pending()->create();

        foreach ([
            ['plan' => 'open', 'mode' => 'months', 'months' => 12],
            ['plan' => 'gold', 'mode' => 'months', 'months' => 12],
            ['plan' => 'community', 'mode' => 'months', 'months' => 0],
            ['plan' => 'community', 'mode' => 'months', 'months' => 999],
            ['plan' => 'community', 'mode' => 'months'],
            ['plan' => 'community', 'mode' => 'until', 'until' => now()->subDay()->format('Y-m-d')],
            ['plan' => 'community', 'mode' => 'until', 'until' => 'вчера'],
            ['plan' => 'community', 'mode' => 'forever'],
        ] as $payload) {
            $this->actingAsAdmin()->post(route('admin.subscriptions.grant', $user), $payload)->assertSessionHasErrors();
        }

        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
        $this->assertSame(0, Subscription::count());

        // Неодобренной участнице тариф не выдаётся.
        $this->actingAsAdmin()->post(route('admin.subscriptions.grant', $pending), ['plan' => 'community', 'mode' => 'months', 'months' => 12])
            ->assertStatus(422);
        $this->assertSame(0, Subscription::count());
    }

    public function test_the_administrator_closes_paid_access_at_once(): void
    {
        $user = BotUser::factory()->private()->create();

        $this->actingAsAdmin()->post(route('admin.subscriptions.revoke', $user))->assertSessionHas('success');

        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
        $this->asParticipant($user)->get(route('account.people'))->assertOk()->assertSee('Купить подписку');
    }

    // ---------------------------------------------------------------- платежи

    public function test_the_payments_list_filters_by_status_and_searches(): void
    {
        $anna = BotUser::factory()->approved()->create(['full_name' => 'Анна Платит']);
        $vera = BotUser::factory()->approved()->create(['full_name' => 'Вера Платит']);
        $paid = $this->checkoutFor($anna, Plan::Active);
        $paid->update(['status' => Payment::STATUS_PAID, 'rrn' => '5550001']);
        $open = $this->checkoutFor($vera, Plan::Private);

        $this->actingAsAdmin()->get(route('admin.payments.index'))
            ->assertOk()->assertSee($paid->invoice_id)->assertSee($open->invoice_id)->assertSee('имитатор');
        $this->actingAsAdmin()->get(route('admin.payments.index', ['status' => 'paid']))
            ->assertSee($paid->invoice_id)->assertDontSee($open->invoice_id);
        $this->actingAsAdmin()->get(route('admin.payments.index', ['q' => 'Вера']))
            ->assertSee($open->invoice_id)->assertDontSee($paid->invoice_id);
        $this->actingAsAdmin()->get(route('admin.payments.index', ['q' => '5550001']))
            ->assertSee($paid->invoice_id)->assertDontSee($open->invoice_id);
    }

    public function test_the_payment_page_shows_details_and_never_the_signature_or_the_password(): void
    {
        $user = BotUser::factory()->approved()->create(['full_name' => 'Ирина Деталь']);
        $payment = $this->checkoutFor($user, Plan::Active);
        $payment->update(['payload' => ['result' => ['status' => 'paid', 'rrn' => '77'], 'anomaly' => 'сумма: у нас 60000, у банка 100']]);

        $html = $this->actingAsAdmin()->get(route('admin.payments.show', $payment))->assertOk()->getContent();

        $this->assertStringContainsString($payment->invoice_id, $html);
        $this->assertStringContainsString('Ирина Деталь', $html);
        $this->assertStringContainsString('600,00', $html);
        $this->assertStringContainsString('Расхождение с банком', $html);
        $this->assertStringContainsString('сумма: у нас 60000, у банка 100', $html);
        $this->assertStringNotContainsString(config('webpayment.merchant_pass'), $html);
    }

    public function test_the_administrator_can_recheck_a_payment_in_the_bank(): void
    {
        $user = BotUser::factory()->approved()->create();
        $payment = $this->checkoutFor($user, Plan::Active);
        $payment->update(['payload' => ['fake' => ['state' => 1, 'sum' => 60000, 'currency' => '000', 'istest' => true, 'rrn' => '9']]]);

        $this->actingAsAdmin()->post(route('admin.payments.recheck', $payment))
            ->assertRedirect(route('admin.payments.show', $payment))
            ->assertSessionHas('success');

        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        $this->assertSame(Plan::Active, $user->fresh()->currentPlan());
    }

    public function test_when_the_bank_answers_in_an_unexpected_way_the_admin_sees_what_it_sent_and_the_payment_stays_open(): void
    {
        $user = BotUser::factory()->approved()->create();
        $payment = $this->checkoutFor($user, Plan::Active);
        config(['webpayment.driver' => 'bank', 'webpayment.merchant_login' => '000123', 'webpayment.merchant_pass' => 'secret-pass']);

        $soap = fn (string $result) => '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            .'<GetStateResponse xmlns="http://webpayment.services.agroprombank.com/"><GetStateResult>'.htmlspecialchars($result, ENT_XML1).'</GetStateResult></GetStateResponse></soap:Body></soap:Envelope>';

        // Один «банк» на весь тест: его ответ меняется между проверками (повторный Http::fake первый не заменяет).
        $reply = $soap('Merchant 000123 is not allowed to use GetState');
        Http::fake(function () use (&$reply) {
            return Http::response($reply, 200, ['Content-Type' => 'text/xml']);
        });

        $this->actingAsAdmin()->post(route('admin.payments.recheck', $payment))->assertSessionHas('error');

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PENDING, $payment->status, 'непонятный ответ не должен ни оплачивать, ни закрывать счёт');
        $this->assertNull($payment->bank_state);
        $this->assertSame(Plan::Community, $user->fresh()->currentPlan());
        $this->assertStringContainsString('Merchant 000123 is not allowed to use GetState', $payment->payload['last_error']);

        $page = $this->actingAsAdmin()->get(route('admin.payments.show', $payment))->assertOk()->getContent();
        $this->assertStringContainsString('Merchant 000123 is not allowed to use GetState', $page);
        $this->assertStringNotContainsString('secret-pass', $page);

        // Тот же счёт, но банк на этот раз ответил XML без Base64: оплата подтверждается, ошибка снимается.
        $xml = '<?xml version="1.0" encoding="utf-8"?><OperationStateResponse><Result><Code>1</Code><Description>OK</Description></Result><StateInfo>'
            .'<state>1</state><sum>'.$payment->amount.'</sum><currency>'.$payment->currency.'</currency><istest>'.($payment->is_test ? '1' : '0').'</istest>'
            .'<invoiceid>'.$payment->invoice_id.'</invoiceid><rrn>123456789012</rrn></StateInfo></OperationStateResponse>';
        $reply = $soap($xml);

        $this->actingAsAdmin()->post(route('admin.payments.recheck', $payment))->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertArrayNotHasKey('last_error', $payment->payload);
        $this->assertSame(Plan::Active, $user->fresh()->currentPlan());
    }

    // ---------------------------------------------------------------- удаление счетов

    private function paymentWith(string $status, bool $test): Payment
    {
        $payment = $this->checkoutFor(BotUser::factory()->approved()->create(), Plan::Active);
        $payment->update(['status' => $status, 'is_test' => $test]);

        return $payment;
    }

    public function test_test_invoices_can_be_deleted_in_any_status_because_no_money_moved(): void
    {
        foreach ([Payment::STATUS_PENDING, Payment::STATUS_VERIFYING, Payment::STATUS_PAID, Payment::STATUS_FAILED, Payment::STATUS_CANCELLED, Payment::STATUS_EXPIRED] as $status) {
            $payment = $this->paymentWith($status, test: true);

            $this->actingAsAdmin()->delete(route('admin.payments.destroy', $payment))
                ->assertRedirect(route('admin.payments.index'))
                ->assertSessionHas('success');

            $this->assertNull(Payment::find($payment->id), "тестовый счёт в статусе {$status} удаляется");
        }
    }

    public function test_live_invoices_that_the_bank_confirmed_unpaid_can_be_deleted(): void
    {
        foreach ([Payment::STATUS_FAILED, Payment::STATUS_CANCELLED, Payment::STATUS_EXPIRED] as $status) {
            $payment = $this->paymentWith($status, test: false);

            $this->actingAsAdmin()->delete(route('admin.payments.destroy', $payment))->assertSessionHas('success');

            $this->assertNull(Payment::find($payment->id), "боевой счёт в статусе {$status} удаляется");
        }
    }

    public function test_live_invoices_that_are_paid_verifying_or_still_open_cannot_be_deleted(): void
    {
        foreach ([Payment::STATUS_PAID, Payment::STATUS_VERIFYING, Payment::STATUS_PENDING] as $status) {
            $payment = $this->paymentWith($status, test: false);

            $this->actingAsAdmin()->delete(route('admin.payments.destroy', $payment))
                ->assertRedirect(route('admin.payments.show', $payment))
                ->assertSessionHas('error', $payment->deleteBlockedReason());

            $this->assertNotNull(Payment::find($payment->id), "боевой счёт в статусе {$status} остаётся");
        }
    }

    public function test_deleting_a_paid_test_invoice_keeps_the_members_plan_and_history(): void
    {
        $user = BotUser::factory()->approved()->create();
        $payment = $this->checkoutFor($user, Plan::Active);
        $payment->update(['payload' => ['fake' => ['state' => 1, 'sum' => 60000, 'currency' => '000', 'istest' => true, 'rrn' => '9']]]);
        $this->actingAsAdmin()->post(route('admin.payments.recheck', $payment));

        $this->assertSame(Plan::Active, $user->fresh()->currentPlan());
        $subscription = $user->subscriptions()->firstOrFail();
        $this->assertSame($payment->id, $subscription->payment_id);

        $this->actingAsAdmin()->delete(route('admin.payments.destroy', $payment->fresh()))->assertSessionHas('success');

        $this->assertNull(Payment::find($payment->id));
        $this->assertSame(Plan::Active, $user->fresh()->currentPlan(), 'тариф остаётся: его снимают отдельно');
        $this->assertNull($subscription->fresh()->payment_id, 'запись истории подписки сохранилась, ссылка на счёт пропала');
    }

    public function test_the_buttons_follow_the_same_rule_and_the_admin_is_told_why_not(): void
    {
        $deletable = $this->paymentWith(Payment::STATUS_EXPIRED, test: false);
        $paid = $this->paymentWith(Payment::STATUS_PAID, test: false);

        $show = $this->actingAsAdmin()->get(route('admin.payments.show', $deletable))->assertOk()->getContent();
        $this->assertStringContainsString(route('admin.payments.destroy', $deletable), $show);
        $this->assertStringContainsString('Удалить счёт', $show);
        $this->assertStringContainsString('Банк подтвердил, что оплаты по этому счёту нет', $show);

        $blocked = $this->actingAsAdmin()->get(route('admin.payments.show', $paid))->assertOk()->getContent();
        $this->assertStringNotContainsString(route('admin.payments.destroy', $paid), $blocked);
        $this->assertStringContainsString('Оплаченный счёт удалить нельзя', $blocked);

        $list = $this->actingAsAdmin()->get(route('admin.payments.index'))->assertOk()->getContent();
        $this->assertStringContainsString('action="'.route('admin.payments.destroy', $deletable).'"', $list);
        $this->assertStringNotContainsString('action="'.route('admin.payments.destroy', $paid).'"', $list);
    }

    public function test_recheck_with_nothing_new_from_the_bank_explains_the_status(): void
    {
        $payment = $this->checkoutFor(BotUser::factory()->approved()->create(), Plan::Active);

        $this->actingAsAdmin()->post(route('admin.payments.recheck', $payment))->assertSessionHas('error');

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_manual_confirmation_turns_the_plan_on_once_and_is_recorded(): void
    {
        $user = BotUser::factory()->approved()->create();
        $payment = $this->checkoutFor($user, Plan::Private);
        $payment->update(['status' => Payment::STATUS_VERIFYING, 'payload' => ['anomaly' => 'сумма: …']]);

        $this->actingAsAdmin()->post(route('admin.payments.confirm', $payment))->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertArrayHasKey('manual_confirmation', $payment->payload);
        $this->assertSame(Plan::Private, $user->fresh()->currentPlan());
        $this->assertSame($payment->id, $user->subscriptions()->first()->payment_id);

        // Повторное подтверждение не продлевает тариф ещё раз.
        $end = $user->fresh()->plan_ends_at->copy();
        $this->actingAsAdmin()->post(route('admin.payments.confirm', $payment))->assertStatus(422);
        $this->assertTrue($user->fresh()->plan_ends_at->equalTo($end));
    }

    public function test_manual_confirmation_for_a_deleted_participant_is_refused(): void
    {
        $user = BotUser::factory()->approved()->create();
        $payment = $this->checkoutFor($user, Plan::Active);
        $user->delete();

        $this->actingAsAdmin()->post(route('admin.payments.confirm', $payment))->assertStatus(422);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_the_menu_counter_shows_payments_stuck_in_verification(): void
    {
        $payment = $this->checkoutFor(BotUser::factory()->approved()->create(), Plan::Active);
        $payment->forceFill(['status' => Payment::STATUS_VERIFYING])->save();

        // Только что — ещё не проблема (банк может успеть подтвердить); через 15 минут — требует внимания.
        $this->assertStringNotContainsString('Платежи, которые нужно разобрать вручную', $this->actingAsAdmin()->get(route('admin.cabinets.dashboard'))->getContent());

        $this->travel(20)->minutes();
        $this->assertStringContainsString('Платежи, которые нужно разобрать вручную', $this->actingAsAdmin()->get(route('admin.cabinets.dashboard'))->getContent());
    }

    public function test_the_subscriptions_service_is_used_by_grants_so_history_is_kept(): void
    {
        $user = BotUser::factory()->approved()->create();
        $service = app(SubscriptionService::class);

        $service->grant($user, Plan::Active, 12);
        $this->actingAsAdmin()->post(route('admin.subscriptions.revoke', $user));
        $service->grant($user->fresh(), Plan::Active, 3);

        $this->assertSame(2, $user->subscriptions()->count(), 'закрытый период остаётся в истории');
        $this->actingAsAdmin()->get(route('admin.subscriptions.edit', $user))->assertSee('Выдано вручную');
    }
}
