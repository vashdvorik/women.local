<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityReport;
use App\Services\ImpactReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * «Статистика»: три вкладки. «Сообщество» — состав и готовность базы (ImpactReport, она же уходит в PDF),
 * «Активность» — заходы и инструменты кабинета, «Рассылки» — доставка и клики по рассылкам в Telegram (ActivityReport).
 * Считается только открытая вкладка. PDF — обычная загрузка файла по обычному маршруту, а не действие компонента.
 */
class StatisticsController extends Controller
{
    private const TABS = ['community', 'activity', 'broadcasts'];

    public function index(Request $request, ImpactReport $report, ActivityReport $activity): View
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'community';

        $data = match ($tab) {
            'activity' => $activity->activity($request->string('period', '30')->toString()),
            'broadcasts' => $activity->broadcasts(),
            default => $report->viewData(),
        };

        return view('admin.statistics.index', ['tab' => $tab] + $data);
    }

    public function pdf(ImpactReport $report): Response
    {
        $binary = Pdf::loadView('admin.statistics.pdf', $report->viewData())
            ->setPaper('a4', 'portrait')
            ->output();

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="impact-report-'.now()->format('Y-m-d').'.pdf"',
        ]);
    }
}
