<?php

namespace App\Actions;

use App\Models\Album;
use App\Support\Blocks;
use App\Support\Locales;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaveAlbum
{
    public function __construct(private readonly ResolveSlug $slugger)
    {
    }

    /**
     * @param  string  $intent  save | publish | unpublish — задаёт целевой статус.
     */
    public function handle(?Album $album, array $data, string $intent = 'save'): Album
    {
        return DB::transaction(function () use ($album, $data, $intent) {
            $album ??= new Album();

            $translations = (array) ($data['translations'] ?? []);
            $primaryTitle = $translations[Locales::PRIMARY]['title'] ?? null;

            $album->slug = $this->slugger->handle($album, $data['slug'] ?? null, $primaryTitle, 'album');
            $album->cover_path = Blocks::normalizePath($data['cover_path'] ?? null);
            $album->blocks = Blocks::canonical($data['blocks'] ?? [], Blocks::ALBUM_KINDS);

            // Целевой статус: новый альбом по умолчанию опубликован (AGENTS.md §15).
            $currentStatus = $album->exists
                ? ($album->status instanceof \App\Enums\PublishStatus ? $album->status->value : $album->status)
                : 'published';

            $targetStatus = match ($intent) {
                'publish' => 'published',
                'unpublish' => 'draft',
                default => $currentStatus,
            };

            $album->status = $targetStatus;
            $album->published_at = $this->resolvePublishedAt($targetStatus, $album->published_at, $data['published_at'] ?? null);

            $album->save();
            $album->loadMissing('translations');

            foreach (Locales::ALL as $locale) {
                $row = $translations[$locale] ?? [];
                $title = $this->clean($row['title'] ?? null);
                $excerpt = $this->clean($row['excerpt'] ?? null);

                $existing = $album->translations->firstWhere('locale', $locale);

                if ($locale !== Locales::PRIMARY && blank($title) && blank($excerpt)) {
                    $existing?->delete();

                    continue;
                }

                $album->translations()->updateOrCreate(
                    ['album_id' => $album->id, 'locale' => $locale],
                    ['title' => $title, 'excerpt' => $excerpt],
                );
            }

            return $album->fresh(['translations']);
        });
    }

    private function resolvePublishedAt(string $status, ?Carbon $current, ?string $submitted): ?Carbon
    {
        if ($status === 'draft') {
            return null;
        }

        if ($submitted) {
            $date = Carbon::parse($submitted);
            if (! $date->isFuture()) {
                return $date; // задним числом можно, вперёд — нет
            }
        }

        return $current ?? now();
    }

    private function clean(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value === '' ? null : $value;
    }
}
