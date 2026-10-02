{{--
    Итог оплаты (SuccessURL / FailURL банка). Статус берётся из нашей базы, а не из адреса, по которому пришла участница.
    Переменные: $user (BotUser, свежий), $payment (Payment|null), $state: paid | processing | failed | unknown.
--}}
@extends('themes.account.'.($accountTheme ?? 'classic').'.layout')

@section('title', __('subscription.result.title'))

@if($state === 'processing')
    {{-- Пока банк не подтвердил оплату, страница сама проверяет её раз в 5 секунд. --}}
    @push('head')
        <meta http-equiv="refresh" content="5">
    @endpush
@endif

@section('content')
<div class="mx-auto max-w-xl py-6">
    <div class="rounded-3xl border bg-white p-8 text-center shadow-sm {{ $state === 'paid' ? 'border-emerald-200' : ($state === 'failed' || $state === 'unknown' ? 'border-red-200' : 'border-gray-200') }}">
        @if($state === 'paid')
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="mt-5 text-2xl font-semibold">{{ __('subscription.result.paid_title') }}</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.result.paid_text', ['plan' => $payment->planEnum()->title(), 'date' => $user->plan_ends_at?->format('d.m.Y')]) }}</p>
            <a href="{{ route('account.index') }}" class="mt-7 inline-flex rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('subscription.result.cabinet') }}</a>
        @elseif($state === 'processing')
            <div class="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-brand-100 border-t-brand-600" role="status" aria-label="{{ __('subscription.result.processing_title') }}"></div>
            <h1 class="mt-5 text-2xl font-semibold">{{ __('subscription.result.processing_title') }}</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.result.processing_text') }}</p>
            <a href="{{ route('account.subscription') }}" class="mt-7 inline-flex rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">{{ __('subscription.result.back') }}</a>
        @elseif($state === 'failed')
            <h1 class="text-2xl font-semibold">{{ __('subscription.result.failed_title') }}</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.result.failed_text') }}</p>
            <a href="{{ route('account.subscription') }}" class="mt-7 inline-flex rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('subscription.result.back') }}</a>
        @else
            <h1 class="text-2xl font-semibold">{{ __('subscription.result.unknown_title') }}</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.result.unknown_text') }}</p>
            <a href="{{ route('account.subscription') }}" class="mt-7 inline-flex rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('subscription.result.back') }}</a>
        @endif
    </div>
</div>
@endsection
