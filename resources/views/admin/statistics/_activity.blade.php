{{-- Вкладка «Активность»: заходы по месяцам, ключевые цифры, инструменты кабинета, пакеты, ушедшие участницы, история.
     Данные: App\Services\ActivityReport::activity(). Учёт заходов идёт с даты запуска ($trackingSince). --}}
@php
    $chart = 140;
    $periodLabels = ['30' => __('30 дней'), '90' => __('90 дней'), '365' => __('Год'), 'all' => __('Всё время')];
@endphp

<div class="space-y-6">
    @if($trackingSince === null)
        <div class="card">
            <p class="text-ui-strong font-semibold">{{ __('Данных об активности пока нет') }}</p>
            <p class="mt-1 text-ui text-ink-muted">{{ __('Учёт заходов в кабинет включён, но ни одна участница ещё не открывала его после запуска. Цифры появятся здесь сразу после первых заходов.') }}</p>
        </div>
    @else
        <p class="text-caption text-ink-muted">
            {{ __('Заходы считаются с :date (прошло дней: :days). До этого дня данных о заходах нет, но история по месяцам ниже взята из базы за весь год.', ['date' => $trackingSince, 'days' => $trackingDays]) }}
        </p>
    @endif

    {{-- Ключевые цифры --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card">
            <p class="field-hint uppercase">{{ __('Заходили за 7 дней') }}</p>
            <p class="mt-2 text-[36px] font-semibold leading-none">{{ $active7 }}</p>
            <p class="mt-2 text-caption text-ink-muted">{{ __('участниц') }}</p>
        </div>
        <div class="card">
            <p class="field-hint uppercase">{{ __('Заходили за 30 дней') }}</p>
            <p class="mt-2 text-[36px] font-semibold leading-none">{{ $active30 }}</p>
            <p class="mt-2 text-caption text-ink-muted">{{ __('участниц, :visits дней с заходом, в среднем :avg на участницу', ['visits' => $visits30, 'avg' => $daysPerActive30]) }}</p>
        </div>
        <div class="card">
            <p class="field-hint uppercase">{{ __('Доля платных участниц') }}</p>
            <p class="mt-2 text-[36px] font-semibold leading-none text-accent">{{ $activeShare }}%</p>
            <p class="mt-2 text-caption text-ink-muted">{{ __(':active из :paid с действующим пакетом заходили за 30 дней', ['active' => $paidActive30, 'paid' => $paidMembers]) }}</p>
        </div>
        <div class="card">
            <p class="field-hint uppercase">{{ __('По пакетам, 30 дней') }}</p>
            <ul class="mt-2 space-y-1 text-ui">
                @foreach($plans as $plan)
                    <li class="flex justify-between"><span>{{ $plan['title'] }}</span><span class="font-semibold">{{ $plan['users'] }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Посещения по месяцам --}}
    <div class="card">
        <h2 class="form-section-title">{{ __('Посещения по месяцам') }}</h2>
        <p class="mb-4 text-caption text-ink-muted">{{ __('Столбик слева — сколько разных участниц заходило в месяц; справа — сколько всего «дней с заходом» (участница и день считаются один раз).') }}</p>
        <div class="flex items-end gap-1.5" style="height: {{ $chart + 24 }}px">
            @foreach($months as $m)
                @php
                    $u = $m['users'] > 0 ? max(4, (int) round($m['users'] / $maxMonthUsers * $chart)) : 0;
                    $v = $m['visits'] > 0 ? max(4, (int) round($m['visits'] / $maxMonthVisits * $chart)) : 0;
                @endphp
                <div class="flex flex-1 flex-col items-center justify-end gap-1">
                    <span class="text-micro text-ink-muted">{{ $m['users'] > 0 ? $m['users'].' / '.$m['visits'] : '' }}</span>
                    <div class="flex items-end gap-0.5">
                        <div class="w-2.5 rounded-xs bg-accent" style="height: {{ $u }}px" title="{{ __(':month: :count участниц', ['month' => $m['label'], 'count' => $m['users']]) }}"></div>
                        <div class="w-2.5 rounded-xs bg-hairline" style="height: {{ $v }}px" title="{{ __(':month: :count дней с заходом', ['month' => $m['label'], 'count' => $m['visits']]) }}"></div>
                    </div>
                    <span class="text-micro text-ink-faint">{{ $m['label'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-3 flex gap-4 text-caption text-ink-muted">
            <span class="inline-flex items-center gap-1.5"><i class="inline-block h-2 w-2 rounded-xs bg-accent"></i> {{ __('Участницы') }}</span>
            <span class="inline-flex items-center gap-1.5"><i class="inline-block h-2 w-2 rounded-xs bg-hairline"></i> {{ __('Дни с заходом') }}</span>
        </div>
    </div>

    {{-- Инструменты и действия --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-section font-semibold">{{ __('Какими инструментами пользуются') }}</h2>
        <div class="tabs overflow-x-auto no-scrollbar rounded-sm">
            @foreach($periodLabels as $key => $label)
                <a href="{{ route('admin.statistics.index', ['tab' => 'activity', 'period' => $key]) }}"
                   class="tab whitespace-nowrap @if($period === (string) $key) tab--active @endif">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach([['title' => __('Разделы кабинета'), 'hint' => __('Сколько участниц открывало раздел и сколько раз'), 'rows' => $sections], ['title' => __('Действия'), 'hint' => __('Что участницы делали в кабинете'), 'rows' => $actions]] as $block)
            @php $top = max(1, collect($block['rows'])->max('hits')); @endphp
            <div class="card">
                <h3 class="form-section-title">{{ $block['title'] }}</h3>
                <p class="mb-4 text-caption text-ink-muted">{{ $block['hint'] }}</p>
                <div class="space-y-3">
                    @foreach($block['rows'] as $row)
                        <div>
                            <div class="flex justify-between gap-3 text-ui">
                                <span>{{ $row['label'] }}</span>
                                <span class="shrink-0 text-ink-muted">{{ __(':users уч. · :hits раз', ['users' => $row['users'], 'hits' => $row['hits']]) }}@if($row['users'] > 0) · {{ $row['share'] }}%@endif</span>
                            </div>
                            <div class="mt-1 h-2 rounded-pill bg-surface-sunken">
                                <div class="h-2 rounded-pill bg-accent" style="width: {{ (int) round($row['hits'] / $top * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    <p class="text-caption text-ink-muted">{{ __('Процент — доля от всех участниц, заходивших за выбранный период.') }}</p>

    {{-- Давно не заходили --}}
    <div class="card">
        <h2 class="form-section-title">{{ __('Давно не заходили') }}</h2>
        @if($inactive === null)
            <p class="text-ui text-ink-muted">{{ __('Учёт идёт меньше :days дней, поэтому список «не заходили» пока был бы неточным. Он появится сам, когда накопится месяц данных.', ['days' => $inactiveDays]) }}</p>
        @elseif($inactive['total'] === 0)
            <p class="text-ui text-ink-muted">{{ __('Все участницы с действующим пакетом заходили за последние :days дней.', ['days' => $inactiveDays]) }}</p>
        @else
            <p class="mb-4 text-ui text-ink-muted">{{ __('Участниц с действующим пакетом, которые не заходили :days дней и больше: :count. Их стоит позвать обратно, например рассылкой.', ['days' => $inactiveDays, 'count' => $inactive['total']]) }}</p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-ui">
                    <thead class="text-left text-caption uppercase text-ink-muted">
                        <tr><th class="py-2 pr-4">{{ __('Участница') }}</th><th class="py-2 pr-4">{{ __('Пакет') }}</th><th class="py-2">{{ __('Последний вход') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-hairline-soft">
                        @foreach($inactive['rows'] as $row)
                            <tr>
                                <td class="py-2 pr-4"><a href="{{ route('admin.profiles.show', $row['id']) }}" class="table-link">{{ $row['name'] }}</a></td>
                                <td class="py-2 pr-4">{{ $row['plan'] }}</td>
                                <td class="py-2 text-ink-muted">{{ $row['last'] ?? __('не заходила после запуска учёта') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($inactive['total'] > count($inactive['rows']))
                <p class="mt-3 text-caption text-ink-muted">{{ __('Показаны первые :n из :total.', ['n' => count($inactive['rows']), 'total' => $inactive['total']]) }}</p>
            @endif
        @endif
    </div>

    {{-- История по месяцам из базы --}}
    <div class="card">
        <h2 class="form-section-title">{{ __('История по месяцам') }}</h2>
        <p class="mb-4 text-caption text-ink-muted">{{ __('Взято из базы за последний год, поэтому есть и за время до включения учёта заходов.') }}</p>
        <div class="overflow-x-auto">
            <table class="min-w-full text-ui">
                <thead class="text-left text-caption uppercase text-ink-muted">
                    <tr>
                        <th class="py-2 pr-4">{{ __('Месяц') }}</th>
                        <th class="py-2 pr-4 text-right">{{ __('Новые участницы') }}</th>
                        <th class="py-2 pr-4 text-right">{{ __('Оплаты') }}</th>
                        <th class="py-2 pr-4 text-right">{{ __('Сумма оплат') }}</th>
                        <th class="py-2 pr-4 text-right">{{ __('Вопросы AI-менеджеру') }}</th>
                        <th class="py-2 text-right">{{ __('Инициативы') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline-soft">
                    @foreach(array_reverse($history['rows']) as $row)
                        <tr>
                            <td class="py-2 pr-4 font-semibold">{{ $row['label'] }}</td>
                            <td class="py-2 pr-4 text-right">{{ $row['registrations'] }}</td>
                            <td class="py-2 pr-4 text-right">{{ $row['payments'] }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($row['revenue'], 0, ',', ' ') }}</td>
                            <td class="py-2 pr-4 text-right">{{ $row['questions'] }}</td>
                            <td class="py-2 text-right">{{ $row['initiatives'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
