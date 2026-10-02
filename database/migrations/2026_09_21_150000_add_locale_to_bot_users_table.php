<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Язык участницы для сообщений бота (ru / en / ro). Берётся из языка Telegram при заявке и обновляется,
     * когда она пишет боту. Нужен там, где сообщение уходит не в ответ на её слова: решение по заявке,
     * рассылка о публикациях, вход с сайта. Пусто — русский.
     */
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->string('locale', 5)->nullable()->after('telegram_username');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
