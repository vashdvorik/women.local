<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Album;
use App\Services\PublicThemeView;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * «Медиатека → Фото»: фотоальбомы, введённые в админке. На странице списка альбомы идут один за другим готовым
 * полотном: название со ссылкой и все фото рядами, без карточек-обложек. Альбомы показываются порциями по четыре
 * с кнопкой «Смотреть ещё». Страница альбома: название, число фото и то же полотно во всю ширину.
 *
 * Список и опубликованные альбомы лежат в кеше публичных страниц (App\Support\PublicCache), а не читаются из базы.
 */
class AlbumsController extends ContentPageController
{
    /** Сколько альбомов в одной порции. */
    public const PER_PAGE = 4;

    /**
     * Полная страница или, с ?partial=1 (так просит js/load-more.js), JSON: готовая разметка следующей порции
     * альбомов и адрес порции за ней.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        [$albums, $paginator] = $this->cachedCards(
            'photos.list',
            fn () => Album::with('translations')->published()->ordered(),
            fn (Album $album) => PublicCards::albumWall($album),
            self::PER_PAGE,
        );

        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('themes.public.miro.partials.album-walls', ['albums' => $albums])->render(),
                'next' => $paginator->hasMorePages() ? $paginator->nextPageUrl() : null,
            ]);
        }

        // Страницы за последним альбомом нет: открываем список с начала.
        if ($albums === [] && $paginator->currentPage() > 1) {
            return redirect()->route('media.photos');
        }

        return PublicThemeView::render('content-albums', [
            'hero' => [
                'eyebrow' => $this->tri('Медиатека', 'Media library', 'Mediatecă'),
                'title' => $this->tri('Фото', 'Photos', 'Fotografii'),
                'intro' => $this->tri(
                    'Фотографии с мероприятий, встреч и важных моментов сообщества.',
                    'Photos from events, meetings and important community moments.',
                    'Fotografii de la evenimente, întâlniri și momente importante ale comunității.'
                ),
            ],
            'albums' => $albums,
            'paginator' => $paginator,
        ]);
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
        $wall = PublicCards::albumWall($album);

        return [
            'title' => $wall['title'],
            'excerpt' => PublicCards::field($album, 'excerpt'),
            'image' => null,
            'badge' => null,
            'badge_style' => null,
            // Под названием число фото, а не дата: даты у альбомов на сайте не показываются.
            'meta' => $wall['count'],
            // Полотно из рядов фото во всю ширину сетки, без боковой колонки, одно на все языки.
            'wide' => true,
            // Просмотр в полном размере (без обрезки) только у опубликованного альбома; черновик открывает плитки.
            'full_photos' => $album->isPublished(),
            'blocks' => ['ru' => $wall['blocks']],
            'back' => [
                'href' => route('media.photos'),
                'label' => $this->tri('Все фотоальбомы', 'All albums', 'Toate albumele'),
            ],
            'draft' => ! $album->isPublished(),
        ];
    }
}
