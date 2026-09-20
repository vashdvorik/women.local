<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\SiteOpportunity;
use App\Support\Locales;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** «Возможности»: гранты, программы и предложения, введённые в админке (не посты участниц кабинета). */
class OpportunitiesController extends ContentPageController
{
    public function index(): View
    {
        $items = SiteOpportunity::with(['translations', 'tag.translations'])
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return $this->listing('opportunities', [
            'eyebrow' => $this->tri('Платформа', 'Platform', 'Platformă'),
            'title' => $this->tri('Возможности', 'Opportunities', 'Oportunități'),
            'intro' => $this->tri(
                'Гранты, программы, события и партнёрские предложения для развития бизнеса.',
                'Grants, programmes, events and partnership offers for business growth.',
                'Granturi, programe, evenimente și oferte de parteneriat pentru dezvoltarea afacerii.'
            ),
        ], $items->getCollection()->map(fn (SiteOpportunity $item) => PublicCards::opportunity($item))->all(), $items);
    }

    public function show(Request $request, SiteOpportunity $opportunity): View
    {
        $opportunity->load(['translations', 'tag.translations']);
        $this->abortUnlessVisible($request, $opportunity->isPublished());

        $card = PublicCards::opportunity($opportunity);

        return $this->article('opportunities', $this->tri('Возможности', 'Opportunities', 'Oportunități'), [
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
        ]);
    }
}
