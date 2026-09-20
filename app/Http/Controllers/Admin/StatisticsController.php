<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImpactReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

/**
 * «Статистика платформы» и её PDF-выгрузка (раньше — Filament ImpactMetrics).
 * PDF — обычная загрузка файла по обычному маршруту, а не действие компонента.
 */
class StatisticsController extends Controller
{
    public function index(ImpactReport $report): View
    {
        return view('admin.statistics.index', $report->viewData());
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
