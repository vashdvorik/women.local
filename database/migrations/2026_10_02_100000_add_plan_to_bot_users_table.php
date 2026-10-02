<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Текущий тариф участницы (App\Enums\Plan) и дата, до которой он действует. Это «срез» для быстрой проверки
     * доступа на каждом запросе; история лежит в таблице subscriptions и пересчитывается сервисом
     * App\Services\Subscriptions\SubscriptionService.
     *
     * Все, кто уже есть в базе, получают Open: платный доступ включается оплатой или вручную в админке.
     * plan_notified_stage — какое напоминание об окончании уже отправлено (0 — никакое, 1 — «скоро», 2 — «считаные
     * дни», 3 — «закончилась»); сбрасывается при каждой активации.
     */
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->string('plan', 20)->default('open')->after('status');
            $table->timestamp('plan_ends_at')->nullable()->after('plan');
            $table->unsignedTinyInteger('plan_notified_stage')->default(0)->after('plan_ends_at');

            $table->index(['plan', 'plan_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropIndex(['plan', 'plan_ends_at']);
            $table->dropColumn(['plan', 'plan_ends_at', 'plan_notified_stage']);
        });
    }
};
