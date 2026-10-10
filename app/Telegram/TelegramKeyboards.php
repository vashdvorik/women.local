<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Support\BotMessages;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class TelegramKeyboards
{
    /**
     * Кнопка «Открыть кабинет» под сообщениями бота: открывает кабинет мини-приложением прямо в Telegram, а страница
     * входа сама входит по подписи Telegram (TmaAuthController), без ссылок и паролей.
     *
     * Telegram принимает для такой кнопки только https-адрес; на локальной машине (http) кнопки нет, а сообщение
     * всё равно уходит: иначе Telegram отклонил бы его целиком.
     */
    public static function cabinetButton(string $locale = BotMessages::DEFAULT_LOCALE): ?InlineKeyboardButton
    {
        $url = route('account.login');

        if (! str_starts_with($url, 'https://')) {
            return null;
        }

        return InlineKeyboardButton::make(BotMessages::text('cabinet_open_button', $locale), web_app: WebAppInfo::make($url));
    }

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
