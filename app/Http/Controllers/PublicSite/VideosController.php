<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Video;
use App\Support\PublicCache;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;

/** «Медиатека → Видео»: ролики YouTube, введённые в админке; порядок задаётся стрелками. */
class VideosController extends ContentPageController
{
    public function __invoke(): View
    {
        // Карточки лежат в кеше публичных страниц (App\Support\PublicCache), а не читаются из базы на каждый заход.
        $cards = PublicCache::remember('videos.cards', fn () => Video::with('translations')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn (Video $video) => PublicCards::video($video))
            ->all());

        return $this->listing('videos', [
            'eyebrow' => $this->tri('Медиатека', 'Media library', 'Mediatecă'),
            'title' => $this->tri('Видео', 'Videos', 'Video'),
            'intro' => $this->tri(
                'Видео с мероприятий, интервью и образовательных программ платформы.',
                'Videos from events, interviews and platform learning programmes.',
                'Videoclipuri de la evenimente, interviuri și programele educaționale ale platformei.'
            ),
        ], $cards, null, 'media', bare: true);
    }
}
