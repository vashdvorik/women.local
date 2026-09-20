@props(['title' => 'Панель', 'flush' => false])

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ menu: false }" class="bg-canvas text-ink">

    <div x-show="menu" x-transition.opacity @click="menu = false"
         class="fixed inset-0 z-30 bg-black/40 lg:hidden" hidden></div>

    <x-admin.sidebar x-bind:class="menu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                     class="transition-transform lg:transition-none" />

    <div class="admin-main">
        @unless($flush)
            <header class="admin-header">
                <button type="button" @click="menu = !menu" class="btn-icon lg:hidden" aria-label="Меню">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 5h14M3 10h14M3 15h14" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="min-w-0">
                    {{-- В каком лагере админки находится страница: «Внешний сайт» или «Кабинеты участниц». --}}
                    <p class="text-micro uppercase text-ink-muted truncate">{{ \App\Support\AdminCamp::label(\App\Support\AdminCamp::current()) }}</p>
                    <h1 class="text-page font-semibold truncate">{{ $title }}</h1>
                </div>
                @isset($actions)
                    <div class="ml-auto flex items-center gap-3">{{ $actions }}</div>
                @endisset
            </header>
        @endunless

        <main class="{{ $flush ? 'flex-1' : 'admin-body' }}">
            {{ $slot }}
        </main>
    </div>

    <x-admin.toast />
</body>
</html>
