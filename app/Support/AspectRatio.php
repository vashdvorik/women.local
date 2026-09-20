<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Закреплённые пропорции — контракт (AGENTS.md §14). Одно и то же соотношение
 * применяется и в интерфейсе (CSS aspect-ratio), и при серверном кадрировании,
 * поэтому увиденное в форме и оказавшееся на сайте — буквально один кадр.
 *
 * Меняется соотношение — оно меняется здесь, в одном месте, и подхватывается
 * обеими сторонами.
 */
class AspectRatio
{
    /**
     * Слот => [ширина кадра, высота кадра] в пикселях. Соотношение берётся
     * делением; пиксели задают целевой размер серверного кадрирования.
     *
     * @var array<string, array{int, int}>
     */
    public const SLOTS = [
        'cover'      => [1320, 600],  // 2.2 : 1  — обложка новости и возможности
        'image'      => [1280, 720],  // 16 : 9   — отдельная картинка в статье
        'album'      => [1200, 900],  // 4 : 3    — обложка и фотографии фотоальбома
        'gallery_2'  => [1200, 900],  // 4 : 3
        'gallery_3'  => [1200, 900],  // 4 : 3
        'gallery_4'  => [900, 1200],  // 3 : 4
        'video_cover' => [1280, 720], // 16 : 9   — собственная обложка видео
        'project'    => [1280, 720],  // 16 : 9   — картинка карточки проекта
        'avatar'     => [400, 400],   // 1 : 1    — фото автора отзыва
        'expert'     => [800, 800],   // 1 : 1    — портрет эксперта
        'event'      => [1280, 720],  // 16 : 9   — обложка события
    ];

    /** @return array{int, int} */
    public static function dimensions(string $slot): array
    {
        return self::SLOTS[$slot] ?? throw new InvalidArgumentException("Неизвестный слот изображения: {$slot}");
    }

    public static function exists(string $slot): bool
    {
        return isset(self::SLOTS[$slot]);
    }

    /** Строка для CSS `aspect-ratio`, например «2.2 / 1» → «1320 / 600». */
    public static function css(string $slot): string
    {
        [$w, $h] = self::dimensions($slot);

        return "{$w} / {$h}";
    }

    /** Слот для блока-галереи по числу ячеек. */
    public static function forGallery(int $cells): string
    {
        return match ($cells) {
            2 => 'gallery_2',
            3 => 'gallery_3',
            4 => 'gallery_4',
            default => throw new InvalidArgumentException("Галерея на {$cells} ячеек не поддерживается"),
        };
    }
}
