<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Expert;
use App\Services\PublicThemeView;
use Illuminate\Contracts\View\View;

/** Главная: превью экспертов и событий берутся из БД (админка), остальное — вёрстка темы. */
class LandingController extends Controller
{
    public function __invoke(): View
    {
        return PublicThemeView::render('landing', [
            'landingExperts' => Expert::published()->ordered()->with('translations')->limit(6)->get(),
            'landingEvents' => Event::published()->ordered()->with('translations')->limit(3)->get(),
        ]);
    }
}
