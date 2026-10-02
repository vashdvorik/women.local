<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Страницы новостей: у новости появляется адрес (`slug`) и текст из блоков на каждом языке
 * (`event_translations.content`, тот же формат, что у публикаций). Пока текста нет, карточка
 * новости ведёт по прежней внешней ссылке.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->unique()->after('id');
        });

        Schema::table('event_translations', function (Blueprint $table) {
            $table->json('content')->nullable()->after('description');
        });

        // Адрес для уже заведённых новостей: из русского названия, уникальный.
        $used = [];

        foreach (DB::table('events')->orderBy('id')->pluck('id') as $id) {
            $title = (string) DB::table('event_translations')
                ->where('event_id', $id)->where('locale', 'ru')->value('title');

            $base = trim(mb_substr(Str::slug($title, '-', 'ru'), 0, 100), '-') ?: 'news-'.$id;
            $slug = $base;

            for ($i = 2; in_array($slug, $used, true); $i++) {
                $slug = $base.'-'.$i;
            }

            $used[] = $slug;
            DB::table('events')->where('id', $id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('event_translations', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
