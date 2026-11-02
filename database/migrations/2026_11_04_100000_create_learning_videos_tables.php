<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Обучающие видео для кабинета участницы (раздел «Обучение»). Не связаны с видео на открытом сайте (таблица videos).
        Schema::create('learning_videos', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_id')->unique();
            $table->string('youtube_url');
            $table->string('cover_path')->nullable(); // без неё берётся миниатюра YouTube
            $table->integer('position')->default(0);  // ручная сортировка
            $table->boolean('is_published')->default(true); // выключено: видео в черновике, участницы его не видят
            $table->timestamps();

            $table->index(['is_published', 'position']);
        });

        Schema::create('learning_video_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_video_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['learning_video_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_video_translations');
        Schema::dropIfExists('learning_videos');
    }
};
