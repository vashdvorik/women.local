{{--
    «Подписка». Общая для всех тем: подключает макет текущей темы кабинета.
    Переменные: $user (BotUser), $current (Plan), $payments, $testMode, $popular (ключ тарифа с меткой «Самый популярный» или null).

    Страница зависит от состояния участницы:
      - тарифа нет: «Выберите тариф» и все три карточки;
      - тариф есть: «Ваша подписка» (тариф, срок, «Продлить») и карточки только тех тарифов, что выше текущего;
      - самый высокий тариф: только «Ваша подписка».
    В карточке коротко (цена, три главных пункта), полный список заказчика — в раскрывающемся «Что входит в тариф».
--}}
@extends('themes.account.'.($accountTheme ?? \App\Models\SiteSetting::DEFAULT_ACCOUNT_THEME).'.layout')

@section('title', __('subscription.page.title'))

@section('notices_inline', '1')

@section('content')
@php
    use App\Enums\Plan;

    $hasPlan = $current->isPaid();
    $offers = array_values(array_filter(Plan::paid(), fn (Plan $plan): bool => $plan->rank() > $current->rank()));
@endphp
<div class="mx-auto max-w-6xl">
    @include('account.partials.notices')

    <header class="mb-6 max-w-3xl">
        <h1 class="text-3xl font-semibold tracking-tight">{{ $hasPlan ? __('subscription.page.heading_current') : __('subscription.page.heading_choose') }}</h1>
        @if(! $hasPlan)
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.page.intro_choose') }}</p>
        @elseif($offers !== [])
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.page.intro_upgrade') }}</p>
        @endif
    </header>

    @if($testMode)
        <p class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ __('subscription.page.test_mode') }}</p>
    @endif

    @if($hasPlan)
        <section class="sub-current" aria-labelledby="current-plan">
            <div>
                <p class="sub-current__label">{{ __('subscription.page.your_plan') }}</p>
                <h2 id="current-plan" class="sub-current__name">{{ $current->title() }}</h2>
                @if($user->plan_ends_at)
                    <p class="sub-current__until">{{ __('subscription.page.until', ['date' => $user->plan_ends_at->format('d.m.Y')]) }}</p>
                @endif
            </div>
            <div class="sub-current__action">
                @include('account.subscription._buy', ['plan' => $current, 'current' => $current, 'user' => $user])
            </div>
        </section>
    @endif

    @if($offers !== [])
        <div class="sub-grid sub-grid--{{ count($offers) }}">
            @foreach($offers as $plan)
                <section class="sub-card sub-card--{{ $plan->value }}{{ $plan->value === $popular ? ' sub-card--popular' : '' }}" aria-labelledby="plan-{{ $plan->value }}">
                    @if($plan->value === $popular)
                        <span class="sub-card__badge">{{ __('subscription.page.popular') }}</span>
                    @endif
                    <div class="sub-card__head">
                        <h2 id="plan-{{ $plan->value }}" class="sub-card__name">{{ $plan->title() }}</h2>
                        <p class="sub-card__tagline">@foreach(preg_split('/(?<=[.!?])\s+/u', trim(__('subscription.plans.'.$plan->value.'.tagline'))) as $sentence)<span>{{ $sentence }}</span>@endforeach</p>
                    </div>

                    <div class="sub-card__body">
                        <p class="sub-card__price">
                            {{ $plan->priceLabel() }}
                            <span>{{ __('subscription.per_year') }}</span>
                        </p>
                        @if(\Illuminate\Support\Facades\Lang::has('subscription.plans.'.$plan->value.'.monthly'))
                            <p class="sub-card__month">{{ __('subscription.plans.'.$plan->value.'.monthly', ['price' => $plan->pricePerMonth()]) }}</p>
                        @endif

                        @include('account.subscription._features', ['plan' => $plan])

                        <div class="sub-card__buy">
                            @include('account.subscription._buy', ['plan' => $plan, 'current' => $current, 'user' => $user])
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    <p class="mt-6 text-xs text-gray-500">{{ __('subscription.page.pay_note') }}</p>

    <section class="mt-10" aria-labelledby="payments-title">
        <h2 id="payments-title" class="text-lg font-semibold">{{ __('subscription.history.title') }}</h2>
        @if($payments->isEmpty())
            <p class="mt-3 text-sm text-gray-500">{{ __('subscription.history.empty') }}</p>
        @else
            <div class="mt-4 overflow-x-auto rounded-2xl border border-gray-200 bg-white">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('subscription.history.date') }}</th>
                            <th class="px-4 py-3">{{ __('subscription.history.plan') }}</th>
                            <th class="px-4 py-3">{{ __('subscription.history.amount') }}</th>
                            <th class="px-4 py-3">{{ __('subscription.history.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($payments as $payment)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $payment->planEnum()->shortTitle() }}@if($payment->is_test) <span class="text-xs text-amber-700">(test)</span>@endif</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ number_format($payment->rubles(), 0, ',', "\u{00A0}") }} {{ __('subscription.currency') }}</td>
                                <td class="px-4 py-3">{{ __('subscription.history.status_labels.'.$payment->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
