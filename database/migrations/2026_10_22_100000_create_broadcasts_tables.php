<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Рассылка сообщения участницам в Telegram из админки (App\Models\Broadcast).
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->json('texts');                                   // {ru, en, ro}: текст сообщения на языках
            $table->json('buttons')->nullable();                     // [{url, label: {ru, en, ro}}], до 3 кнопок-ссылок
            $table->string('audience', 16);                          // approved | members | open
            $table->string('photo_path')->nullable();                // фото на закрытом диске originals
            $table->string('photo_file_id')->nullable();             // file_id Telegram после первой отправки фото
            $table->string('status', 16)->default('draft');          // draft | scheduled | sending | sent | cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sent')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->unsignedInteger('blocked')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });

        // По строке на получательницу: отчёт «кому дошло» и защита от повторной отправки при перезапуске задания.
        Schema::create('broadcast_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('bot_user_id')->nullable();
            $table->bigInteger('telegram_id');
            $table->string('locale', 5)->default('ru');
            $table->string('status', 12)->default('pending');        // pending | sent | failed | blocked
            $table->string('error', 500)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['broadcast_id', 'telegram_id']);
            $table->index(['broadcast_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_deliveries');
        Schema::dropIfExists('broadcasts');
    }
};
