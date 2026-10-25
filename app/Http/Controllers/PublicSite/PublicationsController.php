<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Catalog;
use App\Models\Post;
use App\Support\PublicCache;
use App\Support\Locales;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * «Медиатека → Публикации»: каталоги, брошюры и другие материалы организации, введённые в админке (блочный редактор).
 * Список и опубликованные страницы лежат в кеше публичных страниц (App\Support\PublicCache).
 */
class PublicationsController extends ContentPageController
{
    public function index(): View
    {
        [$cards, $posts] = $this->cachedCards(
            'publications.list',
            fn () => Post::with(['translations', 'tag.translations'])->published()->orderByDesc('published_at'),
            fn (Post $post) => PublicCards::post($post),
        );

        // Каталоги с перелистыванием: особые карточки над списком, только на первой странице
        $catalogs = [];

        if ((int) request()->query('page', 1) <= 1) {
            $catalogs = PublicCache::remember('catalogs.cards', fn () => Catalog::with('translations')->published()->ordered()->get()
                ->filter(fn (Catalog $catalog) => $catalog->hasPages())
                ->map(fn (Catalog $catalog) => PublicCards::catalog($catalog))
                ->values()
                ->all());
        }

        return $this->listing('publications', [
            'eyebrow' => $this->tri('Медиатека', 'Media library', 'Mediatecă'),
            'title' => $this->tri('Публикации', 'Publications', 'Publicații'),
            'intro' => $this->tri(
                'Каталоги, брошюры и другие материалы, которые опубликовала организация.',
                'Catalogues, brochures and other materials published by the organisation.',
                'Cataloage, broșuri și alte materiale publicate de organizație.'
            ),
        ], $cards, $posts, catalogs: $catalogs);
    }

    public function show(Request $request, string $post): View
    {
        return $this->materialPage(
            $request,
            'publications',
            $this->tri('Публикации', 'Publications', 'Publicații'),
            $post,
            fn (bool $withDrafts) => ($withDrafts ? Post::query() : Post::published())->with(['translations', 'tag.translations'])->where('slug', $post)->first(),
            fn (Post $item) => $this->articleData($item),
        );
    }

    /** @return array<string, mixed> */
    private function articleData(Post $post): array
    {
        $card = PublicCards::post($post);

        return [
            'title' => $card['title'],
            'excerpt' => $card['excerpt'],
            'image' => $card['image'],
            'badge' => $card['badge'],
            'badge_style' => $card['badge_style'],
            'meta' => $card['date'],
            'blocks' => collect(Locales::ALL)->mapWithKeys(fn (string $l) => [$l => $post->renderBlocks($l)])->all(),
            'back' => [
                'href' => route('media.publications'),
                'label' => $this->tri('Все публикации', 'All publications', 'Toate publicațiile'),
            ],
            'draft' => ! $post->isPublished(),
        ];
    }
}
