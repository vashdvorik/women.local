<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Event;
use App\Services\PublicThemeView;
use App\Support\Locales;
use App\Support\PublicCache;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * «Новости»: список карточек (/events) и страница новости с текстом и фото (/events/{slug}).
 * Список и опубликованные страницы лежат в кеше публичных страниц (App\Support\PublicCache), а не читаются из базы.
 */
class EventsController extends ContentPageController
{
    public function __invoke(): View
    {
        return PublicThemeView::render('events', [
            'events' => PublicCache::remember('events.list', fn () => Event::published()->ordered()->with('translations')->get()),
        ]);
    }

    public function show(Request $request, string $event): View
    {
        return $this->materialPage(
            $request,
            'events',
            $this->tri('Новости', 'News', 'Noutăți'),
            $event,
            fn (bool $withDrafts) => ($withDrafts ? Event::query() : Event::published())->with('translations')->where('slug', $event)->first(),
            fn (Event $item) => $this->articleData($item),
        );
    }

    /** @return array<string, mixed> */
    private function articleData(Event $event): array
    {
        $type = PublicCards::field($event, 'type');

        $date = [];
        foreach (Locales::ALL as $locale) {
            $date[$locale] = $event->dateLabel($locale);
        }

        return [
            'title' => PublicCards::field($event, 'title'),
            'excerpt' => PublicCards::field($event, 'description'),
            'image' => $event->imageUrl(),
            'badge' => array_filter($type) ? $type : null,
            'badge_style' => $event->tagStyle(),
            'meta' => array_filter($date) ? $date : null,
            'blocks' => collect(Locales::ALL)->mapWithKeys(fn (string $l) => [$l => $event->renderBlocks($l)])->all(),
            'back' => [
                'href' => route('events'),
                'label' => $this->tri('Все новости', 'All news', 'Toate noutățile'),
            ],
            'draft' => ! $event->is_published,
        ];
    }
}
