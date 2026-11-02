<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Почта для связи: её участница может указать в анкете, и тогда другим показывается она. Не указала: показывается
     * почта, с которой она зарегистрировалась (bot_users.email). Вход и письма платформы идут на регистрационную почту.
     */
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->string('contact_email', 191)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn('contact_email');
        });
    }
};
