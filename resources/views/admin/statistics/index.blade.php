@php
    $tone = fn (string $t) => match ($t) {
        'success' => 'bg-success',
        'warning' => 'bg-warning',
        'error' => 'bg-danger',
        default => 'bg-accent',
    };
    $chartHeight = 140;
@endphp

<x-layouts.admin title="Статистика платформы">
    <x-slot:actions>
        <a href="{{ route('admin.statistics.pdf') }}" class="btn-primary">Скачать PDF</a>
        <button type="button" onclick="window.print()" class="btn-secondary hidden md:inline-flex">Печать</button>
    </x-slot:actions>

    <div class="space-y-6">

        {{-- Итог: индекс готовности + воронка заявок --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card">
                <p class="field-hint uppercase">Индекс готовности сообщества</p>
                <p class="mt-2 text-[44px] font-semibold leading-none text-accent">{{ $platformReadiness }}%</p>
                <p class="mt-3 text-ui text-ink-muted">
                    Среднее по заполненности профилей, AI-индексации, активности кабинета и публикациям.
                    Сформировано {{ $generatedAt }}.
                </p>
            </div>

            <div class="card">
                <p class="field-hint uppercase">Заявки на участие</p>
                <p class="mt-2 text-[44px] font-semibold leading-none">{{ $formatNumber($approvedCount) }}</p>
                <p class="mt-3 text-ui text-ink-muted">
                    одобренных участниц из <b class="text-ink">{{ $formatNumber($totalApplications) }}</b> заявок — {{ $approvalRate }}% принятых.
                    За 30 дней: {{ $formatNumber($newApplicationsLast30) }} новых заявок, {{ $formatNumber($approvedLast30) }} одобрено.
                </p>
                <div class="mt-4 space-y-3">
                    @foreach($statusRows as $row)
                        <div>
                            <div class="flex justify-between text-ui">
                                <span>{{ $row['label'] }}</span>
                                <span class="text-ink-muted">{{ $formatNumber($row['value']) }} · {{ $row['percent'] }}%</span>
                            </div>
                            <div class="mt-1 h-2 rounded-pill bg-surface-sunken">
                                <div class="h-2 rounded-pill {{ $tone($row['tone']) }}" style="width: {{ $safePercent($row['percent']) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Готовность базы --}}
        <div class="card">
            <h2 class="form-section-title">Готовность базы участниц</h2>
            <p class="mb-4 text-ui text-ink-muted">
                Насколько профили одобренных участниц готовы приносить пользу сообществу: от заполненности до цифровой активности.
            </p>
            <div class="grid gap-x-8 gap-y-5 md:grid-cols-2">
                @foreach($qualityRows as $row)
                    <div>
                        <div class="flex justify-between gap-3 text-ui">
                            <span class="font-semibold">{{ $row['label'] }}</span>
                            <span class="shrink-0 text-ink-muted">{{ $formatNumber($row['value']) }} / {{ $formatNumber($row['total']) }} · {{ $row['percent'] }}%</span>
                        </div>
                        <p class="text-caption text-ink-muted">{{ $row['caption'] }}</p>
                        <div class="mt-1 h-2 rounded-pill bg-surface-sunken">
                            <div class="h-2 rounded-pill bg-accent" style="width: {{ $safePercent($row['percent']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Динамика --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card">
                <h2 class="form-section-title">Динамика сообщества</h2>
                <p class="mb-4 text-caption text-ink-muted">Заявки и одобрения по месяцам за последний год.</p>
                <div class="flex items-end gap-1.5" style="height: {{ $chartHeight + 24 }}px">
                    @foreach($registrationChart as $month)
                        @php
                            $a = $month['applications'] > 0 ? max(4, (int) round($month['applications'] / $maxMemberChart * $chartHeight)) : 0;
                            $b = $month['approved'] > 0 ? max(4, (int) round($month['approved'] / $maxMemberChart * $chartHeight)) : 0;
                        @endphp
                        <div class="flex flex-1 flex-col items-center justify-end gap-1">
                            <div class="flex items-end gap-0.5">
                                <div class="w-2 rounded-xs bg-hairline" style="height: {{ $a }}px" title="{{ $month['label'] }}: {{ $month['applications'] }} заявок"></div>
                                <div class="w-2 rounded-xs bg-accent" style="height: {{ $b }}px" title="{{ $month['label'] }}: {{ $month['approved'] }} одобрено"></div>
                            </div>
                            <span class="text-micro text-ink-faint">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex gap-4 text-caption text-ink-muted">
                    <span class="inline-flex items-center gap-1.5"><i class="inline-block h-2 w-2 rounded-xs bg-hairline"></i> Заявки</span>
                    <span class="inline-flex items-center gap-1.5"><i class="inline-block h-2 w-2 rounded-xs bg-accent"></i> Одобрено</span>
                </div>
            </div>

            <div class="card">
                <h2 class="form-section-title">Публикации участниц</h2>
                <p class="text-[36px] font-semibold leading-none">{{ $formatNumber($opportunitiesTotal) }}</p>
                <p class="mt-2 mb-4 text-ui text-ink-muted">
                    одобренных публикаций от {{ $formatNumber($opportunityAuthors) }} участниц,
                    {{ $formatNumber($opportunitiesLast30) }} за последние 30 дней.
                </p>
                @if($opportunitiesTotal === 0)
                    <p class="text-ui text-ink-muted">Пока нет одобренных публикаций.</p>
                @else
                    <div class="space-y-3">
                        @foreach($opportunityTypeRows as $row)
                            <div>
                                <div class="flex justify-between text-ui">
                                    <span>{{ $row['label'] }}</span>
                                    <span class="text-ink-muted">{{ $formatNumber($row['count']) }} · {{ $row['percent'] }}%</span>
                                </div>
                                <div class="mt-1 h-2 rounded-pill bg-surface-sunken">
                                    <div class="h-2 rounded-pill bg-accent" style="width: {{ $safePercent($row['percent']) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Последние --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card">
                <h2 class="form-section-title">Новые одобренные участницы</h2>
                @if($latestMembers->isEmpty())
                    <p class="text-ui text-ink-muted">Одобренных профилей пока нет.</p>
                @else
                    <ul class="divide-y divide-hairline-soft">
                        @foreach($latestMembers as $member)
                            <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-pill bg-accent-soft text-ui-strong text-accent">
                                    {{ mb_strtoupper(mb_substr($member->full_name ?: '?', 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-ui-strong font-semibold">
                                        {{ $member->full_name ?: 'Без имени' }}
                                        <span class="ml-2 font-normal text-ink-faint">{{ $member->approved_at?->format('d.m.Y') }}</span>
                                    </p>
                                    <p class="line-clamp-2 text-caption text-ink-muted">{{ $member->description ?: 'Описание профиля пока не заполнено.' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <h2 class="form-section-title">Последние публикации</h2>
                @if($latestOpportunities->isEmpty())
                    <p class="text-ui text-ink-muted">Публикаций пока нет.</p>
                @else
                    <ul class="divide-y divide-hairline-soft">
                        @foreach($latestOpportunities as $post)
                            @php $type = $typeMeta[$post->type] ?? ['label' => $post->type]; @endphp
                            <li class="py-3 first:pt-0 last:pb-0">
                                <p class="text-ui-strong font-semibold">
                                    {{ $post->title }}
                                    <span class="ml-2 font-normal text-ink-faint">{{ $post->created_at?->format('d.m.Y') }}</span>
                                </p>
                                <p class="text-caption text-ink-muted">
                                    {{ $type['label'] }} · {{ $post->author?->full_name ? 'Опубликовала: '.$post->author->full_name : 'Автор не указан' }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
