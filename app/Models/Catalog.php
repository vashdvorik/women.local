<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Каталог с перелистыванием страниц («Медиатека → Публикации»). Администратор загружает PDF, браузер администратора один
 * раз превращает его страницы в лёгкие WebP-картинки (диск uploads, catalogs/…); посетителю уходят только эти картинки,
 * по мере листания, и никакого разбора PDF на его устройстве нет.
 */
class Catalog extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'pages',
        'page_ratio',
        'cover_path',
        'pdf_path',
        'allow_download',
        'is_published',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'pages' => 'array',
            'page_ratio' => 'float',
            'allow_download' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /** @param  Builder<Catalog>  $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** @param  Builder<Catalog>  $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    /** @return list<string> */
    public function pagePaths(): array
    {
        return array_values(array_filter((array) $this->pages, fn ($path) => is_string($path) && $path !== ''));
    }

    public function pageUrl(string $path): string
    {
        return '/uploads/'.ltrim($path, '/');
    }

    /** Обложка: своя либо первая страница. */
    public function coverUrl(): ?string
    {
        $path = $this->cover_path ?: ($this->pagePaths()[0] ?? null);

        return $path ? $this->pageUrl($path) : null;
    }

    public function pdfUrl(): ?string
    {
        return $this->allow_download && $this->pdf_path ? $this->pageUrl($this->pdf_path) : null;
    }

    /** Каталог можно открыть, только если в нём есть страницы. */
    public function hasPages(): bool
    {
        return $this->pagePaths() !== [];
    }

    /** Файлы каталога удаляются вместе с ним. */
    public function deleteFiles(?array $paths = null): void
    {
        $paths ??= [...$this->pagePaths(), $this->cover_path, $this->pdf_path];

        foreach (array_filter($paths) as $path) {
            Storage::disk('uploads')->delete($path);
        }
    }

    /**
     * Ручной порядок: приходит списком id в новом порядке (перетаскивание в админке); не вошедшие в список идут следом.
     *
     * @param  list<int>  $ids
     */
    public static function applyOrder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $ids = array_values(array_unique(array_map('intval', $ids)));
            $rest = static::query()->whereNotIn('id', $ids)->ordered()->pluck('id')->all();

            foreach ([...$ids, ...$rest] as $index => $id) {
                static::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });

        PublicCache::flush();
    }
}
