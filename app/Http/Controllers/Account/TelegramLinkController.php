<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Models\TelegramLink;
use Illuminate\Http\RedirectResponse;

/**
 * Подключение Telegram для уведомлений. Входить через Telegram нельзя: бот только присылает сообщения.
 * Участница открывает ссылку t.me/<бот>?start=link_<токен>, бот привязывает её чат к кабинету (routes/telegram.php).
 */
class TelegramLinkController extends Controller
{
    public function connect(): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        return redirect()->away(TelegramLink::issueFor($user)->url());
    }

    public function disconnect(): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        $user->forceFill(['telegram_id' => null, 'telegram_username' => null])->save();

        return back()->with('success', __('participant.telegram.disconnected'));
    }
}
