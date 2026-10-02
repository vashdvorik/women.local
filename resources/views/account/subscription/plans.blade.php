{{--
    «Подписка» — три тарифа кабинета. Общая для всех тем: подключает макет текущей темы кабинета и использует её палитру brand-*.
    Переменные: $user (BotUser), $current (Plan), $payments, $testMode.
--}}
@extends('themes.account.'.($accountTheme ?? 'classic').'.layout')

@section('title', __('subscription.page.title'))

@section('content')
@php use App\Enums\Plan; @endphp
<div class="mx-auto max-w-6xl">
    <header class="mb-8 max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-600">{{ __('subscription.page.eyebrow') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ __('subscription.page.heading') }}</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.page.intro') }}</p>
    </header>

    <div class="mb-8 flex flex-col gap-3 rounded-2xl border border-brand-100 bg-brand-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-700">{{ __('subscription.page.your_plan') }}</p>
            <p class="mt-1 text-lg font-semibold">{{ $current->title() }}</p>
        </div>
        <p class="text-sm text-gray-600">
            @if($current->isPaid() && $user->plan_ends_at)
                {{ __('subscription.page.until', ['date' => $user->plan_ends_at->format('d.m.Y')]) }}
            @else
                {{ __('subscription.page.no_expiry') }}
            @endif
        </p>
    </div>

    @if($testMode)
        <p class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ __('subscription.page.test_mode') }}</p>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        @foreach(Plan::cases() as $plan)
            @php $isCurrent = $current === $plan; @endphp
            <section class="flex flex-col rounded-3xl border bg-white p-6 shadow-sm {{ $isCurrent ? 'border-brand-400 ring-2 ring-brand-100' : 'border-gray-200' }}" aria-labelledby="plan-{{ $plan->value }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="plan-{{ $plan->value }}" class="text-base font-semibold leading-tight">{{ $plan->title() }}</h2>
                        <p class="mt-1 text-xs text-gray-500">{{ __('subscription.plans.'.$plan->value.'.tagline') }}</p>
                    </div>
                    @if($isCurrent)
                        <span class="shrink-0 rounded-full bg-brand-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-white">{{ __('subscription.page.open_badge') }}</span>
                    @endif
                </div>

                <p class="mt-5 text-3xl font-semibold tracking-tight">
                    {{ $plan->priceLabel() }}
                    @if($plan->isPaid())<span class="text-sm font-medium text-gray-500"> {{ __('subscription.per_year') }}</span>@endif
                </p>

                <div class="mt-5 flex-1">
                    @include('account.subscription._features', ['plan' => $plan])
                </div>

                <div class="mt-6">
                    @if($plan->isPaid())
                        @include('account.subscription._buy', ['plan' => $plan, 'current' => $current, 'user' => $user])
                    @elseif($isCurrent)
                        <p class="rounded-xl bg-gray-50 px-4 py-3 text-center text-sm font-medium text-gray-500">{{ __('subscription.page.included') }}</p>
                    @endif
                </div>
            </section>
        @endforeach
    </div>

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
