<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Album extends Model
{
    use HasTranslations, Publishable;

    protected $fillable = [
        'slug',
        'position',
        'cover_path',
        'status',
        'published_at',
        'blocks',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'blocks' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Порядок на сайте и в админке: ручной (position), при равенстве новые по дате публикации выше. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * Записывает порядок: id из $ids получают позиции 1, 2, 3…, а альбомы, которых в списке нет (например, добавленные
     * в соседней вкладке), встают следом в прежнем порядке. Кеш публичных страниц сбрасывается: запрос напрямую в базу
     * событий модели не вызывает.
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

    /** Реальное количество фотографий, суммированное по всем блокам. */
    public function photoCount(): int
    {
        return count($this->photoPaths());
    }

    /** Пути всех фото альбома по порядку: ряды-галереи (images) и одиночные картинки (path). @return list<string> */
    public function photoPaths(): array
    {
        return collect($this->blocks ?? [])
            ->flatMap(fn (array $block) => $block['data']['images'] ?? [$block['data']['path'] ?? null])
            ->filter(fn ($path) => filled($path))
            ->values()
            ->all();
    }
}
