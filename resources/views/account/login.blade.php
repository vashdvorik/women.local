<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — INSPIRE Community</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>body { font-family: "Inter", sans-serif; }</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 px-4">

    {{-- TMA loading overlay (hidden by default, shown via JS when inside Telegram) --}}
    <div id="tma-loading" style="display:none"
         class="fixed inset-0 flex flex-col items-center justify-center bg-gray-50 z-50">
        <div class="w-10 h-10 rounded-full border-4 border-brand-200 border-t-brand-600 animate-spin mb-4"
             style="border-color:rgba(124,58,237,.2);border-top-color:#7c3aed"></div>
        <p class="text-sm text-gray-500">Выполняется вход…</p>
    </div>

    {{-- TMA: not a member message --}}
    <div id="tma-not-member" style="display:none"
         class="w-full max-w-sm text-center">
        <span class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg mb-4 mx-auto"
              style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </span>
        <h1 style="font-family:'Cormorant Garamond',serif" class="text-3xl font-semibold text-gray-900 mb-2">INSPIRE</h1>
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <p class="text-sm text-gray-700 font-medium mb-1">Доступ пока закрыт</p>
            <p class="text-sm text-gray-500">Ваша заявка ещё не одобрена или не подана. Напишите боту /start чтобы подать заявку.</p>
        </div>
    </div>

    {{-- Normal login form (hidden when inside Telegram) --}}
    <div id="login-form-wrapper" class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <span class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg mb-4" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <h1 style="font-family:'Cormorant Garamond',serif" class="text-3xl font-semibold text-gray-900">INSPIRE</h1>
            <p class="text-sm text-gray-400 mt-1">Личный кабинет участника</p>
        </div>

        @if(session('error'))
        <div class="mb-4 flex items-start gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
        @endif

        <div class="bg-white border border-gray-100 rounded-2xl p-7 shadow-sm text-center">
            <h2 class="font-semibold text-gray-900 mb-2">Войти через Telegram</h2>

            <div class="text-left space-y-3 mb-6">
                <div class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-violet-100 text-violet-700 text-xs font-bold flex items-center justify-center mt-0.5">1</span>
                    <p class="text-sm text-gray-600">Откройте бота по кнопке ниже</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-violet-100 text-violet-700 text-xs font-bold flex items-center justify-center mt-0.5">3</span>
                    <p class="text-sm text-gray-600">Перейдите по ссылке из сообщения бота</p>
                </div>
            </div>

            <a href="https://t.me/Inspiremoldova_bot?start=login" id="tg-login-btn" target="_blank"
               class="inline-flex items-center justify-center gap-2.5 w-full px-5 py-3 text-sm font-semibold text-white rounded-xl transition hover:shadow-md hover:-translate-y-px"
               style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Открыть @Inspiremoldova_bot
            </a>
            <p class="mt-3 text-xs text-gray-400">Доступ только для одобренных участников</p>
        </div>

        <p class="text-center mt-6 text-xs text-gray-400">
            <a href="/" class="hover:underline">← Вернуться на сайт</a>
        </p>
    </div>{{-- /login-form-wrapper --}}

    <script>
        // ── Telegram Mini App: auto-auth ──────────────────────────────────────
        (function () {
            var tg = window.Telegram && window.Telegram.WebApp;
            if (!tg || !tg.initData) return;

            // If the user explicitly logged out (?logout=1), do NOT auto-login —
            // show the form so they can choose to log back in manually.
            var params = new URLSearchParams(window.location.search);
            if (params.get('logout') === '1') {
                document.getElementById('login-form-wrapper').style.display = 'block';
                return;
            }

            // We're inside Telegram — hide form, show spinner, attempt auto-auth
            document.getElementById('login-form-wrapper').style.display = 'none';
            document.getElementById('tma-loading').style.display = 'flex';

            tg.ready();

            fetch('{{ route('account.tma-auth') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ init_data: tg.initData })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    window.location.href = data.redirect;
                } else if (data.reason === 'not_member') {
                    document.getElementById('tma-loading').style.display = 'none';
                    document.getElementById('tma-not-member').style.display = 'block';
                } else {
                    // Unexpected error — fall back to normal form
                    document.getElementById('tma-loading').style.display = 'none';
                    document.getElementById('login-form-wrapper').style.display = 'block';
                }
            })
            .catch(function () {
                document.getElementById('tma-loading').style.display = 'none';
                document.getElementById('login-form-wrapper').style.display = 'block';
            });
        })();

        // ── Telegram button behaviour in regular browser ──────────────────────
        (function () {
            var btn = document.getElementById('tg-login-btn');
            if (!btn) return;

            btn.addEventListener('click', function (e) {
                if (window.Telegram && window.Telegram.WebApp) {
                    e.preventDefault();
                    window.Telegram.WebApp.openTelegramLink(
                        'https://t.me/Inspiremoldova_bot?start=login'
                    );
                    window.Telegram.WebApp.close();
                }
                // Иначе — обычный браузер, открывается стандартная ссылка target="_blank"
            });
        })();
    </script>
</body>
</html>