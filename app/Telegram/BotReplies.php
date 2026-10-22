<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\LoginToken;
use App\Support\BotMessages;
use App\Telegram\Conversations\HelpConversation;
use App\Telegram\Conversations\RegistrationConversation;
use App\Telegram\Conversations\SearchConversation;
use SergiX44\Nutgram\Nutgram;

/**
 * Ответы бота, которые нужны нескольким обработчикам из routes/telegram.php. Тексты — из
 * resources/data/bot_messages.php. Это класс, а не функции в файле маршрутов: файл маршрутов подключается
 * заново при каждом создании приложения (в тестах — много раз за один процесс), и функции в нём дали бы
 * «Cannot redeclare».
 */
final class BotReplies
{
    /** /start и вход: новой участнице — анкета, ожидающей — статус, одобренной — ссылка в кабинет. */
    public static function startOrLogin(Nutgram $bot, bool $sendLoginLink): void
    {
        $user = BotUser::where('telegram_id', $bot->userId())->first();

        if ($user === null) {
            RegistrationConversation::begin($bot);

            return;
        }

        $user->rememberLocale($bot->user()?->language_code);
        $locale = TelegramLocale::for($bot, $user);

        if ($user->isPending()) {
            self::pending($bot, $user, $locale);

            return;
        }

        if ($user->isApproved() && $sendLoginLink) {
            self::loginLink($bot, $user, $locale);

            return;
        }

        self::rejected($bot, $locale);
    }

    public static function pending(Nutgram $bot, BotUser $user, string $locale): void
    {
        $bot->sendMessage(
            BotMessages::text('status_pending', $locale, ['name' => BotMessages::firstName($user->full_name)]),
            parse_mode: 'HTML',
        );
    }

    public static function rejected(Nutgram $bot, string $locale): void
    {
        $bot->sendMessage(BotMessages::text('status_rejected', $locale), parse_mode: 'HTML');
    }

    /** Подпись кнопки главного меню (нынешней или прежней): по ней бот узнаёт нажатие. */
    public static function isMenuText(?string $text): bool
    {
        foreach (['menu_cabinet_button', 'menu_help_button', 'menu_matches_button', 'menu_chat_button'] as $key) {
            if (BotMessages::matches($key, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Нажатие кнопки главного меню одобренной участницей. «Кабинет» (если Telegram не открыл мини-приложение сам) —
     * ссылка для входа; «AI помощник» — разговор с помощником. Кнопки прежнего меню («Найти контакты», «Чат
     * сообщества») на телефоне живут, пока она не получит новую клавиатуру: в ответ приходит сообщение об обновлении
     * меню с новыми кнопками (а поиск контактов, пока он есть в подписке, по-прежнему запускается).
     */
    public static function handleMenuText(Nutgram $bot, BotUser $user, ?string $text, string $locale): void
    {
        match (true) {
            BotMessages::matches('menu_cabinet_button', $text) => self::loginLink($bot, $user, $locale),
            BotMessages::matches('menu_help_button', $text) => HelpConversation::begin($bot),
            BotMessages::matches('menu_matches_button', $text) => self::legacySearch($bot, $user, $locale),
            BotMessages::matches('menu_chat_button', $text) => self::menuUpdated($bot, $user, $locale),
            default => null,
        };
    }

    /** Команда, отправленная посреди разговора с помощником: те же ответы, что и вне разговора. */
    public static function handleCommandText(Nutgram $bot, string $text): void
    {
        $command = strtolower(explode(' ', ltrim($text, '/'))[0]);
        $command = explode('@', $command)[0];

        if ($command === 'login') {
            $user = BotUser::where('telegram_id', $bot->userId())->first();

            $user !== null && $user->isApproved()
                ? self::loginLink($bot, $user, TelegramLocale::for($bot, $user))
                : self::startOrLogin($bot, sendLoginLink: false);

            return;
        }

        self::startOrLogin($bot, sendLoginLink: true);
    }

    /** Сообщение «меню обновилось» с новой клавиатурой. */
    public static function menuUpdated(Nutgram $bot, BotUser $user, string $locale): void
    {
        $bot->sendMessage(
            BotMessages::text('menu_updated', $locale, ['name' => BotMessages::firstName($user->full_name)]),
            parse_mode: 'HTML',
            reply_markup: TelegramKeyboards::mainMenu($locale),
        );
    }

    private static function legacySearch(Nutgram $bot, BotUser $user, string $locale): void
    {
        // Сначала то, что она просила (поиск контактов — только по подписке Community и выше; Open получает ссылку на
        // оплату), а следом сообщение с новым меню: кнопка прежняя, значит и клавиатура на телефоне прежняя.
        $user->hasPlan(Plan::Community)
            ? SearchConversation::begin($bot)
            : $bot->sendMessage(BotMessages::text('plan_required', $locale, ['url' => route('account.subscription')]), parse_mode: 'HTML');

        self::menuUpdated($bot, $user, $locale);
    }

    public static function loginLink(Nutgram $bot, BotUser $user, string $locale): void
    {
        $token = LoginToken::generateFor((int) $user->telegram_id);
        $url = url('/go/'.substr($token->token, 0, 8));

        $bot->sendMessage(
            BotMessages::text('login_link', $locale, ['name' => BotMessages::firstName($user->full_name), 'url' => $url]),
            parse_mode: 'HTML',
            reply_markup: TelegramKeyboards::mainMenu($locale),
        );
    }
}
