<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Сколько раз пробовали отправить: временные сбои (сеть, 5xx Telegram) повторяются до трёх раз, и только потом «ошибка».
        Schema::table('broadcast_deliveries', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0)->after('status');
        });

        // Почему рассылка на паузе (Telegram не отвечает, токен не принят): показывается на странице рассылки и сам очищается.
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->string('last_error', 500)->nullable()->after('blocked');
        });
    }

    public function down(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->dropColumn('last_error');
        });

        Schema::table('broadcast_deliveries', function (Blueprint $table) {
            $table->dropColumn('attempts');
        });
    }
};
