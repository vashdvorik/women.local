@php
    $accountTheme = 'fortuntwo';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('account.cabinet')) — {{ __('account.brand') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset_v('themes/account/fortuntwo/images/brand/favicon-w4w.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Prata&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Manrope', 'Inter', 'system-ui', 'sans-serif'], serif: ['Prata', 'Georgia', 'serif'] }, colors: { brand: { 50:'#F1E3DA',100:'#E6CFC1',200:'#D8C5BA',300:'#DBB89B',400:'#C99A73',500:'#B9855B',600:'#8E5B3D',700:'#74472F' } } } } }
    </script>
    <link rel="stylesheet" href="{{ asset_v('themes/account/fortuntwo/css/theme.css') }}">
    @stack('head')
</head>
<body class="h-full bg-white text-[#21140F] antialiased account-theme-fortuntwo" x-data="{ sidebarOpen: false }">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-black/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

    <div class="flex h-full">
        <aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="account-sidebar fixed inset-y-0 left-0 z-30 flex w-64 shrink-0 flex-col bg-white transition-transform duration-200 ease-out lg:static lg:translate-x-0 lg:z-auto">

            @include('account.partials.brand-logo')

            <div class="account-user-card mx-3 mb-4 shrink-0 rounded-2xl p-4">
                <div class="flex items-center gap-3">
                    @if($accountUser->avatar_path)
                        <img src="{{ Storage::url($accountUser->avatar_path) }}" alt="{{ $accountUser->full_name }}" class="h-10 w-10 shrink-0 rounded-full object-cover">
                    @else
                        <div class="account-avatar-placeholder flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-medium">
                            {{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-[#21140F]">{{ explode(' ', (string) $accountUser->full_name)[0] }}</p>
                        <p class="mt-0.5 truncate text-xs font-medium text-brand-600">{{ $accountUser->currentPlan()->isPaid() ? $accountUser->currentPlan()->shortTitle() : $accountUser->email }}</p>
                    </div>
                </div>
            </div>

            <div class="account-language-switcher mx-3 mb-4 flex rounded-full border border-gray-200 bg-gray-50 p-1">
                @foreach(['ru' => 'RU', 'en' => 'EN', 'ro' => 'RO'] as $locale => $label)
                    <a href="{{ route('language.switch', $locale) }}"
                       class="flex-1 rounded-full px-2 py-1 text-center text-xs font-semibold transition {{ app()->getLocale() === $locale ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:text-brand-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <nav class="flex-1 overflow-y-auto px-3">
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('account.nav_title') }}</p>
                @php $navItems = \App\Support\CabinetNav::items($accountUser, includeLocked: false); @endphp
                @foreach($navItems as $item)
                    @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
                    <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
                       class="group mb-0.5 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150 {{ $active ? 'bg-brand-50 font-semibold text-brand-700' : 'font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="account-nav-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $active ? 'account-nav-icon--active text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['path'] }}"/></svg>
                        </span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="shrink-0 border-t border-gray-100 px-3 py-4">
                <a href="{{ url('/') }}" class="account-site-link">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12h18M3 12l6-6m-6 6 6 6"/></svg>
                    <span>{{ __('account.back_to_site') }}</span>
                </a>
                <form action="{{ route('account.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-[#8C776E] transition hover:bg-[#F1E3DA] hover:text-[#74472F]">
                        {{ __('account.logout') }}
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="account-mobile-header sticky top-0 z-10 flex h-14 shrink-0 items-center gap-3 border-b border-gray-100 bg-white px-4 lg:hidden">
                <button @click="sidebarOpen = true" class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 transition" aria-label="{{ __('account.open_menu') }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="text-sm font-medium text-[#21140F]">@yield('title', __('account.cabinet'))</span>
            </header>

            <main class="account-main flex-1 overflow-y-auto px-4 pb-28 pt-6 sm:px-6 lg:px-10 lg:pb-8 lg:pt-8">
                {{-- Страницы с центрированной колонкой (@section('notices_inline')) выводят сообщения сами, внутри колонки --}}
                @if(trim($__env->yieldContent('notices_inline')) === '')
                    @include('account.partials.notices')
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Нижняя панель на телефоне: главные разделы всегда под рукой, «Ещё» открывает полное меню. --}}
    @php
        $barKeys = ['home', 'possibilities', 'search', 'profile'];
        $barItems = array_values(array_filter($navItems, fn ($item) => in_array($item['key'], $barKeys, true)));
    @endphp
    {{-- Одна страница в меню (тарифа ещё нет): нижняя панель не нужна, «Подписка» открывается из верхней полосы --}}
    @if(count($navItems) > 1)
    <nav class="account-bottom-nav fixed inset-x-0 bottom-0 z-20 border-t border-gray-200 bg-white lg:hidden" aria-label="{{ __('account.nav_title') }}">
        <div class="mx-auto flex max-w-lg items-stretch justify-around pb-[env(safe-area-inset-bottom)]">
            @foreach($barItems as $item)
                @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
                <a href="{{ route($item['route']) }}" @if($active) aria-current="page" @endif
                   class="account-bottom-nav__item flex min-w-0 flex-1 flex-col items-center gap-1 px-0.5 pb-2 pt-2.5 text-[10px] font-medium transition {{ $active ? 'text-brand-700' : 'text-gray-500' }}">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" stroke-width="{{ $active ? '2' : '1.7' }}" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['path'] }}"/></svg>
                    <span class="max-w-full truncate">{{ match ($item['key']) { 'search' => __('account.nav.search_short'), 'profile' => __('account.nav.profile_short'), default => $item['label'] } }}</span>
                </a>
            @endforeach
            <button type="button" @click="sidebarOpen = true" aria-label="{{ __('account.open_menu') }}"
                    class="account-bottom-nav__item flex min-w-0 flex-1 flex-col items-center gap-1 px-0.5 pb-2 pt-2.5 text-[10px] font-medium text-gray-500 transition">
                <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Support\CabinetNav::moreIcon() }}"/></svg>
                <span>{{ __('account.nav.more') }}</span>
            </button>
        </div>
    </nav>
    @endif

<script>
    (function () {
        if (!window.Telegram || !window.Telegram.WebApp) return;
        var tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();
        var logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) logoutForm.closest('div').style.display = 'none';
        document.querySelectorAll('a[href*="t.me/WomenComBot"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                tg.openTelegramLink(link.href);
                tg.close();
            });
        });
    })();
</script>
@stack('scripts')
</body>
</html>
