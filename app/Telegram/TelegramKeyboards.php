<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Support\BotMessages;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class TelegramKeyboards
{
    /**
     * Главное меню одобренной участницы. Подписи берутся из resources/data/bot_messages.php (правятся в админке);
     * нажатие узнаётся через BotMessages::matches(), а не по сравнению с константой.
     */
    public static function mainMenu(string $locale = BotMessages::DEFAULT_LOCALE): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(BotMessages::text('menu_matches_button', $locale)),
                KeyboardButton::make(BotMessages::text('menu_chat_button', $locale)),
            );
    }
}
