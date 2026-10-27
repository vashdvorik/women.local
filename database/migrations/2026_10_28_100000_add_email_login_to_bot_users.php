<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Участница теперь регистрируется по почте, а Telegram — необязательное подключение для уведомлений.
     *
     *  - telegram_id становится необязательным (подключается позже по ссылке из кабинета);
     *  - почта, пароль и вход через Google, телефон и отметка «анкета заполнена»;
     *  - бесплатного тарифа больше нет: «open» теперь платный тариф за 600, а «нет тарифа» — это 'none'.
     *    Все, кто хранил прежний бесплатный 'open', получают 'none';
     *  - заявки на закрытые тарифы (plan_requests) и одноразовые ссылки подключения Telegram (telegram_links).
     *
     * Платежи не теряются вместе с участницей, поэтому telegram_id в payments тоже необязателен: у участницы без
     * Telegram его просто нет.
     */
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->bigInteger('telegram_id')->nullable()->change();
            $table->string('email', 191)->nullable()->unique()->after('telegram_username');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('password')->nullable()->after('email_verified_at');
            $table->string('google_id', 64)->nullable()->unique()->after('password');
            $table->string('phone', 32)->nullable()->after('google_id');
            $table->timestamp('profile_completed_at')->nullable()->after('phone');
            $table->rememberToken();
        });

        Schema::table('bot_users', function (Blueprint $table) {
            $table->string('plan', 20)->default('none')->change();
        });

        DB::table('bot_users')->where('plan', 'open')->update(['plan' => 'none']);

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('telegram_id')->nullable()->change();
        });

        // Цены, заданные в админке для прежней схемы (Community был за 600), больше не подходят: Community теперь
        // 5000, а 600 стоит новый Open. Цена Private («19 000, как сейчас») остаётся, какой её задали.
        $stored = DB::table('site_settings')->where('key', 'subscription_prices')->first();

        if ($stored !== null) {
            $prices = json_decode((string) $stored->value, true);
            $prices = is_array($prices) ? $prices : [];
            $prices['open'] = 600;
            $prices['community'] = 5000;
            $prices['private'] = max((int) ($prices['private'] ?? 19000), 5000);

            DB::table('site_settings')->where('key', 'subscription_prices')->update(['value' => json_encode($prices)]);
            Cache::forget('subscription_prices');
        }

        Schema::create('participant_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Заявка участницы на закрытый тариф. status: new (ждёт звонка), contacted (менеджер позвонил), unlocked (кнопка
        // «Купить» открыта этой участнице), purchased (оплатила), declined (менеджер закрыл заявку).
        Schema::create('plan_requests', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('plan', 20);
            $table->string('status', 20)->default('new');
            $table->text('comment')->nullable();
            $table->text('manager_note')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
            $table->index(['bot_user_id', 'plan']);
            $table->index('status');
        });

        // Одноразовая ссылка t.me/<бот>?start=link_<token>: так участница подключает Telegram для уведомлений.
        Schema::create('telegram_links', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_links');
        Schema::dropIfExists('plan_requests');
        Schema::dropIfExists('participant_password_reset_tokens');

        DB::table('bot_users')->where('plan', 'none')->update(['plan' => 'open']);

        Schema::table('bot_users', function (Blueprint $table) {
            $table->string('plan', 20)->default('open')->change();
            $table->dropUnique(['email']);
            $table->dropUnique(['google_id']);
            $table->dropColumn(['email', 'email_verified_at', 'password', 'google_id', 'phone', 'profile_completed_at', 'remember_token']);
        });
    }
};
