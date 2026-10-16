<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Album;
use App\Models\SiteSetting;
use App\Support\AlbumWall;
use App\Support\AspectRatio;
use App\Support\Locales;
use App\Support\PublicCache;
use Carbon\Carbon;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

/**
 * Фотоальбомы организации: девять альбомов с фото, которые показываются на странице «Фото» готовым полотном
 * (ряд за рядом), а не карточками. Перечень, названия на трёх языках и порядок — в database/seeders/data/albums_archive.php,
 * сжатые фото — в public/themes/public/miro/images/albums/{адрес}/ (их готовит `php artisan albums:compress-archive`).
 *
 * На сервере достаточно одной команды, повторный запуск безопасен:
 *
 *   php artisan db:seed --class=AlbumArchiveSeeder --force
 *
 * Что делает:
 *  - каждый альбом создаёт целиком: фото разложены рядами по ориентации (App\Support\AlbumWall: вертикальные по четыре,
 *    горизонтальные по три или по два), названия на русском, английском и румынском;
 *  - каждое фото проходит тот же конвейер, что загрузка в админке (кадр по слоту → WebP со сжатием из настроек),
 *    а сжатое фото целиком сохраняется на закрытом диске: по нему в админке меняется кадр, а на сайте фото открывается
 *    в полном размере без обрезки;
 *  - порядок альбомов на странице задаёт дата публикации: первому по списку даётся самая поздняя. Даты на сайте не
 *    показываются, поменять порядок можно датой публикации в админке;
 *  - запоминает загруженные альбомы (site_settings: albums_archive): удалённый в админке альбом повторный запуск не
 *    возвращает, а картинки, пропавшие с диска (папка uploads не в git), возвращает из исходников.
 *
 * Ничего, что уже есть, не перезаписывает: альбом с таким адресом не дублируется.
 */
class AlbumArchiveSeeder extends Seeder
{
    use ImportsThemeImages;

    /** Ключ настройки со списком загруженных альбомов. */
    public const MARKER_KEY = 'albums_archive';

    /** Папка с исходниками фото внутри images/ темы. */
    private const PHOTOS_DIR = 'albums';

    /** Насколько окно кадра сдвинуто к верху, когда фото выше нужного (0 — верх, 0,5 — центр): на портретах лица сверху. */
    private const FOCUS_Y = 0.25;

    /** Дата публикации первого альбома списка (не в будущем при любом запуске); каждый следующий на минуту раньше. */
    private const ANCHOR = '2026-10-15 12:00:00';

    /** @var array<string, string> загруженные картинки за этот запуск: «файл|слот» → путь */
    private array $imported = [];

    public function run(): void
    {
        $items = $this->items();

        if ($items === []) {
            $this->command?->warn('Альбомы не найдены: database/seeders/data/albums_archive.php или фото в images/albums.');

            return;
        }

        $done = $this->doneKeys();
        $created = $restored = 0;

        DB::transaction(function () use ($items, &$done, &$created, &$restored) {
            foreach ($items as $position => $item) {
                $album = Album::query()->where('slug', $item['slug'])->first();

                if ($album !== null || in_array($item['slug'], $done, true)) {
                    // Уже загружался: удалённый в админке не возвращаем, пропавшие картинки восстанавливаем.
                    $restored += $album ? $this->restoreImages($album, $item) : 0;
                } else {
                    $this->create($item, $position);
                    $created++;
                }

                $done[] = $item['slug'];
            }

            SiteSetting::write(self::MARKER_KEY, ['keys' => array_values(array_unique($done))]);
        });

        // Под DatabaseSeeder события моделей отключены, и кеш публичных страниц сам не сбросился бы.
        PublicCache::flush();

        $this->command?->info("Фотоальбомы: добавлено {$created}, восстановлено картинок {$restored}.");
    }

    // ------------------------------------------------------------------ данные

    /**
     * Альбомы по списку, у которых есть сжатые фото. Метод защищённый, чтобы тест мог взять один-два альбома.
     *
     * @return list<array{slug: string, folder: string, title: array<string, string>}>
     */
    protected function items(): array
    {
        $items = (array) require database_path('seeders/data/albums_archive.php');

        return array_values(array_filter($items, fn (array $item) => $this->photoFiles($item['slug']) !== []));
    }

    /** @return list<string> */
    private function doneKeys(): array
    {
        $stored = SiteSetting::read(self::MARKER_KEY, []);

        return is_array($stored) ? array_values(array_map('strval', (array) ($stored['keys'] ?? []))) : [];
    }

    /** Сжатые фото альбома в порядке имён: «02.webp» перед «10.webp». @return list<string> абсолютные пути */
    private function photoFiles(string $slug): array
    {
        $files = glob(public_path('themes/public/miro/images/'.self::PHOTOS_DIR.'/'.$slug.'/*.webp')) ?: [];
        usort($files, 'strnatcasecmp');

        return $files;
    }

    // ------------------------------------------------------------------ раскладка

    /**
     * Что и где стоит в альбоме: фото по порядку папки, ряды (вид блока и номера фото) и фото для обложки.
     *
     * @return array{photos: list<array{file: string, w: int, h: int}>, rows: list<array{type: string, photos: list<int>, uid: string}>, cover: array{photo: int, slot: string}}
     */
    private function plan(array $item): array
    {
        $photos = [];

        foreach ($this->photoFiles($item['slug']) as $absolute) {
            [$w, $h] = getimagesize($absolute) ?: [0, 0];
            $photos[] = ['file' => self::PHOTOS_DIR.'/'.$item['slug'].'/'.basename($absolute), 'w' => $w, 'h' => $h];
        }

        $rows = [];

        foreach (AlbumWall::rows($photos) as $index => $row) {
            $row['uid'] = Uuid::uuid5(Uuid::NAMESPACE_URL, "women-hub-albums-archive:{$item['slug']}:{$index}")->toString();
            $rows[] = $row;
        }

        // Обложка (миниатюра в админке): первый горизонтальный кадр, он 4:3 или 16:9; нет такого — первый кадр альбома.
        $coverRow = collect($rows)->first(fn (array $row) => $row['type'] !== 'gallery_4') ?? $rows[0];

        return ['photos' => $photos, 'rows' => $rows, 'cover' => ['photo' => $coverRow['photos'][0], 'slot' => $coverRow['type']]];
    }

