<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SiteOpportunity;
use App\Models\SiteSetting;
use App\Support\PublicCache;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * «Возможности» и материалы раздела «Обучение» в кабинете: карточки, которые редактор публикует в админке. Это возможности
 * (SiteOpportunity) и новости (Post), отмеченные галочкой «В кабинете участниц, в «Возможностях»» или «…в «Обучении»»,
 * в том числе закрытые для открытого сайта. Читают все тарифы.
 */
class SiteOpportunityController extends Controller
{
    private const PER_PAGE = 12;

    public function index(): View
    {
        $entries = self::entries('cabinet');
        $page = LengthAwarePaginator::resolveCurrentPage();

        $items = new LengthAwarePaginator(
            $entries->forPage($page, self::PER_PAGE)->values(),
            $entries->count(),
            self::PER_PAGE,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()],
        );

        return $this->themedView('possibilities.index', ['items' => $items, 'cards' => self::cards($items->getCollection(), 'possibilities')]);
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

    /** Новость, добавленная в «Возможности». */
    public function showPost(string $post): View
    {
        $item = Post::with(['translations', 'tag.translations'])
            ->published()->inCabinet()
            ->where('slug', $post)
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

    /** Новость, добавленная в «Обучение». */
    public function showLearningPost(string $post): View
    {
        $item = Post::with(['translations', 'tag.translations'])
            ->published()->inLearning()
            ->where('slug', $post)
            ->firstOrFail();

        return $this->themedView('possibilities.show', [
            'article' => self::article($item, app()->getLocale(), route('account.knowledge'), __('account.knowledge.back')),
        ]);
    }

    /**
     * Карточки раздела «Обучение»: возможности и новости с галочкой «В обучении», новые сверху.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function learningCards(): Collection
    {
        // Одинаковы для всех участниц: лежат в кеше публичных страниц, правка в админке его сбрасывает
        return collect(PublicCache::remember('cabinet.learning.'.app()->getLocale(), fn () => self::cards(self::entries('learning'), 'knowledge')->all()));
    }

    /**
     * Свежие карточки раздела для главной страницы: возможности и новости, новые сверху. В карточке есть метка времени at.
     *
     * @param  'cabinet'|'learning'  $place
     * @return Collection<int, array<string, mixed>>
     */
    public static function latest(string $place, int $limit): Collection
    {
        return self::cards(self::entries($place)->take($limit), $place === 'learning' ? 'knowledge' : 'possibilities');
    }

    /**
     * Лента раздела: возможности и новости вместе, по дате публикации. Сначала берутся только id и даты (лёгкий запрос),
     * полные карточки загружаются позже и только для тех, что попали на страницу.
     *
     * @param  'cabinet'|'learning'  $place
     * @return Collection<int, array{type: string, id: int, at: int}>
     */
    private static function entries(string $place): Collection
    {
        $scope = $place === 'learning' ? 'inLearning' : 'inCabinet';

        $row = fn (string $type) => fn (Model $model): array => [
            'type' => $type,
            'id' => (int) $model->getKey(),
            'at' => (int) $model->published_at?->getTimestamp(),
        ];

        return SiteOpportunity::query()->published()->{$scope}()->get(['id', 'published_at'])->map($row('opportunity'))
            ->concat(Post::query()->published()->{$scope}()->get(['id', 'published_at'])->map($row('post')))
            ->sortByDesc('at')
            ->values();
    }

    /**
     * Полные карточки для ссылок вида [type, id].
     *
     * @param  Collection<int, array{type: string, id: int, at: int}>  $entries
     * @param  'possibilities'|'knowledge'  $section
     * @return Collection<int, array<string, mixed>>
     */
    private static function cards(Collection $entries, string $section): Collection
    {
        $locale = app()->getLocale();
        $with = ['translations', 'tag.translations'];

        $load = function (string $model, string $type) use ($entries, $with) {
            $ids = $entries->where('type', $type)->pluck('id');

            return $ids->isEmpty() ? collect() : $model::with($with)->whereIn('id', $ids)->get()->keyBy('id');
        };

        $opportunities = $load(SiteOpportunity::class, 'opportunity');
        $posts = $load(Post::class, 'post');

        return $entries->map(function (array $entry) use ($opportunities, $posts, $locale, $section): ?array {
            $isPost = $entry['type'] === 'post';
            $item = $isPost ? $posts->get($entry['id']) : $opportunities->get($entry['id']);

            if ($item === null) {
                return null;
            }

            $route = $section.($isPost ? '.post' : '.show');

            return self::card($item, $locale, 'account.'.$route) + ['at' => $entry['at']];
        })->filter()->values();
    }

    /** @return array{href: string, image: ?string, badge: string, date: string, title: string, excerpt: string} */
    private static function card(SiteOpportunity|Post $item, string $locale, string $route): array
    {
        $card = self::publicCard($item);

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
    private static function article(SiteOpportunity|Post $item, string $locale, string $backUrl, string $backLabel): array
    {
        $card = self::publicCard($item);

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

    /** @return array<string, mixed> */
    private static function publicCard(SiteOpportunity|Post $item): array
    {
        return $item instanceof Post ? PublicCards::post($item) : PublicCards::opportunity($item);
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
