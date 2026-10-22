<?php

namespace Database\Seeders;

use App\Models\EventTranslation;
use App\Support\PublicCache;
use Illuminate\Database\Seeder;

/**
 * Убирает плашки («Новости», «Конференция», «Нетворкинг»…) со всех новостей, уже загруженных в базу: очищает поле
 * «Плашка (тип)» на всех трёх языках. Сами новости, тексты, фото и цвета карточек не меняются.
 *
 *   php artisan db:seed --class=NewsTagsClearSeeder --force
 *
 * Запускается вручную и сознательно не входит в ContentSeeder: иначе каждый деплой стирал бы плашки, которые потом
 * поставили в админке. Новые загрузки сидерами (EventSeeder, NewsArchiveSeeder) плашки уже не ставят. Повторный запуск
 * безопасен: убирать нечего, пока плашки не поставили снова.
 */
class NewsTagsClearSeeder extends Seeder
{
    public function run(): void
    {
        $cleared = EventTranslation::query()->whereNotNull('type')->update(['type' => null]);

        // Массовое обновление не вызывает события моделей, и кеш публичных страниц сам не сбросился бы.
        PublicCache::flush();

        $this->command?->info("Плашки убраны у переводов новостей: {$cleared}.");
    }
}
