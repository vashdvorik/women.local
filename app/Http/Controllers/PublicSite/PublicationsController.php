<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Post;
use App\Support\Locales;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** «Медиатека → Публикации»: новости и статьи, введённые в админке (блочный редактор). */
class PublicationsController extends ContentPageController
{
    public function index(): View
    {
        $posts = Post::with(['translations', 'tag.translations'])
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return $this->listing('publications', [
            'eyebrow' => $this->tri('Медиатека', 'Media library', 'Mediatecă'),
            'title' => $this->tri('Публикации', 'Publications', 'Publicații'),
            'intro' => $this->tri(
                'Публикации, статьи и материалы платформы.',
                'Platform publications, articles and materials.',
                'Publicațiile, articolele și materialele platformei.'
            ),
        ], $posts->getCollection()->map(fn (Post $post) => PublicCards::post($post))->all(), $posts);
    }

    public function show(Request $request, Post $post): View
    {
        $post->load(['translations', 'tag.translations']);
        $this->abortUnlessVisible($request, $post->isPublished());

        $card = PublicCards::post($post);

        return $this->article('publications', $this->tri('Публикации', 'Publications', 'Publicații'), [
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
        ]);
    }
}
