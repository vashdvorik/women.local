<x-filament-panels::page>
@php
    $formatNumber = fn (int|float $value): string => number_format((float) $value, 0, ',', ' ');

    $statusRows = [
        ['label' => 'Одобренные участницы', 'value' => $approvedCount, 'percent' => $approvalRate, 'class' => 'is-green'],
        ['label' => 'Ожидают рассмотрения', 'value' => $pendingCount, 'percent' => $totalApplications > 0 ? round($pendingCount / $totalApplications * 100) : 0, 'class' => 'is-amber'],
        ['label' => 'Отклонённые заявки', 'value' => $rejectedCount, 'percent' => $totalApplications > 0 ? round($rejectedCount / $totalApplications * 100) : 0, 'class' => 'is-rose'],
    ];

    $qualityRows = [
        ['label' => 'Полные бизнес-профили', 'caption' => 'Описание бизнеса и запрос к сообществу', 'value' => $completeProfiles, 'total' => $approvedCount, 'percent' => $profileCompletionRate, 'class' => 'is-pink'],
        ['label' => 'AI-готовые профили', 'caption' => 'Есть embedding для рекомендаций и поиска', 'value' => $withEmbedding, 'total' => $approvedCount, 'percent' => $aiReadinessRate, 'class' => 'is-violet'],
        ['label' => 'Активировали кабинет', 'caption' => 'Хотя бы один успешный вход по Telegram-токену', 'value' => $activeCabinetUsers, 'total' => $approvedCount, 'percent' => $cabinetActivationRate, 'class' => 'is-cyan'],
        ['label' => 'Публиковали возможности', 'caption' => 'Создали запрос, партнёрство или событие', 'value' => $opportunityAuthors, 'total' => $approvedCount, 'percent' => $publicationActivationRate, 'class' => 'is-green'],
    ];

    $typeMeta = [
        'project' => ['label' => 'Запросы', 'icon' => '💼', 'color' => '#ec4899', 'class' => 'is-pink'],
        'meeting' => ['label' => 'Партнёрства', 'icon' => '🤝', 'color' => '#10b981', 'class' => 'is-green'],
        'event' => ['label' => 'События', 'icon' => '📅', 'color' => '#8b5cf6', 'class' => 'is-violet'],
    ];

    $opportunityTypeRows = collect($typeMeta)->map(function (array $meta, string $type) use ($opportunitiesByType, $opportunitiesTotal) {
        $count = (int) ($opportunitiesByType[$type] ?? 0);

        return [
            ...$meta,
            'type' => $type,
            'count' => $count,
            'percent' => $opportunitiesTotal > 0 ? (int) round($count / $opportunitiesTotal * 100) : 0,
        ];
    })->values();

    $donutParts = [];
    $donutStart = 0;

    foreach ($opportunityTypeRows as $row) {
        $donutEnd = $donutStart + ($opportunitiesTotal > 0 ? ($row['count'] / $opportunitiesTotal * 360) : 0);
        $donutParts[] = $row['color'] . ' ' . $donutStart . 'deg ' . $donutEnd . 'deg';
        $donutStart = $donutEnd;
    }

    $donutGradient = $opportunitiesTotal > 0
        ? 'conic-gradient(' . implode(', ', $donutParts) . ')'
        : 'conic-gradient(#e5e7eb 0deg 360deg)';

    $maxMemberChart = max(
        1,
        (int) collect($registrationChart)->max('applications'),
        (int) collect($registrationChart)->max('approved')
    );

    $maxOpportunityChart = max(1, (int) collect($opportunityChart)->max('opportunities'));

    $headlineCards = [
        ['label' => 'Одобренных участниц', 'value' => $approvedCount, 'note' => $approvedLast30 . ' за 30 дней', 'class' => 'is-pink', 'icon' => '♀'],
        ['label' => 'Заявок всего', 'value' => $totalApplications, 'note' => $newApplicationsLast30 . ' новых за 30 дней', 'class' => 'is-amber', 'icon' => '↗'],
        ['label' => 'Возможностей', 'value' => $opportunitiesTotal, 'note' => $opportunitiesLast30 . ' опубликовано за 30 дней', 'class' => 'is-violet', 'icon' => '✦'],
        ['label' => 'Индекс готовности', 'value' => $platformReadiness . '%', 'note' => 'профили, AI, кабинет, активность', 'class' => 'is-green', 'icon' => '✓'],
    ];
