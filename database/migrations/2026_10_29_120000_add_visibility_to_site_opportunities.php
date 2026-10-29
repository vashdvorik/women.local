<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * «Возможности» из админки теперь можно показывать по-разному: на открытом сайте (show_on_site) и в кабинете
     * участниц (show_in_cabinet). Часть возможностей открыта всем, а большая часть только участницам.
     * Все уже созданные возможности остаются видимыми и там и там.
     */
    public function up(): void
    {
        Schema::table('site_opportunities', function (Blueprint $table) {
            $table->boolean('show_on_site')->default(true)->after('status');
            $table->boolean('show_in_cabinet')->default(true)->after('show_on_site');
        });

        DB::table('site_opportunities')->update(['show_on_site' => true, 'show_in_cabinet' => true]);
    }

    public function down(): void
    {
        Schema::table('site_opportunities', function (Blueprint $table) {
            $table->dropColumn(['show_on_site', 'show_in_cabinet']);
        });
    }
};
