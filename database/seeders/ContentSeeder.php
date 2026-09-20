<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Начальный контент публичного сайта, перенесённый из Blade-массивов в БД
 * (эксперты, новости). Безопасно запускать повторно: уже заведённое не трогается.
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
        ]);
    }
}
