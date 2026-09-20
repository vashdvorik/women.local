<?php

namespace App\Support;

use App\Models\SiteOpportunity;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;

/**
 * Данные для Alpine-редактора новости/возможности. Собираются здесь, чтобы
 * контроллер оставался тонким.
 */
class ArticleEditorData
{
    public static function make(Model $article): array
    {
        $isOpportunity = $article instanceof SiteOpportunity;

        $ruContent = Blocks::canonical(
            $article->exists ? ($article->rawTranslation('ru')?->content ?? []) : [],
            Blocks::ARTICLE_KINDS,
        );

        $fields = [];
        $seo = [];
        $blocks = [];

        foreach (Locales::ALL as $locale) {
            $t = $article->exists ? $article->rawTranslation($locale) : null;

            $fields[$locale] = [
                'title' => $t?->title ?? '',
                'excerpt' => $t?->excerpt ?? '',
            ];
            $seo[$locale] = [
                'seo_title' => $t?->seo_title ?? '',
                'seo_description' => $t?->seo_description ?? '',
            ];

            $blocks[$locale] = $locale === 'ru'
                ? $ruContent
                : Blocks::mergeTranslation($ruContent, $t?->content ?? []);
        }

        return [
            'slug' => $article->slug ?? '',
            'author' => $article->author ?? '',
            'published_at' => optional($article->published_at)->format('Y-m-d\TH:i') ?? '',
            'cover' => $article->cover_path ?? '',
            'deadline_at' => $isOpportunity ? (optional($article->deadline_at)->format('Y-m-d') ?? '') : '',
            'tag_id' => $article->tag_id ?? '',
            'fields' => $fields,
            'seo' => $seo,
            'blocks' => $blocks,
            'kinds' => Blocks::ARTICLE_KINDS,
            'uploadUrl' => route('admin.uploads.store'),
            'ratios' => \App\Support\AspectRatio::SLOTS,
            'tags' => self::tags(),
        ];
    }

    private static function tags(): array
    {
        return Tag::with('translations')->get()
            ->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->rawTranslation('ru')?->name ?? '#'.$tag->id,
                'color' => $tag->color,
            ])
            ->sortBy('name', SORT_FLAG_CASE | SORT_NATURAL)
            ->values()
            ->all();
    }
}
