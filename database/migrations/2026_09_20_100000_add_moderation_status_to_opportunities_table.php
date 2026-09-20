<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Премодерация постов участниц из кабинета: новая запись ждёт решения админа
 * (`pending`), одобренная показывается всем (`approved`), отклонённая видна
 * только автору (`rejected`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->string('status', 16)->default('pending')->after('contact_url')->index();
            $table->timestamp('moderated_at')->nullable()->after('status');
        });

        // Всё, что уже опубликовано до появления модерации, остаётся видимым.
        DB::table('opportunities')->update(['status' => 'approved', 'moderated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'moderated_at']);
        });
    }
};
