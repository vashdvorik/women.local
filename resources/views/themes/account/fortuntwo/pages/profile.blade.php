@extends('themes.account.fortuntwo.layout')
@section('title', __('account.profile.title'))

@section('content')
<div class="fortun-profile-page">
    <header class="fortun-profile-header">
        <div class="fortun-profile-header__copy">
            <h1 class="fortun-profile-header__title">{{ __('account.profile.title') }}</h1>
            <p class="fortun-profile-header__description">{{ __('account.profile.subtitle') }}</p>
            @if($accountUser->status === \App\Models\BotUser::STATUS_PENDING)
                <p class="fortun-profile-note"><span aria-hidden="true"></span>{{ __('participant.banner.pending') }}</p>
            @endif
        </div>
        <a href="{{ route('account.profile.edit') }}" class="miro-button miro-button--dark fortun-profile-header__action">
            {{ __('account.profile.edit') }}
        </a>
    </header>

    <div class="fortun-profile-layout">
        <aside class="fortun-profile-identity">
            <div class="fortun-profile-identity__topline">
                <span class="fortun-profile-badge">
                    <span class="fortun-profile-badge__dot" aria-hidden="true"></span>
                    {{ $accountUser->is_hidden ? __('account.profile.hidden_label') : __('account.profile.public_label') }}
                </span>
            </div>

            <div class="fortun-profile-avatar">
                @if($accountUser->avatar_path)
                    <img src="{{ Storage::url($accountUser->avatar_path) }}" alt="{{ $accountUser->full_name ?: __('account.profile.contact_info') }}">
                @else
                    <span class="fortun-profile-avatar__placeholder">{{ mb_strtoupper(mb_substr($accountUser->full_name ?: $accountUser->telegram_username ?: '?', 0, 1)) }}</span>
                @endif
            </div>

            <p class="fortun-profile-identity__label">{{ __('account.profile.contact_info') }}</p>
            <h2 class="fortun-profile-identity__name">{{ $accountUser->full_name ?: __('account.not_specified') }}</h2>

            <p class="fortun-profile-identity__empty">{{ $accountUser->email }}</p>
            @if($accountUser->phone)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $accountUser->phone) }}" class="fortun-profile-telegram">{{ $accountUser->phone }}</a>
            @endif

            {{-- Telegram нужен только для уведомлений: входа через него нет --}}
            <div class="mt-6 w-full border-t border-[#E9DCD5] pt-5 text-left">
                <p class="fortun-profile-identity__label">{{ __('participant.telegram.title') }}</p>
                @if($accountUser->hasTelegram())
                    <p class="mt-2 text-sm font-medium text-[#526B5A]">{{ __('participant.telegram.connected') }}@if($accountUser->telegram_username) · {{ '@' . $accountUser->telegram_username }}@endif</p>
                    <form action="{{ route('account.telegram.disconnect') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="miro-button miro-button--outline w-full">{{ __('participant.telegram.disconnect') }}</button>
                    </form>
                @else
                    <p class="mt-2 text-sm leading-6 text-[#756158]">{{ __('participant.telegram.text') }}</p>
                    <form action="{{ route('account.telegram.connect') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="miro-button miro-button--dark w-full">{{ __('participant.telegram.connect') }}</button>
                    </form>
                @endif
            </div>
        </aside>

        <div class="fortun-profile-content">
            <section class="fortun-profile-panel">
                <div class="fortun-profile-panel__header">
                    <h2 class="fortun-profile-panel__title">{{ __('account.profile.business_profile') }}</h2>
                </div>

                <div class="fortun-profile-fields">
                    <article class="fortun-profile-field">
                        <p class="fortun-profile-field__label">{{ __('account.profile.description') }}</p>
                        <p class="fortun-profile-field__text">{{ $accountUser->description ?: __('account.profile.description_empty') }}</p>
                    </article>

                    <article class="fortun-profile-field fortun-profile-field--coral">
                        <p class="fortun-profile-field__label">{{ __('account.profile.expectation') }}</p>
                        <p class="fortun-profile-field__text">{{ $accountUser->expectation ?: __('account.profile.expectation_empty') }}</p>
                    </article>
                </div>
            </section>

            <aside class="fortun-profile-tip">
                <div>
                    <h2 class="fortun-profile-tip__title">{{ __('account.profile.profile_tip_title') }}</h2>
                    <p class="fortun-profile-tip__text">{{ __('account.profile.profile_tip_text') }}</p>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
