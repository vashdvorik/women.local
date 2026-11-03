{{--
    Итог оплаты (SuccessURL / FailURL банка). Статус берётся из нашей базы, а не из адреса, по которому пришла участница.
    Оплата прошла: контроллер сразу ведёт в кабинет, а «Оплата прошла» показывает всплывающее окно на главной
    (account/home.blade.php). Эта страница нужна для остальных исходов: ждём банк, платёж не прошёл, платёж не найден.
    Переменные: $user (BotUser, свежий), $payment (Payment|null), $state: processing | failed | unknown.
--}}
@extends('themes.account.'.($accountTheme ?? \App\Models\SiteSetting::DEFAULT_ACCOUNT_THEME).'.layout')

@section('title', __('subscription.result.title'))

@if($state === 'processing')
    {{-- Пока банк не подтвердил оплату, страница сама проверяет её раз в 5 секунд. --}}
    @push('head')
        <meta http-equiv="refresh" content="5">
    @endpush
@endif

@section('notices_inline', '1')

@php
    $managerUrl = 'https://t.me/'.ltrim((string) config('site.contacts.telegram'), '@');
@endphp

@section('content')
<div class="pay-result-wrap">
    @include('account.partials.notices')
    <section class="pay-result pay-result--{{ $state }}" aria-live="polite">
        @if($state === 'processing')
            <div class="pay-result__icon" role="status" aria-label="{{ __('subscription.result.processing_title') }}"><span class="pay-result__spinner"></span></div>
            <h1 class="pay-result__title">{{ __('subscription.result.processing_title') }}</h1>
            <p class="pay-result__text">{{ __('subscription.result.processing_text') }}</p>
            <div class="pay-result__actions">
                <a href="{{ route('account.subscription') }}" class="pay-result__btn pay-result__btn--ghost">{{ __('subscription.result.back') }}</a>
            </div>
        @elseif($state === 'failed')
            <div class="pay-result__icon" aria-hidden="true">
                <svg width="30" height="30" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 3.5h.01M10.3 3.9L2.4 18a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
            </div>
            <h1 class="pay-result__title">{{ __('subscription.result.failed_title') }}</h1>
            <p class="pay-result__text">{{ __('subscription.result.failed_text') }}</p>

            @if($payment)
                <p class="pay-result__badge">{{ __('subscription.result.failed_amount', ['plan' => $payment->planEnum()->title(), 'sum' => number_format($payment->rubles(), 0, ',', ' ')]) }}</p>
            @endif

            <div class="pay-result__reasons">
                <p class="pay-result__reasons-title">{{ __('subscription.result.failed_reasons_title') }}</p>
                <ul>
                    @foreach(__('subscription.result.failed_reasons') as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="pay-result__actions">
                <a href="{{ route('account.subscription') }}" class="pay-result__btn">{{ __('subscription.result.retry') }}</a>
                <a href="{{ $managerUrl }}" target="_blank" rel="noopener" class="pay-result__btn pay-result__btn--ghost">{{ __('subscription.result.contact') }}</a>
            </div>
            <p class="pay-result__note">{{ __('subscription.result.contact_hint') }}</p>
        @else
            <div class="pay-result__icon" aria-hidden="true">
                <svg width="30" height="30" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
            </div>
            <h1 class="pay-result__title">{{ __('subscription.result.unknown_title') }}</h1>
            <p class="pay-result__text">{{ __('subscription.result.unknown_text') }}</p>
            <div class="pay-result__actions">
                <a href="{{ route('account.subscription') }}" class="pay-result__btn">{{ __('subscription.result.back') }}</a>
                <a href="{{ $managerUrl }}" target="_blank" rel="noopener" class="pay-result__btn pay-result__btn--ghost">{{ __('subscription.result.contact') }}</a>
            </div>
        @endif
    </section>
</div>
@endsection
