<?php

namespace Database\Seeders;

use App\Support\PublicCache;
use Illuminate\Database\Seeder;

/**
 * Начальный контент публичного сайта, перенесённый из Blade-массивов в БД
 * (эксперты, новости). Безопасно запускать повторно: уже заведённое не трогается.
 *
 * Новости загружаются двумя сидерами: EventSeeder — карточки страницы «Новости», NewsArchiveSeeder — полные
 * новости организации (текст на трёх языках и фото). Второй дополняет первый, поэтому идёт после него.
 * AlbumArchiveSeeder заводит фотоальбомы организации (страница «Фото»), VideoArchiveSeeder — видео (страница «Видео»).
 * ExpertRosterSeeder дополняет ExpertSeeder: добавляет экспертов, которых в базе ещё нет, и меняет фото, помеченное
 * в данных (на пустой базе ExpertSeeder заводит всех сам, и дополнять нечего).
 *
 *   php artisan db:seed --class=ContentSeeder
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ExpertSeeder::class,
            ExpertRosterSeeder::class,
            EventSeeder::class,
            NewsArchiveSeeder::class,
            ProjectSeeder::class,
            AlbumArchiveSeeder::class,
            VideoArchiveSeeder::class,
        ]);

        // Под DatabaseSeeder события моделей отключены: кеш публичных страниц сбрасываем явно.
        PublicCache::flush();
    }
}
