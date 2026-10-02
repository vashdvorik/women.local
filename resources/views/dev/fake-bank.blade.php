{{--
    ИМИТАТОР БАНКА (только локально и в тестах). Страница оплаты вместо epay.apb.online:
    показывает, что банк получил от формы, проверяет подпись так же, как проверил бы настоящий банк, и даёт нажать «Оплатить» или «Отказаться».
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Имитатор банка</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; background: #eef1f5; color: #0f172a; }
        main { max-width: 560px; margin: 40px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 700; }
        dl { display: grid; grid-template-columns: 150px 1fr; gap: 6px 12px; font-size: 14px; }
        dt { color: #64748b; } dd { margin: 0; word-break: break-all; }
        .ok { color: #047857; font-weight: 600; } .bad { color: #b91c1c; font-weight: 600; }
        .row { display: flex; gap: 12px; margin-top: 20px; }
        button { flex: 1; padding: 13px; border: 0; border-radius: 12px; font: inherit; font-weight: 600; cursor: pointer; color: #fff; }
        .pay { background: #047857; } .decline { background: #64748b; }
        button:disabled { opacity: .4; cursor: not-allowed; }
    </style>
</head>
<body>
<main>
    <div class="card">
        <span class="badge">ИМИТАТОР БАНКА · деньги не списываются</span>
        <h1 style="font-size:22px;margin:14px 0 6px">Оплата счёта {{ $fields['nivid'] ?? '—' }}</h1>

        @php $good = $signatureOk && $loginOk && $payment; @endphp

        <dl>
            <dt>Сумма</dt><dd>{{ isset($fields['RequestSum']) ? number_format(((int) $fields['RequestSum']) / 100, 2, ',', ' ') : '—' }} (код валюты {{ $fields['RequestCurrCode'] ?? '—' }}, в копейках: {{ $fields['RequestSum'] ?? '—' }})</dd>
            <dt>Описание</dt><dd>{{ $fields['Desc'] ?? '—' }}</dd>
            <dt>Признак теста</dt><dd>{{ ($fields['IsTest'] ?? '') === '1' ? 'тест (1)' : 'боевой (0)' }}</dd>
            <dt>Время жизни счёта</dt><dd>{{ $fields['LifeTime'] ?? '—' }} мин.</dd>
            <dt>Торговец</dt><dd class="{{ $loginOk ? 'ok' : 'bad' }}">{{ $fields['MerchantLogin'] ?? '—' }} {{ $loginOk ? '✓ известен' : '✗ неизвестен' }}</dd>
            <dt>Подпись запроса</dt><dd class="{{ $signatureOk ? 'ok' : 'bad' }}">{{ $signatureOk ? '✓ верна' : '✗ НЕ СОВПАЛА: банк отклонил бы этот запрос' }}</dd>
            <dt>Счёт у нас</dt><dd class="{{ $payment ? 'ok' : 'bad' }}">{{ $payment ? '✓ найден ('.$payment->statusLabel().')' : '✗ не найден' }}</dd>
        </dl>

        <div class="row">
            <form method="POST" action="{{ route('dev.fakebank.complete') }}" style="flex:1;display:flex">
                @csrf
                <input type="hidden" name="nivid" value="{{ $fields['nivid'] ?? '' }}">
                <input type="hidden" name="decision" value="pay">
                <button class="pay" type="submit" @disabled(! $good)>Оплатить</button>
            </form>
            <form method="POST" action="{{ route('dev.fakebank.complete') }}" style="flex:1;display:flex">
                @csrf
                <input type="hidden" name="nivid" value="{{ $fields['nivid'] ?? '' }}">
                <input type="hidden" name="decision" value="decline">
                <button class="decline" type="submit" @disabled(! $payment)>Отказаться</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>
