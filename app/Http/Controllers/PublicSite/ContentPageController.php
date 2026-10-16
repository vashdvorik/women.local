<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\PublicThemeView;
use App\Support\PublicCache;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Общая часть публичных страниц с материалами из админки: список карточек и страница
 * материала. Оформление — единое (тема miro): шапка-«герой», сетка карточек или статья
 * из блоков. Тексты везде на трёх языках сразу (data-lang).
 */
abstract class ContentPageController extends Controller
{
    /**
     * @param  array{eyebrow: array<string, string>, title: array<string, string>, intro: array<string, string>}  $hero
     * @param  list<array<string, mixed>>  $cards
     */
    protected function listing(string $pageKey, array $hero, array $cards, ?LengthAwarePaginator $paginator = null, string $cardVariant = 'text'): View
    {
        return PublicThemeView::render('content-list', [
            'pageKey' => $pageKey,
            'hero' => $hero,
            'cards' => $cards,
            'paginator' => $paginator,
            'cardVariant' => $cardVariant,
        ]);
    }

    /**
     * @param  array<string, mixed>  $article  title, excerpt, image, date, badge, badge_style, meta, blocks, back
     * @param  array<string, string>  $eyebrow
     */
    protected function article(string $pageKey, array $eyebrow, array $article): View
    {
        return PublicThemeView::render('content-article', [
            'pageKey' => $pageKey,
            'eyebrow' => $eyebrow,
            'article' => $article,
        ]);
    }

    /**
     * Карточки списка с постраничной навигацией: из кеша публичных страниц (App\Support\PublicCache), а не из базы.
     * Кешируется каждая страница списка отдельно; дальше PublicCache::MAX_CACHED_PAGE страница собирается каждый раз.
     *
     * @param  Closure(): \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query  запрос опубликованных материалов
     * @param  Closure(\Illuminate\Database\Eloquent\Model): array<string, mixed>  $toCard
     * @return array{0: list<array<string, mixed>>, 1: LengthAwarePaginator}
     */
    protected function cachedCards(string $key, Closure $query, Closure $toCard, int $perPage = 12): array
    {
        $page = max(1, (int) request()->query('page', 1));

        $build = function () use ($query, $toCard, $perPage, $page): array {
            $paginator = $query()->paginate($perPage, ['*'], 'page', $page);

            return [
                'cards' => $paginator->getCollection()->map($toCard)->all(),
                'total' => $paginator->total(),
            ];
        };

        $data = $page <= PublicCache::MAX_CACHED_PAGE ? PublicCache::remember("{$key}.page.{$page}", $build) : $build();

        return [
            $data['cards'],
            new LengthAwarePaginator($data['cards'], $data['total'], $perPage, $page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]),
        ];
    }

    /**
     * Страница материала. Опубликованный материал собирается один раз и лежит в кеше готовым массивом (тексты на трёх
     * языках, очищенные блоки): ни запроса к базе, ни очистки текста на каждый заход. Черновик показывается только
     * администратору (предпросмотр) и в кеш не попадает; несуществующий адрес даёт 404.
     *
     * @param  array<string, string>  $eyebrow
     * @param  Closure(bool): ?\Illuminate\Database\Eloquent\Model  $find  найти материал: true — любой, false — только опубликованный
     * @param  Closure(\Illuminate\Database\Eloquent\Model): array<string, mixed>  $build  данные страницы (см. article())
     */
    protected function materialPage(Request $request, string $pageKey, array $eyebrow, string $slug, Closure $find, Closure $build): View
    {
        $article = PublicCache::remember("{$pageKey}.article.{$slug}", function () use ($find, $build): ?array {
            $model = $find(false);

            return $model === null ? null : $build($model);
        });

        if ($article === null) {
            $model = $find(true) ?? abort(404);
            $this->abortUnlessVisible($request, false);
            $article = $build($model);
        }

        return $this->article($pageKey, $eyebrow, $article);
    }

    /** Неопубликованный материал видит только администратор (предпросмотр из админки). */
    protected function abortUnlessVisible(Request $request, bool $published): void
    {
        abort_unless($published || $request->user()?->email === config('admin.email'), 404);
    }

    /** @return array<string, string> */
    protected function tri(string $ru, string $en, string $ro): array
    {
        return ['ru' => $ru, 'en' => $en, 'ro' => $ro];
    }
}