    // ------------------------------------------------------------------ создание

    private function create(array $item, int $position): Album
    {
        $plan = $this->plan($item);
        $blocks = [];
        $paths = [];

        foreach ($plan['rows'] as $row) {
            $images = [];

            foreach ($row['photos'] as $photoIndex) {
                $path = $this->importPhoto($plan['photos'][$photoIndex], $row['type']);

                if ($path !== null) {
                    $images[] = $path;
                    $paths[$photoIndex.'|'.$row['type']] = $path;
                }
            }

            if ($images === []) {
                continue;
            }

            $blocks[] = [
                'uid' => $row['uid'],
                'type' => $row['type'],
                'data' => $row['type'] === 'image' ? ['path' => $images[0]] : ['images' => $images],
            ];
        }

        $album = Album::create([
            'slug' => $item['slug'],
            'cover_path' => $paths[$plan['cover']['photo'].'|'.$plan['cover']['slot']] ?? null,
            'status' => PublishStatus::Published,
            'published_at' => Carbon::parse(self::ANCHOR)->subMinutes($position),
            'blocks' => $blocks,
        ]);

        foreach (Locales::ALL as $locale) {
            $album->translations()->create(['locale' => $locale, 'title' => $item['title'][$locale], 'excerpt' => null]);
        }

        return $album;
    }

    // ------------------------------------------------------------------ картинки

    /** @param  array{file: string, w: int, h: int}  $photo */
    private function importPhoto(array $photo, string $slot): ?string
    {
        $cacheKey = $photo['file'].'|'.$slot;

        if (isset($this->imported[$cacheKey]) && Storage::disk('uploads')->exists($this->imported[$cacheKey])) {
            return $this->imported[$cacheKey];
        }

        $path = $this->importThemeImage($photo['file'], $slot, $this->crop($photo, $slot));

        return $path === null ? null : $this->imported[$cacheKey] = $path;
    }

    /**
     * Прямоугольник, который вырезается из фото под слот. Шире нужного — режем по бокам от центра; выше нужного
     * (вертикальные снимки в горизонтальной плитке) — окно сдвинуто к верху кадра, чтобы не срезать лица.
     *
     * @param  array{file: string, w: int, h: int}  $photo
     * @return ?array{x: int, y: int, width: int, height: int}
     */
    private function crop(array $photo, string $slot): ?array
    {
        if ($photo['w'] <= 0 || $photo['h'] <= 0) {
            return null;
        }

        [$targetWidth, $targetHeight] = AspectRatio::dimensions($slot);
        $ratio = $targetWidth / $targetHeight;

        if ($photo['w'] / $photo['h'] >= $ratio) {
            $width = (int) round($photo['h'] * $ratio);

            return ['x' => intdiv($photo['w'] - $width, 2), 'y' => 0, 'width' => $width, 'height' => $photo['h']];
        }

        $height = (int) round($photo['w'] / $ratio);

        return ['x' => 0, 'y' => (int) round(($photo['h'] - $height) * self::FOCUS_Y), 'width' => $photo['w'], 'height' => $height];
    }

    /**
     * Возвращает с диска пропавшие картинки альбома: фото в рядах и обложку. Если файл на месте или у ряда нет
     * картинки в этой позиции (убрана редактором), ничего не меняется.
     *
     * @return int сколько картинок восстановлено
     */
    private function restoreImages(Album $album, array $item): int
    {
        $plan = $this->plan($item);
        $rows = collect($plan['rows'])->keyBy('uid');
        $blocks = $album->blocks ?? [];
        $replaced = [];

        foreach ($blocks as &$stored) {
            $row = $rows[$stored['uid'] ?? ''] ?? null;

            if ($row === null) {
                continue;
            }

            $key = $row['type'] === 'image' ? 'path' : 'images';
            $current = $row['type'] === 'image' ? [$stored['data']['path'] ?? null] : array_values((array) ($stored['data']['images'] ?? []));

            foreach ($row['photos'] as $position => $photoIndex) {
                $path = $current[$position] ?? null;

                if (blank($path) || Storage::disk('uploads')->exists($path)) {
                    continue;
                }

                $new = $this->importPhoto($plan['photos'][$photoIndex], $row['type']);

                if ($new !== null) {
                    $replaced[$path] = $new;
                    $current[$position] = $new;
                }
            }

            $stored['data'][$key] = $row['type'] === 'image' ? ($current[0] ?? null) : $current;
        }
        unset($stored);

        $cover = $album->cover_path;
        $restored = count($replaced);

        if (filled($cover) && isset($replaced[$cover])) {
            $cover = $replaced[$cover];
        } elseif (filled($cover) && ! Storage::disk('uploads')->exists($cover)) {
            $cover = $this->importPhoto($plan['photos'][$plan['cover']['photo']], $plan['cover']['slot']) ?? $cover;
            $restored++;
        }

        if ($replaced !== [] || $cover !== $album->cover_path) {
            $album->forceFill(['blocks' => $blocks, 'cover_path' => $cover])->save();
        }

        return $restored;
    }
}
