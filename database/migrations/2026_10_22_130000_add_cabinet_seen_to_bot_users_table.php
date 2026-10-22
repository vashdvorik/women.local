<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Какие разделы кабинета участница уже открывала (['matches', 'search', …]): по ним на главной кабинета
        // ставятся галочки в списке «С чего начать».
        Schema::table('bot_users', function (Blueprint $table) {
            $table->json('cabinet_seen')->nullable()->after('cabinet_reminded_at');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn('cabinet_seen');
        });
    }
};
