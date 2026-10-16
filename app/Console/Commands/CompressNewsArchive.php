<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Сжимает фотографии подготовленных новостей (папка с постами: dizayn/women/{номер}_post-…/image-01.jpg …)
 * в WebP и кладёт их в папку темы: public/themes/public/miro/images/news/archive/{номер}/01.webp …
 *
 * Это исходники для сидера NewsArchiveSeeder, как фото прежних новостей в images/news: их хранит git,
 * а сидер на сервере прогоняет каждую через тот же конвейер, что и загрузка в админке (кадр по слоту → WebP),
 * и кладёт результат в public/uploads. Исходники самих постов (десятки мегабайт) в git не нужны.
 *
 * Берутся только посты, для которых есть файл данных (database/seeders/data/news_archive/{номер}.php): убранная
 * новость не возвращается при повторном запуске.
 *
 * Здесь картинка только уменьшается (длинная сторона не больше --max-side: итоговые кадры на сайте всё равно не шире
 * 1280 px) и пережимается; кадрирует её позже сидер. Запускается на машине разработчика, на сервере не нужна.
 *
 *   php artisan news:compress-archive
 *   php artisan news:compress-archive --from=dizayn/women --max-side=1400 --quality=76 --force
 */
class CompressNewsArchive extends Command
{
    protected $signature = 'news:compress-archive
                            {--from=dizayn/women : Папка с постами (относительно корня проекта или абсолютная)}
                            {--max-side=1400 : Самая длинная сторона, px}
                            {--quality=76 : Качество WebP, 1–100}
                            {--force : Пересоздать и те файлы, что уже есть}';

    protected $description = 'Сжимает фото подготовленных новостей в WebP для сидера NewsArchiveSeeder';

    public function handle(): int
    {
        $from = (string) $this->option('from');
        $from = (str_starts_with($from, '/') || preg_match('~^[A-Za-z]:[\\\\/]~', $from)) ? $from : base_path($from);
        $maxSide = max(400, min(4000, (int) $this->option('max-side')));
        $quality = max(1, min(100, (int) $this->option('quality')));

        if (! is_dir($from)) {
            $this->error("Папка с постами не найдена: {$from}");

            return self::FAILURE;
        }

        $target = public_path('themes/public/miro/images/news/archive');
        $manager = ImageManager::usingDriver(new Driver());

        $made = 0;
        $kept = 0;
        $skipped = 0;
        $bytesBefore = 0;
        $bytesAfter = 0;

        $posts = glob($from.'/*_post-*', GLOB_ONLYDIR) ?: [];
        sort($posts);

        foreach ($posts as $post) {
            $key = substr(basename($post), 0, 3);

            // Новость без файла данных в архив не входит (её убрали): фото для неё не нужны.
            if (! is_file(database_path("seeders/data/news_archive/{$key}.php"))) {
                $skipped++;

                continue;
            }

            foreach (glob($post.'/image-*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [] as $source) {
                if (! preg_match('/image-(\d+)\./', basename($source), $m)) {
                    continue;
                }

                $file = "{$target}/{$key}/".str_pad($m[1], 2, '0', STR_PAD_LEFT).'.webp';
                $bytesBefore += filesize($source);

                if (! $this->option('force') && is_file($file)) {
                    $kept++;
                    $bytesAfter += filesize($file);

                    continue;
                }

                if (! is_dir(dirname($file))) {
                    mkdir(dirname($file), 0775, true);
                }

                $encoded = $manager->decode($source)
                    ->scaleDown(width: $maxSide, height: $maxSide)
                    ->encode(new WebpEncoder(quality: $quality));
                $encoded->save($file);

                // Размер берём у закодированного изображения: filesize() сразу после записи может вернуть старое значение.
                $bytesAfter += $encoded->size();
                $made++;
            }
        }

        $mb = fn (int $bytes): string => number_format($bytes / 1048576, 1, ',', ' ').' МБ';

        $this->info("Фото новостей: сжато {$made}, уже были {$kept}. Было {$mb($bytesBefore)}, стало {$mb($bytesAfter)}.");

        if ($skipped > 0) {
            $this->line("Постов без файла данных (пропущены): {$skipped}.");
        }

        return self::SUCCESS;
    }
}
