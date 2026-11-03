<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Блокировка участницы запоминает, какой была отметка «видима другим» до неё, чтобы «Разблокировать» вернула её же,
 * а не одобряла всех подряд.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->string('status_before_block', 16)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->dropColumn('status_before_block');
        });
    }
};
