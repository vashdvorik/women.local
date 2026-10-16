<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Сжимает фотографии альбомов (папка dizayn/foto/{папка альбома}/*.jpg) в WebP и кладёт их в папку темы:
 * public/themes/public/miro/images/albums/{адрес альбома}/01.webp, 02.webp … (по порядку имён файлов).
 *
 * Это исходники для сидера AlbumArchiveSeeder, как фото архива новостей: их хранит git, а сидер на сервере прогоняет
 * каждую через тот же конвейер, что и загрузка в админке (кадр по слоту → WebP), и кладёт результат в public/uploads.
 * Перечень альбомов, их папки и адреса — в database/seeders/data/albums_archive.php.
 *
 * Промежуточная копия сжимается слабо (длинная сторона до 2048 px, качество 85): из неё режется кадр для сайта и она же
 * становится «целым фото» для кнопки «Изменить кадр» и для просмотра в полном размере (подробности — в
 * CompressNewsArchive). Запускается на машине разработчика, на сервере не нужна.
 *
 *   php artisan albums:compress-archive
 *   php artisan albums:compress-archive --from=dizayn/foto --max-side=2048 --quality=85 --force
 */
class CompressAlbumsArchive extends Command
{
    protected $signature = 'albums:compress-archive
                            {--from=dizayn/foto : Папка с папками альбомов (относительно корня проекта или абсолютная)}
                            {--max-side=2048 : Самая длинная сторона, px}
                            {--quality=85 : Качество WebP, 1–100}
                            {--force : Пересоздать и те файлы, что уже есть}';

    protected $description = 'Сжимает фото фотоальбомов в WebP для сидера AlbumArchiveSeeder';

    public function handle(): int
    {
        $from = (string) $this->option('from');
        $from = (str_starts_with($from, '/') || preg_match('~^[A-Za-z]:[\\\\/]~', $from)) ? $from : base_path($from);
        $maxSide = max(400, min(4000, (int) $this->option('max-side')));
        $quality = max(1, min(100, (int) $this->option('quality')));

        if (! is_dir($from)) {
            $this->error("Папка с альбомами не найдена: {$from}");

            return self::FAILURE;
        }

        $albums = (array) require database_path('seeders/data/albums_archive.php');
        $target = public_path('themes/public/miro/images/albums');
        $manager = ImageManager::usingDriver(new Driver());

        $made = 0;
        $kept = 0;
        $missing = [];
        $bytesBefore = 0;
        $bytesAfter = 0;

        foreach ($albums as $album) {
            $folder = $from.'/'.$album['folder'];
            $sources = is_dir($folder) ? $this->sources($folder) : [];

            if ($sources === []) {
                $missing[] = $album['folder'];

                continue;
            }

            foreach ($sources as $index => $source) {
                $file = "{$target}/{$album['slug']}/".str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'.webp';
                $bytesBefore += filesize($source);

                if (! $this->option('force') && is_file($file)) {
                    $kept++;
                    $bytesAfter += filesize($file);

                    continue;
                }

                if (! is_dir(dirname($file))) {
                    mkdir(dirname($file), 0775, true);
                }

                // Содержимое, а не путь: в путях к папкам есть кириллица, и такой путь декодер за файл не принимает.
                $encoded = $manager->decode((string) file_get_contents($source))
                    ->scaleDown(width: $maxSide, height: $maxSide)
                    ->encode(new WebpEncoder(quality: $quality));
                $encoded->save($file);

                // Размер берём у закодированного изображения: filesize() сразу после записи может вернуть старое значение.
                $bytesAfter += $encoded->size();
                $made++;
            }
        }

        $mb = fn (int $bytes): string => number_format($bytes / 1048576, 1, ',', ' ').' МБ';

        $this->info("Фото альбомов: сжато {$made}, уже были {$kept}. Было {$mb($bytesBefore)}, стало {$mb($bytesAfter)}.");

        if ($missing !== []) {
            $this->warn('Нет фото в папках: '.implode(', ', $missing).'.');
        }

        return self::SUCCESS;
    }

    /** Файлы-картинки папки в порядке имён («2.jpg» раньше «10.jpg»). @return list<string> */
    private function sources(string $folder): array
    {
        $files = [];

        foreach (scandir($folder) ?: [] as $name) {
            if (preg_match('/\.(jpe?g|jfif|png|webp)$/i', $name) && is_file($folder.'/'.$name)) {
                $files[] = $name;
            }
        }

        usort($files, 'strnatcasecmp');

        return array_map(fn (string $name) => $folder.'/'.$name, $files);
    }
}
