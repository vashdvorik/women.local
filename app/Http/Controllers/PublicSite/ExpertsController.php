<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Services\PublicThemeView;
use App\Support\PublicCache;
use Illuminate\Contracts\View\View;

class ExpertsController extends Controller
{
    public function __invoke(): View
    {
        return PublicThemeView::render('experts', [
            // Список лежит в кеше публичных страниц (App\Support\PublicCache), а не читается из базы на каждый заход.
            'experts' => PublicCache::remember('experts.list', fn () => Expert::published()->ordered()->with('translations')->get()),
        ]);
    }
}
