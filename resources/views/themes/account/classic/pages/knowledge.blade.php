@extends('themes.account.classic.layout')

@section('title', __('account.knowledge.title'))

@section('content')
    <div class="miro-page">
        <header class="miro-page-header">
            <div class="miro-page-header__copy">
                <p class="miro-eyebrow">{{ __('account.nav.knowledge') }}</p>
                <h1 class="miro-page-title">{{ __('account.knowledge.title') }}</h1>
                <p class="miro-page-description">{{ __('account.knowledge.subtitle') }}</p>
            </div>
            <span class="miro-icon-tile miro-icon-tile--coral hidden sm:inline-grid"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15ZM4 20.5A2.5 2.5 0 0 1 6.5 18H20"/></svg></span>
        </header>

        <div class="miro-card miro-card--pink p-6 sm:p-10">
            <div class="grid gap-10 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
                <div>
                    <span class="inline-flex rounded-full bg-white/75 px-4 py-2 text-xs font-medium text-[#050038]">{{ __('account.nav.knowledge') }}</span>
                    <h2 class="mt-6 max-w-xl text-3xl font-medium tracking-tight text-[#050038] sm:text-4xl">{{ __('account.knowledge.coming_title') }}</h2>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-[#050038]/75">{{ __('account.knowledge.coming_text') }}</p>
                    <a href="{{ route('account.opportunities.index') }}" class="miro-button miro-button--dark mt-7">{{ __('account.nav.opportunities') }}</a>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @foreach(__('account.knowledge.modules') as $index => $module)
                        <div class="rounded-3xl border border-white/70 bg-white/65 p-5">
                            <div class="mb-8 flex h-10 w-10 items-center justify-center rounded-2xl {{ $index % 2 === 0 ? 'bg-[#c3faf5] text-[#187574]' : 'bg-[#ffc6c6] text-[#050038]' }} text-sm font-medium">0{{ $index + 1 }}</div>
                            <p class="font-medium text-[#050038]">{{ $module }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

