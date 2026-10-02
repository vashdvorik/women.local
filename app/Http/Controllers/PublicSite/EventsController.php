<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Event;
use App\Services\PublicThemeView;
use App\Support\Locales;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** «Новости»: список карточек (/events) и страница новости с текстом и фото (/events/{slug}). */
class EventsController extends ContentPageController
{
    public function __invoke(): View
    {
        return PublicThemeView::render('events', [
            'events' => Event::published()->ordered()->with('translations')->get(),
        ]);
    }

    public function show(Request $request, Event $event): View
    {
        $event->load('translations');
        $this->abortUnlessVisible($request, $event->is_published);

        $type = PublicCards::field($event, 'type');

        $date = [];
        foreach (Locales::ALL as $locale) {
            $date[$locale] = $event->dateLabel($locale);
        }

        return $this->article('events', $this->tri('Новости', 'News', 'Noutăți'), [
            'title' => PublicCards::field($event, 'title'),
            'excerpt' => PublicCards::field($event, 'description'),
            'image' => $event->imageUrl(),
            'badge' => array_filter($type) ? $type : null,
            'badge_style' => $event->tagStyle(),
            'meta' => array_filter($date) ? $date : null,
            'blocks' => collect(Locales::ALL)->mapWithKeys(fn (string $l) => [$l => $event->renderBlocks($l)])->all(),
            // Полный текст мог быть и на сайте организации: ссылку на него не теряем.
            'source' => filled($event->url) ? [
                'href' => $event->url,
                'label' => $this->tri('Читать на сайте организации', 'Read on the organisation’s website', 'Citește pe site-ul organizației'),
            ] : null,
            'back' => [
                'href' => route('events'),
                'label' => $this->tri('Все новости', 'All news', 'Toate noutățile'),
            ],
            'draft' => ! $event->is_published,
        ]);
    }
}
