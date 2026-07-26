@extends('account.layout')

@section('title', __('account.matches.title'))

@section('content')
    <div class="miro-page">
        <header class="miro-page-header">
            <div class="miro-page-header__copy">
                <p class="miro-eyebrow">{{ __('account.nav.matches') }}</p>
                <h1 class="miro-page-title">{{ __('account.matches.title') }}</h1>
                <p class="miro-page-description">{{ __('account.matches.subtitle') }}</p>
            </div>
            <span class="miro-icon-tile miro-icon-tile--teal hidden sm:inline-grid">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z"/></svg>
            </span>
        </header>
    </div>

    @if($matches->isEmpty())
        <div class="miro-page">
            <div class="miro-empty">
                <span class="miro-empty__mark"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z"/></svg></span>
                <h2>{{ __('account.matches.empty_title') }}</h2>
                <p>{{ __('account.matches.empty_text') }}</p>
                <a href="{{ route('account.profile.edit') }}" class="miro-button miro-button--dark mt-6">{{ __('account.profile.edit') }}</a>
            </div>
        </div>
    @else
        <div class="miro-page space-y-4">
            @foreach($matches as $match)
                @php($person = $match['user'])
                <article class="miro-card p-5 sm:p-6">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 gap-4">
                            <div class="miro-directory-card__avatar shrink-0">
                                @if($person->avatar_path)
                                    <img src="{{ Storage::url($person->avatar_path) }}" alt="{{ $person->full_name ?: __('account.people.title') }}">
                                @else
                                    <div class="miro-directory-card__avatar-placeholder">{{ mb_strtoupper(mb_substr($person->full_name ?: $person->telegram_username ?: '?', 0, 1)) }}</div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="miro-eyebrow mb-2">{{ __('account.matches.score') }} · {{ max(0, min(100, round((float) $match['score'] * 100))) }}%</p>
                                <h2 class="break-words text-xl font-medium tracking-tight text-[#050038]">{{ $person->full_name ?: __('account.not_specified') }}</h2>
                                @if($person->telegram_username)<p class="mt-1 text-sm text-[#6b6f7e]">@{{ $person->telegram_username }}</p>@endif
                                <p class="mt-3 line-clamp-2 text-sm leading-6 text-[#555a6a]">{{ $person->description ?: $person->expectation ?: __('account.not_filled') }}</p>
                            </div>
                        </div>
                        <div class="miro-actions shrink-0">
                            <a href="{{ route('account.people.show', $person) }}" class="miro-button miro-button--dark">{{ __('account.people.open') }}</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
