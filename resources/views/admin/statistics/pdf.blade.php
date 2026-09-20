<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Отчёт платформы — {{ $generatedAt }}</title>
    <style>
        /* dompdf only: no CSS grid/flexbox/gradients, only floats/inline-block/tables.
           Prata/Manrope aren't embedded for PDF, so DejaVu Serif/Sans stand in for them —
           they're dompdf's built-in fonts with full Cyrillic coverage, unlike the base-14
           PDF fonts (Georgia/Arial) the web view's fallback stack points to. */
        @page { margin: 30px 34px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #34231F; font-family: "DejaVu Sans", sans-serif; font-size: 10.5px; line-height: 1.55; }
        h1, h2, p { margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        .serif { font-family: "DejaVu Serif", serif; }

        .eyebrow { margin: 0 0 10px; color: #74472F; font-size: 8px; font-weight: bold; letter-spacing: 1.4px; text-transform: uppercase; }
        .eyebrow.on-dark { color: #C99A73; }

        .hero { padding: 26px 30px; border-radius: 8px; background: #21140F; color: #FFF9F5; }
        .hero h1 { font-size: 26px; font-weight: normal; line-height: 1.15; }
        .hero p { max-width: 320px; margin-top: 10px; color: #DCC8BC; font-size: 9.5px; }
        .hero .stat { font-size: 46px; line-height: 1; }
        .hero .stat-label { margin-top: 6px; color: #DCC8BC; font-size: 8px; max-width: 200px; }
        .hero .meta { margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,249,245,.16); color: #DCC8BC; font-size: 7.5px; }

        .section-title { margin: 22px 0 4px; color: #21140F; font-size: 17px; font-weight: normal; }
        .section-title.compact { font-size: 13px; font-family: "DejaVu Sans", sans-serif; font-weight: bold; }
        .section-note { margin: 0 0 12px; color: #756158; font-size: 9px; max-width: 380px; }

        .lead-stat { font-size: 46px; line-height: 1; color: #21140F; }
        .lead-caption { max-width: 260px; margin-top: 8px; color: #756158; font-size: 9px; }

        .row { padding-top: 10px; border-top: 1px solid #E9DCD5; margin-bottom: 10px; }
        .row .top { font-size: 9.5px; }
        .row .top .val { float: right; color: #756158; font-weight: bold; }
        .row .label { font-weight: bold; color: #34231F; }
        .row .cap { margin: 3px 0 6px; color: #9C887F; font-size: 8px; }
        .track { height: 4px; border-radius: 4px; background: #F2E8E3; }
        .fill { height: 4px; border-radius: 4px; background: #21140F; }
        .fill.success { background: #526B5A; }
        .fill.warning { background: #9B6A3A; }
        .fill.error { background: #8C4842; }
        .fill.caramel { background: #B9855B; }

        .chart-table td { text-align: center; vertical-align: bottom; padding: 0 1px; }
        .chart-bar { margin: 0 auto; width: 55%; border-radius: 2px 2px 0 0; background: #F1E3DA; }
        .chart-bar.approved { background: #B9855B; }
        .chart-label { margin-top: 3px; color: #9C887F; font-size: 6px; font-weight: bold; }
        .legend { margin-top: 8px; color: #756158; font-size: 8px; }

        .avatar { display: inline-block; width: 22px; height: 22px; border-radius: 11px; background: #F1E3DA; color: #74472F; font-weight: bold; font-size: 9px; text-align: center; line-height: 22px; }
        .list-item { padding: 8px 0; border-top: 1px solid #E9DCD5; }
        .list-item .name { font-size: 10px; font-weight: bold; color: #21140F; }
        .list-item .date { float: right; color: #9C887F; font-size: 8px; font-weight: bold; }
        .list-item .tag { color: #74472F; font-size: 8px; font-weight: bold; }
        .list-item p { margin-top: 3px; color: #756158; font-size: 8.5px; }
        .empty-note { padding-top: 10px; border-top: 1px solid #E9DCD5; color: #9C887F; font-size: 9px; }

        .footer-note { margin-top: 20px; padding-top: 10px; border-top: 1px solid #E9DCD5; color: #9C887F; font-size: 7.5px; }
    </style>
</head>
<body>
    <div class="hero">
        <table><tr>
            <td style="width:60%; vertical-align:top;">
                <p class="eyebrow on-dark">Отчёт платформы</p>
                <h1 class="serif">Платформа женщин предпринимателей</h1>
                <p>Живой срез по составу сообщества, готовности профилей и активности участниц.</p>
            </td>
            <td style="width:40%; vertical-align:top; text-align:right;">
                <div class="stat serif">{{ $platformReadiness }}%</div>
                <div class="stat-label" style="margin-left:auto;">Индекс готовности сообщества</div>
                <div class="meta">Сформировано {{ $generatedAt }}</div>
            </td>
        </tr></table>
    </div>

    <table><tr>
        <td style="width:54%; vertical-align:top; padding-right:20px;">
            <p class="eyebrow">Заявки на участие</p>
            <div class="lead-stat serif">{{ $formatNumber($approvedCount) }}</div>
            <p class="lead-caption">одобренных участниц из {{ $formatNumber($totalApplications) }} заявок всего — {{ $approvalRate }}% принятых. За 30 дней: {{ $formatNumber($newApplicationsLast30) }} новых, {{ $formatNumber($approvedLast30) }} одобрено.</p>
        </td>
        <td style="width:46%; vertical-align:top;">
            @foreach($statusRows as $row)
                <div class="row">
                    <div class="top"><span class="val">{{ $formatNumber($row['value']) }} · {{ $row['percent'] }}%</span><span class="label">{{ $row['label'] }}</span></div>
                    <div class="track" style="margin-top:5px;"><div class="fill {{ $row['tone'] }}" style="width: {{ $safePercent($row['percent']) }}%;"></div></div>
                </div>
            @endforeach
        </td>
    </tr></table>

    <div class="section-title serif">Готовность базы участниц</div>
    <p class="section-note">Насколько профили одобренных участниц готовы приносить пользу сообществу.</p>
    <table><tr>
        @foreach(array_chunk($qualityRows, (int) ceil(count($qualityRows) / 2)) as $column)
            <td style="width:50%; vertical-align:top; padding-right: {{ $loop->first ? 20 : 0 }}px;">
                @foreach($column as $row)
                    <div class="row">
                        <div class="top"><span class="val">{{ $formatNumber($row['value']) }}/{{ $formatNumber($row['total']) }} · {{ $row['percent'] }}%</span><span class="label">{{ $row['label'] }}</span></div>
                        <p class="cap">{{ $row['caption'] }}</p>
                        <div class="track"><div class="fill" style="width: {{ $safePercent($row['percent']) }}%;"></div></div>
                    </div>
                @endforeach
            </td>
        @endforeach
    </tr></table>

    <div class="section-title serif">Динамика сообщества</div>
    <p class="section-note">Заявки и одобрения по месяцам за последний год.</p>
    <table class="chart-table"><tr>
        @php($maxM = $maxMemberChart)
        @foreach($registrationChart as $month)
            <td style="width: {{ 100 / max(1, count($registrationChart)) }}%;">
                <table style="width:100%;"><tr>
                    <td style="width:50%; height:60px; vertical-align:bottom;"><div class="chart-bar" style="height:{{ $month['applications'] > 0 ? max(3, (int) round($month['applications'] / $maxM * 55)) : 0 }}px;"></div></td>
                    <td style="width:50%; height:60px; vertical-align:bottom;"><div class="chart-bar approved" style="height:{{ $month['approved'] > 0 ? max(3, (int) round($month['approved'] / $maxM * 55)) : 0 }}px;"></div></td>
                </tr></table>
                <div class="chart-label">{{ $month['label'] }}</div>
            </td>
        @endforeach
    </tr></table>
    <p class="legend"><span style="color:#DBB89B;">■</span> Заявки &nbsp;&nbsp; <span style="color:#B9855B;">■</span> Одобрено</p>

    <table><tr>
        <td style="width:32%; vertical-align:top; padding-right:20px;">
            <p class="eyebrow">Возможности</p>
            <div class="lead-stat serif" style="font-size:34px;">{{ $formatNumber($opportunitiesTotal) }}</div>
            <p class="lead-caption">публикаций от {{ $formatNumber($opportunityAuthors) }} участниц, {{ $formatNumber($opportunitiesLast30) }} за 30 дней</p>
        </td>
        <td style="width:68%; vertical-align:top;">
            <div class="section-title compact" style="margin-top:0;">Из чего складываются публикации</div>
            @if($opportunitiesTotal === 0)
                <p class="empty-note">Пока ни одна участница не опубликовала запрос, партнёрство или событие.</p>
            @else
                @foreach($opportunityTypeRows as $row)
                    <div class="row">
                        <div class="top"><span class="val">{{ $formatNumber($row['count']) }} · {{ $row['percent'] }}%</span><span class="label">{{ $row['label'] }}</span></div>
                        <div class="track" style="margin-top:5px;"><div class="fill caramel" style="width: {{ $safePercent($row['percent']) }}%;"></div></div>
                    </div>
                @endforeach
            @endif
        </td>
    </tr></table>

    <table><tr>
        <td style="width:50%; vertical-align:top; padding-right:16px;">
            <div class="section-title compact">Новые одобренные участницы</div>
            @if($latestMembers->isEmpty())
                <p class="empty-note">Одобренных профилей пока нет.</p>
            @else
                @foreach($latestMembers as $member)
                    <div class="list-item">
                        <span class="date">{{ $member->approved_at?->format('d.m.Y') }}</span>
                        <span class="name">{{ $member->full_name ?: 'Без имени' }}</span>
                        <p>{{ \Illuminate\Support\Str::limit($member->description ?: 'Описание профиля пока не заполнено.', 110) }}</p>
                    </div>
                @endforeach
            @endif
        </td>
        <td style="width:50%; vertical-align:top;">
            <div class="section-title compact">Последние публикации</div>
            @if($latestOpportunities->isEmpty())
                <p class="empty-note">Публикаций пока нет.</p>
            @else
                @foreach($latestOpportunities as $opportunity)
                    @php($type = $typeMeta[$opportunity->type] ?? ['label' => $opportunity->type])
                    <div class="list-item">
                        <span class="date">{{ $opportunity->created_at?->format('d.m.Y') }}</span>
                        <span class="name">{{ $opportunity->title }}</span>
                        <p><span class="tag">{{ $type['label'] }}</span> · {{ $opportunity->author?->full_name ? 'Опубликовала: ' . $opportunity->author->full_name : 'Автор не указан' }}</p>
                    </div>
                @endforeach
            @endif
        </td>
    </tr></table>

    <p class="footer-note">Сформировано автоматически из текущих данных платформы · {{ $generatedAt }}</p>
</body>
</html>
