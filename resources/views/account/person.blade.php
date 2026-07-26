@extends('account.layout')

@section('title', $person->full_name ?: __('account.person.title'))

@section('content')
    <div class="miro-page">
        <a href="{{ route('account.people') }}" class="miro-button miro-button--text mb-5">← {{ __('account.back') }}</a>

        <div class="grid gap-6 lg:grid-cols-[.8fr_1.2fr]">
            <aside class="miro-card miro-card--pink p-6 sm:p-8">
                <div class="miro-avatar-xl mx-auto">
                    @if($person->avatar_path)
                        <img src="{{ Storage::url($person->avatar_path) }}" alt="{{ $person->full_name ?: __('account.person.title') }}">
                    @else
                        <div class="miro-avatar-xl__placeholder">{{ mb_strtoupper(mb_substr($person->full_name ?: $person->telegram_username ?: '?', 0, 1)) }}</div>
                    @endif
                </div>

                <p class="miro-eyebrow miro-person-profile__eyebrow">{{ __('account.person.title') }}</p>
                <h1 class="break-words text-center text-2xl font-medium tracking-tight text-[#050038]">{{ $person->full_name ?: __('account.not_specified') }}</h1>

                @if($person->telegram_username)
                    <p class="mt-2 text-center text-sm text-[#6b6f7e]">@{{ $person->telegram_username }}</p>
                    <a href="https://t.me/{{ $person->telegram_username }}" target="_blank" rel="noopener" class="miro-button miro-button--dark mt-7 w-full">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 3-7.2 18-3.4-7.4L3 10.2 21 3Zm0 0-10.6 10.6" />
                        </svg>
                        {{ __('account.contact') }}
                    </a>
                @else
                    <p class="mt-7 text-center text-sm leading-6 text-[#050038]/60">{{ __('account.profile.telegram_empty') }}</p>
                @endif
            </aside>

            <section class="space-y-6">
                <div class="miro-card p-6 sm:p-7">
                    <div class="miro-section-heading">
                        <h2>{{ __('account.person.about') }}</h2>
                        <span class="miro-icon-tile miro-icon-tile--teal"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0"/></svg></span>
                    </div>
                    <p class="whitespace-pre-line text-sm leading-7 text-[#555a6a]">{{ $person->description ?: __('account.profile.description_empty') }}</p>
                </div>

                <div class="miro-card miro-card--cream p-6 sm:p-7">
                    <div class="miro-section-heading">
                        <h2>{{ __('account.person.request') }}</h2>
                        <span class="miro-icon-tile miro-icon-tile--cream"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18m9-9H3"/></svg></span>
                    </div>
                    <p class="whitespace-pre-line text-sm leading-7 text-[#555a6a]">{{ $person->expectation ?: __('account.profile.expectation_empty') }}</p>
                </div>
            </section>
        </div>
    </div>
@endsection
