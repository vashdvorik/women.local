<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Рассылка, созданная из новости, публикации или возможности галочкой «Создать черновик для рассылки»:
        // по этой паре находим уже созданные черновики и не плодим второй на тот же материал.
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('created_by');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');

            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
