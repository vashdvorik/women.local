<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Models\BotUser;
use App\Support\BotMessages;
use SergiX44\Nutgram\Nutgram;

/**
 * Язык, на котором бот отвечает в текущем диалоге: язык Telegram участницы (он приходит с каждым обновлением),
 * а если его нет или он пуст — язык, запомненный в её профиле; иначе русский.
 */
final class TelegramLocale
{
    public static function for(Nutgram $bot, ?BotUser $user = null): string
    {
        $code = $bot->user()?->language_code;

        return BotMessages::locale(filled($code) ? $code : $user?->locale);
    }
}
