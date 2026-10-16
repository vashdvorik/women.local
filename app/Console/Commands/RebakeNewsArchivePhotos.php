<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\NewsArchiveSeeder;
use Illuminate\Console\Command;

/**
 * Переделывает фото уже загруженных новостей из архива заново: из нынешних исходников темы и с нынешним качеством
 * из «Настроек сайта». Обычный запуск сидера готовые картинки не трогает, поэтому, если исходники или качество
 * поменялись, фото на сайте остаются прежними: для них и нужна эта команда. Подробности — в NewsArchiveSeeder::rebakePhotos().
 *
 *   php artisan news:rebake-archive-photos
 *
 * Фото, замененные в админке, и кадры, перекадрированные там кнопкой «Изменить кадр», не затрагиваются.
 */
class RebakeNewsArchivePhotos extends Command
{
    protected $signature = 'news:rebake-archive-photos';

    protected $description = 'Переделывает фото загруженных новостей из архива из нынешних исходников и с нынешним качеством';

    public function handle(): int
    {
        $seeder = $this->laravel->make(NewsArchiveSeeder::class);
        $seeder->setCommand($this);

        $count = $seeder->rebakePhotos();

        $this->info("Фото новостей переделано: {$count}.");

        return self::SUCCESS;
    }
}
