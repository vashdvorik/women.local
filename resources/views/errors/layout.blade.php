{{--
    Общий вид страниц ошибок (403, 404, 419, 429, 500, 503 и остальные 4xx/5xx). Отдельная самодостаточная страница без
    шаблона темы: она должна открываться, даже когда сломалась база или тема. Язык определяет App\Support\ErrorPage, потому
    что middleware языка к этому моменту мог не сработать. $code — ключ из lang/*/errors.php.
--}}
@php
    $locale = \App\Support\ErrorPage::locale(request());
    app()->setLocale($locale);

    $known = ['403', '404', '419', '429', '500', '503'];
    $code = (string) ($code ?? '500');
    $key = in_array($code, $known, true) ? $code : (str_starts_with($code, '5') ? '500' : '4xx');
    $shown = $code;

    $inCabinet = request()->is('app/account', 'app/account/*');
    $wait = $code === '429' && isset($exception) ? (int) ($exception->getHeaders()['Retry-After'] ?? 0) : 0;
    $canReload = in_array($key, ['500', '503'], true);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __("errors.{$key}.title") }} — Women's Hub</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: #FAF4F1; color: #21140F; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        main { width: 100%; max-width: 520px; padding: 36px 32px; border: 1px solid #E9DCD5; border-radius: 24px; background: #FFFDFC; box-shadow: 0 12px 32px rgba(33, 20, 15, .06); text-align: center; }
        .logo { display: inline-block; margin-bottom: 20px; }
        .logo img { display: block; height: 52px; width: auto; }
        .code { margin: 0 0 8px; color: #8C776E; font-size: 13px; font-weight: 600; letter-spacing: .14em; }
        h1 { margin: 0 0 12px; font-size: 26px; line-height: 1.25; font-weight: 600; }
        p { margin: 0 0 12px; line-height: 1.6; color: #49342B; }
        .wait { color: #2A1913; font-weight: 600; }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 22px; }
        .btn { display: inline-block; padding: 12px 22px; border: 1px solid #2A1913; border-radius: 999px; background: #2A1913; color: #fff; text-decoration: none; font-size: 15px; font-weight: 600; cursor: pointer; font-family: inherit; }
        .btn--ghost { background: transparent; color: #2A1913; }
        .btn:hover { opacity: .9; }
        .help { margin-top: 20px; font-size: 14px; }
        .help a, .lang a { color: #74472F; }
        .lang { margin-top: 22px; font-size: 13px; color: #8C776E; }
        .lang a { margin: 0 6px; text-decoration: none; }
        .lang a.is-active { font-weight: 700; text-decoration: underline; }
    </style>
</head>
<body>
    <main>
        <a class="logo" href="{{ url('/') }}" aria-label="Women's Hub"><img src="{{ asset('themes/public/miro/images/brand/logo-platform.webp') }}" alt="Women's Hub"></a>
        <p class="code">{{ $shown }}</p>
        <h1>{{ __("errors.{$key}.title") }}</h1>
        <p>{{ __("errors.{$key}.text") }}</p>
        @if($wait > 0)
            <p class="wait">{{ __('errors.wait', ['seconds' => $wait]) }}</p>
        @endif

        <div class="actions">
            <a class="btn" href="{{ $inCabinet ? route('account.index') : url('/') }}">{{ $inCabinet ? __('errors.cabinet') : __('errors.home') }}</a>
            @if($canReload)
                <a class="btn btn--ghost" href="javascript:location.reload()">{{ __('errors.reload') }}</a>
            @else
                <a class="btn btn--ghost" href="javascript:history.back()">{{ __('errors.back') }}</a>
            @endif
        </div>

        @if(\Illuminate\Support\Facades\Route::has('contact'))
            <p class="help"><a href="{{ route('contact') }}">{{ __('errors.contact') }}</a></p>
        @endif

        @if(request()->isMethod('GET'))
            <p class="lang">
                @foreach(['ru' => 'RU', 'en' => 'EN', 'ro' => 'RO'] as $code2 => $label)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $code2]) }}" @class(['is-active' => $locale === $code2])>{{ $label }}</a>
                @endforeach
            </p>
        @endif
    </main>
</body>
</html>
