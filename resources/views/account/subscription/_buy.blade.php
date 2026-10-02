{{--
    Кнопка оформления тарифа. $plan — App\Enums\Plan (платный), $current — действующий тариф участницы.
    Цена в кнопке берётся из настроек; серверная сторона всё равно берёт цену из настроек, а не из формы.
--}}
@php
    $price = $plan->priceLabel();
    $same = $current === $plan;
    $higher = $current->rank() > $plan->rank();
    $label = $same
        ? __('subscription.page.renew', ['price' => $price])
        : ($current->isPaid()
            ? __('subscription.page.upgrade', ['plan' => $plan->shortTitle(), 'price' => $price])
            : __('subscription.page.subscribe', ['price' => $price]));
@endphp

@if($higher)
    <p class="rounded-xl bg-gray-50 px-4 py-3 text-center text-sm font-medium text-gray-500">{{ __('subscription.page.lower_included') }}</p>
@else
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
