<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Платежи за подписку через Web-платёж Агропромбанка. Одна строка — один счёт (invoice) в банке.
     *
     * Строка остаётся, даже если участница удалила профиль (bot_user_id обнуляется), поэтому telegram_id хранится
     * отдельно: платёжная история не должна пропадать вместе с профилем.
     *
     * status: pending (ждём оплату), verifying (банк сообщил об оплате, ждём подтверждения через GetState),
     * paid, failed, cancelled, expired.
     * amount — копейки, как в запросе к банку (RequestSum).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            // Внешние ключи работают только между таблицами InnoDB; на некоторых хостингах по умолчанию MyISAM.
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('bot_user_id')->nullable()->constrained('bot_users')->nullOnDelete();
            $table->unsignedBigInteger('telegram_id')->index();
            $table->string('plan', 20);
            $table->unsignedSmallInteger('months');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 8);
            $table->string('invoice_id', 20)->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('is_test')->default(true);
            $table->string('driver', 10);
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->string('rrn', 40)->nullable();
            $table->string('last_digits', 4)->nullable();
            $table->unsignedTinyInteger('bank_state')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
