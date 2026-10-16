<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Event;
use App\Services\PublicThemeView;
use App\Support\Locales;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * «Новости»: список карточек (/events) и страница новости с текстом и фото (/events/{slug}).
 * Список и опубликованные страницы лежат в кеше публичных страниц (App\Support\PublicCache), а не читаются из базы.
 */
class EventsController extends ContentPageController
{
    /** Сколько новостей в одной порции: три ряда по три карточки. Дальше кнопка «Смотреть ещё». */
    public const PER_PAGE = 9;

    /**
     * Список новостей порциями. Полная страница: первая порция (или та, что названа в ?page=N, это запасной ход для
     * браузера без скриптов) и кнопка «Смотреть ещё». С ?partial=1 (так просит js/load-more.js) отвечает JSON: готовые
     * карточки следующей порции и адрес порции за ней.
     */
    public function __invoke(Request $request): View|JsonResponse|RedirectResponse
    {
        [$events, $paginator] = $this->cachedCards(
            'events.list',
            fn () => Event::published()->ordered()->with('translations'),
            fn (Event $event) => $event,
            self::PER_PAGE,
        );

        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('themes.public.miro.partials.event-cards', ['events' => $events])->render(),
                'next' => $paginator->hasMorePages() ? $paginator->nextPageUrl() : null,
            ]);
        }

        // Страницы за последней новостью нет: открываем список с начала.
        if ($events === [] && $paginator->currentPage() > 1) {
            return redirect()->route('events');
        }

        return PublicThemeView::render('events', ['events' => $events, 'paginator' => $paginator]);
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
