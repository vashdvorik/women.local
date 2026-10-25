<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ResolveSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogRequest;
use App\Models\Catalog;
use App\Support\Blocks;
use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Каталоги с перелистыванием («Медиатека → Публикации»). PDF разбирается в браузере администратора (resources/js/catalog-pdf.js):
 * каждая страница уходит на сервер отдельной WebP-картинкой через page(), а форма сохраняет только список путей.
 */
class CatalogController extends Controller
{
    public function __construct(private readonly ResolveSlug $slugger)
    {
    }

    public function index(): View
    {
        $catalogs = Catalog::with('translations')->ordered()->get();

        return view('admin.catalogs.index', compact('catalogs'));
    }

    public function create(): View
    {
        return view('admin.catalogs.create', ['catalog' => new Catalog(['pages' => [], 'allow_download' => false, 'is_published' => true])]);
    }

    public function store(CatalogRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $catalog = Catalog::create([
                'slug' => $this->slugFor(new Catalog(), $request),
                'pages' => $request->pagePaths,
                'page_ratio' => $request->input('page_ratio') ?: 0.7071,
                'cover_path' => Blocks::normalizePath($request->validated('cover_path')),
                'pdf_path' => $this->storePdf($request->file('pdf')),
                'allow_download' => $request->boolean('allow_download'),
                'is_published' => $request->boolean('is_published'),
                'position' => (Catalog::max('position') ?? 0) + 1,
            ]);

            $this->syncTranslations($catalog, $request->validated('translations', []));
        });

        return redirect()->route('admin.catalogs.index')->with('success', __('Каталог добавлен.'));
    }

    public function edit(Catalog $catalog): View
    {
        $catalog->load('translations');

        return view('admin.catalogs.edit', compact('catalog'));
    }

    public function update(CatalogRequest $request, Catalog $catalog): RedirectResponse
    {
        $removed = array_diff($catalog->pagePaths(), $request->pagePaths);
        $oldPdf = $catalog->pdf_path;
        $newPdf = $this->storePdf($request->file('pdf'));

        DB::transaction(function () use ($request, $catalog, $newPdf) {
            $catalog->update([
                'slug' => $this->slugFor($catalog, $request),
                'pages' => $request->pagePaths,
                'page_ratio' => $request->input('page_ratio') ?: $catalog->page_ratio,
                'cover_path' => Blocks::normalizePath($request->validated('cover_path')),
                'pdf_path' => $newPdf ?? $catalog->pdf_path,
                'allow_download' => $request->boolean('allow_download'),
                'is_published' => $request->boolean('is_published'),
            ]);

            $this->syncTranslations($catalog, $request->validated('translations', []));
        });

        // Файлы убранных страниц и заменённого PDF больше не нужны
        $catalog->deleteFiles([...$removed, ...($newPdf !== null ? [$oldPdf] : [])]);

        return redirect()->route('admin.catalogs.index')->with('success', __('Каталог сохранён.'));
    }

    public function destroy(Catalog $catalog): RedirectResponse
    {
        $catalog->delete();
        $catalog->deleteFiles();

        return redirect()->route('admin.catalogs.index')->with('success', __('Каталог удалён.'));
    }

    /** Порядок каталогов на сайте: приходит списком id в новом порядке (перетаскивание и кнопки в списке). */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:catalogs,id'],
        ]);

        Catalog::applyOrder($data['ids']);

        return response()->json(['ok' => true]);
    }

    /** Одна страница каталога, уже превращённая браузером в WebP: сохраняется на диске uploads, в ответ — путь. */
    public function page(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimetypes:image/webp,image/jpeg,image/png', 'max:4096']]);

        $file = $request->file('file');
        $extension = match ($file->getMimeType()) {
            'image/webp' => 'webp',
            'image/png' => 'png',
            default => 'jpg',
        };
        $path = 'catalogs/'.now()->format('Y/m').'/'.Str::lower(Str::random(14)).'.'.$extension;

        Storage::disk('uploads')->putFileAs(dirname($path), $file, basename($path));

        return response()->json(['path' => $path, 'url' => '/uploads/'.$path]);
    }

    private function storePdf(?UploadedFile $file): ?string
    {
        if ($file === null) {
            return null;
        }

        $path = 'catalogs/pdf/'.Str::lower(Str::random(14)).'.pdf';
        Storage::disk('uploads')->putFileAs(dirname($path), $file, basename($path));

        return $path;
    }

    private function slugFor(Catalog $catalog, CatalogRequest $request): string
    {
        $title = (string) $request->validated('translations.ru.title', '');

        return $this->slugger->handle($catalog, $request->validated('slug'), mb_substr($title, 0, 80), 'catalog');
    }

    /** Русское название обязательно; en/ro — только если заполнены; пустой перевод не хранится. */
    private function syncTranslations(Catalog $catalog, array $translations): void
    {
        $catalog->loadMissing('translations');

        foreach (Locales::ALL as $locale) {
            $title = trim((string) ($translations[$locale]['title'] ?? ''));
            $description = trim((string) ($translations[$locale]['description'] ?? ''));
            $row = $catalog->translations->firstWhere('locale', $locale);

            if ($title === '' && $description === '') {
                $row?->delete();

                continue;
            }

            $catalog->translations()->updateOrCreate(
                ['locale' => $locale],
                ['title' => $title, 'description' => $description !== '' ? $description : null],
            );
        }
    }
}
