<?php

declare(strict_types=1);

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\BotUser;
use App\Models\TelegramLink;
use App\Support\BotMessages;
use SergiX44\Nutgram\Nutgram;

// Бот только присылает уведомления и рассылки: меню, регистрации, поиска и входа в кабинет в нём нет.
// Единственное, что он умеет по просьбе участницы, — подключить Telegram к кабинету по ссылке из «Мой профиль»
// (t.me/<бот>?start=link_<токен>). Все остальные сообщения получают короткий ответ «как подключить уведомления».
//
// Ни одного текста для участницы здесь нет: все они лежат в resources/data/bot_messages.php и правятся в админке
// («Кабинеты участниц → Сообщения бота»). Функции в этом файле объявлять нельзя, он подключается при каждом создании приложения.

$replyLocale = fn (Nutgram $bot, ?BotUser $user = null): string => BotMessages::locale($user?->locale ?? $bot->user()?->language_code);

// Подключение Telegram: «/start link_<токен>».
$bot->onText('/start link_{token}', function (Nutgram $bot, string $token) use ($replyLocale) {
    $link = TelegramLink::query()->where('token', trim($token))->with('botUser')->first();
    $user = $link?->botUser;

    if ($link === null || $user === null || ! $link->isValid()) {
        $bot->sendMessage(BotMessages::text('link_invalid', $replyLocale($bot)), parse_mode: 'HTML');

        return;
    }

    $telegramId = $bot->userId();

    // Один Telegram — один кабинет: чужую привязку молча не перебиваем.
    $taken = BotUser::query()->where('telegram_id', $telegramId)->whereKeyNot($user->getKey())->exists();

    if ($taken) {
        $bot->sendMessage(BotMessages::text('link_conflict', $replyLocale($bot, $user)), parse_mode: 'HTML');

        return;
    }

    $user->forceFill([
        'telegram_id' => $telegramId,
        'telegram_username' => $bot->user()?->username,
    ])->save();
    $user->rememberLocale($bot->user()?->language_code);
    $link->delete();

    $bot->sendMessage(
        BotMessages::text('link_success', $replyLocale($bot, $user->fresh()), ['name' => BotMessages::firstName($user->full_name)]),
        parse_mode: 'HTML',
    );
});

// ->description() нужен встроенной команде nutgram:register-commands; берётся из эталонного файла (без обращения к базе).
$bot->onCommand('start', function (Nutgram $bot) use ($replyLocale) {
    $user = BotUser::where('telegram_id', $bot->userId())->first();

    $bot->sendMessage(BotMessages::text('start_info', $replyLocale($bot, $user), ['url' => url('/')]), parse_mode: 'HTML');
})->description(BotMessages::default('menu_command_start', BotMessages::DEFAULT_LOCALE));

$bot->fallback(function (Nutgram $bot) use ($replyLocale) {
    $user = BotUser::where('telegram_id', $bot->userId())->first();

    $bot->sendMessage(BotMessages::text('start_info', $replyLocale($bot, $user), ['url' => url('/')]), parse_mode: 'HTML');
});
