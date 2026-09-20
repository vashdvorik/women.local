<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('type', 120)->nullable();            // «Обучение», «Нетворкинг»
            $table->string('date_label', 120)->nullable();      // замена даты: «Открыт набор»
            $table->string('title', 191)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_translations');
    }
};
