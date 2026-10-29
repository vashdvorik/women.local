<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SiteOpportunity;
use App\Models\SiteSetting;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;

/**
 * «Возможности» в кабинете: гранты, программы и предложения, которые редактор публикует в админке. Участницам видны все
 * опубликованные с галочкой «В кабинете участниц», в том числе те, что закрыты для открытого сайта. Читают все тарифы.
 */
class SiteOpportunityController extends Controller
{
    public function index(): View
    {
        $locale = app()->getLocale();

        $items = SiteOpportunity::with(['translations', 'tag.translations'])
            ->published()->inCabinet()
            ->orderByDesc('published_at')
            ->paginate(12);

        $cards = $items->getCollection()->map(function (SiteOpportunity $item) use ($locale): array {
            $card = PublicCards::opportunity($item);

            return [
                'href' => route('account.possibilities.show', $item->slug),
                'image' => $card['image'],
                'badge' => self::pick($card['badge'] ?? [], $locale),
                'date' => self::pick($card['date'] ?? [], $locale),
                'title' => self::pick($card['title'] ?? [], $locale),
                'excerpt' => self::pick($card['excerpt'] ?? [], $locale),
            ];
        });

        return $this->themedView('possibilities.index', ['items' => $items, 'cards' => $cards]);
    }

    public function show(string $opportunity): View
    {
        $locale = app()->getLocale();

        $item = SiteOpportunity::with(['translations', 'tag.translations'])
            ->published()->inCabinet()
            ->where('slug', $opportunity)
            ->firstOrFail();

        $card = PublicCards::opportunity($item);

        return $this->themedView('possibilities.show', [
            'article' => [
                'image' => $card['image'],
                'badge' => self::pick($card['badge'] ?? [], $locale),
                'date' => self::pick($card['date'] ?? [], $locale),
                'title' => self::pick($card['title'] ?? [], $locale),
                'blocks' => $item->renderBlocks($locale),
                'locale' => $locale,
            ],
        ]);
    }

    /**
     * Страница из папки действующей темы кабинета (themes/account/{тема}/pages).
     *
     * @param  array<string, mixed>  $data
     */
    private function themedView(string $page, array $data = []): View
    {
        $theme = view()->shared('accountTheme');
        $theme = is_string($theme) && array_key_exists($theme, SiteSetting::ACCOUNT_THEMES)
            ? $theme
            : SiteSetting::DEFAULT_ACCOUNT_THEME;

        return view("themes.account.{$theme}.pages.{$page}", $data);
    }

    /** Текст на нужном языке; если перевода нет, русский. Не массив (нет значка или даты) даёт пустую строку. */
    private static function pick(mixed $texts, string $locale): string
    {
        if (! is_array($texts)) {
            return '';
        }

        $text = trim((string) ($texts[$locale] ?? ''));

        return $text !== '' ? $text : trim((string) ($texts['ru'] ?? ''));
    }
}
