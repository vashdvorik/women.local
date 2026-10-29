{{--
    Главная кабинета (account.index) для всех тарифов: человек сразу видит, где что лежит и с чего начать, а не упирается
    в чат. Список «С чего начать» с галочками, плитки всех разделов (дублируют меню, с пояснением «что это» и «как
    пользоваться»), «Что нового». У Open те же плитки, закрытые разделы с замком, вместо «Что нового» — материалы
    публичного сайта и приглашение подписаться.
    Переменные: App\Support\CabinetHome::build() (name, paid, steps, doneCount, tiles, opportunities, members, feed) и $user.
--}}
@extends('themes.account.'.($accountTheme ?? \App\Models\SiteSetting::DEFAULT_ACCOUNT_THEME).'.layout')

@section('title', __('account.nav.home'))

@php
    $allDone = $doneCount === count($steps);
@endphp

@section('notices_inline', '1')

@section('content')
<div class="account-home mx-auto max-w-6xl">
    @include('account.partials.notices')

    <header class="mb-6 max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-600">{{ __('account.home.eyebrow') }}</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('account.home.hello', ['name' => $name]) }}</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">{{ $paid ? __('account.home.intro') : __('account.home.intro_community') }}</p>
    </header>

    {{-- Открыть сообщество: только у тарифа Open --}}
    @unless($paid)
        <section class="mb-6 flex flex-col gap-4 rounded-3xl border border-brand-100 bg-brand-50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6" aria-labelledby="upgrade-title">
            <div class="max-w-2xl">
                <h2 id="upgrade-title" class="text-base font-semibold">{{ __('subscription.community_home.upgrade_title') }}</h2>
                <p class="mt-1.5 text-sm leading-6 text-gray-600">{{ __('subscription.community_home.upgrade_text') }}</p>
            </div>
            <a href="{{ route('account.subscription') }}" class="inline-flex shrink-0 justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('subscription.community_home.upgrade_cta') }}</a>
        </section>
    @endunless

    {{-- С чего начать --}}
    <section class="account-home-steps mb-8 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="home-steps-title">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="home-steps-title" class="text-base font-semibold">{{ __('account.home.steps_title') }}</h2>
            <p class="text-xs font-medium text-gray-500">{{ __('account.home.steps_progress', ['done' => $doneCount, 'total' => count($steps)]) }}</p>
        </div>
        <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-valuemin="0" aria-valuemax="{{ count($steps) }}" aria-valuenow="{{ $doneCount }}">
            <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ count($steps) > 0 ? (int) round($doneCount / count($steps) * 100) : 0 }}%"></div>
        </div>

        @if($allDone)
            <p class="mt-4 text-sm text-gray-600">{{ __('account.home.steps_all_done') }}</p>
        @else
            <ol class="mt-4 space-y-3">
                @foreach($steps as $i => $step)
                    <li class="account-home-step flex items-start gap-3 {{ $step['done'] ? 'account-home-step--done' : '' }}">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $step['done'] ? 'bg-emerald-100 text-emerald-700' : 'bg-brand-50 text-brand-700' }}" aria-hidden="true">
                            @if($step['done'])✓@else{{ $i + 1 }}@endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold {{ $step['done'] ? 'text-gray-400 line-through' : '' }}">{{ __('account.home.steps.'.$step['key'].'.title') }}</p>
                            <p class="mt-0.5 text-sm leading-5 text-gray-600">{{ __('account.home.steps.'.$step['key'].'.text') }}</p>
                        </div>
                        @if($step['done'])
                            <span class="shrink-0 pt-1 text-xs font-medium text-emerald-700">{{ __('account.home.step_done') }}</span>
                        @else
                            <a href="{{ route($step['route']) }}" class="shrink-0 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('account.home.step_open') }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    {{-- Как тут всё устроено (после «С чего начать», чтобы главное действие было на первом экране): закрывается и больше не показывается на этом устройстве --}}
    <section x-data="{ open: (() => { try { return ! localStorage.getItem('cabinetHintClosed'); } catch (e) { return true; } })() }"
             x-show="open" x-cloak
             class="account-home-hint mb-6 flex items-start gap-3 rounded-2xl border border-brand-100 bg-brand-50 p-4 sm:p-5" aria-labelledby="home-hint-title">
        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white" aria-hidden="true">i</span>
        <div class="min-w-0 flex-1">
            <h2 id="home-hint-title" class="text-sm font-semibold">{{ __('account.home.hint_title') }}</h2>
            <p class="mt-1 text-sm leading-6 text-gray-700 lg:hidden">{{ __('account.home.hint_mobile') }}</p>
            <p class="mt-1 hidden text-sm leading-6 text-gray-700 lg:block">{{ __('account.home.hint_desktop') }}</p>
            <button type="button" class="mt-3 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-brand-700 shadow-sm ring-1 ring-brand-100 transition hover:bg-brand-50"
                    @click="open = false; try { localStorage.setItem('cabinetHintClosed', '1'); } catch (e) {}">{{ __('account.home.hint_close') }}</button>
        </div>
    </section>

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

    {{-- Что нового: возможности участниц видят все тарифы, новых участниц и каталог — Community и Private --}}
    <section class="mb-10" aria-labelledby="home-fresh-title">
        <h2 id="home-fresh-title" class="mb-4 text-base font-semibold">{{ __('account.home.fresh_title') }}</h2>
        <div class="grid gap-6 {{ $paid ? 'lg:grid-cols-2' : '' }}">
            @if(true)
                <div class="flex flex-col rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <h3 class="text-sm font-semibold">{{ __('account.home.fresh_opportunities') }}</h3>
                    @forelse($opportunities as $item)
                        <article class="mt-4 border-t border-gray-100 pt-4 first:border-0 first:pt-0">
                            <p class="text-xs text-gray-500">{{ $item->created_at->format('d.m.Y') }}@if($item->author) · {{ $item->author->full_name }}@endif</p>
                            <h4 class="mt-1 text-sm font-semibold leading-snug"><a href="{{ route('account.opportunities.index') }}" class="hover:text-brand-700 hover:underline">{{ $item->title }}</a></h4>
                        </article>
                    @empty
                        <p class="mt-4 text-sm text-gray-500">{{ __('account.home.fresh_empty') }}</p>
                    @endforelse
                    <a href="{{ route('account.opportunities.index') }}" class="mt-auto pt-5 text-sm font-medium text-brand-700 hover:underline">{{ __('account.home.fresh_all_opportunities') }} →</a>
                </div>

            @endif
            @if($paid)
                <div class="flex flex-col rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <h3 class="text-sm font-semibold">{{ __('account.home.fresh_members') }}</h3>
                    @forelse($members as $member)
                        <a href="{{ route('account.people.show', $member->id) }}" class="mt-4 flex items-start gap-3 border-t border-gray-100 pt-4 first:border-0 first:pt-0">
                            @if($member->avatar_path)
                                <img src="{{ Storage::url($member->avatar_path) }}" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover" loading="lazy">
                            @else
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-sm font-medium text-brand-700" aria-hidden="true">{{ mb_strtoupper(mb_substr((string) $member->full_name, 0, 1)) }}</span>
                            @endif
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold leading-snug hover:text-brand-700">{{ $member->full_name }}</span>
                                <span class="mt-0.5 block text-xs leading-5 text-gray-500">{{ filled($member->description) ? \Illuminate\Support\Str::limit($member->description, 90) : __('account.home.no_description') }}</span>
                            </span>
                        </a>
                    @empty
                        <p class="mt-4 text-sm text-gray-500">{{ __('account.home.fresh_empty') }}</p>
                    @endforelse
                    <a href="{{ route('account.people') }}" class="mt-auto pt-5 text-sm font-medium text-brand-700 hover:underline">{{ __('account.home.fresh_all_members') }} →</a>
                </div>
            @endif
        </div>
    </section>

    @unless($paid)
        {{-- Тариф Open: то, что есть на публичном сайте --}}
        @php
            $sections = [
                ['key' => 'news', 'title' => __('subscription.community_home.news'), 'all' => route('events')],
                ['key' => 'publications', 'title' => __('subscription.community_home.publications'), 'all' => route('media.publications')],
                ['key' => 'opportunities', 'title' => __('subscription.community_home.opportunities'), 'all' => route('opportunities')],
            ];
        @endphp
        <div class="mb-10 grid gap-6 lg:grid-cols-3">
            @foreach($sections as $section)
                <section class="flex flex-col rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="feed-{{ $section['key'] }}">
                    <h2 id="feed-{{ $section['key'] }}" class="text-base font-semibold">{{ $section['title'] }}</h2>

                    @forelse($feed[$section['key']] as $item)
                        <article class="mt-4 border-t border-gray-100 pt-4 first:mt-4 first:border-0 first:pt-0">
                            @if($item['date'] !== '')
                                <p class="text-xs text-gray-500">{{ $item['date'] }}</p>
                            @endif
                            <h3 class="mt-1 text-sm font-semibold leading-snug">
                                <a href="{{ $item['href'] }}"@if($item['external']) target="_blank" rel="noopener"@endif class="hover:text-brand-700 hover:underline">{{ $item['title'] }}</a>
                            </h3>
                            @if($item['excerpt'] !== '')
                                <p class="mt-1 line-clamp-2 text-sm leading-6 text-gray-600">{{ $item['excerpt'] }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="mt-4 text-sm text-gray-500">{{ __('subscription.community_home.empty') }}</p>
                    @endforelse

                    <a href="{{ $section['all'] }}" target="_blank" rel="noopener" class="mt-auto pt-5 text-sm font-medium text-brand-700 hover:underline">{{ __('subscription.community_home.all') }} →</a>
                </section>
            @endforeach
        </div>
    @endunless
</div>
@endsection
