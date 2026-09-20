<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('image_path')->nullable();          // обложка, слот event (16:9)
            $table->string('tone', 16)->default('pink');       // цвет подложки карточки
            $table->date('starts_at')->nullable();             // дата начала; без неё показывается только плашка
            $table->string('url', 2000)->nullable();           // «Подробнее»
            $table->boolean('is_published')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
