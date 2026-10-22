<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Обращения из формы «Связаться с нами» на странице «Контакты». Хранятся в базе (раздел «Обращения» в админке),
        // а не только уходят письмом: если почта не дошла или письмо попало в спам, обращение не теряется.
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 191);
            $table->string('phone', 32)->nullable();
            $table->text('message');
            $table->string('locale', 5)->default('ru');         // язык страницы, с которой пишут
            $table->timestamp('read_at')->nullable();           // когда администратор открыл обращение
            $table->timestamp('mail_sent_at')->nullable();      // письмо на почту команды отправлено
            $table->string('mail_error', 500)->nullable();      // почему письмо не ушло
            $table->timestamps();

            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
