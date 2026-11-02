<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Переписка участниц с AI-менеджером кабинета. Каждая реплика — строка: role = user или assistant. У ответа
     * (assistant) сохраняется и сам вопрос (question), чтобы админ видел пару целиком, и отметка answered: false, когда
     * менеджер не нашёл ответа (или провайдер AI был недоступен). handled_at — админ разобрал такой вопрос.
     */
    public function up(): void
    {
        Schema::create('manager_messages', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('role', 12);
            $table->text('content');
            $table->text('question')->nullable();
            $table->string('plan', 20)->nullable();
            $table->boolean('answered')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['bot_user_id', 'id']);
            $table->index(['answered', 'handled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_messages');
    }
};
