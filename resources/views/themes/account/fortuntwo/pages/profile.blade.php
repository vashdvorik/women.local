@extends('themes.account.fortuntwo.layout')
@section('title', __('account.profile.title'))

@section('content')
<div class="fortun-profile-page">
    <header class="fortun-profile-header">
        <div class="fortun-profile-header__copy">
            <p class="miro-eyebrow">{{ __('account.nav.profile') }}</p>
            <h1 class="fortun-profile-header__title">{{ __('account.profile.title') }}</h1>
            <p class="fortun-profile-header__description">{{ __('account.profile.subtitle') }}</p>
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
                    {{ __('account.profile.public_label') }}
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

            @if($accountUser->telegram_username)
                <a href="https://t.me/{{ $accountUser->telegram_username }}" target="_blank" rel="noopener" class="fortun-profile-telegram">
                    <svg class="fortun-profile-telegram__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 3-7.2 18-3.4-7.4L3 10.2 21 3Zm0 0-10.6 10.6" />
                    </svg>
                    {{ '@' . $accountUser->telegram_username }}
                </a>
            @else
                <p class="fortun-profile-identity__empty">{{ __('account.profile.telegram_empty') }}</p>
            @endif
        </aside>

        <div class="fortun-profile-content">
            <section class="fortun-profile-panel">
                <div class="fortun-profile-panel__header">
                    <div>
                        <p class="fortun-profile-panel__eyebrow">{{ __('account.profile.business_profile') }}</p>
                        <h2 class="fortun-profile-panel__title">{{ __('account.profile.title') }}</h2>
                    </div>
                    <a href="{{ route('account.profile.edit') }}" class="fortun-profile-panel__link">
                        {{ __('account.profile.edit') }}
                        <span aria-hidden="true">→</span>
                    </a>
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

    <section class="miro-danger-zone fortun-profile-danger">
        <div class="miro-danger-zone__header">
            <div class="min-w-0">
                <h2>{{ __('account.profile.delete_title') }}</h2>
                <p>{{ __('account.profile.delete_text') }}</p>
            </div>
        </div>
        <div class="miro-danger-zone__actions">
            <form id="delete-profile-form" action="{{ route('account.profile.delete') }}" method="POST" onsubmit="return confirmDeleteProfile(event)">@csrf @method('DELETE')
                <button type="submit" class="miro-button miro-button--danger">{{ __('account.profile.delete_button') }}</button>
            </form>
        </div>
    </section>
</div>
@push('scripts')
<script>
    function confirmDeleteProfile(event) {
        if (!window.confirm(@json(__('account.profile.delete_confirm')))) {
            event.preventDefault();
            return false;
        }

        return true;
    }
</script>
@endpush
@endsection
