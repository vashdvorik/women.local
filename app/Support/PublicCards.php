<?php

namespace App\Support;

use App\Models\Album;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteOpportunity;
use App\Models\Tag;
use App\Models\Video;
use Carbon\CarbonInterface;

/**
 * Приводит материалы из админки к единому виду карточки для публичных списков
 * (публикации, возможности, фотоальбомы, видео, проекты). Все тексты — по трём языкам сразу:
 * сайт выводит их в `data-lang`-спанах, переключатель языка скрывает лишнее.
 *
 * Поле карточки:
 *   image, badge {ru,en,ro}|null, badge_style, date {ru,en,ro}|null,
 *   title {ru,en,ro}, excerpt {ru,en,ro}, href, external, play
 */
class PublicCards
{
    /** @return array<string, mixed> */
    public static function post(Post $post): array
    {
        return [
            'image' => $post->cover_path ? '/uploads/'.$post->cover_path : null,
            ...self::tagBadge($post->tag),
            'date' => self::date($post->published_at),
            'title' => self::field($post, 'title'),
            'excerpt' => self::field($post, 'excerpt'),
            'href' => url('media/publications/'.$post->slug),
            'external' => false,
            'play' => false,
        ];
    }

    /** @return array<string, mixed> */
    public static function opportunity(SiteOpportunity $opportunity): array
    {
        return [
            'image' => $opportunity->cover_path ? '/uploads/'.$opportunity->cover_path : null,
            ...self::tagBadge($opportunity->tag),
            'date' => self::deadline($opportunity->deadline_at),
            'title' => self::field($opportunity, 'title'),
            'excerpt' => self::field($opportunity, 'excerpt'),
            'href' => url('opportunities/'.$opportunity->slug),
            'external' => false,
            'play' => false,
        ];
    }

    /** @return array<string, mixed> */
    public static function album(Album $album): array
    {
        $count = $album->photoCount();

        return [
            'image' => $album->cover_path ? '/uploads/'.$album->cover_path : null,
            'badge' => $count > 0 ? [
                'ru' => $count.' фото', // «фото» не склоняется
                'en' => $count.' '.($count === 1 ? 'photo' : 'photos'),
                'ro' => $count.' '.($count === 1 ? 'fotografie' : 'fotografii'),
            ] : null,
            'badge_style' => null,
            'date' => self::date($album->published_at),
            'title' => self::field($album, 'title'),
            'excerpt' => self::field($album, 'excerpt'),
            'href' => url('media/photos/'.$album->slug),
            'external' => false,
            'play' => false,
        ];
    }

    /** @return array<string, mixed> */
    public static function video(Video $video): array
    {
        return [
            'image' => $video->coverUrl(),
            'badge' => null,
            'badge_style' => null,
            'date' => self::date($video->event_date),
            'title' => self::field($video, 'title'),
            'excerpt' => array_fill_keys(Locales::ALL, ''),
            'href' => $video->youtube_url,
            'external' => true,
            'play' => true,
        ];
    }

    /** @return array<string, mixed> */
    public static function project(Project $project): array
    {
        $category = self::field($project, 'category');

        return [
            'image' => $project->imageUrl(),
            'badge' => array_filter($category) ? $category : null,
            'badge_style' => null,
            'date' => null,
            'title' => self::field($project, 'title'),
            'excerpt' => self::field($project, 'text'),
            'href' => $project->url,
            'external' => true,
            'play' => false,
        ];
    }

    /** Значение поля на каждом языке с подстановкой русского. @return array{ru: string, en: string, ro: string} */
    public static function field(object $model, string $name): array
    {
        $out = [];

        foreach (Locales::ALL as $locale) {
            $out[$locale] = (string) $model->field($name, $locale);
        }

        return $out;
    }

    /** @return array<string, string> */
    public static function date(?CarbonInterface $date): ?array
    {
        if ($date === null) {
            return null;
        }

        $out = [];

        foreach (Locales::ALL as $locale) {
            $out[$locale] = $date->copy()->locale($locale)->isoFormat($locale === 'en' ? 'MMMM D, YYYY' : 'D MMMM YYYY');
        }

        return $out;
    }

    /** «До 30 сентября» для возможностей с дедлайном. @return array<string, string>|null */
    public static function deadline(?CarbonInterface $date): ?array
    {
        if ($date === null) {
            return null;
        }

        $out = [];

        foreach (Locales::ALL as $locale) {
            $day = $date->copy()->locale($locale)->isoFormat($locale === 'en' ? 'MMMM D, YYYY' : 'D MMMM YYYY');
            $out[$locale] = match ($locale) {
                'en' => 'Apply by '.$day,
                'ro' => 'Până la '.$day,
                default => 'Подать заявку до '.$day,
            };
        }

        return $out;
    }

    /** @return array{badge: array<string, string>|null, badge_style: string|null} */
    private static function tagBadge(?Tag $tag): array
    {
        if ($tag === null || ! filled($tag->translation()?->name)) {
            return ['badge' => null, 'badge_style' => null];
        }

        return [
            'badge' => self::field($tag, 'name'),
            'badge_style' => 'background:'.$tag->color.';color:'.$tag->textColor(),
        ];
    }
}
