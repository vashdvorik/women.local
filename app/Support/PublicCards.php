<?php

namespace App\Support;

use App\Models\Catalog;
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

    /** Каталог с перелистыванием: карточка в списке «Публикации», ведёт на страницу просмотра. @return array<string, mixed> */
    public static function catalog(Catalog $catalog): array
    {
        return [
            'image' => $catalog->coverUrl(),
            'badge' => ['ru' => 'Каталог', 'en' => 'Catalogue', 'ro' => 'Catalog'],
            'badge_style' => null,
            'date' => null,
            'title' => self::field($catalog, 'title'),
            'excerpt' => self::field($catalog, 'description'),
            'href' => route('media.catalogs.show', $catalog->slug),
            'external' => false,
            'play' => false,
            // Для особой карточки каталога: число страниц, ссылка на PDF и пропорции обложки
            'pages' => count($catalog->pagePaths()),
            'pdf' => $catalog->pdfUrl(),
            'ratio' => round((float) $catalog->page_ratio ?: 0.7071, 4),
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
        return [
            'image' => $album->cover_path ? '/uploads/'.$album->cover_path : null,
            'badge' => self::photoLabel($album->photoCount()),
            'badge_style' => null,
            'date' => self::date($album->published_at),
            'title' => self::field($album, 'title'),
            'excerpt' => self::field($album, 'excerpt'),
            'href' => url('media/photos/'.$album->slug),
            'external' => false,
            'play' => false,
        ];
    }

    /**
     * Альбом для страницы «Фото»: не карточка, а название, число фото и сами ряды фотографий («полотно»).
     * Дат у альбомов на сайте нет: их порядок задаётся датой публикации, но показывать её незачем.
     *
     * @return array{slug: string, title: array<string, string>, href: string, count: ?array<string, string>, blocks: list<array<string, mixed>>}
     */
    public static function albumWall(Album $album, ?int $rows = null): array
    {
        $blocks = array_values($album->blocks ?? []);
        $more = false;

        // Список альбомов показывает только первые ряды фото, остальное — на странице альбома (кнопка «Смотреть альбом»)
        if ($rows !== null) {
            [$blocks, $more] = self::firstRows($blocks, $rows);
        }

        return [
            'slug' => $album->slug,
            'title' => self::field($album, 'title'),
            'href' => url('media/photos/'.$album->slug),
            'count' => self::photoLabel($album->photoCount()),
            'blocks' => $blocks,
            'more' => $more,
        ];
    }

    /**
     * Первые $rows рядов фото из блоков материала. Ряд — это строка галереи (по 2, 3 или 4 фото, как в вёрстке на широком экране)
     * или одиночное фото; текстовые блоки до обрезки сохраняются. Второй элемент ответа true, если часть фото осталась за кадром.
     *
     * @param  list<array<string, mixed>>  $blocks
     * @return array{0: list<array<string, mixed>>, 1: bool}
     */
    public static function firstRows(array $blocks, int $rows): array
    {
        $photoTypes = ['image', 'gallery_2', 'gallery_3', 'gallery_4'];
        $out = [];
        $used = 0;

        foreach ($blocks as $i => $block) {
            $type = $block['type'] ?? '';

            if (! in_array($type, $photoTypes, true)) {
                $out[] = $block;
                continue;
            }

            if ($used >= $rows) {
                // Ряды набраны: всё, что дальше, кроме фото, не нужно, а наличие фото значит «есть ещё»
                return [$out, true];
            }

            if ($type === 'image') {
                $out[] = $block;
                $used++;
                continue;
            }

            $cols = (int) substr($type, -1);
            $images = array_values(array_filter($block['data']['images'] ?? []));
            $need = $rows - $used;

            if (count($images) > $need * $cols) {
                $block['data']['images'] = array_slice($images, 0, $need * $cols);
                $out[] = $block;

                return [$out, true];
            }

            $out[] = $block;
            $used += (int) ceil(count($images) / $cols);
        }

        return [$out, false];
    }

    /** «12 фото» на трёх языках; null, если фотографий нет. @return array{ru: string, en: string, ro: string}|null */
    public static function photoLabel(int $count): ?array
    {
        return $count > 0 ? [
            'ru' => $count.' фото', // «фото» не склоняется
            'en' => $count.' '.($count === 1 ? 'photo' : 'photos'),
            'ro' => $count.' '.($count === 1 ? 'fotografie' : 'fotografii'),
        ] : null;
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
            // Адрес встроенного проигрывателя: по клику видео играет поверх страницы, а ссылка на YouTube остаётся запасной.
            'video' => $video->embedUrl(),
        ];
    }

    /** @return array<string, mixed> */
    public static function project(Project $project): array
    {
        return [
            'image' => $project->imageUrl(),
            // Категория хранится для админки и поиска, но на публичных карточках плашки не нужны.
            'badge' => null,
            'badge_style' => null,
            'date' => null,
            'title' => self::field($project, 'title'),
            'excerpt' => self::field($project, 'text'),
            // Своя страница проекта важнее внешней ссылки: на ней весь рассказ и фотографии
            'href' => $project->hasPage() ? url('projects/'.$project->slug) : $project->url,
            'external' => ! $project->hasPage(),
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
