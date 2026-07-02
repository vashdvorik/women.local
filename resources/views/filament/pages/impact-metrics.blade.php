<x-filament-panels::page>
@php
    $formatNumber = fn (int|float $value): string => number_format((float) $value, 0, ',', ' ');
    $safePercent = fn (int|float $value): int => max(0, min(100, (int) round($value)));

    $statusRows = [
        ['label' => 'Одобренные участницы', 'value' => $approvedCount, 'percent' => $approvalRate, 'class' => 'is-emerald'],
        ['label' => 'Ожидают рассмотрения', 'value' => $pendingCount, 'percent' => $totalApplications > 0 ? round($pendingCount / $totalApplications * 100) : 0, 'class' => 'is-amber'],
        ['label' => 'Отклонённые заявки', 'value' => $rejectedCount, 'percent' => $totalApplications > 0 ? round($rejectedCount / $totalApplications * 100) : 0, 'class' => 'is-muted'],
    ];

    $qualityRows = [
        ['label' => 'Полные бизнес-профили', 'caption' => 'Есть описание бизнеса и запрос к сообществу', 'value' => $completeProfiles, 'total' => $approvedCount, 'percent' => $profileCompletionRate, 'class' => 'is-granat'],
        ['label' => 'AI-готовые профили', 'caption' => 'Сформированы embeddings для рекомендаций и поиска', 'value' => $withEmbedding, 'total' => $approvedCount, 'percent' => $aiReadinessRate, 'class' => 'is-copper'],
        ['label' => 'Активировали кабинет', 'caption' => 'Есть хотя бы один успешный вход по Telegram-токену', 'value' => $activeCabinetUsers, 'total' => $approvedCount, 'percent' => $cabinetActivationRate, 'class' => 'is-amber'],
        ['label' => 'Публиковали возможности', 'caption' => 'Создали запрос, партнёрство или событие', 'value' => $opportunityAuthors, 'total' => $approvedCount, 'percent' => $publicationActivationRate, 'class' => 'is-emerald'],
    ];

    $typeMeta = [
        'project' => ['label' => 'Запросы', 'icon' => '💼', 'color' => '#9f2534', 'class' => 'is-granat'],
        'meeting' => ['label' => 'Партнёрства', 'icon' => '🤝', 'color' => '#d66a3f', 'class' => 'is-copper'],
        'event' => ['label' => 'События', 'icon' => '📅', 'color' => '#d7a348', 'class' => 'is-amber'],
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
        : 'conic-gradient(#eadfd4 0deg 360deg)';

    $maxMemberChart = max(
        1,
        (int) collect($registrationChart)->max('applications'),
        (int) collect($registrationChart)->max('approved')
    );

    $maxOpportunityChart = max(1, (int) collect($opportunityChart)->max('opportunities'));

    $headlineCards = [
        ['label' => 'Одобренных участниц', 'value' => $approvedCount, 'note' => $approvedLast30 . ' за 30 дней', 'class' => 'is-granat', 'icon' => '♀'],
        ['label' => 'Заявок всего', 'value' => $totalApplications, 'note' => $newApplicationsLast30 . ' новых за 30 дней', 'class' => 'is-copper', 'icon' => '↗'],
        ['label' => 'Возможностей', 'value' => $opportunitiesTotal, 'note' => $opportunitiesLast30 . ' опубликовано за 30 дней', 'class' => 'is-amber', 'icon' => '✦'],
        ['label' => 'Индекс готовности', 'value' => $platformReadiness . '%', 'note' => 'профили, AI, кабинет, активность', 'class' => 'is-emerald', 'icon' => '✓'],
    ];

    $reportItems = [
        ['label' => 'Одобрение заявок', 'value' => $approvalRate . '%'],
        ['label' => 'Заполненность профилей', 'value' => $profileCompletionRate . '%'],
        ['label' => 'AI-готовность', 'value' => $aiReadinessRate . '%'],
        ['label' => 'Активность кабинета', 'value' => $cabinetActivationRate . '%'],
        ['label' => 'Авторы публикаций', 'value' => $formatNumber($opportunityAuthors)],
        ['label' => 'Сформировано', 'value' => $generatedAt],
    ];
@endphp

<style>
    .impact-page {
        --granat-950: #2f0d14;
        --granat-900: #49151f;
        --granat-800: #6f1d2c;
        --granat-700: #952838;
        --granat-600: #b73a45;
        --copper: #d66a3f;
        --amber: #d7a348;
        --sand: #f7eadb;
        --cream: #fffaf3;
        --line: #ead8ca;
        --text: #24161a;
        --muted: #7e6a61;
        --emerald: #1f8a68;
        display: grid;
        gap: 24px;
        color: var(--text);
    }

    .impact-page * {
        box-sizing: border-box;
    }

    .impact-hero,
    .impact-panel,
    .impact-report {
        overflow: hidden;
        border: 1px solid rgba(234, 216, 202, 0.95);
        border-radius: 30px;
        background: linear-gradient(180deg, #fffdf8 0%, #fff7ee 100%);
        box-shadow: 0 22px 55px rgba(73, 21, 31, 0.12);
    }

    .impact-hero__top {
        position: relative;
        padding: 40px;
        color: #fff8ef;
        background:
            radial-gradient(circle at 12% 18%, rgba(255, 244, 219, 0.22), transparent 24%),
            radial-gradient(circle at 88% 0%, rgba(214, 106, 63, 0.35), transparent 30%),
            radial-gradient(circle at 88% 88%, rgba(255, 255, 255, 0.12), transparent 22%),
            linear-gradient(135deg, var(--granat-950) 0%, var(--granat-800) 48%, var(--copper) 100%);
    }

    .impact-hero__top::after {
        position: absolute;
        inset: auto -80px -170px auto;
        width: 360px;
        height: 360px;
        border: 1px solid rgba(255, 250, 243, 0.18);
        border-radius: 999px;
        background: rgba(255, 250, 243, 0.08);
        content: "";
    }

    .impact-hero__content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 30px;
    }

    .impact-eyebrow {
        display: inline-flex;
        margin: 0 0 16px;
        padding: 9px 13px;
        border: 1px solid rgba(255, 250, 243, 0.18);
        border-radius: 999px;
        background: rgba(255, 250, 243, 0.1);
        color: #f7d7bd;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .impact-title {
        max-width: 900px;
        margin: 0;
        font-size: clamp(34px, 5vw, 58px);
        font-weight: 950;
        line-height: 0.98;
        letter-spacing: -0.05em;
    }

    .impact-lead {
        max-width: 760px;
        margin: 18px 0 0;
        color: #f6ddcd;
        font-size: 17px;
        line-height: 1.65;
    }

    .impact-stamp {
        min-width: 245px;
        padding: 18px;
        border: 1px solid rgba(255, 250, 243, 0.22);
        border-radius: 24px;
        background: rgba(255, 250, 243, 0.12);
        backdrop-filter: blur(18px);
    }

    .impact-stamp span {
        display: block;
        color: #f6ddcd;
        font-size: 13px;
        font-weight: 750;
    }

    .impact-stamp strong {
        display: block;
        margin-top: 5px;
        font-size: 24px;
        font-weight: 950;
    }

    .impact-print-button {
        width: 100%;
        margin-top: 15px;
        padding: 12px 15px;
        border: 0;
        border-radius: 16px;
        background: var(--cream);
        color: var(--granat-800);
        cursor: pointer;
        font-weight: 900;
        box-shadow: 0 14px 30px rgba(47, 13, 20, 0.24);
    }

    .impact-kpis,
    .impact-quality-grid,
    .impact-facts,
    .impact-report-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .impact-kpis {
        padding: 22px;
        background:
            radial-gradient(circle at 0% 100%, rgba(183, 58, 69, 0.08), transparent 26%),
            var(--cream);
    }

    .impact-kpi,
    .impact-quality,
    .impact-person,
    .impact-opportunity,
    .impact-report-item {
        border: 1px solid rgba(234, 216, 202, 0.9);
        background: rgba(255, 253, 248, 0.84);
        box-shadow: 0 14px 28px rgba(73, 21, 31, 0.06);
    }

    .impact-kpi {
        padding: 22px;
        border-radius: 24px;
    }

    .impact-kpi__head,
    .impact-quality__head,
    .impact-section-head,
    .impact-report__head {
        display: flex;
        justify-content: space-between;
        gap: 18px;
    }

    .impact-kpi__head {
        align-items: center;
        margin-bottom: 22px;
    }

    .impact-kpi__label {
        color: var(--muted);
        font-size: 14px;
        font-weight: 800;
    }

    .impact-icon {
        display: inline-flex;
        width: 46px;
        height: 46px;
        align-items: center;
        justify-content: center;
        border-radius: 17px;
        color: #fff;
        font-size: 20px;
        font-weight: 950;
        box-shadow: 0 14px 26px rgba(73, 21, 31, 0.18);
    }

    .impact-icon.is-granat,
    .impact-fill.is-granat {
        background: linear-gradient(135deg, var(--granat-900), var(--granat-600));
    }

    .impact-icon.is-copper,
    .impact-fill.is-copper {
        background: linear-gradient(135deg, var(--granat-700), var(--copper));
    }

    .impact-icon.is-amber,
    .impact-fill.is-amber {
        background: linear-gradient(135deg, var(--copper), var(--amber));
    }

    .impact-icon.is-emerald,
    .impact-fill.is-emerald {
        background: linear-gradient(135deg, #185c4b, var(--emerald));
    }

    .impact-fill.is-muted {
        background: linear-gradient(135deg, #9a7a70, #c3a391);
    }

    .impact-kpi__value {
        font-size: 42px;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -0.04em;
    }

    .impact-kpi__note,
    .impact-copy,
    .impact-section-head p,
    .impact-report__head p {
        color: var(--muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .impact-kpi__note {
        margin-top: 10px;
    }

    .impact-grid {
        display: grid;
        gap: 24px;
    }

    .impact-grid.is-two {
        grid-template-columns: minmax(0, 1.08fr) minmax(360px, 0.92fr);
    }

    .impact-grid.is-even,
    .impact-list-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .impact-panel,
    .impact-report {
        padding: 28px;
    }

    .impact-panel.is-dark {
        border-color: rgba(73, 21, 31, 0.9);
        color: #fff8ef;
        background:
            radial-gradient(circle at 100% 0%, rgba(214, 106, 63, 0.32), transparent 30%),
            linear-gradient(135deg, var(--granat-950) 0%, var(--granat-800) 100%);
    }

    .impact-panel.is-dark .impact-copy,
    .impact-panel.is-dark .impact-section-head p {
        color: #f1d7c6;
    }

    .impact-section-head,
    .impact-report__head {
        margin-bottom: 24px;
    }

    .impact-section-head h2,
    .impact-report__head h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -0.025em;
    }

    .impact-section-head p,
    .impact-report__head p,
    .impact-copy {
        margin: 7px 0 0;
    }

    .impact-badge {
        display: inline-flex;
        align-items: center;
        align-self: flex-start;
        padding: 12px 16px;
        border-radius: 18px;
        background: #f7eadb;
        color: var(--granat-800);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .impact-badge strong {
        margin-left: 8px;
        font-size: 24px;
        line-height: 1;
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
        color: #4b3435;
        font-size: 14px;
        font-weight: 800;
    }

    .impact-bar__value {
        color: var(--granat-900);
        font-weight: 950;
        white-space: nowrap;
    }

    .impact-track {
        height: 14px;
        overflow: hidden;
        border-radius: 999px;
        background: #eadfd4;
    }

    .impact-fill {
        height: 100%;
        min-width: 3px;
        border-radius: inherit;
    }

    .impact-donor-grid,
    .impact-list-grid {
        display: grid;
        gap: 16px;
    }

    .impact-donor-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 24px;
    }

    .impact-donor-item {
        padding: 18px;
        border: 1px solid rgba(255, 250, 243, 0.13);
        border-radius: 22px;
        background: rgba(255, 250, 243, 0.1);
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
        color: #f1d7c6;
        font-size: 12px;
        line-height: 1.45;
    }

    .impact-note {
        margin-top: 22px;
        padding: 18px;
        border: 1px solid rgba(255, 250, 243, 0.14);
        border-radius: 22px;
        background: rgba(255, 250, 243, 0.1);
        color: #f6ddcd;
        font-size: 14px;
        line-height: 1.65;
    }

    .impact-chart,
    .impact-mini-chart {
        display: flex;
        align-items: flex-end;
    }

    .impact-chart {
        height: 290px;
        gap: 9px;
        padding-top: 16px;
    }

    .impact-mini-chart {
        height: 145px;
        gap: 12px;
        margin-top: 18px;
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
        gap: 4px;
    }

    .impact-chart__bar {
        width: 46%;
        min-height: 5px;
        border-radius: 12px 12px 0 0;
        background: linear-gradient(180deg, #dc8c5e 0%, var(--granat-700) 100%);
    }

    .impact-chart__bar.is-approved {
        background: linear-gradient(180deg, #6bc5a0 0%, var(--emerald) 100%);
    }

    .impact-mini-chart .impact-chart__bar {
        width: 100%;
        background: linear-gradient(180deg, #e1ad69 0%, var(--copper) 100%);
    }

    .impact-chart__label {
        width: 100%;
        overflow: hidden;
        color: #9b867a;
        font-size: 10px;
        font-weight: 800;
        text-align: center;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .impact-legend {
        display: flex;
        gap: 18px;
        margin-top: 16px;
        color: var(--muted);
        font-size: 12px;
        font-weight: 800;
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
        background: var(--granat-700);
    }

    .impact-dot.is-emerald {
        background: var(--emerald);
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
        box-shadow: inset 0 0 26px rgba(73, 21, 31, 0.18);
    }

    .impact-donut__inner {
        display: flex;
        width: 128px;
        height: 128px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--cream);
        box-shadow: 0 18px 34px rgba(73, 21, 31, 0.18);
        text-align: center;
    }

    .impact-donut__inner strong {
        font-size: 38px;
        line-height: 1;
        font-weight: 950;
    }

    .impact-donut__inner span,
    .impact-report-item span {
        color: #9b867a;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .impact-donut__inner span {
        margin-top: 4px;
    }

    .impact-quality {
        padding: 20px;
        border-radius: 24px;
    }

    .impact-quality h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 950;
    }

    .impact-quality p {
        margin: 5px 0 0;
        color: var(--muted);
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
        color: var(--muted);
        font-size: 11px;
        white-space: nowrap;
    }

    .impact-facts {
        margin-top: 16px;
    }

    .impact-fact {
        padding: 17px;
        border-radius: 20px;
        background: #f8eadc;
        color: var(--granat-800);
    }

    .impact-fact.is-amber {
        background: #fff3d5;
        color: #8d5c10;
    }

    .impact-fact.is-emerald {
        background: #e6f5ef;
        color: #166548;
    }

    .impact-fact.is-copper {
        background: #fbe4d5;
        color: #944220;
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
        border-radius: 22px;
    }

    .impact-avatar {
        display: flex;
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 17px;
        background: linear-gradient(135deg, var(--granat-900), var(--copper));
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
        font-weight: 950;
    }

    .impact-person p,
    .impact-opportunity p {
        display: -webkit-box;
        overflow: hidden;
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.55;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
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
        background: #f8eadc;
        color: var(--granat-800);
        font-size: 12px;
        font-weight: 900;
    }

    .impact-date {
        color: #9b867a;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .impact-empty {
        padding: 22px;
        border: 1px dashed #cbb6a8;
        border-radius: 22px;
        background: rgba(255, 250, 243, 0.68);
        color: var(--muted);
        font-size: 14px;
    }

    .impact-report {
        background:
            radial-gradient(circle at 0% 0%, rgba(183, 58, 69, 0.11), transparent 28%),
            radial-gradient(circle at 100% 100%, rgba(214, 106, 63, 0.14), transparent 26%),
            var(--cream);
    }

    .impact-report__head {
        align-items: center;
    }

    .impact-report__ready {
        padding: 10px 14px;
        border-radius: 999px;
        background: var(--granat-800);
        color: #fff8ef;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .impact-report-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .impact-report-item {
        padding: 16px;
        border-radius: 20px;
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
                            <div class="impact-fill {{ $row['class'] }}" style="width: {{ $safePercent($row['percent']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="impact-panel is-dark">
            <div class="impact-section-head">
                <div>
                    <h2>Что важно для доноров</h2>
                    <p>Короткая интерпретация данных без технических деталей.</p>
                </div>
            </div>

            <div class="impact-donor-grid">
                <div class="impact-donor-item">
                    <strong>{{ $formatNumber($approvedCount) }}</strong>
                    <span>одобренных участниц уже представлены в экосистеме</span>
                </div>
                <div class="impact-donor-item">
                    <strong>{{ $profileCompletionRate }}%</strong>
                    <span>участниц имеют достаточно данных для видимости и нетворкинга</span>
                </div>
                <div class="impact-donor-item">
                    <strong>{{ $aiReadinessRate }}%</strong>
                    <span>профилей готовы к AI-поиску и рекомендациям</span>
                </div>
                <div class="impact-donor-item">
                    <strong>{{ $opportunitiesLast30 }}</strong>
                    <span>новых возможностей опубликовано за последние 30 дней</span>
                </div>
            </div>

            <p class="impact-note">
                Платформа уже измеряет не только регистрацию, но и качество профилей, цифровую активность, публикации возможностей и готовность к масштабируемому сопровождению участниц.
            </p>
        </article>
    </section>

    <section class="impact-grid is-even">
        <article class="impact-panel">
            <div class="impact-section-head">
                <div>
                    <h2>Динамика участниц</h2>
                    <p>Заявки и одобрения по месяцам.</p>
                </div>
            </div>

            <div class="impact-chart">
                @foreach($registrationChart as $month)
                    @php
                        $applicationHeight = max(5, (int) round($month['applications'] / $maxMemberChart * 235));
                        $approvedHeight = max(5, (int) round($month['approved'] / $maxMemberChart * 235));
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
                <span><i class="impact-dot is-emerald"></i> Одобрено</span>
            </div>
        </article>

        <article class="impact-panel">
            <div class="impact-section-head">
                <div>
                    <h2>Возможности и партнёрства</h2>
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
                                <div class="impact-fill {{ $row['class'] }}" style="width: {{ $safePercent($row['percent']) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <h3 class="impact-copy" style="margin-top: 26px; font-weight: 950; text-transform: uppercase; letter-spacing: 0.08em;">
                Публикации за 6 месяцев
            </h3>
            <div class="impact-mini-chart">
                @foreach($opportunityChart as $month)
                    @php($height = max(5, (int) round($month['opportunities'] / $maxOpportunityChart * 130)))
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
                <h2>Качество цифровой базы</h2>
                <p>Метрики, которые показывают готовность платформы к полезным рекомендациям, поиску и donor reporting.</p>
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
                        <div class="impact-fill {{ $row['class'] }}" style="width: {{ $safePercent($row['percent']) }}%"></div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="impact-facts">
            <div class="impact-fact">
                <strong>{{ $formatNumber($withBusinessDescription) }}</strong>
                <span>описали бизнес, проект или экспертизу</span>
            </div>
            <div class="impact-fact is-amber">
                <strong>{{ $formatNumber($withExpectations) }}</strong>
                <span>указали запросы и форматы сотрудничества</span>
            </div>
            <div class="impact-fact is-emerald">
                <strong>{{ $formatNumber($withUsername) }}</strong>
                <span>имеют Telegram-контакт для связи</span>
            </div>
            <div class="impact-fact is-copper">
                <strong>{{ $formatNumber($withAvatar) }}</strong>
                <span>добавили визуальный профиль</span>
            </div>
        </div>
    </section>

    <section class="impact-list-grid">
        <article class="impact-panel">
            <div class="impact-section-head">
                <div>
                    <h2>Новые одобренные участницы</h2>
                    <p>Последние профили, добавленные в сообщество.</p>
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
                                    <h3>{{ $member->full_name ?: 'Без имени' }}</h3>
                                    @if($member->telegram_username)
                                        <span class="impact-tag">@{{ $member->telegram_username }}</span>
                                    @endif
                                </div>
                                <p>{{ $member->description ?: 'Описание профиля пока не заполнено.' }}</p>
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
                    <p>Свежие публикации, которые показывают практическую активность сообщества.</p>
                </div>
            </div>

            @if($latestOpportunities->isEmpty())
                <div class="impact-empty">Публикаций пока нет.</div>
            @else
                <div class="impact-bars">
                    @foreach($latestOpportunities as $opportunity)
                        @php($type = $typeMeta[$opportunity->type] ?? ['label' => $opportunity->type, 'icon' => '📌'])
                        <article class="impact-opportunity">
                            <div class="impact-opportunity__body">
                                <div class="impact-tag-row">
                                    <span class="impact-tag">{{ $type['icon'] }} {{ $type['label'] }}</span>
                                    <span class="impact-date">{{ $opportunity->created_at?->format('d.m.Y') }}</span>
                                </div>
                                <h3>{{ $opportunity->title }}</h3>
                                <p>{{ $opportunity->author?->full_name ? 'Опубликовала: ' . $opportunity->author->full_name : 'Автор не указан' }}</p>
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
                <h2>Краткий donor snapshot</h2>
                <p>Блок можно использовать для быстрой демонстрации прогресса платформы.</p>
            </div>
            <span class="impact-report__ready">готово к печати</span>
        </div>

        <div class="impact-report-grid">
            @foreach($reportItems as $item)
                <article class="impact-report-item">
                    <span>{{ $item['label'] }}</span>
                    <strong>{{ $item['value'] }}</strong>
                </article>
            @endforeach
        </div>
    </section>
</div>
</x-filament-panels::page>
