<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique(); // временный вид album-xxxxxxxxxxxx
            $table->string('cover_path')->nullable();
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->dateTime('published_at')->nullable();
            $table->json('blocks')->nullable(); // фотографии общие для всех языков
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
