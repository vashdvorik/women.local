<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Участницы платформы. Список лежит в resources/data/participants.php и общий для каталога
 * /members и карусели «Участницы платформы» на главной: править участницу нужно в одном месте.
 *
 * Для карусели фото отдаются не оригиналами, а миниатюрами: аватар рисуется 56 px, а оригинал
 * ~225×300 и весит в разы больше. Миниатюры делает `php artisan participants:thumbnails`.
 */
final class Participants
{
    /** Где лежат миниатюры (внутри themes/public/miro/images) и сторона квадрата в px: 128 хватает экранам ×2. */
    public const THUMB_DIR = 'participants/thumb';

    public const THUMB_SIZE = 128;

    /**
     * @return list<array{photo: ?string, name: array<string, string>, tag: array<string, string>, summary: array<string, string>}>
     */
    public static function all(): array
    {
        static $all = null;

        return $all ??= require resource_path('data/participants.php');
    }

    /**
     * Порядок карусели: сначала те, у кого есть фото, чтобы первые страницы выглядели живыми,
     * затем карточки с инициалами. Внутри каждой группы порядок каталога сохраняется.
     *
     * @return list<array{photo: ?string, name: array<string, string>, tag: array<string, string>, summary: array<string, string>}>
     */
    public static function forCarousel(): array
    {
        $all = self::all();

        return array_merge(
            array_values(array_filter($all, fn (array $p): bool => $p['photo'] !== null)),
            array_values(array_filter($all, fn (array $p): bool => $p['photo'] === null)),
        );
    }

    /** «participants/participant-andreeva.jpg» → «participants/thumb/participant-andreeva.webp». */
    public static function thumb(string $photo): string
    {
        return self::THUMB_DIR.'/'.pathinfo($photo, PATHINFO_FILENAME).'.webp';
    }
}
