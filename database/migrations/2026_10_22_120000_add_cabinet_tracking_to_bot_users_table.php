<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Заходила ли участница в кабинет (первый вход) и напоминали ли ей об этом: напоминание уходит один раз тем,
        // кто одобрен, но так и не открыл кабинет (artisan bot:remind-cabinet).
        Schema::table('bot_users', function (Blueprint $table) {
            $table->timestamp('cabinet_opened_at')->nullable()->after('approved_at');
            $table->timestamp('cabinet_reminded_at')->nullable()->after('cabinet_opened_at');
        });

        // Те, кого одобрили до появления напоминаний, о кабинете уже слышали: им напоминание не нужно, иначе первый же
        // запуск написал бы разом всем, кто раньше не заходил.
        DB::table('bot_users')->where('status', 'approved')->update(['cabinet_reminded_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn(['cabinet_opened_at', 'cabinet_reminded_at']);
        });
    }
};
