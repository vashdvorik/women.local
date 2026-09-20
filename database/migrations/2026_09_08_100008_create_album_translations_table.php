<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('album_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title', 191)->nullable();
            $table->text('excerpt')->nullable();
            $table->timestamps();

            $table->unique(['album_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('album_translations');
    }
};
