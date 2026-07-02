<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('account.login.title') }} — {{ __('account.brand') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>body { font-family: "Inter", sans-serif; }</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="fixed right-4 top-4 flex rounded-full border border-gray-200 bg-white p-1 shadow-sm">
        @foreach(['ru' => 'RU', 'en' => 'EN', 'ro' => 'RO'] as $locale => $label)
            <a href="{{ route('language.switch', $locale) }}"
               class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === $locale ? 'bg-violet-700 text-white' : 'text-gray-500 hover:text-violet-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div id="tma-loading" style="display:none" class="fixed inset-0 flex flex-col items-center justify-center bg-gray-50 z-50">
        <div class="mb-4 h-10 w-10 animate-spin rounded-full border-4" style="border-color:rgba(124,58,237,.2);border-top-color:#7c3aed"></div>
        <p class="text-sm text-gray-500">{{ __('account.login.loading') }}</p>
    </div>

    <div id="tma-not-member" style="display:none" class="w-full max-w-sm text-center">
        <span class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl shadow-lg" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </span>
        <h1 class="mb-2 text-2xl font-bold text-gray-900">{{ __('account.brand') }}</h1>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <p class="mb-1 text-sm font-medium text-gray-700">{{ __('account.login.closed_title') }}</p>
            <p class="text-sm text-gray-500">{{ __('account.login.closed_text') }}</p>
        </div>
    </div>

    <div id="login-form-wrapper" class="w-full max-w-sm">
        <div class="mb-8 flex flex-col items-center text-center">
            <span class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl shadow-lg" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('account.brand') }}</h1>
            <p class="mt-1 text-sm text-gray-400">{{ __('account.login.subtitle') }}</p>
        </div>

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-gray-100 bg-white p-7 text-center shadow-sm">
            <h2 class="mb-2 font-semibold text-gray-900">{{ __('account.login.telegram_title') }}</h2>
            <div class="mb-6 space-y-3 text-left">
                @foreach([__('account.login.step_1'), __('account.login.step_2')] as $index => $step)
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">{{ $index + 1 }}</span>
                        <p class="text-sm text-gray-600">{{ $step }}</p>
                    </div>
                @endforeach
            </div>

            <a href="https://t.me/WomenComBot?start=login" id="tg-login-btn" target="_blank"
               class="inline-flex w-full items-center justify-center gap-2.5 rounded-xl px-5 py-3 text-sm font-semibold text-white transition hover:-translate-y-px hover:shadow-md"
               style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                {{ __('account.login.open_bot') }}
            </a>
            <p class="mt-3 text-xs text-gray-400">{{ __('account.login.approved_only') }}</p>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400"><a href="/" class="hover:underline">{{ __('account.back_to_site') }}</a></p>
    </div>

    <script>
        (function () {
            var tg = window.Telegram && window.Telegram.WebApp;
            if (!tg || !tg.initData) return;
            var params = new URLSearchParams(window.location.search);
            if (params.get('logout') === '1') return;
            document.getElementById('login-form-wrapper').style.display = 'none';
            document.getElementById('tma-loading').style.display = 'flex';
            tg.ready();
            fetch('{{ route('account.tma-auth') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ init_data: tg.initData })
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data.ok) window.location.href = data.redirect;
                else if (data.reason === 'not_member') {
                    document.getElementById('tma-loading').style.display = 'none';
                    document.getElementById('tma-not-member').style.display = 'block';
                } else {
                    document.getElementById('tma-loading').style.display = 'none';
                    document.getElementById('login-form-wrapper').style.display = 'block';
                }
            }).catch(function () {
                document.getElementById('tma-loading').style.display = 'none';
                document.getElementById('login-form-wrapper').style.display = 'block';
            });
        })();
        (function () {
            var btn = document.getElementById('tg-login-btn');
            if (!btn) return;
            btn.addEventListener('click', function (e) {
                if (window.Telegram && window.Telegram.WebApp) {
                    e.preventDefault();
                    window.Telegram.WebApp.openTelegramLink('https://t.me/WomenComBot?start=login');
                    window.Telegram.WebApp.close();
                }
            });
        })();
    </script>
</body>
</html>
