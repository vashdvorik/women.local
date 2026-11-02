{{--
    Главная кабинета (account.index) для всех тарифов: человек сразу видит, где что лежит. При первом входе поверх страницы
    идут подсказки («Далее» / «Закрыть», account/partials/tour.blade.php), потом их можно открыть снова. Плитки всех разделов (дублируют меню, с пояснением «что это» и «как
    пользоваться»), «Что нового». У Open те же плитки, закрытые разделы с замком, вместо «Что нового» — материалы
    публичного сайта и приглашение подписаться.
    Переменные: App\Support\CabinetHome::build() (name, paid, tiles, opportunities, members, feed), $user и $showTour (показать подсказки сами).
--}}
@extends('themes.account.'.($accountTheme ?? \App\Models\SiteSetting::DEFAULT_ACCOUNT_THEME).'.layout')

@section('title', __('account.nav.home'))

@section('notices_inline', '1')

@section('content')
<div class="account-home mx-auto max-w-6xl">
    @include('account.partials.notices')

    <header class="mb-6 max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-600">{{ __('account.home.eyebrow') }}</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('account.home.hello', ['name' => $name]) }}</h1>
        @if($paid)
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('account.home.intro') }}</p>
        @endif
        <button type="button" data-tour-start class="mt-3 text-sm font-semibold text-brand-700 hover:underline">{{ __('account.tour.replay') }}</button>
    </header>

    {{-- Заметка о тарифе Community: что доступно сейчас и что откроет Active. Закрывается и больше не показывается --}}
    @if($showPlanNote ?? false)
        <section x-data="{ open: true }" x-show="open" x-cloak
                 class="mb-6 flex items-start gap-4 rounded-2xl border border-brand-100 bg-brand-50 p-4 sm:items-center sm:p-5" aria-label="{{ __('account.home.plan_note_label') }}">
            <p class="min-w-0 flex-1 text-sm leading-6 text-gray-700">{{ __('account.home.intro_community') }}</p>
            <form method="POST" action="{{ route('account.notice.seen') }}" class="shrink-0"
                  @submit.prevent="open = false; fetch($el.action, { method: 'POST', body: new FormData($el), headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })">
                @csrf
                <button type="submit" class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-brand-700 shadow-sm ring-1 ring-brand-100 transition hover:bg-brand-50">{{ __('account.tour.close') }}</button>
            </form>
        </section>
    @endif

    {{-- Разделы кабинета: то же, что в меню, с пояснением --}}
    <section class="mb-10" aria-labelledby="home-tiles-title">
        <h2 id="home-tiles-title" class="mb-4 text-base font-semibold">{{ __('account.home.tiles_title') }}</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tiles as $tile)
                <a href="{{ route($tile['route']) }}" class="account-home-tile group flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-brand-300 hover:shadow-md">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tile['locked'] ? 'bg-gray-100 text-gray-400' : 'bg-brand-50 text-brand-700' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tile['path'] }}"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-semibold leading-snug">{{ $tile['label'] }}</h3>
                            @if($tile['locked'])
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4m-1 0h10a1 1 0 011 1v7a1 1 0 01-1 1H7a1 1 0 01-1-1v-7a1 1 0 011-1z"/></svg>
                                    {{ __('account.home.tile_locked') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-gray-700">{{ __('account.home.tiles.'.$tile['key'].'.what') }}</p>
                    <p class="mt-2 text-xs leading-5 text-gray-500"><span class="font-semibold text-gray-600">{{ __('account.home.tile_how') }}</span> {{ __('account.home.tiles.'.$tile['key'].'.how') }}</p>
                    <span class="mt-auto pt-4 text-sm font-medium text-brand-700 group-hover:underline">{{ __('account.home.tile_go') }}</span>
                </a>
            @endforeach
        </div>
    </section>
</div>
@include('account.partials.tour', ['tourSteps' => \App\Support\CabinetTour::steps($user), 'replaySteps' => \App\Support\CabinetTour::steps($user, true), 'autoStart' => $showTour ?? false])
@endsection
