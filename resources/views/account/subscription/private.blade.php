{{--
    Особая вкладка Private: описание премиального пакета и цена; если пакет уже оплачен — его статус.
    Переменные: $user (BotUser), $current (Plan), $testMode.
--}}
@extends('themes.account.'.($accountTheme ?? 'classic').'.layout')

@section('title', __('subscription.private.title'))

@section('content')
@php
    $plan = \App\Enums\Plan::Private;
    $active = $current === $plan;
    $managerUrl = 'https://t.me/lesnichenkoP';
@endphp
<div class="mx-auto max-w-3xl">
    <header class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-600">{{ __('subscription.private.eyebrow') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ __('subscription.private.heading') }}</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.private.intro') }}</p>
    </header>

    @if($active)
        <div class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-4">
            <p class="font-semibold text-brand-700">{{ __('subscription.private.active', ['date' => $user->plan_ends_at?->format('d.m.Y')]) }}</p>
            <p class="mt-1 text-sm leading-6 text-gray-600">{{ __('subscription.private.active_text') }}</p>
            <a href="{{ $managerUrl }}" target="_blank" rel="noopener" class="mt-3 inline-flex rounded-xl border border-brand-300 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ __('subscription.private.contact') }}</a>
        </div>
    @endif

    @if($testMode && ! $active)
        <p class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ __('subscription.page.test_mode') }}</p>
    @endif

    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="private-title">
        <h2 id="private-title" class="text-lg font-semibold">{{ $plan->title() }}</h2>
        <p class="mt-3 text-3xl font-semibold tracking-tight">{{ $plan->priceLabel() }} <span class="text-sm font-medium text-gray-500">{{ __('subscription.per_year') }}</span></p>

        <div class="mt-6">
            @include('account.subscription._features', ['plan' => $plan])
        </div>

        <div class="mt-8">
            @include('account.subscription._buy', ['plan' => $plan, 'current' => $current, 'user' => $user])
        </div>
        <p class="mt-4 text-xs text-gray-500">{{ __('subscription.page.pay_note') }}</p>
    </section>

    <p class="mt-6"><a href="{{ route('account.subscription') }}" class="text-sm font-medium text-brand-700 hover:underline">{{ __('subscription.result.back') }}</a></p>
</div>
@endsection
