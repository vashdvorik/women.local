<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Album;
use App\Support\Locales;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * «Медиатека → Фото»: фотоальбомы с галереями, введённые в админке. Список и опубликованные альбомы лежат в кеше
 * публичных страниц (App\Support\PublicCache), а не читаются из базы.
 */
class AlbumsController extends ContentPageController
{
    public function index(): View
    {
        [$cards, $albums] = $this->cachedCards(
            'photos.list',
            fn () => Album::with('translations')->published()->orderByDesc('published_at'),
            fn (Album $album) => PublicCards::album($album),
        );

        return $this->listing('photos', [
            'eyebrow' => $this->tri('Медиатека', 'Media library', 'Mediatecă'),
            'title' => $this->tri('Фото', 'Photos', 'Fotografii'),
            'intro' => $this->tri(
                'Фотографии с мероприятий, встреч и важных моментов сообщества.',
                'Photos from events, meetings and important community moments.',
                'Fotografii de la evenimente, întâlniri și momente importante ale comunității.'
            ),
        ], $cards, $albums);
    }

    public function show(Request $request, string $album): View
    {
        return $this->materialPage(
            $request,
            'photos',
            $this->tri('Фото', 'Photos', 'Fotografii'),
            $album,
            fn (bool $withDrafts) => ($withDrafts ? Album::query() : Album::published())->with('translations')->where('slug', $album)->first(),
            fn (Album $item) => $this->articleData($item),
        );
    }

    /** @return array<string, mixed> */
    private function articleData(Album $album): array
    {
        $card = PublicCards::album($album);

        // Структура альбома — галереи — общая для всех языков; переводятся только заголовок и описание.
        $blocks = $album->blocks ?? [];

        return [
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
        ];
    }
}
