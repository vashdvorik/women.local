<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->json('embedding')->nullable()->after('expectation');
            $table->timestamp('embedding_updated_at')->nullable()->after('embedding');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn(['embedding', 'embedding_updated_at']);
        });
    }
};
