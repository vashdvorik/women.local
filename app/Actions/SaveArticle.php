<?php

namespace App\Actions;

use App\Support\Blocks;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Сохранение новости или возможности. Никаких проверок публикации — черновик
 * сохраняется в любом состоянии (AGENTS.md §11).
 *
 * Несколько записей подряд (материал + три перевода) — только в транзакции
 * (AGENTS.md §5.1).
 */
class SaveArticle
{
    public function __construct(private readonly ResolveSlug $slugger)
    {
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $data  провалидированные данные запроса
     */
    public function handle(string $modelClass, ?Model $article, array $data): Model
    {
        return DB::transaction(function () use ($modelClass, $article, $data) {
            /** @var Model $article */
            $article ??= new $modelClass();

            $translationsInput = (array) ($data['translations'] ?? []);
            $primaryTitle = $translationsInput[Locales::PRIMARY]['title'] ?? null;

            $article->slug = $this->slugger->handle(
                $article,
                $data['slug'] ?? null,
                $primaryTitle,
            );

            $article->author = $data['author'] ?? null;
            $article->cover_path = Blocks::normalizePath($data['cover_path'] ?? null);

            if (! $article->exists) {
                $article->status = 'draft';
            }

            $article->published_at = $this->resolvePublishedAt($article, $data);

            if (array_key_exists('deadline_at', $data)) {
                $article->deadline_at = $data['deadline_at'] ?: null;
            }
            if (array_key_exists('tag_id', $data)) {
                $article->tag_id = $data['tag_id'] ?: null;
            }

            $article->save();
            $article->loadMissing('translations');

            $this->syncTranslations($article, $translationsInput);

            return $article->fresh(['translations']);
        });
    }

    /**
     * Правило даты при каждом сохранении (AGENTS.md §11):
     *  - черновик → дата очищается;
     *  - опубликовано → нет даты: текущий момент; есть: как есть, но не в будущем.
     */
    private function resolvePublishedAt(Model $article, array $data): ?Carbon
    {
        if (($article->status->value ?? $article->status) === 'draft') {
            return null;
        }

        $submitted = $data['published_at'] ?? null;

        if (! $submitted) {
            return $article->published_at ?? now();
        }

        $date = Carbon::parse($submitted);

        return $date->isFuture() ? ($article->published_at ?? now()) : $date;
    }

    private function syncTranslations(Model $article, array $input): void
    {
        $foreignKey = Str::snake(class_basename($article)).'_id';

        $primaryBlocks = Blocks::canonical(
            $input[Locales::PRIMARY]['content'] ?? [],
            Blocks::ARTICLE_KINDS,
        );

        foreach (Locales::ALL as $locale) {
            $row = $input[$locale] ?? [];

            $content = $locale === Locales::PRIMARY
                ? $primaryBlocks
                : Blocks::mergeTranslation($primaryBlocks, $row['content'] ?? []);

            $attributes = [
                'title' => $this->clean($row['title'] ?? null),
                'excerpt' => $this->clean($row['excerpt'] ?? null),
                'seo_title' => $this->clean($row['seo_title'] ?? null),
                'seo_description' => $this->clean($row['seo_description'] ?? null),
                'content' => $content,
            ];

            $isEmptySecondary = $locale !== Locales::PRIMARY
                && blank($attributes['title'])
                && blank($attributes['excerpt'])
                && blank($attributes['seo_title'])
                && blank($attributes['seo_description'])
                && ! $this->blocksHaveText($content);

            $existing = $article->translations->firstWhere('locale', $locale);

            if ($isEmptySecondary) {
                // Очистка перевода должна его удалять (AGENTS.md §7).
                $existing?->delete();

                continue;
            }

            $article->translations()->updateOrCreate(
                [$foreignKey => $article->getKey(), 'locale' => $locale],
                $attributes,
            );
        }
    }

    private function blocksHaveText(array $blocks): bool
    {
        foreach ($blocks as $block) {
            if ($block['type'] === 'text' && filled(trim(strip_tags($block['data']['html'] ?? '')))) {
                return true;
            }
            if ($block['type'] === 'heading' && filled($block['data']['text'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function clean(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value === '' ? null : $value;
    }
}
