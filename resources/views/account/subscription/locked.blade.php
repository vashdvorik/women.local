{{--
    Закрытый раздел для тарифа ниже нужного (RequirePlan): сообщение на месте раздела и кнопка «Купить подписку».
    Переменные: $plan (Plan — минимальный тариф раздела), $section (название раздела из меню или null).
--}}
@extends('themes.account.'.($accountTheme ?? \App\Models\SiteSetting::DEFAULT_ACCOUNT_THEME).'.layout')

@section('title', $section ?? __('subscription.locked.title'))

@section('content')
<div class="mx-auto max-w-xl py-6 sm:py-10">
    <section class="rounded-3xl border border-gray-200 bg-white p-8 text-center shadow-sm sm:p-10" aria-labelledby="locked-title">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-700" aria-hidden="true">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4.5" y="10.5" width="15" height="9.5" rx="2.5"/>
                <path d="M8 10.5V8a4 4 0 018 0v2.5"/>
            </svg>
        </span>

        <h1 id="locked-title" class="mt-5 text-2xl font-semibold tracking-tight">{{ $section ?? __('subscription.locked.title') }}</h1>

        <p class="mx-auto mt-3 max-w-md text-base leading-7 text-gray-600">{{ __('subscription.locked.notice', ['plan' => $plan->title()]) }}</p>

        <a href="{{ route('account.subscription') }}"
           class="mt-7 inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-brand-700 sm:w-auto">
            {{ __('subscription.locked.buy') }}
        </a>
    </section>
</div>
@endsection
