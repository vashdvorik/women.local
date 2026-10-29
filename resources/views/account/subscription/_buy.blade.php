{{--
    Кнопка оформления тарифа. $plan — App\Enums\Plan (платный), $current — действующий тариф участницы.
    Цена в кнопке берётся из настроек; серверная сторона всё равно берёт цену из настроек, а не из формы.

    Покупка тарифа может быть закрыта администратором («Подписки → Закрыть покупку»): тогда вместо «Купить» стоит форма
    «Оставить заявку», а после заявки — пояснение, что менеджер позвонит. Когда менеджер откроет покупку лично, кнопка
    «Купить» появляется снова (PlanPurchase::canBuy).
--}}
@php
    $purchase = app(\App\Services\Subscriptions\PlanPurchase::class);
    $price = $plan->priceLabel();
    $same = $current === $plan;
    $higher = $current->rank() > $plan->rank();
    $closed = $purchase->isClosed($plan);
    $canBuy = $purchase->canBuy($user, $plan);
    $pending = $closed && ! $canBuy ? $purchase->pendingRequest($user, $plan) : null;
    $label = $same
        ? __('subscription.page.renew', ['price' => $price])
        : ($current->isPaid()
            ? __('subscription.page.upgrade', ['plan' => $plan->shortTitle(), 'price' => $price])
            : __('subscription.page.choose', ['price' => $price]));
@endphp

@if($higher)
    <p class="rounded-xl bg-gray-50 px-4 py-3 text-center text-sm font-medium text-gray-500">{{ __('subscription.page.lower_included') }}</p>
@elseif($closed && ! $canBuy)
    @if($pending)
        <div class="rounded-xl border border-brand-100 bg-brand-50 px-4 py-3 text-center">
            <p class="text-sm font-semibold text-brand-700">{{ __('participant.plans.request_sent') }}</p>
            <p class="mt-1 text-xs leading-5 text-gray-600">{{ __('participant.plans.request_sent_text') }}</p>
        </div>
    @else
        {{-- Заявку нельзя отправить случайным нажатием: сначала окно подтверждения (Esc и «Отмена» закрывают его) --}}
        <form method="POST" action="{{ route('account.plan-request.store') }}" class="space-y-3"
              x-data="{ confirm: false }" @keydown.escape.window="confirm = false">
            @csrf
            <input type="hidden" name="plan" value="{{ $plan->value }}">
            <p class="text-xs leading-5 text-gray-500">{{ __('participant.plans.request_hint') }}</p>
            <button type="button" @click="confirm = true" class="w-full rounded-xl border border-brand-600 bg-white px-4 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-400 focus-visible:ring-offset-2">
                {{ __('participant.plans.request') }}
            </button>

            <div x-show="confirm" x-cloak class="sub-confirm" role="dialog" aria-modal="true" aria-labelledby="confirm-{{ $plan->value }}" @click.self="confirm = false">
                <div class="sub-confirm__box">
                    <h3 id="confirm-{{ $plan->value }}" class="sub-confirm__title">{{ __('participant.plans.confirm_title') }}</h3>
                    <p class="sub-confirm__text">{{ __('participant.plans.confirm_text', ['plan' => $plan->title()]) }}</p>
                    <div class="sub-confirm__actions">
                        <button type="button" class="sub-confirm__cancel" @click="confirm = false">{{ __('participant.plans.confirm_cancel') }}</button>
                        <button type="submit" class="sub-confirm__send">{{ __('participant.plans.confirm_send') }}</button>
                    </div>
                </div>
            </div>
        </form>
    @endif
@else
    @if($closed && $canBuy)
        <p class="mb-2 text-center text-xs font-medium text-brand-700">{{ __('participant.plans.unlocked') }}</p>
    @endif
    <form method="POST" action="{{ route('account.subscription.checkout') }}">
        @csrf
        <input type="hidden" name="plan" value="{{ $plan->value }}">
        <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-400 focus-visible:ring-offset-2">
            {{ $label }}
        </button>
    </form>
    @if($same && $user->plan_ends_at)
        <p class="mt-2 text-center text-xs text-gray-500">{{ __('subscription.page.renew_hint') }}</p>
    @endif
@endif
