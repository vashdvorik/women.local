<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Рассылки в Telegram') }} — {{ $generatedAt }}</title>
    @include('admin.statistics._pdf-style')
</head>
<body>
    <div class="hero">
        <p class="eyebrow on-dark">{{ __('Отчёт платформы') }}</p>
        <h1 class="serif">{{ __('Рассылки в Telegram') }}</h1>
        <p>
            {{ __('Доставлено, заблокировали бота и нажатия на кнопки.') }}
            {{ __('Сформировано :date', ['date' => $generatedAt]) }}
        </p>
    </div>

    <div class="section-title">{{ __('За 30 дней') }}</div>
    <table class="kpi"><tr>
        <td><div class="kpi-box"><div class="kpi-label">{{ __('Рассылок за 30 дней') }}</div><div class="kpi-value serif">{{ $count30 }}</div></div></td>
        <td><div class="kpi-box"><div class="kpi-label">{{ __('Доставлено сообщений') }}</div><div class="kpi-value serif">{{ $sent30 }}</div><div class="kpi-note">{{ __('заблокировали бота: :n', ['n' => $blocked30]) }}</div></div></td>
        <td><div class="kpi-box"><div class="kpi-label">{{ __('Нажатий на кнопки') }}</div><div class="kpi-value serif accent">{{ $clicks30 }}</div></div></td>
        <td><div class="kpi-box"><div class="kpi-label">{{ __('Подключили Telegram') }}</div><div class="kpi-value serif">{{ $connected }}</div><div class="kpi-note">{{ __('из :paid с действующим пакетом', ['paid' => $paid]) }}</div></div></td>
    </tr></table>
    <p class="section-note" style="margin-top:8px;">{{ __('«Прочитано» Telegram боту не сообщает, поэтому такой цифры нет. Ссылки, вписанные прямо в текст сообщения, не считаются: считаются только кнопки.') }}</p>

    <div class="section-title">{{ __('Последние рассылки') }}</div>
    @if($rows === [])
        <p class="empty-note">{{ __('Отправленных рассылок пока нет.') }}</p>
    @else
        <table class="data">
            <thead><tr>
                <th>{{ __('Рассылка') }}</th>
                <th>{{ __('Кому') }}</th>
                <th class="num">{{ __('Доставлено') }}</th>
                <th class="num">{{ __('Заблокировали') }}</th>
                <th class="num">{{ __('Ошибки') }}</th>
                <th class="num">{{ __('Нажали') }}</th>
                <th>{{ __('По кнопкам') }}</th>
            </tr></thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td><b>{{ $row['title'] }}</b><div class="small muted">{{ $row['date'] }}</div></td>
                        <td class="small">{{ $row['audience'] }}</td>
                        <td class="num">{{ $row['delivered'] }} <span class="muted">/ {{ $row['total'] }}</span></td>
                        <td class="num">{{ $row['blocked'] }}</td>
                        <td class="num">{{ $row['failed'] }}</td>
                        <td class="num">@if($row['trackable'])<b>{{ $row['clickers'] }}</b> <span class="muted">· {{ $row['ctr'] }}%</span>@else<span class="muted">{{ __('без кнопок') }}</span>@endif</td>
                        <td class="small">
                            @forelse($row['buttons'] as $button)
                                <div>{{ $button['label'] }}: {{ __(':clicks нажатий, :people чел.', ['clicks' => $button['clicks'], 'people' => $button['people']]) }}</div>
                            @empty
                                —
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="section-note" style="margin-top:6px;">{{ __('«Нажали» — сколько разных получательниц нажали хотя бы на одну кнопку и какая это доля от доставленных.') }}</p>
    @endif

    <p class="footer-note">{{ __('Сформировано автоматически из текущих данных платформы') }} · {{ $generatedAt }}</p>
</body>
</html>
