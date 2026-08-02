<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Локальный вход в кабинет</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f7f5f3;
            color: #1c1c1e;
        }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; box-sizing: border-box; }
        main { width: min(100%, 560px); background: #fff; border: 1px solid #e5e1df; border-radius: 24px; padding: 28px; box-shadow: 0 18px 50px rgba(28, 28, 30, .08); }
        h1 { margin: 0 0 8px; font-size: 24px; }
        p { margin: 0 0 22px; color: #6d6866; line-height: 1.5; }
        ul { display: grid; gap: 10px; padding: 0; margin: 0; list-style: none; }
        button { width: 100%; border: 1px solid #1c1c1e; border-radius: 12px; background: #1c1c1e; color: #fff; cursor: pointer; padding: 13px 16px; text-align: left; font: inherit; }
        button:hover { background: #353539; }
        small { display: block; margin-top: 4px; color: #c9c3c0; }
        .empty { padding: 16px; border-radius: 12px; background: #fff4c4; color: #600000; }
    </style>
</head>
<body>
    <main>
        <h1>Локальный вход в кабинет</h1>
        <p>Эта страница работает только на локальном сервере и заменяет вход через Telegram во время разработки.</p>

        @if ($users->isEmpty())
            <div class="empty">В локальной базе нет одобренных участниц.</div>
        @else
            <ul>
                @foreach ($users as $user)
                    <li>
                        <form method="POST" action="{{ route('dev.account.login.submit') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <button type="submit">
                                {{ $user->full_name ?: 'Участница #' . $user->id }}
                                @if ($user->telegram_username)
                                    <small>@&#8203;{{ $user->telegram_username }}</small>
                                @endif
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </main>
</body>
</html>
