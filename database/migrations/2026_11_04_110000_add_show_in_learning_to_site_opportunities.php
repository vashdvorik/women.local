<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Карточку из «Возможностей» можно показать ещё и в разделе кабинета «Обучение» (show_in_learning).
     * Уже созданные возможности остаются там, где были: в обучение они не попадают, пока редактор не поставит галочку.
     */
    public function up(): void
    {
        Schema::table('site_opportunities', function (Blueprint $table) {
            $table->boolean('show_in_learning')->default(false)->after('show_in_cabinet');
        });
    }

    public function down(): void
    {
        Schema::table('site_opportunities', function (Blueprint $table) {
            $table->dropColumn('show_in_learning');
        });
    }
};
