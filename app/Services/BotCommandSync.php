<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\BotMessages;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Telegram\Types\Command\BotCommand;

/**
 * Отправляет в Telegram список команд бота с описаниями из resources/data/bot_messages.php (с правками
 * администратора), по языкам: у каждого языка Telegram свой список, для остальных действует русский.
 * Вызывается после сохранения текстов в админке и командой `php artisan bot:sync-commands`.
 */
class BotCommandSync
{
    /** Команда бота → ключ её описания в bot_messages.php. */
    public const COMMANDS = [
        'start' => 'menu_command_start',
        'login' => 'menu_command_login',
    ];

    /** Токена бота нет (локальная разработка, тесты): синхронизировать некуда. */
    public function isConfigured(): bool
    {
        return filled(config('nutgram.token'));
    }

    /** @throws \Throwable если Telegram отклонил запрос или недоступен */
    public function sync(): void
    {
        // Список без языка — для всех, чьего языка нет среди трёх: там русский.
        $this->push(BotMessages::DEFAULT_LOCALE, null);

        foreach (BotMessages::LOCALES as $locale) {
            $this->push($locale, $locale);
        }
    }

    private function push(string $locale, ?string $languageCode): void
    {
        $commands = [];

        foreach (self::COMMANDS as $command => $key) {
            $commands[] = BotCommand::make($command, BotMessages::text($key, $locale));
        }

        Telegram::setMyCommands($commands, language_code: $languageCode);
    }
}
