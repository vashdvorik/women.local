<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_opportunity_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_opportunity_id')->constrained('site_opportunities')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title', 191)->nullable();
            $table->text('excerpt')->nullable();
            $table->json('content')->nullable();
            $table->string('seo_title', 191)->nullable();
            $table->string('seo_description', 240)->nullable();
            $table->timestamps();

            $table->unique(['site_opportunity_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_opportunity_translations');
    }
};
