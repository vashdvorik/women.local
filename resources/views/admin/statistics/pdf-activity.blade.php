<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Активность участниц') }} — {{ $generatedAt }}</title>
    @include('admin.statistics._pdf-style')
</head>
<body>
    @php
        $periodLabels = ['30' => __('30 дней'), '90' => __('90 дней'), '365' => __('Год'), 'all' => __('Всё время')];
        $barMax = 60;
    @endphp

    <div class="hero">
        <p class="eyebrow on-dark">{{ __('Отчёт платформы') }}</p>
        <h1 class="serif">{{ __('Активность участниц в кабинете') }}</h1>
        <p>
            {{ __('Инструменты за период: :period.', ['period' => $periodLabels[$period]]) }}
            @if($trackingSince)
                {{ __('Заходы считаются с :date (прошло дней: :days).', ['date' => $trackingSince, 'days' => $trackingDays]) }}
            @else
                {{ __('Данных о заходах пока нет: учёт включён, но заходов после запуска ещё не было.') }}
            @endif
            {{ __('Сформировано :date', ['date' => $generatedAt]) }}
        </p>
    </div>

    <div class="section-title">{{ __('Ключевые цифры') }}</div>
    <table class="kpi"><tr>
        <td><div class="kpi-box"><div class="kpi-label">{{ __('Заходили за 7 дней') }}</div><div class="kpi-value serif">{{ $active7 }}</div><div class="kpi-note">{{ __('участниц') }}</div></div></td>
        <td><div class="kpi-box"><div class="kpi-label">{{ __('Заходили за 30 дней') }}</div><div class="kpi-value serif">{{ $active30 }}</div><div class="kpi-note">{{ __(':visits дней с заходом, в среднем :avg на участницу', ['visits' => $visits30, 'avg' => $daysPerActive30]) }}</div></div></td>
        <td><div class="kpi-box"><div class="kpi-label">{{ __('Доля платных участниц') }}</div><div class="kpi-value serif accent">{{ $activeShare }}%</div><div class="kpi-note">{{ __(':active из :paid заходили за 30 дней', ['active' => $paidActive30, 'paid' => $paidMembers]) }}</div></div></td>
        <td><div class="kpi-box"><div class="kpi-label">{{ __('По пакетам, 30 дней') }}</div>
            @foreach($plans as $plan)<div class="kpi-note" style="font-size:9px;">{{ $plan['title'] }}: <b>{{ $plan['users'] }}</b></div>@endforeach
        </div></td>
    </tr></table>

    <div class="section-title">{{ __('Посещения по месяцам') }}</div>
    <p class="section-note">{{ __('Левый столбик — сколько разных участниц заходило в месяц; правый — «дни с заходом» (участница и день считаются один раз).') }}</p>
    <table class="chart-table"><tr>
        @foreach($months as $m)
            <td>
                <div class="chart-value">{{ $m['users'] > 0 ? $m['users'].'/'.$m['visits'] : '' }}</div>
                <table><tr>
                    <td style="width:50%; height:{{ $barMax + 4 }}px; vertical-align:bottom;"><div class="bar-a" style="height:{{ $m['users'] > 0 ? max(3, (int) round($m['users'] / $maxMonthUsers * $barMax)) : 0 }}px;"></div></td>
                    <td style="width:50%; height:{{ $barMax + 4 }}px; vertical-align:bottom;"><div class="bar-b" style="height:{{ $m['visits'] > 0 ? max(3, (int) round($m['visits'] / $maxMonthVisits * $barMax)) : 0 }}px;"></div></td>
                </tr></table>
                <div class="chart-label">{{ $m['label'] }}</div>
            </td>
        @endforeach
    </tr></table>
    <p class="legend"><span style="color:#8E5B3D;">■</span> {{ __('Участницы') }} &nbsp;&nbsp; <span style="color:#E6CFC1;">■</span> {{ __('Дни с заходом') }}</p>

    <div class="section-title">{{ __('Какими инструментами пользуются') }}</div>
    <p class="section-note">{{ __('За период: :period. Процент — доля от всех участниц, заходивших за этот период.', ['period' => $periodLabels[$period]]) }}</p>
    <table><tr>
        @foreach([[__('Разделы кабинета'), $sections], [__('Действия'), $actions]] as [$title, $rows])
            @php $top = max(1, collect($rows)->max('hits')); @endphp
            <td style="width:50%; vertical-align:top; {{ $loop->first ? 'padding-right:14px;' : '' }}">
                <div style="font-weight:bold; margin-bottom:6px;">{{ $title }}</div>
                @foreach($rows as $row)
                    <div class="row">
                        <div class="top"><span class="val">{{ __(':users уч. · :hits раз', ['users' => $row['users'], 'hits' => $row['hits']]) }}@if($row['users'] > 0) · {{ $row['share'] }}%@endif</span><span class="label">{{ $row['label'] }}</span></div>
                        <div class="track"><div class="fill" style="width: {{ (int) round($row['hits'] / $top * 100) }}%;"></div></div>
                    </div>
                @endforeach
            </td>
        @endforeach
    </tr></table>

    <div class="section-title">{{ __('Давно не заходили') }}</div>
    @if($inactive === null)
        <p class="empty-note">{{ __('Учёт идёт меньше :days дней, поэтому список «не заходили» пока был бы неточным. Он появится сам, когда накопится месяц данных.', ['days' => $inactiveDays]) }}</p>
    @elseif($inactive['total'] === 0)
        <p class="empty-note">{{ __('Все участницы с действующим пакетом заходили за последние :days дней.', ['days' => $inactiveDays]) }}</p>
    @else
        <p class="section-note">{{ __('Участниц с действующим пакетом, которые не заходили :days дней и больше: :count.', ['days' => $inactiveDays, 'count' => $inactive['total']]) }}</p>
        <table class="data">
            <thead><tr><th>{{ __('Участница') }}</th><th>{{ __('Пакет') }}</th><th>{{ __('Последний вход') }}</th></tr></thead>
            <tbody>
                @foreach($inactive['rows'] as $row)
                    <tr><td>{{ $row['name'] }}</td><td>{{ $row['plan'] }}</td><td class="muted">{{ $row['last'] ?? __('не заходила после запуска учёта') }}</td></tr>
                @endforeach
            </tbody>
        </table>
        @if($inactive['total'] > count($inactive['rows']))
            <p class="section-note" style="margin-top:4px;">{{ __('Показаны первые :n из :total.', ['n' => count($inactive['rows']), 'total' => $inactive['total']]) }}</p>
        @endif
    @endif

    <div class="section-title">{{ __('История по месяцам') }}</div>
    <p class="section-note">{{ __('Взято из базы за последний год, поэтому есть и за время до включения учёта заходов.') }}</p>
    <table class="data">
        <thead><tr>
            <th>{{ __('Месяц') }}</th>
            <th class="num">{{ __('Новые участницы') }}</th>
            <th class="num">{{ __('Оплаты') }}</th>
            <th class="num">{{ __('Сумма оплат') }}</th>
            <th class="num">{{ __('Вопросы AI-менеджеру') }}</th>
            <th class="num">{{ __('Инициативы') }}</th>
        </tr></thead>
        <tbody>
            @foreach(array_reverse($history['rows']) as $row)
                <tr>
                    <td><b>{{ $row['label'] }}</b></td>
                    <td class="num">{{ $row['registrations'] }}</td>
                    <td class="num">{{ $row['payments'] }}</td>
                    <td class="num">{{ number_format($row['revenue'], 0, ',', ' ') }}</td>
                    <td class="num">{{ $row['questions'] }}</td>
                    <td class="num">{{ $row['initiatives'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer-note">{{ __('Сформировано автоматически из текущих данных платформы') }} · {{ $generatedAt }}</p>
</body>
</html>
