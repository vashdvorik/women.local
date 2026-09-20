<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Album;
use App\Support\Locales;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** «Медиатека → Фото»: фотоальбомы с галереями, введённые в админке. */
class AlbumsController extends ContentPageController
{
    public function index(): View
    {
        $albums = Album::with('translations')
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return $this->listing('photos', [
            'eyebrow' => $this->tri('Медиатека', 'Media library', 'Mediatecă'),
            'title' => $this->tri('Фото', 'Photos', 'Fotografii'),
            'intro' => $this->tri(
                'Фотографии с мероприятий, встреч и важных моментов сообщества.',
                'Photos from events, meetings and important community moments.',
                'Fotografii de la evenimente, întâlniri și momente importante ale comunității.'
            ),
        ], $albums->getCollection()->map(fn (Album $album) => PublicCards::album($album))->all(), $albums);
    }

    public function show(Request $request, Album $album): View
    {
        $album->load('translations');
        $this->abortUnlessVisible($request, $album->isPublished());

        $card = PublicCards::album($album);

        // Структура альбома — галереи — общая для всех языков; переводятся только заголовок и описание.
        $blocks = $album->blocks ?? [];

        return $this->article('photos', $this->tri('Фото', 'Photos', 'Fotografii'), [
            'title' => $card['title'],
            'excerpt' => $card['excerpt'],
            'image' => null,
            'badge' => $card['badge'],
            'badge_style' => null,
            'meta' => $card['date'],
            'blocks' => array_fill_keys(Locales::ALL, $blocks),
            'back' => [
                'href' => route('media.photos'),
                'label' => $this->tri('Все фотоальбомы', 'All albums', 'Toate albumele'),
            ],
            'draft' => ! $album->isPublished(),
        ]);
    }
}
