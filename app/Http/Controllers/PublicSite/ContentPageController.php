<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\PublicThemeView;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
