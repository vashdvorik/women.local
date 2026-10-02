<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Миграция страниц новостей добавляет адрес и текст, а новостям, которые уже есть в базе (на рабочем сервере
 * они заведены раньше), сразу строит адреса: без них карточки не смогли бы ссылаться на свои страницы.
 */
class NewsAddressMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = 'database/migrations/2026_09_21_120000_add_slug_and_content_to_events.php';

    public function test_existing_news_get_unique_readable_addresses(): void
    {
        // Возвращаем базу к состоянию до миграции: новости есть, адреса и текста у них ещё нет.
        $this->artisan('migrate:rollback', ['--path' => self::MIGRATION])->assertSuccessful();
        $this->assertFalse(Schema::hasColumn('events', 'slug'));
        $this->assertFalse(Schema::hasColumn('event_translations', 'content'));

        $now = now()->toDateTimeString();
        foreach ([1, 2, 3, 4] as $id) {
            DB::table('events')->insert(['id' => $id, 'tone' => 'pink', 'is_published' => 1, 'position' => $id, 'created_at' => $now, 'updated_at' => $now]);
        }
        DB::table('event_translations')->insert([
            ['event_id' => 1, 'locale' => 'ru', 'title' => 'Белый шум', 'created_at' => $now, 'updated_at' => $now],
            ['event_id' => 2, 'locale' => 'ru', 'title' => 'Белый шум', 'created_at' => $now, 'updated_at' => $now],  // то же название
            ['event_id' => 3, 'locale' => 'ru', 'title' => '!!!', 'created_at' => $now, 'updated_at' => $now],       // из названия адрес не построить
            // у четвёртой новости русского названия нет вовсе
            ['event_id' => 4, 'locale' => 'en', 'title' => 'Only English', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->artisan('migrate', ['--path' => self::MIGRATION])->assertSuccessful();

        $slugs = DB::table('events')->orderBy('id')->pluck('slug', 'id')->all();

        $this->assertSame([1 => 'belyy-shum', 2 => 'belyy-shum-2', 3 => 'news-3', 4 => 'news-4'], $slugs);
        $this->assertTrue(Schema::hasColumn('event_translations', 'content'));
    }
}
