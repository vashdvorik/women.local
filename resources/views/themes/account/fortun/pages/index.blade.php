@extends('themes.account.fortun.layout')
@section('title', __('account.dashboard.title'))

@section('content')
<div class="miro-page">
    <header class="miro-page-header">
        <div class="miro-page-header__copy">
            <p class="miro-eyebrow">{{ __('account.dashboard.eyebrow') }}</p>
            <h1 class="miro-page-title">{{ __('account.dashboard.hello', ['name' => explode(' ', (string) $accountUser->full_name)[0]]) }}</h1>
            <p class="miro-page-description">{{ __('account.dashboard.intro') }}</p>
        </div>
        <div class="miro-actions">
            <a href="{{ route('account.profile.edit') }}" class="miro-button miro-button--dark">{{ __('account.dashboard.action_edit') }}</a>
            <a href="{{ route('account.search') }}" class="miro-button miro-button--outline">{{ __('account.dashboard.action_search') }}</a>
        </div>
    </header>

    @if(empty($accountUser->description))
        <div class="miro-alert miro-alert--cream mb-7">
            <span class="miro-icon-tile miro-icon-tile--cream shrink-0">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 3.8 2.7 18a2 2 0 0 0 1.75 3h15.1a2 2 0 0 0 1.75-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold">{{ __('account.dashboard.complete_title') }}</p>
                <p class="mt-1 text-xs leading-relaxed opacity-80">{{ __('account.dashboard.complete_text') }}</p>
            </div>
            <a href="{{ route('account.profile.edit') }}" class="miro-button miro-button--dark shrink-0">{{ __('account.dashboard.complete_button') }}</a>
        </div>
    @endif

    <div class="miro-profile-hero">
        <section class="miro-card miro-card--pink miro-profile-hero__main">
            <div class="flex items-start gap-4">
                <div class="miro-avatar-xl shrink-0">
                    @if($accountUser->avatar_path)
                        <img src="{{ Storage::url($accountUser->avatar_path) }}" alt="{{ $accountUser->full_name }}">
                    @else
                        <div class="miro-avatar-xl__placeholder">{{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}</div>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="miro-card__label">{{ __('account.dashboard.profile_label') }}</p>
                    <h2 class="miro-card__title">{{ $accountUser->full_name ?: __('account.not_specified') }}</h2>
                    @if($accountUser->telegram_username)
                        <p class="mt-2 text-sm text-[#261153]/70">{{ '@' . $accountUser->telegram_username }}</p>
                    @endif
                </div>
            </div>
            @if($accountUser->description)
                <p class="mt-7 max-w-xl text-sm leading-6 text-[#261153]/80">{{ mb_strlen($accountUser->description) > 180 ? mb_substr($accountUser->description, 0, 180) . '…' : $accountUser->description }}</p>
                <p class="mt-5 inline-flex rounded-full bg-white/70 px-3 py-1.5 text-xs font-medium text-[#261153]">{{ __('account.dashboard.profile_ready_title') }}</p>
            @else
                <p class="mt-7 max-w-xl text-sm leading-6 text-[#261153]/80">{{ __('account.dashboard.profile_ready_text') }}</p>
            @endif
        </section>

        <aside class="miro-card miro-card--teal miro-profile-hero__side">
            <span class="miro-icon-tile miro-icon-tile--teal">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18m9-9H3m15.36-6.36L5.64 18.36m12.72 0L5.64 5.64"/></svg>
            </span>
            <p class="miro-card__label mt-5">{{ __('account.dashboard.spotlight_label') }}</p>
            <h2 class="miro-card__title">{{ __('account.dashboard.spotlight_title') }}</h2>
            <p class="miro-card__text">{{ __('account.dashboard.spotlight_text') }}</p>
            <a href="{{ route('account.people') }}" class="miro-button miro-button--dark mt-6">{{ __('account.dashboard.explore_link') }}</a>
        </aside>
    </div>

    <div class="miro-section-heading">
        <div>
            <h2>{{ __('account.dashboard.explore_title') }}</h2>
            <p>{{ __('account.dashboard.explore_text') }}</p>
        </div>
    </div>

    <div class="miro-stat-grid">
        @foreach([
            ['route' => 'account.profile', 'title' => __('account.nav.profile'), 'note' => __('account.dashboard.profile_note'), 'value' => '01', 'tone' => 'pink'],
            ['route' => 'account.matches', 'title' => __('account.nav.matches'), 'note' => __('account.dashboard.matches_note'), 'value' => '02', 'tone' => 'teal'],
            ['route' => 'account.people', 'title' => __('account.nav.people'), 'note' => __('account.dashboard.people_note'), 'value' => '03', 'tone' => 'coral'],
            ['route' => 'account.opportunities.index', 'title' => __('account.nav.opportunities'), 'note' => __('account.dashboard.opportunities_note'), 'value' => '04', 'tone' => 'gold'],
        ] as $card)
            <a href="{{ route($card['route']) }}" class="miro-card miro-stat miro-card--{{ $card['tone'] }} group">
                <span class="miro-stat__value">{{ $card['value'] }}</span>
                <p class="miro-stat__label font-medium text-[#261153]">{{ $card['title'] }}</p>
                <p class="miro-stat__label">{{ $card['note'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="miro-card miro-card--soft mt-7 flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="miro-card__label">{{ __('account.dashboard.tip_label') }}</p>
            <p class="m-0 text-sm leading-6 text-[#585364]">{{ __('account.dashboard.tip_text') }}</p>
        </div>
        <a href="{{ route('account.opportunities.create') }}" class="miro-button miro-button--outline shrink-0">{{ __('account.dashboard.tip_action') }}</a>
    </div>
</div>
@endsection
