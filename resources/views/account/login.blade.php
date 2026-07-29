@php
    $publicTheme = \App\Models\SiteSetting::landingTheme();
    $publicTheme = array_key_exists($publicTheme, \App\Models\SiteSetting::LANDING_THEMES) ? $publicTheme : 'miro';
    $themeLogo = asset('themes/public/' . $publicTheme . '/images/brand/logo.png');
    $themeFavicon = asset('themes/public/' . $publicTheme . '/images/brand/favicon.png');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('account.login.title') }} — {{ __('account.brand') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/png" href="{{ $themeFavicon }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --login-primary: #1c1c1e;
            --login-primary-hover: #452074;
            --login-canvas: #ffffff;
            --login-surface: #f8fafb;
            --login-card: #ffffff;
            --login-ink: #261153;
            --login-text: #585364;
            --login-muted: #706a79;
            --login-border: #e7e7e9;
            --login-border-strong: #cfc6d9;
            --login-accent: #e9e1f2;
            --login-accent-ink: #261153;
            --login-focus: #53288a;
            --login-success: #078890;
            --login-error-bg: #e9e1f2;
            --login-error-border: #cfc1dd;
            --login-error-text: #261153;
            --login-shadow: 0 12px 32px rgba(38, 17, 83, .08);
            --login-radius-card: 28px;
            --login-radius-button: 9999px;
        }

        body.public-theme-miro {
            --login-canvas: #fafbfc;
            --login-surface: #f7f8fa;
            --login-primary-hover: #2c2c34;
            --login-ink: #050038;
            --login-text: #555a6a;
            --login-muted: #6b6f7e;
            --login-border: #eef0f3;
            --login-border-strong: #c7cad5;
            --login-accent: #ffd8f4;
            --login-accent-ink: #050038;
            --login-focus: #4262ff;
            --login-success: #00b473;
            --login-error-bg: #fbd4d4;
            --login-error-border: #e3c5c5;
            --login-error-text: #600000;
            --login-shadow: 0 12px 32px rgba(5, 0, 56, .06);
            --login-radius-card: 24px;
        }

        body.public-theme-platform {
            --login-primary: #4a1d96;
            --login-primary-hover: #36136f;
            --login-canvas: #f9fafb;
            --login-surface: #f3f4f6;
            --login-ink: #1f2937;
            --login-text: #4b5563;
            --login-muted: #6b7280;
            --login-border: #e5e7eb;
            --login-border-strong: #d1d5db;
            --login-accent: #f3e8ff;
            --login-accent-ink: #4a1d96;
            --login-focus: #4a1d96;
            --login-success: #0d9488;
            --login-error-bg: #fee2e2;
            --login-error-border: #fecaca;
            --login-error-text: #991b1b;
            --login-shadow: 0 12px 32px rgba(17, 24, 39, .08);
            --login-radius-card: 16px;
        }

        body.account-login-page {
            min-height: 100vh;
            background: var(--login-canvas) !important;
            color: var(--login-ink) !important;
            font-family: "Roobert PRO", "Noto Sans", Inter, system-ui, sans-serif;
        }

        body.public-theme-platform.account-login-page { font-family: Inter, system-ui, sans-serif; }
        body.account-login-page :focus-visible { outline: 2px solid var(--login-focus); outline-offset: 3px; border-radius: 8px; }
        body.account-login-page .account-login-languages {
            border-color: var(--login-border) !important;
            background: var(--login-card) !important;
            box-shadow: 0 8px 20px rgba(38, 17, 83, .05);
        }
        body.account-login-page .account-login-language { color: var(--login-muted) !important; }
        body.account-login-page .account-login-language:hover { color: var(--login-focus) !important; }
        body.account-login-page .account-login-language.is-active {
            background: var(--login-primary) !important;
            color: #fff !important;
        }
        body.account-login-page .account-login-card {
            border-color: var(--login-border) !important;
            border-radius: var(--login-radius-card) !important;
            background: var(--login-card) !important;
            box-shadow: var(--login-shadow) !important;
        }
        body.account-login-page .account-login-step { color: var(--login-text) !important; }
        body.account-login-page .account-login-step__number {
            background: var(--login-accent) !important;
            color: var(--login-accent-ink) !important;
        }
        body.account-login-page .account-login-button {
            border-radius: var(--login-radius-button) !important;
            background: #1c1c1e !important;
            color: #fff !important;
            box-shadow: none !important;
        }
        body.account-login-page .account-login-button:hover { background: var(--login-primary-hover) !important; }
        body.account-login-page .account-login-muted { color: var(--login-muted) !important; }
        body.account-login-page .account-login-heading { color: var(--login-ink) !important; }
        body.account-login-page .account-login-subtitle { color: var(--login-muted) !important; }
        body.account-login-page .account-login-back { color: var(--login-focus) !important; }
        body.account-login-page .account-login-alert--error {
            border-color: var(--login-error-border) !important;
            background: var(--login-error-bg) !important;
            color: var(--login-error-text) !important;
        }
        body.account-login-page .account-login-alert--success {
            border-color: var(--login-success) !important;
            background: color-mix(in srgb, var(--login-success) 14%, #fff) !important;
            color: var(--login-success) !important;
        }
        body.account-login-page .account-login-spinner {
            border-color: color-mix(in srgb, var(--login-focus) 20%, transparent) !important;
            border-top-color: var(--login-focus) !important;
        }
        body.public-theme-platform .account-login-step__number { background: #ccfbf1 !important; color: #0d9488 !important; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 px-4 account-login-page public-theme-{{ $publicTheme }}">
    <div class="account-login-languages fixed right-4 top-4 flex rounded-full border border-gray-200 bg-white p-1 shadow-sm">
        @foreach(['ru' => 'RU', 'en' => 'EN', 'ro' => 'RO'] as $locale => $label)
            <a href="{{ route('language.switch', $locale) }}"
               class="account-login-language rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === $locale ? 'is-active' : 'is-idle' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div id="tma-loading" style="display:none" class="fixed inset-0 flex flex-col items-center justify-center bg-gray-50 z-50">
        <div class="account-login-spinner mb-4 h-10 w-10 animate-spin rounded-full border-4"></div>
        <p class="account-login-muted text-sm">{{ __('account.login.loading') }}</p>
    </div>

    <div id="tma-not-member" style="display:none" class="w-full max-w-sm text-center">
        <img src="{{ $themeLogo }}" alt="Women Entrepreneurs Platform" class="mx-auto mb-4 h-16 w-auto max-w-[280px] object-contain">
        <h1 class="account-login-heading mb-2 text-2xl font-bold">{{ __('account.brand') }}</h1>
        <div class="account-login-card rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <p class="account-login-heading mb-1 text-sm font-medium">{{ __('account.login.closed_title') }}</p>
            <p class="account-login-muted text-sm">{{ __('account.login.closed_text') }}</p>
        </div>
    </div>

    <div id="login-form-wrapper" class="w-full max-w-sm">
        <div class="mb-8 flex flex-col items-center text-center">
            <img src="{{ $themeLogo }}" alt="Women Entrepreneurs Platform" class="mb-4 h-16 w-auto max-w-[280px] object-contain">
            <h1 class="account-login-heading text-2xl font-bold">{{ __('account.brand') }}</h1>
            <p class="account-login-subtitle mt-1 text-sm">{{ __('account.login.subtitle') }}</p>
        </div>

        @if(session('error'))
        <div class="account-login-alert--error mb-4 rounded-xl border px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="account-login-alert--success mb-4 rounded-xl border px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="account-login-card rounded-2xl border border-gray-100 bg-white p-7 text-center shadow-sm">
            <h2 class="account-login-heading mb-2 font-semibold">{{ __('account.login.telegram_title') }}</h2>
            <div class="mb-6 space-y-3 text-left">
                @foreach([__('account.login.step_1'), __('account.login.step_2')] as $index => $step)
                    <div class="flex items-start gap-3">
                        <span class="account-login-step__number mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">{{ $index + 1 }}</span>
                        <p class="account-login-step text-sm text-gray-600">{{ $step }}</p>
                    </div>
                @endforeach
            </div>

            <a href="https://t.me/WomenComBot?start=login" id="tg-login-btn" target="_blank"
               aria-label="{{ __('account.login.open_bot') }}"
               class="account-login-button inline-flex w-full items-center justify-center gap-2.5 rounded-xl px-5 py-3 text-sm font-semibold text-white transition hover:-translate-y-px hover:shadow-md">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 3-6.7 18-3.8-7.5L3 9.7 21 3Zm0 0L10.5 13.5"/></svg>
                {{ __('account.login.open_bot') }}
            </a>
            <p class="account-login-muted mt-3 text-xs text-gray-400">{{ __('account.login.approved_only') }}</p>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400"><a href="/" class="account-login-back hover:underline">{{ __('account.back_to_site') }}</a></p>
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
