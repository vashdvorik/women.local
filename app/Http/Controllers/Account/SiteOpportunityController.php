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

        $cards = $items->getCollection()->map(fn (SiteOpportunity $item): array => self::card($item, $locale, 'account.possibilities.show'));

        return $this->themedView('possibilities.index', ['items' => $items, 'cards' => $cards]);
    }

    public function show(string $opportunity): View
    {
        $item = SiteOpportunity::with(['translations', 'tag.translations'])
            ->published()->inCabinet()
            ->where('slug', $opportunity)
            ->firstOrFail();

        return $this->themedView('possibilities.show', [
            'article' => self::article($item, app()->getLocale(), route('account.possibilities.index'), __('account.possibilities.back')),
        ]);
    }

    /** Карточка из «Возможностей», выложенная и в раздел «Обучение» (галочка «В обучении»): та же страница, но с возвратом в обучение. */
    public function showLearning(string $opportunity): View
    {
        $item = SiteOpportunity::with(['translations', 'tag.translations'])
            ->published()->inLearning()
            ->where('slug', $opportunity)
            ->firstOrFail();

        return $this->themedView('possibilities.show', [
            'article' => self::article($item, app()->getLocale(), route('account.knowledge'), __('account.knowledge.back')),
        ]);
    }

    /**
     * Карточки раздела «Обучение»: возможности редактора с галочкой «В обучении».
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public static function learningCards(): \Illuminate\Support\Collection
    {
        $locale = app()->getLocale();

        return SiteOpportunity::with(['translations', 'tag.translations'])
            ->published()->inLearning()
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (SiteOpportunity $item): array => self::card($item, $locale, 'account.knowledge.show'));
    }

    /** @return array{href: string, image: ?string, badge: string, date: string, title: string, excerpt: string} */
    private static function card(SiteOpportunity $item, string $locale, string $route): array
    {
        $card = PublicCards::opportunity($item);

        return [
            'href' => route($route, $item->slug),
            'image' => $card['image'],
            'badge' => self::pick($card['badge'] ?? [], $locale),
            'date' => self::pick($card['date'] ?? [], $locale),
            'title' => self::pick($card['title'] ?? [], $locale),
            'excerpt' => self::pick($card['excerpt'] ?? [], $locale),
        ];
    }

    /** @return array<string, mixed> */
    private static function article(SiteOpportunity $item, string $locale, string $backUrl, string $backLabel): array
    {
        $card = PublicCards::opportunity($item);

        return [
            'image' => $card['image'],
            'badge' => self::pick($card['badge'] ?? [], $locale),
            'date' => self::pick($card['date'] ?? [], $locale),
            'title' => self::pick($card['title'] ?? [], $locale),
            'blocks' => $item->renderBlocks($locale),
            'locale' => $locale,
            'back_url' => $backUrl,
            'back_label' => $backLabel,
        ];
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
