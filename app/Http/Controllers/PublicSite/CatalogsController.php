<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Catalog;
use App\Services\PublicThemeView;
use App\Support\PublicCache;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Страница каталога с перелистыванием. Страницы — готовые WebP-картинки с диска uploads (их делает браузер администратора
 * из PDF), в разметке только адреса: картинки подгружает js/catalog.js по мере листания, а не все сразу.
 */
class CatalogsController extends ContentPageController
{
    public function __invoke(Request $request, string $catalog): View
    {
        $data = PublicCache::remember("catalogs.show.{$catalog}", function () use ($catalog): ?array {
            $model = Catalog::with('translations')->published()->where('slug', $catalog)->first();

            return $model && $model->hasPages() ? $this->data($model) : null;
        });

        if ($data === null) {
            // Черновик видит только администратор (предпросмотр из админки)
            $model = Catalog::with('translations')->where('slug', $catalog)->first() ?? abort(404);
            $this->abortUnlessVisible($request, false);
            abort_unless($model->hasPages(), 404);
            $data = $this->data($model) + ['draft' => true];
        }

        return PublicThemeView::render('catalog', ['catalog' => $data]);
    }

    /** @return array<string, mixed> */
    private function data(Catalog $catalog): array
    {
        $card = PublicCards::catalog($catalog);

        return [
            'title' => $card['title'],
            'description' => $card['excerpt'],
            'pages' => array_map(fn (string $path) => $catalog->pageUrl($path), $catalog->pagePaths()),
            'ratio' => round((float) $catalog->page_ratio ?: 0.7071, 4),
            'pdf' => $catalog->pdfUrl(),
            'image' => $card['image'],
            'draft' => ! $catalog->is_published,
            'back' => [
                'href' => route('media.publications'),
                'label' => $this->tri('Все публикации', 'All publications', 'Toate publicațiile'),
            ],
        ];
    }
}
