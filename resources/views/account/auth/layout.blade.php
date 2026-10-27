{{--
    Общий макет страниц до кабинета: вход, регистрация, восстановление пароля, подтверждение почты и анкета.
    Оформление то же, что у публичного сайта (шоколадный и карамельный цвета). Секции: title, content.
    Переменная $wide — шире карточка (анкета).
--}}
@php
    $publicTheme = \App\Models\SiteSetting::landingTheme();
    $publicTheme = array_key_exists($publicTheme, \App\Models\SiteSetting::LANDING_THEMES) ? $publicTheme : \App\Models\SiteSetting::DEFAULT_LANDING_THEME;
    $themeFavicon = asset_v('themes/public/' . $publicTheme . '/images/brand/favicon-w4w.png');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ __('account.brand') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/png" href="{{ $themeFavicon }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Prata&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --login-primary: #2A1913;
            --login-primary-hover: #34231F;
            --login-canvas: #FFFDFC;
            --login-surface: #FAF4F1;
            --login-card: #FFFDFC;
            --login-ink: #21140F;
            --login-text: #756158;
            --login-muted: #8C776E;
            --login-border: #E9DCD5;
            --login-border-strong: #D8C5BA;
            --login-accent: #F1E3DA;
            --login-focus: #74472F;
            --login-success: #526B5A;
            --login-error-bg: #F4E6E2;
            --login-error-border: rgba(140,72,66,.35);
            --login-error-text: #8C4842;
            --login-shadow: 0 12px 32px rgba(33, 20, 15, .06);
            --login-display-font: "Prata", Georgia, "Times New Roman", serif;
        }
        body.account-login-page {
            min-height: 100vh;
            background: var(--login-canvas);
            color: var(--login-ink);
            font-family: "Manrope", Inter, system-ui, sans-serif;
        }
        body.account-login-page :focus-visible { outline: 2px solid var(--login-focus); outline-offset: 3px; border-radius: 8px; }
        .account-login-heading { font-family: var(--login-display-font); font-weight: 400; color: var(--login-ink); }
        .account-login-muted { color: var(--login-muted); }
        .account-login-card {
            border: 1px solid var(--login-border);
            border-radius: 24px;
            background: var(--login-card);
            box-shadow: var(--login-shadow);
        }
        .account-login-languages { border: 1px solid var(--login-border); background: var(--login-card); }
        .account-login-language { color: var(--login-muted); }
        .account-login-language:hover { color: var(--login-focus); }
        .account-login-language.is-active { background: var(--login-primary); color: #fff; }
        .account-login-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--login-ink); }
        .account-login-input {
            width: 100%;
            border: 1px solid var(--login-border-strong);
            border-radius: 14px;
            background: #fff;
            padding: 12px 14px;
            font-size: 15px;
            color: var(--login-ink);
        }
        .account-login-input::placeholder { color: #B5A59C; }
        .account-login-input:focus { border-color: var(--login-focus); outline: none; box-shadow: 0 0 0 3px rgba(116,71,47,.15); }
        .account-login-input.is-invalid { border-color: var(--login-error-text); }
        .account-login-hint { margin-top: 5px; font-size: 12px; color: var(--login-muted); }
        .account-login-fielderror { margin-top: 5px; font-size: 12px; color: var(--login-error-text); }
        .account-login-button {
            display: inline-flex; width: 100%; align-items: center; justify-content: center; gap: 10px;
            border-radius: 9999px; background: var(--login-primary); color: #fff;
            padding: 13px 20px; font-size: 14px; font-weight: 600; transition: background .15s;
        }
        .account-login-button:hover { background: var(--login-primary-hover); }
        .account-login-button--ghost { background: #fff; color: var(--login-ink); border: 1px solid var(--login-border-strong); }
        .account-login-button--ghost:hover { background: var(--login-accent); }
        .account-login-link { color: var(--login-focus); font-weight: 600; }
        .account-login-link:hover { text-decoration: underline; }
        .account-login-alert { margin-bottom: 16px; border-radius: 14px; border: 1px solid; padding: 12px 16px; font-size: 14px; }
        .account-login-alert--error { border-color: var(--login-error-border); background: var(--login-error-bg); color: var(--login-error-text); }
        .account-login-alert--success { border-color: var(--login-success); background: color-mix(in srgb, var(--login-success) 12%, #fff); color: var(--login-success); }
        .account-login-divider { display: flex; align-items: center; gap: 12px; margin: 18px 0; font-size: 12px; color: var(--login-muted); }
        .account-login-divider::before, .account-login-divider::after { content: ""; height: 1px; flex: 1; background: var(--login-border); }
        .account-steps { display: flex; justify-content: center; gap: 8px; margin-bottom: 24px; font-size: 12px; font-weight: 600; }
        .account-steps__item { display: flex; align-items: center; gap: 8px; color: var(--login-muted); }
        .account-steps__num { display: flex; height: 24px; width: 24px; align-items: center; justify-content: center; border-radius: 9999px; background: var(--login-accent); color: var(--login-ink); font-size: 12px; }
        .account-steps__item.is-done .account-steps__num { background: var(--login-success); color: #fff; }
        .account-steps__item.is-current { color: var(--login-ink); }
        .account-steps__item.is-current .account-steps__num { background: var(--login-primary); color: #fff; }
        .account-steps__line { width: 28px; height: 1px; align-self: center; background: var(--login-border-strong); }
    </style>
</head>
<body class="account-login-page flex min-h-screen items-center justify-center px-4 py-10 public-theme-{{ $publicTheme }}">
    <div class="account-login-languages fixed right-4 top-4 flex rounded-full p-1 shadow-sm">
        @foreach(['ru' => 'RU', 'en' => 'EN', 'ro' => 'RO'] as $locale => $label)
            <a href="{{ route('language.switch', $locale) }}"
               class="account-login-language rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === $locale ? 'is-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="w-full {{ ($wide ?? false) ? 'max-w-lg' : 'max-w-sm' }}">
        <div class="mb-7 flex flex-col items-center text-center">
            <a href="{{ url('/') }}" class="account-login-heading text-3xl">{{ __('account.brand') }}</a>
            @hasSection('subtitle')
                <p class="account-login-muted mt-1 text-sm">@yield('subtitle')</p>
            @endif
        </div>

        @if(session('error'))
            <div class="account-login-alert account-login-alert--error" role="alert">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="account-login-alert account-login-alert--success" role="status">{{ session('success') }}</div>
        @endif

        @yield('content')

        <p class="mt-6 text-center text-xs"><a href="{{ url('/') }}" class="account-login-link">{{ __('participant.back_to_site') }}</a></p>
    </div>
</body>
</html>
