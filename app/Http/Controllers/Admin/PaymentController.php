<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Payments\WebPayment\PaymentReconciler;
use App\Services\Subscriptions\SubscriptionNotifier;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * «Платежи» — лагерь «Кабинеты участниц»: все счета Web-платежа, их статусы, проверка в банке вручную и
 * подтверждение, если банк и мы разошлись (статус «Проверяется в банке» с пометкой о расхождении).
 */
class PaymentController extends Controller
{
    private const STATUSES = [
        Payment::STATUS_PENDING,
        Payment::STATUS_VERIFYING,
        Payment::STATUS_PAID,
        Payment::STATUS_FAILED,
        Payment::STATUS_CANCELLED,
        Payment::STATUS_EXPIRED,
    ];

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, self::STATUSES, true) ? $status : '';
        $search = trim($request->string('q')->toString());

        $payments = Payment::query()
            ->with(['botUser', 'subscription'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('invoice_id', 'like', "%{$search}%")
                ->orWhere('rrn', 'like', "%{$search}%")
                ->orWhereHas('botUser', fn ($u) => $u->where('full_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('telegram_username', 'like', "%{$search}%"))))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'status' => $status,
            'search' => $search,
            'counts' => Payment::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Payment $payment): View
    {
        return view('admin.payments.show', ['payment' => $payment->load(['botUser', 'subscription'])]);
    }

    /** Спросить банк о состоянии счёта прямо сейчас (GetState) и применить ответ. */
    public function recheck(Payment $payment, PaymentReconciler $reconciler): RedirectResponse
    {
        $payment = $reconciler->check($payment);

        $message = $payment->isPaid()
            ? __('Банк подтвердил оплату, тариф включён.')
            : __('Статус счёта: «:status».', ['status' => $payment->statusLabel()]).(($payment->payload['last_error'] ?? null) ? ' '.__('Банк не ответил: :error', ['error' => $payment->payload['last_error']]) : '');

        return redirect()->route('admin.payments.show', $payment)->with($payment->isPaid() ? 'success' : 'error', $message);
    }

    /**
     * Подтвердить оплату вручную: когда администратор сам увидел деньги в кабинете банка, а автоматическая проверка
     * не сошлась (расхождение суммы, потерянное оповещение). Включает тариф так же, как обычная оплата.
     */
    public function confirm(Payment $payment, SubscriptionService $subscriptions, SubscriptionNotifier $notifier): RedirectResponse
    {
        abort_if($payment->botUser === null, 422, __('Участницы уже нет: тариф выдать некому. Верните деньги в банке.'));

        $subscription = DB::transaction(function () use ($payment, $subscriptions): Subscription {
            // Строка блокируется, чтобы двойной клик (или одновременная сверка с банком) не выдал тариф дважды.
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            abort_if($payment->isPaid(), 422, __('Платёж уже подтверждён.'));

            $payment->forceFill([
                'status' => Payment::STATUS_PAID,
                'paid_at' => now(),
                'payload' => array_merge($payment->payload ?? [], ['manual_confirmation' => now()->toIso8601String()]),
            ])->save();

            return $subscriptions->grant(
                $payment->botUser,
                $payment->planEnum(),
                $payment->months,
                Subscription::SOURCE_PAYMENT,
                $payment,
                __('Подтверждено вручную: :invoice', ['invoice' => $payment->invoice_id]),
            );
        });

        $notifier->activated($subscription);

        return redirect()->route('admin.payments.show', $payment)->with('success', __('Оплата подтверждена вручную, тариф включён.'));
    }

    /**
     * Удалить счёт. Разрешено только тому, за что деньги не принимались (Payment::deleteBlockedReason): тестовым
     * счетам и боевым, по которым банк подтвердил, что оплаты нет. Условие проверяется под блокировкой строки:
     * пока администратор читал окно подтверждения, фоновая сверка могла перевести счёт в «оплачен».
     * Подписка участницы остаётся, у неё просто пропадает ссылка на счёт (внешний ключ nullOnDelete).
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $invoice = $payment->invoice_id;

        $blocked = DB::transaction(function () use ($payment): ?string {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $reason = $locked->deleteBlockedReason();

            if ($reason === null) {
                $locked->delete();
                Log::notice('Payment deleted by admin', [
                    'invoice' => $locked->invoice_id,
                    'status' => $locked->status,
                    'is_test' => $locked->is_test,
                    'amount' => $locked->amount,
                    'admin_id' => auth()->id(),
                ]);
            }

            return $reason;
        });

        if ($blocked !== null) {
            return redirect()->route('admin.payments.show', $payment)->with('error', $blocked);
        }

        return redirect()->route('admin.payments.index')->with('success', __('Счёт :id удалён.', ['id' => $invoice]));
    }
}
