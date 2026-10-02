{{--
    Главная кабинета на тарифе Open: только то, что есть на публичном сайте (новости, публикации, возможности),
    и приглашение подписаться. Каталога участниц, поиска и ИИ-помощника здесь нет.
    Переменные: $user (BotUser), $feed (App\Support\OpenFeed).
--}}
@extends('themes.account.'.($accountTheme ?? 'classic').'.layout')

@section('title', __('subscription.open_home.title'))

@section('content')
<div class="mx-auto max-w-6xl">
    <header class="mb-8 max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-600">{{ __('subscription.open_home.eyebrow') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ __('subscription.open_home.hello', ['name' => explode(' ', (string) $user->full_name)[0]]) }}</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('subscription.open_home.intro') }}</p>
    </header>

    <section class="mb-10 flex flex-col gap-5 rounded-3xl border border-brand-100 bg-brand-50 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8" aria-labelledby="upgrade-title">
        <div class="max-w-2xl">
            <h2 id="upgrade-title" class="text-lg font-semibold">{{ __('subscription.open_home.upgrade_title') }}</h2>
            <p class="mt-2 text-sm leading-6 text-gray-600">{{ __('subscription.open_home.upgrade_text') }}</p>
        </div>
        <a href="{{ route('account.subscription') }}" class="inline-flex shrink-0 justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">{{ __('subscription.open_home.upgrade_cta') }}</a>
    </section>

    @php
        $sections = [
            ['key' => 'news', 'title' => __('subscription.open_home.news'), 'all' => route('events')],
            ['key' => 'publications', 'title' => __('subscription.open_home.publications'), 'all' => route('media.publications')],
            ['key' => 'opportunities', 'title' => __('subscription.open_home.opportunities'), 'all' => route('opportunities')],
        ];
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        @foreach($sections as $section)
            <section class="flex flex-col rounded-3xl border border-gray-200 bg-white p-6 shadow-sm" aria-labelledby="feed-{{ $section['key'] }}">
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
                    <p class="mt-4 text-sm text-gray-500">{{ __('subscription.open_home.empty') }}</p>
                @endforelse

                <a href="{{ $section['all'] }}" target="_blank" rel="noopener" class="mt-auto pt-5 text-sm font-medium text-brand-700 hover:underline">{{ __('subscription.open_home.all') }} →</a>
            </section>
        @endforeach
    </div>

    <p class="mt-8"><a href="{{ route('account.profile') }}" class="text-sm font-medium text-brand-700 hover:underline">{{ __('subscription.open_home.profile') }}</a></p>
</div>
@endsection
