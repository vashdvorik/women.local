{{--
    Страница-переходник на платёжный сайт банка: форма со счётом (раздел 3 документа банка) отправляется сама.
    Нужна, потому что банку параметры можно передать только методом POST/GET из браузера участницы.
    Пароль торговца сюда не попадает: в форме только подпись (MD5), по которой пароль не восстановить.
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('subscription.result.title') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: system-ui, sans-serif; background: #f7f8fa; color: #0f172a; text-align: center; }
        main { padding: 24px; max-width: 420px; }
        button { margin-top: 16px; padding: 12px 22px; border: 0; border-radius: 12px; background: #0f172a; color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
<main>
    <p>{{ __('subscription.result.redirecting') }}</p>
    <form id="bank-form" method="POST" action="{{ $url }}">
        @foreach($fields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <noscript><button type="submit">{{ __('subscription.result.redirect_button') }}</button></noscript>
    </form>
    <script>document.getElementById('bank-form').submit();</script>
</main>
</body>
</html>
