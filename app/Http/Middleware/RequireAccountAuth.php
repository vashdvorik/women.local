<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\BotUser;
use Closure;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
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

        $user = BotUser::query()
            ->where('bot_users.telegram_id', $telegramId)
            ->where('bot_users.status', BotUser::STATUS_APPROVED)
            ->select('bot_users.*')
            ->selectSub(
                SiteSetting::query()
                    ->select('value')
                    ->where('key', SiteSetting::ACCOUNT_THEME_KEY)
                    ->limit(1),
                'account_theme_setting'
            )
            ->first();

        if (! $user) {
            session()->forget('account_telegram_id');
            session()->forget('_account_expires');

            return redirect()->route('account.login')
                ->with('error', __('account.messages.access_closed'));
        }

        $themeValue = $user->getAttribute('account_theme_setting');
        $themeValue = is_string($themeValue) ? json_decode($themeValue, true) : $themeValue;
        $accountTheme = is_array($themeValue) ? ($themeValue['theme'] ?? 'classic') : 'classic';
        $accountTheme = array_key_exists($accountTheme, SiteSetting::ACCOUNT_THEMES) ? $accountTheme : 'classic';

        view()->share('accountUser', $user);
        view()->share('accountTheme', $accountTheme);

        return $next($request);
    }
}
