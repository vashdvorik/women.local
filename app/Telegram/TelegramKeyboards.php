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
     * Адрес кабинета для мини-приложения Telegram: страница входа сама входит по подписи Telegram (TmaAuthController),
     * без ссылок и паролей. Telegram принимает для такой кнопки только https-адрес; на локальной машине (http) его
     * нет, и вместо кнопки-мини-приложения остаётся обычная кнопка (бот присылает в ответ ссылку для входа), а
     * сообщение всё равно уходит: иначе Telegram отклонил бы его целиком.
     */
    private static function cabinetUrl(): ?string
    {
        $url = route('account.login');

        return str_starts_with($url, 'https://') ? $url : null;
    }

    /**
     * Кнопка «Открыть кабинет» под сообщениями бота: открывает кабинет мини-приложением прямо в Telegram.
     */
    public static function cabinetButton(string $locale = BotMessages::DEFAULT_LOCALE): ?InlineKeyboardButton
    {
        $url = self::cabinetUrl();

        if ($url === null) {
            return null;
        }

        return InlineKeyboardButton::make(BotMessages::text('cabinet_open_button', $locale), web_app: WebAppInfo::make($url));
    }

    /** Кнопка «Кабинет» главного меню: мини-приложение с https, иначе обычная кнопка (её нажатие бот узнаёт по подписи). */
    private static function menuCabinetButton(string $locale): KeyboardButton
    {
        $caption = BotMessages::text('menu_cabinet_button', $locale);
        $url = self::cabinetUrl();

        return $url !== null
            ? KeyboardButton::make($caption, web_app: WebAppInfo::make($url))
            : KeyboardButton::make($caption);
    }

    /**
     * Главное меню одобренной участницы: «Кабинет» (открывает кабинет) и «AI помощник» (объясняет, как пользоваться
     * ботом). Подписи берутся из resources/data/bot_messages.php (правятся в админке); нажатие узнаётся через
     * BotMessages::matches(), а не по сравнению с константой.
     */
    public static function mainMenu(string $locale = BotMessages::DEFAULT_LOCALE): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                self::menuCabinetButton($locale),
                KeyboardButton::make(BotMessages::text('menu_help_button', $locale)),
            );
    }

    /** Клавиатура на время разговора с помощником: кабинет и выход из разговора. */
    public static function helpMenu(string $locale = BotMessages::DEFAULT_LOCALE): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                self::menuCabinetButton($locale),
                KeyboardButton::make(BotMessages::text('help_exit_button', $locale)),
            );
    }

    /** Кнопка «Спросить помощника» под сообщениями бота: начинает разговор с помощником. */
    public static function helpAskButton(string $locale = BotMessages::DEFAULT_LOCALE): InlineKeyboardButton
    {
        return InlineKeyboardButton::make(BotMessages::text('help_ask_button', $locale), callback_data: 'help_start');
    }
}
