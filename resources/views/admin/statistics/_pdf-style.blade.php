{{-- Общие стили PDF-отчётов «Активность» и «Рассылки». Только то, что понимает dompdf: таблицы, float, inline-block; без flex и grid.
     Шрифты DejaVu: у них есть кириллица (базовые шрифты PDF её не имеют). --}}
<style>
    @page { margin: 30px 34px; }
    * { box-sizing: border-box; }
    body { margin: 0; color: #34231F; font-family: "DejaVu Sans", sans-serif; font-size: 10px; line-height: 1.5; }
    h1, h2, p { margin: 0; }
    table { border-collapse: collapse; width: 100%; }
    .serif { font-family: "DejaVu Serif", serif; }

    .eyebrow { margin: 0 0 8px; color: #74472F; font-size: 8px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
    .eyebrow.on-dark { color: #C99A73; }

    .hero { padding: 22px 26px; border-radius: 8px; background: #21140F; color: #FFF9F5; }
    .hero h1 { font-size: 22px; font-weight: normal; line-height: 1.2; }
    .hero p { margin-top: 8px; color: #DCC8BC; font-size: 9px; }

    .section-title { margin: 20px 0 3px; color: #21140F; font-size: 14px; font-weight: bold; }
    .section-note { margin: 0 0 10px; color: #756158; font-size: 8.5px; }

    .kpi td { width: 25%; padding: 0 6px 0 0; vertical-align: top; }
    .kpi-box { padding: 10px 12px; border: 1px solid #E9DCD5; border-radius: 6px; background: #FFFBF8; }
    .kpi-label { color: #8C776E; font-size: 7px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; }
    .kpi-value { margin-top: 4px; color: #21140F; font-size: 24px; line-height: 1.1; }
    .kpi-value.accent { color: #8E5B3D; }
    .kpi-note { margin-top: 3px; color: #756158; font-size: 7.5px; }

    .data th { padding: 5px 6px; border-bottom: 1px solid #D8C5BA; color: #8C776E; font-size: 7px; font-weight: bold; text-align: left; text-transform: uppercase; }
    .data td { padding: 5px 6px; border-bottom: 1px solid #F0E6E0; font-size: 9px; vertical-align: top; }
    .data .num { text-align: right; }
    .data .muted { color: #9C887F; }
    .data .small { font-size: 8px; color: #756158; }

    .row { padding-top: 6px; border-top: 1px solid #E9DCD5; margin-bottom: 6px; }
    .row .top { font-size: 9px; }
    .row .top .val { float: right; color: #756158; font-weight: bold; }
    .row .label { font-weight: bold; color: #34231F; }
    .track { height: 4px; border-radius: 4px; background: #F2E8E3; margin-top: 3px; }
    .fill { height: 4px; border-radius: 4px; background: #8E5B3D; }

    .chart-table td { text-align: center; vertical-align: bottom; padding: 0 1px; }
    .bar-a { margin: 0 auto; width: 70%; border-radius: 2px 2px 0 0; background: #8E5B3D; }
    .bar-b { margin: 0 auto; width: 70%; border-radius: 2px 2px 0 0; background: #E6CFC1; }
    .chart-label { margin-top: 3px; color: #9C887F; font-size: 6.5px; font-weight: bold; }
    .chart-value { color: #756158; font-size: 6.5px; }
    .legend { margin-top: 6px; color: #756158; font-size: 8px; }

    .empty-note { padding-top: 8px; border-top: 1px solid #E9DCD5; color: #9C887F; font-size: 9px; }
    .footer-note { margin-top: 18px; padding-top: 8px; border-top: 1px solid #E9DCD5; color: #9C887F; font-size: 7.5px; }
</style>
