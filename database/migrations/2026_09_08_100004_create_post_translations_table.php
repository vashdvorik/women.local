<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title', 191)->nullable();
            $table->text('excerpt')->nullable();
            $table->json('content')->nullable(); // последовательность блоков
            $table->string('seo_title', 191)->nullable();
            $table->string('seo_description', 240)->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_translations');
    }
};
