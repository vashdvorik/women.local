<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Enums\Plan;
use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Services\Payments\WebPayment\Checkout;
use App\Services\Payments\WebPayment\PaymentReconciler;
use App\Services\Payments\WebPayment\PaymentsUnavailable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Подписка в кабинете: страница тарифов, вкладка Private, оформление оплаты и возврат из банка.
 * Страницы общие для всех четырёх тем кабинета (они подключают макет текущей темы), поэтому лежат в
 * resources/views/account/subscription.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly Checkout $checkout,
        private readonly PaymentReconciler $reconciler,
    ) {}

    /** «Подписка»: три тарифа, текущий отмечен, ниже история оплат. */
    public function show(): View
    {
        $user = $this->user();

        return view('account.subscription.plans', [
            'user' => $user,
            'current' => $user->currentPlan(),
            'payments' => $user->payments()->latest('id')->limit(10)->get(),
            'testMode' => (bool) config('webpayment.is_test'),
        ]);
    }

    /** Особая вкладка Private: описание пакета и цена или, если пакет уже оплачен, его статус. */
    public function private(): View
    {
        $user = $this->user();

        return view('account.subscription.private', [
            'user' => $user,
            'current' => $user->currentPlan(),
            'testMode' => (bool) config('webpayment.is_test'),
        ]);
    }

    /**
     * «Оплатить»: создаёт счёт и отдаёт страницу, которая сама отправляет участницу на платёжный сайт банка.
     * Цена и срок берутся из настроек по тарифу, а не из запроса: подменить сумму из браузера нельзя.
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(array_map(fn (Plan $plan): string => $plan->value, Plan::paid()))],
        ], [
            'plan.required' => __('subscription.errors.invalid_plan'),
            'plan.in' => __('subscription.errors.invalid_plan'),
        ]);

        try {
            $payment = $this->checkout->begin($this->user(), Plan::from($data['plan']));
        } catch (PaymentsUnavailable $e) {
            return redirect()->route('account.subscription')->with('error', $e->getMessage());
        }

        // Банк может вернуть участницу без номера счёта в запросе — тогда страница результата найдёт счёт по сессии.
        session(['payment_invoice' => $payment->invoice_id]);

        return view('account.subscription.redirect', [
            'url' => $this->checkout->startUrl($payment),
            'fields' => $this->checkout->fields($payment),
        ]);
    }

    /**
     * Страница, на которую банк возвращает участницу после оплаты (SuccessURL) или отказа (FailURL).
     *
     * Что написано в адресе, значения не имеет: статус берётся только из нашей базы, а если счёт ещё не закрыт,
     * мы сами спрашиваем банк (GetState). Искать можно только свои счета.
     */
    public function result(Request $request): View
    {
        $user = $this->user();

        $invoice = (string) ($request->input('invoiceid') ?? $request->input('InvoiceId') ?? $request->input('nivid') ?? session('payment_invoice', ''));
        $payment = $invoice !== '' ? $user->payments()->where('invoice_id', $invoice)->first() : null;

        if ($payment !== null && $payment->isOpen()) {
            $payment = $this->reconciler->check($payment);
        }

        $state = match (true) {
            $payment === null => 'unknown',
            $payment->isPaid() => 'paid',
            $payment->isOpen() => 'processing',
            default => 'failed',
        };

        return view('account.subscription.result', [
            'user' => $user->fresh(),
            'payment' => $payment,
            'state' => $state,
        ]);
    }

    private function user(): BotUser
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        return $user;
    }
}
