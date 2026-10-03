<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * История подписок участницы: какой тариф, с какого дня по какой и откуда он взялся (оплата в банке или
     * ручная выдача в админке). Текущий тариф берётся отсюда и копируется в bot_users.plan / plan_ends_at.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            // Внешние ключи работают только между таблицами InnoDB; на некоторых хостингах по умолчанию MyISAM.
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('plan', 20);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('source', 10);
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['bot_user_id', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
