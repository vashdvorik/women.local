<x-filament-panels::page>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- HEADER                                                  --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="flex flex-col gap-1 mb-6">
    <p class="text-sm text-gray-400">Сформировано: {{ $generatedAt }}</p>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ROW 1 — KEY NUMBERS                                     --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 mb-8">

    @php
    $cards = [
        ['label' => 'Всего заявок',      'value' => $totalMembers,      'color' => 'bg-gray-50   border-gray-200',  'text' => 'text-gray-800'],
        ['label' => 'Участников',        'value' => $approvedCount,     'color' => 'bg-green-50  border-green-200', 'text' => 'text-green-700'],
        ['label' => 'Ожидают',           'value' => $pendingCount,      'color' => 'bg-yellow-50 border-yellow-200','text' => 'text-yellow-700'],
        ['label' => 'Отклонено',         'value' => $rejectedCount,     'color' => 'bg-red-50    border-red-200',   'text' => 'text-red-700'],
        ['label' => 'С профилем',        'value' => $withProfile,       'color' => 'bg-blue-50   border-blue-200',  'text' => 'text-blue-700'],
        ['label' => 'Возможности',       'value' => $opportunitiesTotal,'color' => 'bg-purple-50 border-purple-200','text' => 'text-purple-700'],
    ];
    @endphp

    @foreach($cards as $card)
    <div class="rounded-xl border {{ $card['color'] }} p-4 flex flex-col gap-1">
        <span class="text-xs font-medium text-gray-500 leading-tight">{{ $card['label'] }}</span>
        <span class="text-3xl font-bold {{ $card['text'] }}">{{ $card['value'] }}</span>
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ROW 2 — PROFILE COMPLETENESS + OPPORTUNITIES BY TYPE   --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">

    {{-- Profile completeness --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Заполненность профилей</h2>

        @php
            $profilePct   = $approvedCount > 0 ? round($withProfile  / $approvedCount * 100) : 0;
            $embeddingPct = $approvedCount > 0 ? round($withEmbedding / $approvedCount * 100) : 0;
        @endphp

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Заполнили описание</span>
                    <span class="font-semibold text-gray-700">{{ $withProfile }} / {{ $approvedCount }} ({{ $profilePct }}%)</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-gray-100">
                    <div class="h-2.5 rounded-full bg-blue-500 transition-all"
                         style="width: {{ $profilePct }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>AI-эмбеддинг вычислен</span>
                    <span class="font-semibold text-gray-700">{{ $withEmbedding }} / {{ $approvedCount }} ({{ $embeddingPct }}%)</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-gray-100">
                    <div class="h-2.5 rounded-full bg-violet-500 transition-all"
                         style="width: {{ $embeddingPct }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Одобрено от всех заявок</span>
                    @php $approvedPct = $totalMembers > 0 ? round($approvedCount / $totalMembers * 100) : 0; @endphp
                    <span class="font-semibold text-gray-700">{{ $approvedCount }} / {{ $totalMembers }} ({{ $approvedPct }}%)</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-gray-100">
                    <div class="h-2.5 rounded-full bg-green-500 transition-all"
                         style="width: {{ $approvedPct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Opportunities by type --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Возможности по типу</h2>

        @php
        $typeConfig = [
            'project' => ['emoji' => '🚀', 'label' => 'Проекты',      'bar' => 'bg-blue-400'],
            'meeting' => ['emoji' => '🤝', 'label' => 'Встречи',       'bar' => 'bg-emerald-400'],
            'event'   => ['emoji' => '🎉', 'label' => 'Мероприятия',   'bar' => 'bg-purple-400'],
        ];
        $maxOpp = max(array_values($oppByType) ?: [1]);
        @endphp

        @if($opportunitiesTotal === 0)
        <p class="text-sm text-gray-400">Возможности ещё не публиковались.</p>
        @else
        <div class="space-y-3">
            @foreach($typeConfig as $key => $cfg)
            @php $cnt = $oppByType[$key] ?? 0; $pct = $maxOpp > 0 ? round($cnt / $maxOpp * 100) : 0; @endphp
            <div>
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>{{ $cfg['emoji'] }} {{ $cfg['label'] }}</span>
                    <span class="font-semibold text-gray-700">{{ $cnt }}</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-gray-100">
                    <div class="h-2.5 rounded-full {{ $cfg['bar'] }} transition-all"
                         style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ROW 3 — REGISTRATION CHART (text bars)                 --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm mb-8">
    <h2 class="mb-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Динамика регистраций (12 месяцев)</h2>

    @php
    $maxReg = max(array_values($registrationChart) ?: [1]);
    $monthNames = [
        '01'=>'Янв','02'=>'Фев','03'=>'Мар','04'=>'Апр',
        '05'=>'Май','06'=>'Июн','07'=>'Июл','08'=>'Авг',
        '09'=>'Сен','10'=>'Окт','11'=>'Ноя','12'=>'Дек',
    ];
    @endphp

    <div class="flex items-end gap-2 h-32">
        @foreach($registrationChart as $month => $cnt)
        @php
            $heightPct  = $maxReg > 0 ? round($cnt / $maxReg * 100) : 0;
            $heightPx   = max(4, (int) round($heightPct * 112 / 100));
            [$yr, $mo]  = explode('-', $month);
            $label      = ($monthNames[$mo] ?? $mo) . ' ' . substr($yr, 2);
        @endphp
        <div class="flex flex-1 flex-col items-center gap-1 group">
            <span class="text-[10px] font-semibold text-gray-700 opacity-0 group-hover:opacity-100 transition">{{ $cnt }}</span>
            <div class="w-full rounded-t-md bg-amber-400 transition-all hover:bg-amber-500"
                 style="height: {{ $heightPx }}px"
                 title="{{ $label }}: {{ $cnt }} заявок"></div>
            <span class="text-[10px] text-gray-400 leading-none">{{ $label }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ROW 4 — OPP CHART + LATEST MEMBERS                     --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">

    {{-- Opportunities chart --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Публикация возможностей (6 месяцев)</h2>

        @php $maxOppM = max(array_values($oppChart) ?: [1]); @endphp

        <div class="flex items-end gap-2 h-24">
            @foreach($oppChart as $month => $cnt)
            @php
                $hPct  = $maxOppM > 0 ? round($cnt / $maxOppM * 100) : 0;
                $hPx   = max(4, (int) round($hPct * 80 / 100));
                [$yr, $mo] = explode('-', $month);
                $lbl   = ($monthNames[$mo] ?? $mo) . ' ' . substr($yr, 2);
            @endphp
            <div class="flex flex-1 flex-col items-center gap-1 group">
                <span class="text-[10px] font-semibold text-gray-700 opacity-0 group-hover:opacity-100 transition">{{ $cnt }}</span>
                <div class="w-full rounded-t-md bg-purple-400 transition-all hover:bg-purple-500"
                     style="height: {{ $hPx }}px"
                     title="{{ $lbl }}: {{ $cnt }}"></div>
                <span class="text-[10px] text-gray-400 leading-none">{{ $lbl }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Latest approved members --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Последние участники</h2>

        @if($latestMembers->isEmpty())
        <p class="text-sm text-gray-400">Одобренных участников нет.</p>
        @else
        <div class="space-y-3">
            @foreach($latestMembers as $member)
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-700">
                    {{ mb_strtoupper(mb_substr($member->full_name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $member->full_name ?? '—' }}</p>
                    @if($member->telegram_username)
                    <p class="text-xs text-gray-400">@{{ $member->telegram_username }}</p>
                    @endif
                </div>
                @if($member->approved_at)
                <span class="shrink-0 text-xs text-gray-400">{{ $member->approved_at->format('d.m.Y') }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ROW 5 — GRANT REPORT SUMMARY (printable)               --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
            📋 Резюме для грантового отчёта
        </h2>
        <button onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm hover:bg-gray-50 transition">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Распечатать
        </button>
    </div>

    <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
        @php
        $lines = [
            ['Дата формирования отчёта',                 $generatedAt],
            ['Всего зарегистрировалось в платформе',      $totalMembers . ' чел.'],
            ['Одобрено и активно в сообществе',           $approvedCount . ' чел.'],
            ['Заполнили профиль (описание)',               $withProfile . ' чел. (' . $profilePct . '%)'],
            ['AI-эмбеддинг вычислен (профиль в системе AI)', $withEmbedding . ' чел. (' . $embeddingPct . '%)'],
            ['Опубликовано возможностей (проекты/встречи/ивенты)', $opportunitiesTotal],
            ['— из них Проектов',                         $oppByType['project'] ?? 0],
            ['— из них Встреч',                           $oppByType['meeting'] ?? 0],
            ['— из них Мероприятий',                      $oppByType['event']   ?? 0],
        ];
        @endphp

        @foreach($lines as [$label, $value])
        <div class="flex justify-between gap-2 rounded-lg bg-white border border-gray-100 px-4 py-2.5">
            <span class="text-gray-500">{{ $label }}</span>
            <span class="font-semibold text-gray-800 shrink-0">{{ $value }}</span>
        </div>
        @endforeach
    </div>
</div>

</x-filament-panels::page>
