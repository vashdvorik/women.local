@extends('account.layout')

@section('title', __('account.search.title'))

@section('content')
<div class="miro-page">
    <header class="miro-page-header">
        <div class="miro-page-header__copy">
            <p class="miro-eyebrow">{{ __('account.nav.search') }}</p>
            <h1 class="miro-page-title">{{ __('account.search.title') }}</h1>
            <p class="miro-page-description">{{ __('account.search.subtitle') }}</p>
        </div>
        <span class="miro-icon-tile miro-icon-tile--pink hidden sm:inline-grid">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/></svg>
        </span>
    </header>

    <form method="GET" action="{{ route('account.search') }}" class="miro-card p-4">
        <div class="flex flex-col gap-3 md:flex-row">
            <input
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="{{ __('account.search.placeholder') }}"
                aria-label="{{ __('account.search.placeholder') }}"
                class="min-h-14 flex-1 rounded-2xl border border-slate-200 px-5 text-slate-900 outline-none transition"
            >
            <button class="miro-button miro-button--dark min-h-14 rounded-2xl px-7">
                {{ __('account.search.button') }}
            </button>
        </div>
    </form>

    <div class="mt-6">
        @if($results === null)
            <div class="miro-alert miro-alert--success">
                <span class="miro-icon-tile miro-icon-tile--teal shrink-0"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 17h.01M12 7v6m9 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                <div><h2 class="text-base font-medium">{{ __('account.search.hint_title') }}</h2><p class="mt-1 text-sm leading-6 opacity-80">{{ __('account.search.hint_text') }}</p></div>
            </div>
        @elseif($results->isEmpty())
            <div class="miro-empty">
                <span class="miro-empty__mark"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 9 6 6m0-6-6 6m12-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                <h2>{{ __('account.search.empty_title') }}</h2>
                <p>{{ __('account.search.empty_text') }}</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($results as $result)
                    @php($person = $result['user'])
                    <article class="miro-card p-5 sm:p-6">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 gap-4">
                                <div class="miro-directory-card__avatar shrink-0">
                                    @if($person->avatar_path)
                                        <img src="{{ Storage::url($person->avatar_path) }}" alt="{{ $person->full_name }}">
                                    @else
                                        <div class="miro-directory-card__avatar-placeholder">{{ mb_strtoupper(mb_substr($person->full_name ?: $person->telegram_username ?: '?', 0, 1)) }}</div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="miro-eyebrow mb-2">{{ __('account.search.title') }}</p>
                                    <h2 class="truncate text-xl font-medium tracking-tight text-[#050038]">{{ $person->full_name ?: __('account.not_specified') }}</h2>
                                    @if($person->telegram_username)<p class="mt-1 text-sm text-[#6b6f7e]">@{{ $person->telegram_username }}</p>@endif
                                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-[#555a6a]">{{ $person->description ?: $person->expectation ?: __('account.not_filled') }}</p>
                                </div>
                            </div>

                            <a href="{{ route('account.people.show', $person) }}" class="miro-button miro-button--dark shrink-0">
                                {{ __('account.people.open') }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
