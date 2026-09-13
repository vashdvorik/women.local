<x-filament-panels::page>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Prata&display=swap" rel="stylesheet">
<style>
    .impact-report {
        --chocolate-950: #160E0B;
        --chocolate-900: #21140F;
        --chocolate-850: #2A1913;
        --chocolate-800: #34231F;
        --chocolate-700: #493129;
        --cognac-700: #74472F;
        --cognac-600: #8E5B3D;
        --caramel-500: #B9855B;
        --caramel-400: #C99A73;
        --caramel-300: #DBB89B;
        --nude-100: #F1E3DA;
        --porcelain-50: #FFF9F5;
        --canvas: #FFFDFC;
        --surface: #FAF4F1;
        --surface-soft: #FCF8F6;
        --ink-deep: #21140F;
        --ink: #34231F;
        --charcoal: #49342B;
        --slate: #756158;
        --steel: #8C776E;
        --stone: #9C887F;
        --on-dark: #FFF9F5;
        --on-dark-muted: #DCC8BC;
        --hairline: #E9DCD5;
        --hairline-soft: #F2E8E3;
        --hairline-strong: #D8C5BA;
        --success-accent: #526B5A;
        --warning-accent: #9B6A3A;
        --error-accent: #8C4842;
        --display-font: "Prata", Georgia, "Times New Roman", serif;
        --ui-font: "Manrope", Inter, Arial, sans-serif;

        display: grid;
        gap: 64px;
        color: var(--ink);
        font-family: var(--ui-font);
    }

    .impact-report * { box-sizing: border-box; min-width: 0; }

    .impact-eyebrow {
        margin: 0 0 16px;
        color: var(--caramel-400);
        font-family: var(--ui-font);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }

    .impact-eyebrow.on-light { color: var(--cognac-600); }

    /* ---------- Hero ---------- */
    .impact-hero {
        display: grid;
        grid-template-columns: minmax(0, 7fr) minmax(280px, 5fr);
        gap: 48px;
        align-items: end;
        padding: 56px;
        border-radius: 24px;
        background: var(--chocolate-900);
        color: var(--on-dark);
    }

    .impact-hero h1 {
        margin: 0;
        max-width: 640px;
        font-family: var(--display-font);
        font-size: 44px;
        font-weight: 400;
        line-height: 1.12;
        letter-spacing: -0.8px;
        overflow-wrap: break-word;
    }

    .impact-hero p {
        max-width: 520px;
        margin: 20px 0 0;
        color: var(--on-dark-muted);
        font-size: 16px;
        line-height: 1.6;
    }

    .impact-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 32px;
    }

    .impact-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 44px;
        padding: 0 22px;
        border-radius: 9999px;
        background: transparent;
        color: var(--on-dark);
        border: 1px solid rgba(255, 249, 245, 0.30);
        font-family: var(--ui-font);
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        text-decoration: none;
        cursor: pointer;
        transition: border-color 220ms cubic-bezier(.22,.61,.36,1), background 220ms cubic-bezier(.22,.61,.36,1);
    }

    .impact-btn:hover { border-color: rgba(255, 249, 245, 0.6); background: rgba(255, 249, 245, 0.06); }

    .impact-btn--solid {
        background: var(--caramel-500);
        border-color: var(--caramel-500);
        color: var(--chocolate-950);
    }

    .impact-btn--solid:hover { background: var(--caramel-400); border-color: var(--caramel-400); }

    .impact-hero__stat { text-align: right; }

    .impact-stat-display {
        font-family: var(--display-font);
        font-size: 76px;
        font-weight: 400;
        line-height: 1;
        letter-spacing: -2px;
        color: var(--on-dark);
    }

    .impact-stat-display.on-light { color: var(--ink-deep); }

    .impact-hero__stat .impact-stat-label {
        margin-top: 8px;
        color: var(--on-dark-muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .impact-hero__meta {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid rgba(255, 249, 245, 0.14);
        color: var(--on-dark-muted);
        font-size: 12px;
    }

    /* ---------- Sections ---------- */
    .impact-section__head { margin-bottom: 32px; }

    .impact-section__head h2 {
        margin: 0;
        max-width: 640px;
        color: var(--ink-deep);
        font-family: var(--display-font);
        font-size: 32px;
        font-weight: 400;
        line-height: 1.18;
        letter-spacing: -0.3px;
        overflow-wrap: break-word;
    }

    .impact-section__head p {
        max-width: 560px;
        margin: 10px 0 0;
        color: var(--slate);
        font-size: 15px;
        line-height: 1.6;
    }

    /* ---------- Funnel (asymmetric 7/5) ---------- */
    .impact-funnel {
        display: grid;
        grid-template-columns: minmax(0, 7fr) minmax(280px, 5fr);
        gap: 56px;
        align-items: start;
    }

    .impact-funnel__lead .impact-stat-display {
        color: var(--ink-deep);
        font-size: 76px;
    }

    .impact-funnel__lead .impact-stat-caption {
        max-width: 420px;
        margin-top: 14px;
        color: var(--slate);
        font-size: 15px;
        line-height: 1.6;
    }

    .impact-funnel__lead .impact-stat-caption strong { color: var(--ink); font-weight: 600; }

    .impact-row {
        padding-top: 20px;
        border-top: 1px solid var(--hairline);
    }

    .impact-row + .impact-row { margin-top: 20px; }

    .impact-row__meta {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 10px;
    }

    .impact-row__meta .label { color: var(--ink); font-size: 15px; font-weight: 600; }
    .impact-row__meta .value { color: var(--slate); font-size: 13px; font-weight: 600; white-space: nowrap; }

    .impact-row__caption {
        margin: -4px 0 10px;
        color: var(--stone);
        font-size: 13px;
        line-height: 1.5;
    }

    .impact-track { height: 6px; border-radius: 9999px; background: var(--hairline-soft); overflow: hidden; }
    .impact-fill { height: 100%; border-radius: inherit; background: var(--chocolate-900); }
    .impact-fill.tone-success { background: var(--success-accent); }
    .impact-fill.tone-warning { background: var(--warning-accent); }
    .impact-fill.tone-error { background: var(--error-accent); }
    .impact-fill.tone-caramel { background: var(--caramel-500); }

    /* ---------- Quality bars ---------- */
    .impact-quality { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 48px; }

    /* ---------- Chart ---------- */
    .impact-chart {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        height: 200px;
        padding-top: 12px;
        border-bottom: 1px solid var(--hairline);
    }

    .impact-chart__col { display: flex; min-width: 0; flex: 1; flex-direction: column; align-items: center; gap: 8px; }
    .impact-chart__bars { display: flex; align-items: flex-end; justify-content: center; gap: 3px; width: 100%; height: 168px; }
    .impact-chart__bar { width: 40%; min-height: 2px; border-radius: 3px 3px 0 0; background: var(--nude-100); }
    .impact-chart__bar.is-approved { background: var(--caramel-500); }
    .impact-chart__label { overflow: hidden; color: var(--stone); font-size: 10px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }

    .impact-legend { display: flex; gap: 20px; margin-top: 16px; color: var(--slate); font-size: 13px; font-weight: 500; }
    .impact-legend span { display: inline-flex; align-items: center; gap: 8px; }
    .impact-legend i { width: 10px; height: 10px; border-radius: 3px; background: var(--nude-100); }
    .impact-legend i.is-approved { background: var(--caramel-500); }

    /* ---------- Opportunities ---------- */
    .impact-opportunities { display: grid; grid-template-columns: minmax(200px, 4fr) minmax(0, 8fr); gap: 56px; align-items: start; }
    .impact-empty-note { padding-top: 20px; border-top: 1px solid var(--hairline); color: var(--stone); font-size: 14px; line-height: 1.6; }

    /* ---------- Editorial lists ---------- */
    .impact-list { display: grid; }
    .impact-list-item { display: flex; gap: 16px; padding: 20px 0; border-top: 1px solid var(--hairline); }
    .impact-list-item:first-child { border-top: 0; padding-top: 0; }

    .impact-avatar {
        display: flex;
        width: 44px;
        height: 44px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: var(--nude-100);
        color: var(--cognac-700);
        font-family: var(--ui-font);
        font-size: 15px;
        font-weight: 700;
    }

    .impact-list-item__body { min-width: 0; flex: 1; }
    .impact-list-item__top { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
    .impact-list-item__top h3 { margin: 0; color: var(--ink-deep); font-size: 16px; font-weight: 600; }
    .impact-list-item__date { flex: 0 0 auto; color: var(--stone); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .impact-list-item__tag { color: var(--cognac-600); font-size: 12px; font-weight: 600; }
    .impact-list-item p { margin: 6px 0 0; color: var(--slate); font-size: 14px; line-height: 1.55; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }

    .impact-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 56px; }

    /* ---------- Footer ---------- */
    .impact-footer {
        padding-top: 24px;
        border-top: 1px solid var(--hairline);
        color: var(--stone);
        font-size: 12px;
    }

    @media (max-width: 900px) {
        .impact-hero { grid-template-columns: 1fr; padding: 36px; }
        .impact-hero h1 { font-size: 32px; }
        .impact-hero__stat { text-align: left; }
        .impact-funnel, .impact-opportunities, .impact-columns { grid-template-columns: 1fr; }
        .impact-quality { grid-template-columns: 1fr; }
        .impact-stat-display { font-size: 56px; }
        .impact-funnel__lead .impact-stat-display { font-size: 56px; }
    }

    @media (max-width: 480px) {
        .impact-hero { padding: 28px 24px; }
        .impact-hero h1 { font-size: 28px; }
        .impact-stat-display { font-size: 44px; }
        .impact-funnel__lead .impact-stat-display { font-size: 44px; }
        .impact-section__head h2 { font-size: 26px; }
    }

    @media print {
        .fi-sidebar, .fi-topbar, .no-print { display: none !important; }
        .impact-hero { break-inside: avoid; }
    }
</style>

<div class="impact-report">
    <section class="impact-hero">
        <div>
            <p class="impact-eyebrow">Отчёт платформы</p>
            <h1>Платформа женщин предпринимателей</h1>
            <p>Живой срез по составу сообщества, готовности профилей и активности участниц — сформирован из текущих данных платформы.</p>
            <div class="impact-hero__actions">
                <a href="{{ route('admin.impact-metrics.pdf') }}" class="impact-btn impact-btn--solid no-print">Скачать PDF</a>
                <button type="button" onclick="window.print()" class="impact-btn no-print">Печать</button>
            </div>
        </div>
        <div class="impact-hero__stat">
            <div class="impact-stat-display">{{ $platformReadiness }}%</div>
            <p class="impact-stat-label">Индекс готовности сообщества — среднее по заполненности профилей, AI-индексации, активности кабинета и публикациям</p>
            <div class="impact-hero__meta">Сформировано {{ $generatedAt }}</div>
        </div>
    </section>

    <section class="impact-funnel">
        <div class="impact-funnel__lead">
            <p class="impact-eyebrow on-light">Заявки на участие</p>
            <div class="impact-stat-display">{{ $formatNumber($approvedCount) }}</div>
            <p class="impact-stat-caption">одобренных участниц из <strong>{{ $formatNumber($totalApplications) }}</strong> заявок всего — {{ $approvalRate }}% принятых. За последние 30 дней: {{ $formatNumber($newApplicationsLast30) }} новых заявок, {{ $formatNumber($approvedLast30) }} одобрено.</p>
        </div>
        <div>
            @foreach($statusRows as $row)
                <div class="impact-row">
                    <div class="impact-row__meta">
                        <span class="label">{{ $row['label'] }}</span>
                        <span class="value">{{ $formatNumber($row['value']) }} · {{ $row['percent'] }}%</span>
                    </div>
                    <div class="impact-track"><div class="impact-fill tone-{{ $row['tone'] }}" style="width: {{ $safePercent($row['percent']) }}%"></div></div>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <div class="impact-section__head">
            <h2>Готовность базы участниц</h2>
            <p>Насколько профили одобренных участниц готовы приносить пользу сообществу — от заполненности до цифровой активности.</p>
        </div>
        <div class="impact-quality">
            @foreach($qualityRows as $row)
                <div class="impact-row">
                    <div class="impact-row__meta">
                        <span class="label">{{ $row['label'] }}</span>
                        <span class="value">{{ $formatNumber($row['value']) }} / {{ $formatNumber($row['total']) }} · {{ $row['percent'] }}%</span>
                    </div>
                    <p class="impact-row__caption">{{ $row['caption'] }}</p>
                    <div class="impact-track"><div class="impact-fill" style="width: {{ $safePercent($row['percent']) }}%"></div></div>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <div class="impact-section__head">
            <h2>Динамика сообщества</h2>
            <p>Заявки и одобрения по месяцам за последний год.</p>
        </div>
        <div class="impact-chart">
            @foreach($registrationChart as $month)
                @php
                    $applicationHeight = $month['applications'] > 0 ? max(4, (int) round($month['applications'] / $maxMemberChart * 168)) : 0;
                    $approvedHeight = $month['approved'] > 0 ? max(4, (int) round($month['approved'] / $maxMemberChart * 168)) : 0;
                @endphp
                <div class="impact-chart__col">
                    <div class="impact-chart__bars">
                        <div class="impact-chart__bar" style="height: {{ $applicationHeight }}px" title="{{ $month['label'] }}: {{ $month['applications'] }} заявок"></div>
                        <div class="impact-chart__bar is-approved" style="height: {{ $approvedHeight }}px" title="{{ $month['label'] }}: {{ $month['approved'] }} одобрено"></div>
                    </div>
                    <div class="impact-chart__label">{{ $month['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="impact-legend">
            <span><i></i> Заявки</span>
            <span><i class="is-approved"></i> Одобрено</span>
        </div>
    </section>

    <section class="impact-opportunities">
        <div>
            <p class="impact-eyebrow on-light">Возможности</p>
            <div class="impact-stat-display on-light" style="font-size:56px;">{{ $formatNumber($opportunitiesTotal) }}</div>
            <p class="impact-stat-label" style="color: var(--slate); margin-top: 8px;">публикаций от {{ $formatNumber($opportunityAuthors) }} участниц, {{ $formatNumber($opportunitiesLast30) }} за последние 30 дней</p>
        </div>
        <div>
            <div class="impact-section__head" style="margin-bottom:20px;">
                <h2 style="font-size:22px; font-family: var(--ui-font); font-weight:600;">Из чего складываются публикации</h2>
            </div>
            @if($opportunitiesTotal === 0)
                <p class="impact-empty-note">Пока ни одна участница не опубликовала запрос, партнёрство или событие.</p>
            @else
                @foreach($opportunityTypeRows as $row)
                    <div class="impact-row">
                        <div class="impact-row__meta">
                            <span class="label">{{ $row['label'] }}</span>
                            <span class="value">{{ $formatNumber($row['count']) }} · {{ $row['percent'] }}%</span>
                        </div>
                        <div class="impact-track"><div class="impact-fill tone-caramel" style="width: {{ $safePercent($row['percent']) }}%"></div></div>
                    </div>
                @endforeach
            @endif
        </div>
    </section>

    <section class="impact-columns">
        <div>
            <div class="impact-section__head">
                <h2 style="font-size:22px; font-family: var(--ui-font); font-weight:600;">Новые одобренные участницы</h2>
            </div>
            @if($latestMembers->isEmpty())
                <p class="impact-empty-note">Одобренных профилей пока нет.</p>
            @else
                <div class="impact-list">
                    @foreach($latestMembers as $member)
                        <article class="impact-list-item">
                            <div class="impact-avatar">{{ mb_strtoupper(mb_substr($member->full_name ?: '?', 0, 1)) }}</div>
                            <div class="impact-list-item__body">
                                <div class="impact-list-item__top">
                                    <h3>{{ $member->full_name ?: 'Без имени' }}</h3>
                                    <span class="impact-list-item__date">{{ $member->approved_at?->format('d.m.Y') }}</span>
                                </div>
                                <p>{{ $member->description ?: 'Описание профиля пока не заполнено.' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <div class="impact-section__head">
                <h2 style="font-size:22px; font-family: var(--ui-font); font-weight:600;">Последние публикации</h2>
            </div>
            @if($latestOpportunities->isEmpty())
                <p class="impact-empty-note">Публикаций пока нет.</p>
            @else
                <div class="impact-list">
                    @foreach($latestOpportunities as $opportunity)
                        @php($type = $typeMeta[$opportunity->type] ?? ['label' => $opportunity->type])
                        <article class="impact-list-item">
                            <div class="impact-list-item__body">
                                <div class="impact-list-item__top">
                                    <h3>{{ $opportunity->title }}</h3>
                                    <span class="impact-list-item__date">{{ $opportunity->created_at?->format('d.m.Y') }}</span>
                                </div>
                                <span class="impact-list-item__tag">{{ $type['label'] }}</span>
                                <p>{{ $opportunity->author?->full_name ? 'Опубликовала: ' . $opportunity->author->full_name : 'Автор не указан' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <div class="impact-footer">Сформировано автоматически из текущих данных платформы · {{ $generatedAt }}</div>
</div>
</x-filament-panels::page>
