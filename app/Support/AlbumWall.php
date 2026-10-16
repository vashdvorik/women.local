<?php

namespace App\Support;

/**
 * Раскладка фотографий альбома «полотном»: ряды разных видов, один за другим.
 *
 *  - вертикальные снимки идут рядами по четыре (блок gallery_4, плитки 3:4);
 *  - горизонтальные: по три в ряд (gallery_3) или по два (gallery_2), плитки 4:3. Остаток от деления на три
 *    не оставляет одиноких снимков: четыре фото идут 2+2, семь — 3+2+2, пять — 3+2;
 *  - ряды вертикальных и горизонтальных чередуются, начиная с того, чей снимок идёт в папке первым.
 *
 * Вертикальных снимков, не набравших полный ряд (один, два или три лишних), становится столько же «горизонтальных»:
 * для плитки выбираются самые «квадратные» из них (меньше всего обрезается), кадр потом можно поменять в админке.
 * Единственный горизонтальный снимок идёт один во всю ширину (блок image). Порядок снимков внутри каждой группы
 * остаётся таким, как был.
 */
final class AlbumWall
{
    /** Снимок считается вертикальным, если он выше своей ширины хотя бы в столько раз. */
    private const TALL_RATIO = 1.1;

    /** Сколько вертикальных снимков в ряду. */
    private const TALL_ROW = 4;

    /**
     * @param  list<array{w: int, h: int}>  $photos  снимки в порядке папки
     * @return list<array{type: string, photos: list<int>}> ряды: вид блока и номера снимков (в порядке показа)
     */
    public static function rows(array $photos): array
    {
        $tall = [];
        $wide = [];

        foreach ($photos as $index => $photo) {
            if (self::isTall($photo)) {
                $tall[] = $index;
            } else {
                $wide[] = $index;
            }
        }

        // Лишние вертикальные (не набравшие четвёрку) переходят к горизонтальным: самые «квадратные» первыми.
        $extra = count($tall) % self::TALL_ROW;

        if ($extra > 0) {
            $byShape = $tall;
            usort($byShape, fn (int $a, int $b) => [self::tallness($photos[$a]), $a] <=> [self::tallness($photos[$b]), $b]);
            $moved = array_slice($byShape, 0, $extra);

            $tall = array_values(array_diff($tall, $moved));
            $wide = array_merge($wide, $moved);
            sort($wide);
        }

        $tallRows = array_map(fn (array $chunk) => ['type' => 'gallery_4', 'photos' => $chunk], array_chunk($tall, self::TALL_ROW));
        $wideRows = self::wideRows($wide);

        if ($tallRows === []) {
            return $wideRows;
        }

        if ($wideRows === []) {
            return $tallRows;
        }

        // Ряды чередуются, начиная с группы, чей снимок в папке раньше.
        [$a, $b] = $tall[0] < $wide[0] ? [$tallRows, $wideRows] : [$wideRows, $tallRows];
        $rows = [];

        while ($a !== [] || $b !== []) {
            if ($a !== []) {
                $rows[] = array_shift($a);
            }

            if ($b !== []) {
                $rows[] = array_shift($b);
            }
        }

        return $rows;
    }

    /** Ряды горизонтальных: тройки, в хвосте двойки; один снимок — во всю ширину. @return list<array{type: string, photos: list<int>}> */
    private static function wideRows(array $indexes): array
    {
        $count = count($indexes);

        if ($count === 0) {
            return [];
        }

        if ($count === 1) {
            return [['type' => 'image', 'photos' => $indexes]];
        }

        $twos = match ($count % 3) {
            0 => 0,
            2 => 1,
            default => 2,
        };

        $threes = intdiv($count - $twos * 2, 3);
        $rows = [];
        $offset = 0;

        foreach (array_merge(array_fill(0, $threes, 3), array_fill(0, $twos, 2)) as $size) {
            $rows[] = ['type' => $size === 3 ? 'gallery_3' : 'gallery_2', 'photos' => array_slice($indexes, $offset, $size)];
            $offset += $size;
        }

        return $rows;
    }

    /** @param array{w: int, h: int} $photo */
    private static function isTall(array $photo): bool
    {
        return self::tallness($photo) >= self::TALL_RATIO;
    }

    /** Во сколько раз снимок выше своей ширины. @param array{w: int, h: int} $photo */
    private static function tallness(array $photo): float
    {
        return $photo['w'] > 0 ? $photo['h'] / $photo['w'] : 1.0;
    }
}
