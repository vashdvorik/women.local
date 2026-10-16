<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Начальный контент публичного сайта, перенесённый из Blade-массивов в БД
 * (эксперты, новости). Безопасно запускать повторно: уже заведённое не трогается.
 *
 * Новости загружаются двумя сидерами: EventSeeder — карточки страницы «Новости», NewsArchiveSeeder — полные
 * новости организации (текст на трёх языках и фото). Второй дополняет первый, поэтому идёт после него.
 *
 *   php artisan db:seed --class=ContentSeeder
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ExpertSeeder::class,
            EventSeeder::class,
            NewsArchiveSeeder::class,
        ]);
    }
}
