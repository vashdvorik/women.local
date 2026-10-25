<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Страница проекта: адрес (slug) и текст страницы блоками (как у публикаций), по языкам.
 * У проектов без страницы (только карточка) оба поля пусты, карточка работает как раньше.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->unique()->after('id');
        });

        Schema::table('project_translations', function (Blueprint $table) {
            $table->json('content')->nullable()->after('text');
        });
    }

    public function down(): void
    {
        Schema::table('project_translations', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
