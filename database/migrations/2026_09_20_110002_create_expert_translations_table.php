<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 191)->nullable();
            $table->string('role', 255)->nullable();            // должность
            $table->string('specialization', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('looking_for')->nullable();            // что ищет
            $table->text('can_offer')->nullable();              // чем может быть полезна
            $table->json('tags')->nullable();                   // короткие ярлыки
            $table->timestamps();

            $table->unique(['expert_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_translations');
    }
};
