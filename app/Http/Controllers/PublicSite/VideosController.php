<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Video;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;

/** «Медиатека → Видео»: ролики YouTube, введённые в админке; порядок задаётся стрелками. */
class VideosController extends ContentPageController
{
    public function __invoke(): View
    {
        $videos = Video::with('translations')->orderBy('position')->orderBy('id')->get();

        return $this->listing('videos', [
            'eyebrow' => $this->tri('Медиатека', 'Media library', 'Mediatecă'),
            'title' => $this->tri('Видео', 'Videos', 'Video'),
            'intro' => $this->tri(
                'Видео с мероприятий, интервью и образовательных программ платформы.',
                'Videos from events, interviews and platform learning programmes.',
                'Videoclipuri de la evenimente, interviuri și programele educaționale ale platformei.'
            ),
        ], $videos->map(fn (Video $video) => PublicCards::video($video))->all(), null, 'media');
    }
}
