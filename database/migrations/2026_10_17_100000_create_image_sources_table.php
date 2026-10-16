<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Сжатое фото целиком (без обрезки) и рамка кадра для каждой картинки сайта. Благодаря этому кадр можно
 * изменить после загрузки: готовый кадр на сайте пересчитывается из сохранённого фото.
 *
 * Строка привязана к пути готового кадра (то, что лежит в записях и блоках страниц), поэтому ни одна таблица
 * с картинками не меняется. У картинок, загруженных до этой таблицы, строки нет: оригинала у них не осталось.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_sources', function (Blueprint $table) {
            $table->id();
            // Готовый кадр на сайте: «2026/10/abc123.webp» на диске uploads.
            $table->string('path')->unique();
            // Сжатое фото целиком: «2026/10/xyz789.webp» на закрытом диске originals.
            $table->string('source_path');
            $table->string('slot', 32);
            $table->unsignedInteger('source_width');
            $table->unsignedInteger('source_height');
            // Рамка кадра в пикселях сохранённого фото.
            $table->unsignedInteger('crop_x');
            $table->unsignedInteger('crop_y');
            $table->unsignedInteger('crop_width');
            $table->unsignedInteger('crop_height');
            $table->timestamps();

            $table->index('source_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_sources');
    }
};
