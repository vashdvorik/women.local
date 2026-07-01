<?php

declare(strict_types=1);

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Telegram\Conversations\RegistrationConversation;
use App\Telegram\Conversations\SearchConversation;
use App\Telegram\TelegramKeyboards;
use SergiX44\Nutgram\Nutgram;

$bot->onCallbackQueryData('restart', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    RegistrationConversation::begin($bot);
});

$bot->onCallbackQueryData('start_guide', function (Nutgram $bot) {
    $bot->answerCallbackQuery();

    $bot->sendMessage(
        "Как начать работу с Women Entrepreneurs Platform of the Two Banks?\n\n"
        . "1. Откройте личный кабинет\n"
        . "Используйте кнопку входа или ссылку из этого бота.\n\n"
        . "2. Заполните профиль\n"
        . "Расскажите, что вы представляете, что ищете и чем можете быть полезны другим участницам.\n\n"
        . "3. Ищите контакты и возможности\n"
        . "Платформа помогает находить предпринимательниц, запросы, предложения, события и полезные материалы.\n\n"
        . "4. Оставайтесь на связи\n"
        . "Telegram будет присылать важные обновления, приглашения и публикации сообщества."
    );
});

$bot->onText('/start login', function (Nutgram $bot) {
    handleStartOrLogin($bot, shouldSendLoginLink: true);
});

$bot->onCommand('start', function (Nutgram $bot) {
    handleStartOrLogin($bot, shouldSendLoginLink: true);
})->description('Подать заявку или войти');

$bot->onCommand('login', function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if (! $user || ! $user->isApproved()) {
        $bot->sendMessage('Вход доступен только одобренным участницам платформы. Чтобы подать заявку, отправьте /start.');
        return;
    }

    sendLoginLink($bot, $user);
})->description('Войти в личный кабинет');

$bot->fallback(function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    if ($user->isPending()) {
        sendPendingMessage($bot, $user);
        return;
    }

    if ($user->isApproved()) {
        $text = $bot->message()?->text;
        match ($text) {
            TelegramKeyboards::BTN_MATCHES => SearchConversation::begin($bot),
            TelegramKeyboards::BTN_CHAT    => $bot->sendMessage('Чат сообщества будет доступен после подключения команды проекта.'),
            default                        => null,
        };
        return;
    }

    sendRejectedMessage($bot);
});

function handleStartOrLogin(Nutgram $bot, bool $shouldSendLoginLink): void
{
    $telegramId = $bot->userId();
    $user = BotUser::where('telegram_id', $telegramId)->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    if ($user->isPending()) {
        sendPendingMessage($bot, $user);
        return;
    }

    if ($user->isApproved() && $shouldSendLoginLink) {
        sendLoginLink($bot, $user);
        return;
    }

    sendRejectedMessage($bot);
}

function sendPendingMessage(Nutgram $bot, BotUser $user): void
{
    $firstName = explode(' ', (string) $user->full_name)[0];

    $bot->sendMessage(
        "{$firstName}, ваша заявка уже на рассмотрении.\n\nКоманда проекта проверит профиль и откроет доступ после одобрения. Если есть вопрос, напишите: @lesnichenkoP"
    );
}

function sendRejectedMessage(Nutgram $bot): void
{
    $bot->sendMessage(
        "Доступ к платформе закрыт.\n\nЕсли у вас есть вопросы по заявке или участию, напишите команде проекта: @lesnichenkoP"
    );
}

function sendLoginLink(Nutgram $bot, BotUser $user): void
{
    $token = LoginToken::generateFor((int) $user->telegram_id);
    $url   = url('/go/' . substr($token->token, 0, 8));

    $firstName = explode(' ', (string) $user->full_name)[0];

    $bot->sendMessage(
        "Здравствуйте, {$firstName}! Перейдите по ссылке ниже, чтобы открыть личный кабинет Women Entrepreneurs Platform of the Two Banks.\n\n🔐 {$url}\n\n⏱ Ссылка действует 24 часа.",
        reply_markup: TelegramKeyboards::mainMenu()
    );
}
