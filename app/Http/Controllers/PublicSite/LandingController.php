<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Expert;
use App\Services\PublicThemeView;
use App\Support\PublicCache;
use Illuminate\Contracts\View\View;

/** Главная: превью экспертов и событий берутся из БД (админка), остальное — вёрстка темы. */
class LandingController extends Controller
{
    public function __invoke(): View
    {
        return PublicThemeView::render('landing', [
            // Превью лежат в кеше публичных страниц (App\Support\PublicCache), а не читаются из базы на каждый заход.
            'landingExperts' => PublicCache::remember('landing.experts', fn () => Expert::published()->ordered()->with('translations')->limit(6)->get()),
            'landingEvents' => PublicCache::remember('landing.events', fn () => Event::published()->ordered()->with('translations')->limit(3)->get()),
        ]);
    }
}
