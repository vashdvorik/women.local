<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ручной порядок альбомов (в админке: перетаскивание за значок слева). Меньше число — выше на сайте. Число может быть
        // отрицательным: новый альбом встаёт первым со значением «наименьшее минус один».
        Schema::table('albums', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('slug')->index();
        });

        // Сначала сохраняем порядок, который был на сайте до этого: новые по дате публикации выше, без даты в конце.
        $ids = DB::table('albums')
            ->orderByRaw('published_at is null')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($ids as $index => $id) {
            DB::table('albums')->where('id', $id)->update(['position' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn('position');
        });
    }
};
