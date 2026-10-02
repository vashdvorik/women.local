<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Support\BotMessages;
use App\Telegram\Conversations\RegistrationConversation;
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
