<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Services\PublicThemeView;
use Illuminate\Contracts\View\View;

class ExpertsController extends Controller
{
    public function __invoke(): View
    {
        return PublicThemeView::render('experts', [
            'experts' => Expert::published()->ordered()->with('translations')->get(),
        ]);
    }
}
