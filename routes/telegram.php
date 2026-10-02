<?php

declare(strict_types=1);

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Enums\Plan;
use App\Models\BotUser;
use App\Support\BotMessages;
use App\Telegram\BotReplies;
use App\Telegram\Conversations\RegistrationConversation;
use App\Telegram\Conversations\SearchConversation;
use App\Telegram\TelegramLocale;
use SergiX44\Nutgram\Nutgram;

// Ни одного текста для участницы здесь нет: все они лежат в resources/data/bot_messages.php и правятся в админке
// («Кабинеты участниц → Сообщения бота»). Описания команд в меню Telegram — оттуда же: php artisan bot:sync-commands.
// Общие ответы — в App\Telegram\BotReplies: функции в этом файле объявлять нельзя, он подключается при каждом создании приложения.

$bot->onCallbackQueryData('restart', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    RegistrationConversation::begin($bot);
});

$bot->onCallbackQueryData('start_guide', function (Nutgram $bot) {
    $bot->answerCallbackQuery();

    $bot->sendMessage(
        BotMessages::text('guide_start', TelegramLocale::for($bot)),
        parse_mode: 'HTML',
    );
});

$bot->onText('/start login', function (Nutgram $bot) {
    BotReplies::startOrLogin($bot, sendLoginLink: true);
});

// ->description() нужен встроенной команде nutgram:register-commands; берётся из эталонного файла (без обращения к базе).
$bot->onCommand('start', function (Nutgram $bot) {
    BotReplies::startOrLogin($bot, sendLoginLink: true);
})->description(BotMessages::default('menu_command_start', BotMessages::DEFAULT_LOCALE));

$bot->onCommand('login', function (Nutgram $bot) {
    $user = BotUser::where('telegram_id', $bot->userId())->first();
    $locale = TelegramLocale::for($bot, $user);

    if (! $user || ! $user->isApproved()) {
        $bot->sendMessage(BotMessages::text('login_not_approved', $locale), parse_mode: 'HTML');
        return;
    }

    $user->rememberLocale($bot->user()?->language_code);

    BotReplies::loginLink($bot, $user, $locale);
})->description(BotMessages::default('menu_command_login', BotMessages::DEFAULT_LOCALE));

$bot->fallback(function (Nutgram $bot) {
    $user = BotUser::where('telegram_id', $bot->userId())->first();

    if ($user === null) {
        RegistrationConversation::begin($bot);
        return;
    }

    $user->rememberLocale($bot->user()?->language_code);
    $locale = TelegramLocale::for($bot, $user);

    if ($user->isPending()) {
        BotReplies::pending($bot, $user, $locale);
        return;
    }

    if ($user->isApproved()) {
        $text = $bot->message()?->text;

        // Кнопки меню узнаются по подписи на любом языке, в том числе прежней: клавиатура на телефоне обновляется не сразу.
        match (true) {
            // Поиск контактов — только по подписке Community и выше; Open получает ссылку на оплату.
            BotMessages::matches('menu_matches_button', $text) => $user->hasPlan(Plan::Community)
                ? SearchConversation::begin($bot)
                : $bot->sendMessage(BotMessages::text('plan_required', $locale, ['url' => route('account.subscription')]), parse_mode: 'HTML'),
            BotMessages::matches('menu_chat_button', $text)    => $bot->sendMessage(BotMessages::text('menu_chat_stub', $locale), parse_mode: 'HTML'),
            default                                            => null,
        };
        return;
    }

    BotReplies::rejected($bot, $locale);
});
