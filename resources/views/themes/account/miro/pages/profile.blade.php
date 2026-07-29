@extends('themes.account.miro.layout')
@section('title', __('account.profile.title'))

@section('content')
<div class="miro-page">
    <header class="miro-page-header">
        <div class="miro-page-header__copy">
            <p class="miro-eyebrow">{{ __('account.nav.profile') }}</p>
            <h1 class="miro-page-title">{{ __('account.profile.title') }}</h1>
            <p class="miro-page-description">{{ __('account.profile.subtitle') }}</p>
        </div>
        <a href="{{ route('account.profile.edit') }}" class="miro-button miro-button--dark miro-profile-header__action">{{ __('account.profile.edit') }}</a>
    </header>

    <div class="grid gap-6 lg:grid-cols-[.78fr_1.22fr]">
        <aside class="miro-card miro-card--pink p-6">
            <div class="miro-avatar-xl">
                @if($accountUser->avatar_path)
                    <img src="{{ Storage::url($accountUser->avatar_path) }}" alt="{{ $accountUser->full_name ?: __('account.profile.contact_info') }}">
                @else
                    <div class="miro-avatar-xl__placeholder">{{ mb_strtoupper(mb_substr($accountUser->full_name ?: $accountUser->telegram_username ?: '?', 0, 1)) }}</div>
                @endif
            </div>
            <p class="miro-card__label miro-profile-contact-label mt-6">{{ __('account.profile.contact_info') }}</p>
            <h2 class="miro-card__title break-words">{{ $accountUser->full_name ?: __('account.not_specified') }}</h2>
            @if($accountUser->telegram_username)
                <a href="https://t.me/{{ $accountUser->telegram_username }}" target="_blank" rel="noopener" class="miro-profile-telegram mt-3">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 3-7.2 18-3.4-7.4L3 10.2 21 3Zm0 0-10.6 10.6" />
                    </svg>
                    {{ '@' . $accountUser->telegram_username }}
                </a>
            @else
                <p class="mt-3 text-sm text-[#050038]/60">{{ __('account.profile.telegram_empty') }}</p>
            @endif
            <a href="{{ route('account.profile.edit') }}" class="miro-button miro-button--dark mt-7">{{ __('account.profile.edit') }}</a>
        </aside>

        <section class="space-y-6">
            <div class="miro-card p-6 sm:p-7">
                <div class="miro-section-heading">
                    <h2>{{ __('account.profile.business_profile') }}</h2>
                    <span class="rounded-full bg-[#c3faf5] px-3 py-1 text-xs font-medium text-[#187574]">{{ __('account.profile.public_label') }}</span>
                </div>
                <div class="space-y-5">
                    <div class="miro-card--soft rounded-2xl p-4">
                        <p class="miro-card__label">{{ __('account.profile.description') }}</p>
                        <p class="m-0 whitespace-pre-line text-sm leading-6 text-[#050038]">{{ $accountUser->description ?: __('account.profile.description_empty') }}</p>
                    </div>
                    <div class="miro-card--pink rounded-2xl p-4">
                        <p class="miro-card__label">{{ __('account.profile.expectation') }}</p>
                        <p class="m-0 whitespace-pre-line text-sm leading-6 text-[#050038]">{{ $accountUser->expectation ?: __('account.profile.expectation_empty') }}</p>
                    </div>
                </div>
            </div>

            <div class="miro-card miro-card--teal flex items-start gap-4 p-6">
                <span class="miro-icon-tile miro-icon-tile--teal shrink-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"/></svg>
                </span>
                <div>
                    <h2 class="m-0 text-base font-medium text-[#050038]">{{ __('account.profile.profile_tip_title') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-[#187574]">{{ __('account.profile.profile_tip_text') }}</p>
                </div>
            </div>
        </section>
    </div>

    <div class="miro-danger-zone">
        <div class="miro-danger-zone__header">
            <span class="miro-danger-zone__icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12m-9 0V5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2m2 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V7m3 4v5m2-5v5" />
                </svg>
            </span>
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
    </div>
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

