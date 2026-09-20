<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->string('cover_path')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->string('author')->nullable();
            $table->date('deadline_at')->nullable(); // «подать заявку до»
            $table->foreignId('tag_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_opportunities');
    }
};
