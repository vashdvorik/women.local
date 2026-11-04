<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Код подтверждения почты (5 цифр) рядом со ссылкой из письма: в базе только его хеш, срок действия и число неверных
 * попыток. Код вводят на странице «Подтвердите почту» там же, где регистрировались, не переходя по ссылке с другого устройства.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->string('email_code_hash', 64)->nullable()->after('email_verified_at');
            $table->timestamp('email_code_expires_at')->nullable()->after('email_code_hash');
            $table->unsignedTinyInteger('email_code_attempts')->default(0)->after('email_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table): void {
            $table->dropColumn(['email_code_hash', 'email_code_expires_at', 'email_code_attempts']);
        });
    }
};
