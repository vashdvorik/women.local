<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // «Скрыт от других участниц»: человек пользуется платформой изнутри (кабинет, рассылки), но не виден в каталоге,
        // поиске, подборе контактов и ИИ-помощнике. Так входят администратор, спонсор, партнёр. Включает только администратор
        // платформы, в админке «Профили участниц».
        Schema::table('bot_users', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropIndex(['is_hidden']);
            $table->dropColumn('is_hidden');
        });
    }
};
