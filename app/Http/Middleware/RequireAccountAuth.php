<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\BotUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAccountAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $telegramId = session('account_telegram_id');

        if (! $telegramId) {
            return redirect()->route('account.login');
        }

        $expires = session('_account_expires');
        if (! $expires || time() > (int) $expires) {
            session()->forget('account_telegram_id');
            session()->forget('_account_expires');

            return redirect()->route('account.login')
                ->with('error', __('account.messages.session_expired'));
        }

        $user = BotUser::where('telegram_id', $telegramId)
            ->where('status', BotUser::STATUS_APPROVED)
            ->first();

        if (! $user) {
            session()->forget('account_telegram_id');
            session()->forget('_account_expires');

            return redirect()->route('account.login')
                ->with('error', __('account.messages.access_closed'));
        }

        view()->share('accountUser', $user);

        return $next($request);
    }
}
