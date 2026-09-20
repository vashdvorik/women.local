<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\PublicThemeView;
use Illuminate\Contracts\View\View;

class EventsController extends Controller
{
    public function __invoke(): View
    {
        return PublicThemeView::render('events', [
            'events' => Event::published()->ordered()->with('translations')->get(),
        ]);
    }
}
