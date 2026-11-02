<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Новость можно показать и в кабинете участниц: в «Возможностях» (show_in_cabinet) и в «Обучении» (show_in_learning).
     * На открытом сайте она остаётся (show_on_site), если редактор не снял галочку. Старые новости остаются как были:
     * видны на сайте, в кабинет не попадают.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('show_on_site')->default(true)->after('status');
            $table->boolean('show_in_cabinet')->default(false)->after('show_on_site');
            $table->boolean('show_in_learning')->default(false)->after('show_in_cabinet');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['show_on_site', 'show_in_cabinet', 'show_in_learning']);
        });
    }
};