@endphp

<style>
    .impact-page {
        --impact-bg: #fff7fb;
        --impact-border: #f4d7e8;
        --impact-text: #111827;
        --impact-muted: #6b7280;
        --impact-pink: #ec4899;
        --impact-rose: #f43f5e;
        --impact-violet: #8b5cf6;
        --impact-green: #10b981;
        --impact-cyan: #06b6d4;
        --impact-amber: #f59e0b;
        display: grid;
        gap: 24px;
        color: var(--impact-text);
    }

    .impact-page * {
        box-sizing: border-box;
    }

    .impact-hero,
    .impact-card,
    .impact-panel,
    .impact-report {
        border: 1px solid rgba(244, 215, 232, 0.95);
        border-radius: 28px;
        background: #fff;
        box-shadow: 0 18px 55px rgba(236, 72, 153, 0.12);
        overflow: hidden;
    }

    .impact-hero__top {
        position: relative;
        overflow: hidden;
        padding: 36px;
        color: #fff;
        background:
            radial-gradient(circle at 85% 0%, rgba(255, 255, 255, 0.28), transparent 28%),
            radial-gradient(circle at 12% 110%, rgba(253, 230, 138, 0.22), transparent 30%),
            linear-gradient(135deg, #f43f5e 0%, #d946ef 48%, #7c3aed 100%);
    }

    .impact-hero__content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 28px;
    }

    .impact-eyebrow {
        margin: 0 0 12px;
        color: #ffe4f1;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.24em;
        text-transform: uppercase;
    }

    .impact-title {
        max-width: 880px;
        margin: 0;
        font-size: clamp(32px, 5vw, 56px);
        font-weight: 950;
        line-height: 0.98;
        letter-spacing: -0.045em;
    }

    .impact-lead {
        max-width: 720px;
        margin: 18px 0 0;
        color: #fff1f7;
        font-size: 17px;
        line-height: 1.65;
    }

    .impact-stamp {
        min-width: 230px;
        padding: 18px;
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(16px);
    }

    .impact-stamp span {
        display: block;
        color: #ffe4f1;
        font-size: 13px;
    }

    .impact-stamp strong {
        display: block;
        margin-top: 4px;
        font-size: 24px;
        font-weight: 900;
    }

    .impact-print-button {
        width: 100%;
        margin-top: 14px;
        padding: 11px 14px;
        border: 0;
        border-radius: 14px;
        background: #fff;
        color: #be185d;
        cursor: pointer;
        font-weight: 850;
        box-shadow: 0 12px 28px rgba(17, 24, 39, 0.18);
    }

    .impact-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        padding: 20px;
    }

    .impact-kpi {
        padding: 22px;
        border: 1px solid #f3f4f6;
        border-radius: 24px;
        background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
    }

    .impact-kpi__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 22px;
    }

    .impact-kpi__label {
        color: var(--impact-muted);
        font-size: 14px;
        font-weight: 750;
    }

    .impact-icon {
        display: inline-flex;
        width: 44px;
        height: 44px;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        color: #fff;
        font-size: 20px;
        font-weight: 900;
        box-shadow: 0 12px 24px rgba(17, 24, 39, 0.16);
    }

    .impact-icon.is-pink,
    .impact-fill.is-pink {
        background: linear-gradient(135deg, var(--impact-pink), var(--impact-rose));
    }

    .impact-icon.is-amber,
    .impact-fill.is-amber {
        background: linear-gradient(135deg, #fbbf24, var(--impact-amber));
    }

    .impact-icon.is-violet,
    .impact-fill.is-violet {
        background: linear-gradient(135deg, var(--impact-violet), #4f46e5);
    }

    .impact-icon.is-green,
    .impact-fill.is-green {
        background: linear-gradient(135deg, var(--impact-green), var(--impact-cyan));
    }

    .impact-icon.is-rose,
    .impact-fill.is-rose {
        background: linear-gradient(135deg, #fb7185, var(--impact-rose));
    }

    .impact-icon.is-cyan,
    .impact-fill.is-cyan {
        background: linear-gradient(135deg, var(--impact-cyan), #2563eb);
    }

    .impact-kpi__value {
        font-size: 42px;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -0.04em;
    }

    .impact-kpi__note {
        margin-top: 10px;
        color: var(--impact-muted);
        font-size: 14px;
    }

    .impact-grid {
        display: grid;
        gap: 24px;
    }

    .impact-grid.is-two {
        grid-template-columns: minmax(0, 1.08fr) minmax(360px, 0.92fr);
    }

    .impact-grid.is-even {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .impact-panel {
        padding: 26px;
    }

    .impact-panel.is-dark {
        border-color: #1f2937;
        color: #fff;
        background:
            radial-gradient(circle at 100% 0%, rgba(236, 72, 153, 0.25), transparent 28%),
            linear-gradient(135deg, #111827 0%, #334155 100%);
    }

    .impact-section-head {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .impact-section-head h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -0.025em;
    }

    .impact-section-head p,
    .impact-copy {
        margin: 7px 0 0;
        color: var(--impact-muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .impact-panel.is-dark .impact-copy,
    .impact-panel.is-dark .impact-section-head p {
        color: #cbd5e1;
    }

    .impact-badge {
        display: inline-flex;
        align-items: center;
        align-self: flex-start;
        padding: 12px 16px;
        border-radius: 18px;
        background: #ecfdf5;
        color: #047857;
        font-size: 12px;
        font-weight: 850;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .impact-badge strong {
        margin-left: 8px;
        font-size: 24px;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .impact-bars {
        display: grid;
        gap: 18px;
    }

    .impact-bar__meta {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 8px;
        color: #374151;
        font-size: 14px;
        font-weight: 750;
    }

    .impact-bar__value {
        color: #111827;
        font-weight: 900;
        white-space: nowrap;
    }

    .impact-track {
        height: 14px;
        overflow: hidden;
        border-radius: 999px;
        background: #f3f4f6;
    }

    .impact-fill {
        height: 100%;
        min-width: 3px;
        border-radius: inherit;
    }

    .impact-donor-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 24px;
    }

    .impact-donor-item {
        padding: 18px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.1);
    }

    .impact-donor-item strong {
        display: block;
        font-size: 34px;
        line-height: 1;
        font-weight: 950;
    }

    .impact-donor-item span {
        display: block;
        margin-top: 7px;
        color: #cbd5e1;
        font-size: 12px;
        line-height: 1.45;
    }

    .impact-note {
        margin-top: 22px;
        padding: 18px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.1);
        color: #e5e7eb;
        font-size: 14px;
        line-height: 1.65;
    }

    .impact-chart {
        display: flex;
        height: 290px;
        align-items: flex-end;
        gap: 9px;
        padding-top: 16px;
    }

    .impact-chart__item {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .impact-chart__bars {
        display: flex;
        width: 100%;
        height: 235px;
        align-items: flex-end;
        justify-content: center;
        gap: 3px;
    }

    .impact-chart__bar {
        width: 46%;
        min-height: 5px;
        border-radius: 10px 10px 0 0;
        background: linear-gradient(180deg, #f9a8d4 0%, #ec4899 100%);
    }

    .impact-chart__bar.is-approved {
        background: linear-gradient(180deg, #5eead4 0%, #10b981 100%);
    }

    .impact-chart__label {
        width: 100%;
        overflow: hidden;
        color: #9ca3af;
        font-size: 10px;
        font-weight: 700;
        text-align: center;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .impact-legend {
        display: flex;
        gap: 18px;
        margin-top: 16px;
        color: var(--impact-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .impact-legend span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .impact-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 999px;
        background: var(--impact-pink);
    }

    .impact-dot.is-green {
        background: var(--impact-green);
    }

    .impact-opportunity-layout {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr);
        gap: 24px;
        align-items: center;
    }

    .impact-donut {
        display: flex;
        width: 210px;
        height: 210px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        box-shadow: inset 0 0 24px rgba(17, 24, 39, 0.14);
    }

    .impact-donut__inner {
        display: flex;
        width: 128px;
        height: 128px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #fff;
        box-shadow: 0 18px 34px rgba(17, 24, 39, 0.13);
        text-align: center;
    }

    .impact-donut__inner strong {
        font-size: 38px;
        line-height: 1;
        font-weight: 950;
    }

    .impact-donut__inner span {
        margin-top: 4px;
        color: #9ca3af;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .impact-mini-chart {
        display: flex;
        height: 145px;
        align-items: flex-end;
        gap: 12px;
        margin-top: 18px;
    }

    .impact-mini-chart .impact-chart__bar {
        width: 100%;
        background: linear-gradient(180deg, #f0abfc 0%, #8b5cf6 100%);
    }

    .impact-quality-grid,
    .impact-list-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .impact-quality {
        padding: 20px;
        border: 1px solid #f3f4f6;
        border-radius: 22px;
        background: #f9fafb;
    }

    .impact-quality__head {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 14px;
    }

    .impact-quality h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
    }

    .impact-quality p {
        margin: 5px 0 0;
        color: var(--impact-muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .impact-quality__value {
        text-align: right;
    }

    .impact-quality__value strong {
        display: block;
        font-size: 28px;
        font-weight: 950;
        line-height: 1;
    }

    .impact-quality__value span {
        display: block;
        margin-top: 4px;
        color: var(--impact-muted);
        font-size: 11px;
        white-space: nowrap;
    }

    .impact-facts {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .impact-fact {
        padding: 16px;
        border-radius: 18px;
        background: #fdf2f8;
        color: #9d174d;
    }

    .impact-fact.is-amber {
        background: #fffbeb;
        color: #92400e;
    }

    .impact-fact.is-cyan {
        background: #ecfeff;
        color: #155e75;
    }

    .impact-fact.is-violet {
        background: #f5f3ff;
        color: #5b21b6;
    }

    .impact-fact strong {
        display: block;
        font-size: 22px;
        font-weight: 950;
    }

    .impact-fact span {
        display: block;
        margin-top: 5px;
        font-size: 13px;
        line-height: 1.45;
    }

    .impact-person,
    .impact-opportunity {
        display: flex;
        gap: 14px;
        padding: 16px;
        border: 1px solid #f3f4f6;
        border-radius: 20px;
        background: #f9fafb;
    }

    .impact-avatar {
        display: flex;
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: linear-gradient(135deg, #ec4899, #8b5cf6);
        color: #fff;
        font-weight: 950;
    }

    .impact-person__body,
    .impact-opportunity__body {
        min-width: 0;
        flex: 1;
    }

    .impact-person h3,
    .impact-opportunity h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 900;
    }

    .impact-person p,
    .impact-opportunity p {
        margin: 6px 0 0;
        color: var(--impact-muted);
        font-size: 13px;
        line-height: 1.55;
    }

    .impact-tag-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .impact-tag {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 999px;
        background: #fff;
        color: #4b5563;
        font-size: 12px;
        font-weight: 850;
    }

    .impact-date {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap;
    }

    .impact-empty {
        padding: 22px;
        border: 1px dashed #d1d5db;
        border-radius: 20px;
        background: #f9fafb;
        color: var(--impact-muted);
        font-size: 14px;
    }

    .impact-report {
        padding: 26px;
        background:
            radial-gradient(circle at 0% 0%, rgba(236, 72, 153, 0.12), transparent 28%),
            radial-gradient(circle at 100% 100%, rgba(139, 92, 246, 0.12), transparent 26%),
            #fff;
    }

    .impact-report__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
    }

    .impact-report__head h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 950;
    }

    .impact-report__head p {
        margin: 6px 0 0;
        color: var(--impact-muted);
        font-size: 14px;
    }

    .impact-report__ready {
        padding: 10px 14px;
        border-radius: 999px;
        background: #fff;
        color: #be185d;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        box-shadow: 0 10px 22px rgba(236, 72, 153, 0.12);
        white-space: nowrap;
    }

    .impact-report-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .impact-report-item {
        padding: 16px;
        border: 1px solid rgba(255, 255, 255, 0.75);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 8px 20px rgba(17, 24, 39, 0.05);
    }

    .impact-report-item span {
        display: block;
        color: #9ca3af;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .impact-report-item strong {
        display: block;
        margin-top: 7px;
        font-size: 20px;
        font-weight: 950;
    }

    @media (max-width: 1180px) {
        .impact-kpis,
        .impact-facts,
        .impact-report-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .impact-grid.is-two,
        .impact-grid.is-even,
        .impact-list-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .impact-hero__top,
        .impact-panel,
        .impact-report {
            padding: 20px;
        }

        .impact-hero__content,
        .impact-section-head,
        .impact-report__head {
            flex-direction: column;
            align-items: stretch;
        }

        .impact-kpis,
        .impact-quality-grid,
        .impact-facts,
        .impact-report-grid,
        .impact-donor-grid,
        .impact-opportunity-layout {
            grid-template-columns: 1fr;
        }

        .impact-donut {
            margin: 0 auto;
        }

        .impact-chart {
            gap: 5px;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .impact-chart__item {
            min-width: 42px;
        }
    }

    @media print {
        .fi-sidebar,
        .fi-topbar,
        .no-print {
            display: none !important;
        }

        .impact-page {
            gap: 16px;
        }

        .impact-hero,
        .impact-card,
        .impact-panel,
        .impact-report {
            break-inside: avoid;
            box-shadow: none !important;
        }
    }
</style>

<div class="impact-page">
    <section class="impact-hero">
        <div class="impact-hero__top">
            <div class="impact-hero__content">
                <div>
                    <p class="impact-eyebrow">Donor-ready dashboard</p>
                    <h1 class="impact-title">Women Entrepreneurs Platform of the Two Banks</h1>
                    <p class="impact-lead">
                        Живая сводка по росту сообщества, качеству профилей, активности участниц и готовности платформы к масштабированию.
                    </p>
                </div>

                <div class="impact-stamp">
                    <span>Сформировано</span>
                    <strong>{{ $generatedAt }}</strong>
                    <button type="button" onclick="window.print()" class="impact-print-button no-print">
                        Распечатать для доноров
                    </button>
                </div>
            </div>
        </div>

        <div class="impact-kpis">
            @foreach($headlineCards as $card)
                <article class="impact-kpi">
                    <div class="impact-kpi__head">
                        <span class="impact-kpi__label">{{ $card['label'] }}</span>
                        <span class="impact-icon {{ $card['class'] }}">{{ $card['icon'] }}</span>
                    </div>
                    <div class="impact-kpi__value">{{ is_numeric($card['value']) ? $formatNumber($card['value']) : $card['value'] }}</div>
                    <div class="impact-kpi__note">{{ $card['note'] }}</div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="impact-grid is-two">
        <article class="impact-panel">
            <div class="impact-section-head">
                <div>
                    <h2>Воронка сообщества</h2>
                    <p>Показывает качество отбора и текущую операционную нагрузку модерации.</p>
                </div>
                <div class="impact-badge">Approval rate <strong>{{ $approvalRate }}%</strong></div>
            </div>

            <div class="impact-bars">
                @foreach($statusRows as $row)
                    <div class="impact-bar">
                        <div class="impact-bar__meta">
                            <span>{{ $row['label'] }}</span>
                            <span class="impact-bar__value">{{ $formatNumber($row['value']) }} · {{ $row['percent'] }}%</span>
                        </div>
                        <div class="impact-track">
                            <div class="impact-fill {{ $row['class'] }}" style="width: {{ $row['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="impact-panel is-dark">
            <div class="impact-section-head">
                <div>
                    <h2>Донорский срез</h2>
                    <p>Короткие показатели, которые легко вынести в отчёт или презентацию.</p>
                </div>
            </div>

            <div class="impact-donor-grid">
                <div class="impact-donor-item">
                    <strong>{{ $profileCompletionRate }}%</strong>
                    <span>профилей готовы для анализа потребностей</span>
                </div>
                <div class="impact-donor-item">
                    <strong>{{ $aiReadinessRate }}%</strong>
                    <span>участниц доступны для AI-рекомендаций</span>
                </div>
                <div class="impact-donor-item">
                    <strong>{{ $publicationActivationRate }}%</strong>
                    <span>участниц уже создавали возможности</span>
                </div>
                <div class="impact-donor-item">
                    <strong>{{ $cabinetActivationRate }}%</strong>
                    <span>активировали личный кабинет</span>
                </div>
            </div>

            <p class="impact-note">
                Чем выше полнота профилей и AI-готовность, тем точнее платформа может связывать предпринимательниц с релевантными контактами, событиями и партнёрскими запросами.
            </p>
        </article>
    </section>

    <section class="impact-grid is-even">
        <article class="impact-panel">
            <div class="impact-section-head">
                <div>
                    <h2>Рост за 12 месяцев</h2>
                    <p>Новые заявки и одобренные участницы по месяцам.</p>
                </div>
            </div>

            <div class="impact-chart">
                @foreach($registrationChart as $month)
                    @php
                        $applicationHeight = max(8, (int) round($month['applications'] / $maxMemberChart * 220));
                        $approvedHeight = max(4, (int) round($month['approved'] / $maxMemberChart * 220));
                    @endphp
                    <div class="impact-chart__item">
                        <div class="impact-chart__bars">
                            <div class="impact-chart__bar" style="height: {{ $applicationHeight }}px" title="{{ $month['label'] }}: {{ $month['applications'] }} заявок"></div>
                            <div class="impact-chart__bar is-approved" style="height: {{ $approvedHeight }}px" title="{{ $month['label'] }}: {{ $month['approved'] }} одобрено"></div>
                        </div>
                        <div class="impact-chart__label">{{ $month['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="impact-legend">
                <span><i class="impact-dot"></i> Заявки</span>
                <span><i class="impact-dot is-green"></i> Одобрено</span>
            </div>
        </article>

        <article class="impact-panel">
            <div class="impact-section-head">
                <div>
                    <h2>Возможности и активность</h2>
                    <p>Что участницы публикуют внутри платформы.</p>
                </div>
            </div>

            <div class="impact-opportunity-layout">
                <div class="impact-donut" style="background: {{ $donutGradient }}">
                    <div class="impact-donut__inner">
                        <strong>{{ $formatNumber($opportunitiesTotal) }}</strong>
                        <span>публикаций</span>
                    </div>
                </div>

                <div class="impact-bars">
                    @foreach($opportunityTypeRows as $row)
                        <div class="impact-bar">
                            <div class="impact-bar__meta">
                                <span>{{ $row['icon'] }} {{ $row['label'] }}</span>
                                <span class="impact-bar__value">{{ $formatNumber($row['count']) }} · {{ $row['percent'] }}%</span>
                            </div>
                            <div class="impact-track">
                                <div class="impact-fill {{ $row['class'] }}" style="width: {{ $row['percent'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <h3 class="impact-copy" style="margin-top: 26px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em;">
                Публикации за 6 месяцев
            </h3>
            <div class="impact-mini-chart">
                @foreach($opportunityChart as $month)
                    @php $height = max(5, (int) round($month['opportunities'] / $maxOpportunityChart * 96)); @endphp
                    <div class="impact-chart__item">
                        <div class="impact-chart__bar" style="height: {{ $height }}px" title="{{ $month['label'] }}: {{ $month['opportunities'] }} публикаций"></div>
                        <div class="impact-chart__label">{{ $month['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="impact-panel">
        <div class="impact-section-head">
            <div>
                <h2>Качество базы и готовность к масштабированию</h2>
                <p>Эти показатели показывают не просто размер сообщества, а пригодность данных для matchmaking, программ поддержки и отчётности.</p>
            </div>
        </div>

        <div class="impact-quality-grid">
            @foreach($qualityRows as $row)
                <article class="impact-quality">
                    <div class="impact-quality__head">
                        <div>
                            <h3>{{ $row['label'] }}</h3>
                            <p>{{ $row['caption'] }}</p>
                        </div>
                        <div class="impact-quality__value">
                            <strong>{{ $row['percent'] }}%</strong>
                            <span>{{ $formatNumber($row['value']) }} / {{ $formatNumber($row['total']) }}</span>
                        </div>
                    </div>
                    <div class="impact-track">
                        <div class="impact-fill {{ $row['class'] }}" style="width: {{ $row['percent'] }}%"></div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="impact-facts">
            <div class="impact-fact">
                <strong>{{ $formatNumber($withBusinessDescription) }}</strong>
                <span>описали бизнес и экспертизу</span>
            </div>
            <div class="impact-fact is-amber">
                <strong>{{ $formatNumber($withExpectations) }}</strong>
                <span>сформулировали запрос к платформе</span>
            </div>
            <div class="impact-fact is-cyan">
                <strong>{{ $formatNumber($withUsername) }}</strong>
                <span>имеют Telegram username для связи</span>
            </div>
            <div class="impact-fact is-violet">
                <strong>{{ $formatNumber($withAvatar) }}</strong>
                <span>добавили аватар для доверия в сети</span>
            </div>
        </div>
    </section>

    <section class="impact-list-grid">
        <article class="impact-panel">
            <div class="impact-section-head">
                <div>
                    <h2>Последние одобренные участницы</h2>
                    <p>Срез помогает быстро показать живое наполнение платформы.</p>
                </div>
            </div>

            @if($latestMembers->isEmpty())
                <div class="impact-empty">Одобренных профилей пока нет.</div>
            @else
                <div class="impact-bars">
                    @foreach($latestMembers as $member)
                        <article class="impact-person">
                            <div class="impact-avatar">{{ mb_strtoupper(mb_substr($member->full_name ?? '?', 0, 1)) }}</div>
                            <div class="impact-person__body">
                                <div class="impact-tag-row">
                                    <h3>{{ $member->full_name ?? 'Без имени' }}</h3>
                                    @if($member->telegram_username)
                                        <span class="impact-tag">@{{ $member->telegram_username }}</span>
                                    @endif
                                </div>
                                <p>{{ $member->description ? \Illuminate\Support\Str::limit($member->description, 120) : 'Описание пока не заполнено.' }}</p>
                            </div>
                            <span class="impact-date">{{ $member->approved_at?->format('d.m.Y') }}</span>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="impact-panel">
            <div class="impact-section-head">
                <div>
                    <h2>Последние возможности</h2>
                    <p>Показывает, как участницы используют платформу для практических связей.</p>
                </div>
            </div>

            @if($latestOpportunities->isEmpty())
                <div class="impact-empty">Публикаций пока нет.</div>
            @else
                <div class="impact-bars">
                    @foreach($latestOpportunities as $opportunity)
                        @php $type = $typeMeta[$opportunity->type] ?? ['label' => $opportunity->type, 'icon' => '📌']; @endphp
                        <article class="impact-opportunity">
                            <div class="impact-opportunity__body">
                                <div class="impact-tag-row">
                                    <span class="impact-tag">{{ $type['icon'] }} {{ $type['label'] }}</span>
                                    <span class="impact-date">{{ $opportunity->created_at?->format('d.m.Y') }}</span>
                                </div>
                                <h3>{{ $opportunity->title }}</h3>
                                <p>Автор: {{ $opportunity->author?->full_name ?? 'участница платформы' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </section>

    <section class="impact-report">
        <div class="impact-report__head">
            <div>
                <h2>Сводка для отчёта</h2>
                <p>Все значения рассчитываются из текущей базы данных платформы.</p>
            </div>
            <span class="impact-report__ready">готово к печати</span>
        </div>

        <div class="impact-report-grid">
            @foreach([
                ['Всего заявок на участие', $formatNumber($totalApplications)],
                ['Одобренных участниц', $formatNumber($approvedCount)],
                ['Заявок на модерации', $formatNumber($pendingCount)],
                ['Полных бизнес-профилей', $formatNumber($completeProfiles) . ' (' . $profileCompletionRate . '%)'],
                ['AI-готовых профилей', $formatNumber($withEmbedding) . ' (' . $aiReadinessRate . '%)'],
                ['Активных пользователей кабинета', $formatNumber($activeCabinetUsers) . ' (' . $cabinetActivationRate . '%)'],
                ['Опубликованных возможностей', $formatNumber($opportunitiesTotal)],
                ['Публикаций за 30 дней', $formatNumber($opportunitiesLast30)],
                ['Запрошенных входов в кабинет', $formatNumber($loginTokensIssued) . ' всего / ' . $formatNumber($loginTokensIssuedLast30) . ' за 30 дней'],
                ['Успешных входов в кабинет', $formatNumber($loginTokensUsed)],
            ] as [$label, $value])
                <article class="impact-report-item">
                    <span>{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </article>
            @endforeach
        </div>
    </section>
</div>
</x-filament-panels::page>
