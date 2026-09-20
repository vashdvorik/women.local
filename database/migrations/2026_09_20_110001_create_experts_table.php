<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->string('photo_path')->nullable();          // портрет, слот expert (1:1)
            $table->string('tone', 16)->default('pink');       // цвет подложки карточки
            $table->boolean('is_published')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experts');
    }
};
