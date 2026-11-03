<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Учёт активности для раздела «Статистика»:
     *  - cabinet_activity: сколько раз участница открыла раздел кабинета или сделала действие (вопрос, поиск, инициатива …)
     *    за день; по строке на участницу, день и ключ, чтобы таблица росла медленно;
     *  - bot_users.last_seen_at: когда участница последний раз была в кабинете (для «кто перестал заходить»);
     *  - broadcast_deliveries.token / clicks / first_clicked_at и broadcast_clicks: клики по кнопкам рассылки через ссылку-переходник.
     *    Прочитано ли сообщение, Telegram боту не сообщает, поэтому показываем только доставлено, блок и клики.
     */
    public function up(): void
    {
        Schema::create('cabinet_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->date('day');
            $table->string('key', 40);                       // раздел (matches, search, …) или действие (manager_question, …)
            $table->unsignedInteger('hits')->default(1);
            $table->timestamps();

            $table->unique(['bot_user_id', 'day', 'key']);
            $table->index(['day', 'key']);
        });

        Schema::table('bot_users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('cabinet_opened_at')->index();
        });

        Schema::table('broadcast_deliveries', function (Blueprint $table) {
            $table->string('token', 32)->nullable()->unique()->after('telegram_id');
            $table->unsignedSmallInteger('clicks')->default(0)->after('sent_at');
            $table->timestamp('first_clicked_at')->nullable()->after('clicks');
        });

        Schema::create('broadcast_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broadcast_delivery_id')->constrained('broadcast_deliveries')->cascadeOnDelete();
            $table->unsignedBigInteger('bot_user_id')->nullable()->index();
            $table->unsignedTinyInteger('button');           // номер кнопки в рассылке, с нуля
            $table->timestamp('clicked_at');

            $table->index(['broadcast_id', 'button']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_clicks');

        Schema::table('broadcast_deliveries', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn(['token', 'clicks', 'first_clicked_at']);
        });

        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn('last_seen_at');
        });

        Schema::dropIfExists('cabinet_activity');
    }
};
