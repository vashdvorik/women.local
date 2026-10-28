{{-- Запрос больше, чем принимает сервер (post_max_size): обычно это PDF каталога или большой файл. Отдельная страница вместо
     безликого «Oops! An Error Occurred». Лимит берётся из настроек PHP на самом сервере. --}}
@php
    $limit = ini_get('post_max_size') ?: '—';
    $upload = ini_get('upload_max_filesize') ?: '—';
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Файл слишком большой</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: #FAF4F1; color: #21140F; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { width: 100%; max-width: 560px; padding: 32px; border: 1px solid #E9DCD5; border-radius: 20px; background: #FFFDFC; box-shadow: 0 12px 32px rgba(33, 20, 15, .06); }
        h1 { margin: 0 0 12px; font-size: 22px; }
        p { margin: 0 0 12px; line-height: 1.55; color: #49342B; }
        code { padding: 2px 6px; border-radius: 6px; background: #F1E3DA; font-size: 14px; }
        a { display: inline-block; margin-top: 8px; padding: 12px 22px; border-radius: 999px; background: #2A1913; color: #fff; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <main>
        <h1>Файл слишком большой для сервера</h1>
        <p>Сервер принимает за один раз не больше <code>{{ $limit }}</code> (один файл — не больше <code>{{ $upload }}</code>), а вы отправили больше. Ничего не сохранилось.</p>
        <p>Выберите файл поменьше. Если нужен именно этот файл (например, PDF каталога до 60 МБ), попросите разработчика поднять на хостинге <code>post_max_size</code> и <code>upload_max_filesize</code> до 64M.</p>
        <a href="javascript:history.back()">Вернуться назад</a>
    </main>
</body>
</html>
