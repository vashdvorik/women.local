<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('pages'); // пути страниц-картинок (диск uploads), по порядку
            $table->decimal('page_ratio', 6, 4)->default(0.7071); // ширина / высота страницы (A4 по умолчанию)
            $table->string('cover_path')->nullable(); // без неё обложка — первая страница
            $table->string('pdf_path')->nullable(); // исходный PDF для кнопки «Скачать»
            $table->boolean('allow_download')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('catalog_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['catalog_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_translations');
        Schema::dropIfExists('catalogs');
    }
};
