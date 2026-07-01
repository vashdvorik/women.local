<?php

declare(strict_types=1);

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Telegram\Conversations\RegistrationConversation;
use App\Telegram\Conversations\SearchConversation;
use App\Telegram\TelegramKeyboards;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

/*
|--------------------------------------------------------------------------
| Nutgram Telegram Bot Handlers
|--------------------------------------------------------------------------
*/

// Inline button: "Вернуться" после удаления профиля — запускает регистрацию заново
$bot->onCallbackQueryData('restart', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    RegistrationConversation::begin($bot);
});

// Inline button: "С чего начать?" — инструкция для новых участников
$bot->onCallbackQueryData('start_guide', function (Nutgram $bot) {
    $bot->answerCallbackQuery();

    $bot->sendMessage(
        "🚀 Как начать работу в Инспайр?\n\n"
        . "1️⃣ Зайди в личный кабинет\n"
        . "Открой мини-приложение через \"Кабинет\" — там твой личный кабинет участника.\n\n"
        . "2️⃣ Заполни профиль\n"
        . "Укажи своё имя, чем занимаешься и чем можешь быть полезен сообществу. "
        . "Чем подробнее — тем точнее AI найдёт тебе нужных людей.\n\n"
        . "3️⃣ Получай рекомендации\n"
        . "AI анализирует профили всех участников и подбирает тех, с кем у тебя больше всего точек пересечения — "
        . "партнёры, единомышленники, потенциальные клиенты.\n\n"
        . "💡 Всё просто: зашёл → заполнил → нашёл нужных людей.\n\n"
        . "4️⃣ Раскрой меню нажав ⊞, если оно не открылось\n"
        . "Там ты увидишь кнопки для матчей, общего чата, и твоей визитки."
    );
});

// /start login — deep link from login page (?start=login)
$bot->onText('/start login', function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    if ($user->isPending()) {
        $firstName = explode(' ', (string) $user->full_name)[0];
        $bot->sendMessage(
            "{$firstName}, твоя заявка уже в работе 🙌\n\nАдминистратор лично рассматривает и свяжется в течение 24 часов.\nЕсли есть срочный вопрос — @lesnichenkoP"
        );
        return;
    }

    if ($user->isApproved()) {
        sendLoginLink($bot, $user);
        return;
    }

    $bot->sendMessage('🔒 Ваш доступ был закрыт.' . "\n\n" . 'Если у тебя есть вопросы или ты хочешь узнать причину — напиши напрямую: @lesnichenkoP');
});

$bot->onCommand('start', function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    if ($user->isPending()) {
        $firstName = explode(' ', (string) $user->full_name)[0];
        $bot->sendMessage(
            "{$firstName}, твоя заявка уже в работе 🙌\n\nАдминистратор лично рассматривает и свяжется в течение 24 часов.\nЕсли есть срочный вопрос — @lesnichenkoP"
        );
        return;
    }

    if ($user->isApproved()) {
        sendLoginLink($bot, $user);
        return;
    }

    $bot->sendMessage('🔒 Ваш доступ был закрыт.' . "\n\n" . 'Если у тебя есть вопросы или ты хочешь узнать причину — напиши напрямую: @lesnichenkoP');
})->description('Запустить бота');

// /login — send magic link to approved users
$bot->onCommand('login', function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if (! $user || ! $user->isApproved()) {
        $bot->sendMessage('🔒 Вход доступен только одобренным участникам сообщества.');
        return;
    }

    sendLoginLink($bot, $user);
})->description('Войти в личный кабинет');

// Fallback: обрабатывает все сообщения, не попавшие в другие обработчики
$bot->fallback(function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    if ($user->isPending()) {
        $firstName = explode(' ', (string) $user->full_name)[0];
        $bot->sendMessage(
            "{$firstName}, твоя заявка уже в работе 🙌\n\nАдминистратор лично рассматривает и свяжется в течение 24 часов.\nЕсли есть срочный вопрос — @lesnichenkoP"
        );
        return;
    }

    if ($user->isApproved()) {
        $text = $bot->message()?->text;
        match ($text) {
            TelegramKeyboards::BTN_MATCHES => SearchConversation::begin($bot),
            TelegramKeyboards::BTN_CHAT    => $bot->sendMessage("Раздел в разработке 🚧"),
            default                        => null,
        };
        return;
    }

    // Отклонённый пользователь
    $bot->sendMessage('🔒 Ваш доступ был закрыт.' . "\n\n" . 'Если у тебя есть вопросы или ты хочешь узнать причину — напиши напрямую: @lesnichenkoP');
});

/**
 * Generate a magic login link and send it to the user.
 */
function sendLoginLink(Nutgram $bot, BotUser $user): void
{
    $token = LoginToken::generateFor((int) $user->telegram_id);
    $url   = url('/go/' . substr($token->token, 0, 8));

    $firstName = explode(' ', (string) $user->full_name)[0];

    $bot->sendMessage(
        "Привет, {$firstName}! Нажми ссылку ниже, чтобы войти в личный кабинет.\n\n🔐 {$url}\n\n⏱ Ссылка действует 24 часа.",
        reply_markup: TelegramKeyboards::mainMenu()
    );
}
