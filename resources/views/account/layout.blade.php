<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Кабинет') — INSPIRE</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans:  ['Inter', 'system-ui', 'sans-serif'],
                        serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                        },
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        :focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; border-radius: 6px; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(124,58,237,.2); border-radius: 4px; }
        @media (max-width: 1023px) { [x-cloak] { display: none !important; } }
    </style>
    @stack('head')
</head>
<body class="h-full bg-[#f7f8fa] text-[#0f172a] antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-20 bg-black/50 backdrop-blur-sm lg:hidden"
         @click="sidebarOpen = false"></div>

    <div class="flex h-full">

        {{-- ── Sidebar ── --}}
        <aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 flex w-64 shrink-0 flex-col bg-white
                      transition-transform duration-200 ease-out
                      lg:static lg:translate-x-0 lg:z-auto"
               style="box-shadow: 1px 0 0 rgba(0,0,0,.06), 4px 0 24px rgba(0,0,0,.03)">

            {{-- Logo --}}
            <div class="flex shrink-0 items-center gap-3 px-5 pb-5 pt-6">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                      style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 12px rgba(124,58,237,.3)">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
                <div class="leading-none">
                    <p class="text-sm font-bold tracking-tight text-[#0f172a]">INSPIRE</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-widest text-gray-400">Community</p>
                </div>
            </div>

            {{-- User card --}}
            <div class="mx-3 mb-5 shrink-0 rounded-2xl p-4"
                 style="background:linear-gradient(135deg,#f5f3ff,#ede9fe)">
                <div class="flex items-center gap-3">
                    @if($accountUser->avatar_path)
                    <img src="{{ Storage::url($accountUser->avatar_path) }}"
                         alt="{{ $accountUser->full_name }}"
                         class="h-10 w-10 shrink-0 rounded-full object-cover"
                         style="box-shadow:0 2px 8px rgba(124,58,237,.3)">
                    @else
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white"
                         style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 2px 8px rgba(124,58,237,.3)">
                        {{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}
                    </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-[#0f172a]">
                            {{ explode(' ', (string) $accountUser->full_name)[0] }}
                        </p>
                        @if($accountUser->telegram_username)
                        <p class="mt-0.5 truncate text-xs font-medium text-brand-600">
                            {{ '@' . $accountUser->telegram_username }}
                        </p>
                        @endif
                    </div>
                    <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-400"
                          style="box-shadow:0 0 0 3px rgba(52,211,153,.2)"></span>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3">
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-gray-400">Навигация</p>
                @php
                    $navItems = [
                        ['route' => 'account.index',     'label' => 'Главная',
                         'path'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'account.matches',   'label' => 'AI-матчи',
                         'path'  => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['route' => 'account.search',    'label' => 'AI-поиск',
                         'path'  => 'M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z'],
                        ['route' => 'account.people',    'label' => 'Люди',
                         'path'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['route' => 'account.opportunities.index', 'label' => 'Возможности',
                         'path'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'account.knowledge', 'label' => 'База знаний',
                         'path'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        ['route' => 'account.profile',   'label' => 'Мой профиль',
                         'path'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ];
                @endphp
                @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
                <a href="{{ route($item['route']) }}"
                   @click="sidebarOpen = false"
                   class="group mb-0.5 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150
                          {{ $active
                             ? 'bg-brand-50 font-semibold text-brand-700'
                             : 'font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all duration-150
                                 {{ $active ? 'text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200 group-hover:text-gray-600' }}"
                          @if($active) style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 2px 6px rgba(124,58,237,.25)" @endif>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['path'] }}"/>
                        </svg>
                    </span>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if($active)
                    <svg class="h-3 w-3 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                    @endif
                </a>
                @endforeach
            </nav>

            {{-- Logout --}}
            <div class="shrink-0 border-t border-gray-100 px-3 py-4">
                <form action="{{ route('account.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium
                               text-gray-400 transition-all duration-150 hover:bg-red-50 hover:text-red-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100
                                     transition-colors group-hover:bg-red-100">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </span>
                        Выйти
                    </button>
                </form>
            </div>

        </aside>

        {{-- ── Main area ── --}}
        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Mobile header --}}
            <header class="sticky top-0 z-10 flex h-14 shrink-0 items-center gap-3 border-b border-gray-100 bg-white px-4 lg:hidden">
                <button @click="sidebarOpen = true"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 transition"
                    aria-label="Открыть меню">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span class="text-sm font-semibold text-[#0f172a]">@yield('title', 'Кабинет')</span>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto px-6 py-8 lg:px-10">
                @if(session('success'))
                <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
                @endif
                @yield('content')
            </main>

        </div>
    </div>

<script>
    (function () {
        if (!window.Telegram || !window.Telegram.WebApp) return;
        var tg = window.Telegram.WebApp;

        // Signal the app is ready and expand to full height
        tg.ready();
        tg.expand();

        // Hide logout button inside Mini App — auto-auth makes it pointless
        var logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) logoutForm.closest('div').style.display = 'none';

        document.querySelectorAll('a[href*="t.me/Inspiremoldova_bot"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                tg.openTelegramLink(link.href);
                tg.close();
            });
        });

        // After logout the server redirects to login with ?logout=1 — no need
        // to close the app, the redirect will show the logged-out state.
    })();
</script>
@stack('scripts')
</body>
</html>
