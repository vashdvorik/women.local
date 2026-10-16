<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\SiteOpportunity;
use App\Support\Locales;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * «Возможности»: гранты, программы и предложения, введённые в админке (не посты участниц кабинета). Список и
 * опубликованные страницы лежат в кеше публичных страниц (App\Support\PublicCache).
 */
class OpportunitiesController extends ContentPageController
{
    public function index(): View
    {
        [$cards, $items] = $this->cachedCards(
            'opportunities.list',
            fn () => SiteOpportunity::with(['translations', 'tag.translations'])->published()->orderByDesc('published_at'),
            fn (SiteOpportunity $item) => PublicCards::opportunity($item),
        );

        return $this->listing('opportunities', [
            'eyebrow' => $this->tri('Платформа', 'Platform', 'Platformă'),
            'title' => $this->tri('Возможности', 'Opportunities', 'Oportunități'),
            'intro' => $this->tri(
                'Гранты, программы, события и партнёрские предложения для развития бизнеса.',
                'Grants, programmes, events and partnership offers for business growth.',
                'Granturi, programe, evenimente și oferte de parteneriat pentru dezvoltarea afacerii.'
            ),
        ], $cards, $items);
    }

    public function show(Request $request, string $opportunity): View
    {
        return $this->materialPage(
            $request,
            'opportunities',
            $this->tri('Возможности', 'Opportunities', 'Oportunități'),
            $opportunity,
            fn (bool $withDrafts) => ($withDrafts ? SiteOpportunity::query() : SiteOpportunity::published())->with(['translations', 'tag.translations'])->where('slug', $opportunity)->first(),
            fn (SiteOpportunity $item) => $this->articleData($item),
        );
    }

    /** @return array<string, mixed> */
    private function articleData(SiteOpportunity $opportunity): array
    {
        $card = PublicCards::opportunity($opportunity);

        return [
            'title' => $card['title'],
            'excerpt' => $card['excerpt'],
            'image' => $card['image'],
            'badge' => $card['badge'],
            'badge_style' => $card['badge_style'],
            'meta' => $card['date'],
            'blocks' => collect(Locales::ALL)->mapWithKeys(fn (string $l) => [$l => $opportunity->renderBlocks($l)])->all(),
            'back' => [
                'href' => route('opportunities'),
                'label' => $this->tri('Все возможности', 'All opportunities', 'Toate oportunitățile'),
            ],
            'draft' => ! $opportunity->isPublished(),
        ];
    }
}
