<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Кабинет участницы. Пускает только вошедшую по почте (guard «participant») и ведёт её по шагам:
 *
 *  1. почта не подтверждена → «Подтвердите почту»;
 *  2. анкета не заполнена → «Расскажите о себе» (имя, телефон и остальное);
 *  3. тарифа нет → «Выберите тариф» (кабинета без тарифа нет, бесплатного доступа тоже);
 *  4. иначе — кабинет; разделы выше Community закрывает RequirePlan.
 *
 * Одобрение анкеты администратором в шаги не входит: тариф можно купить сразу, а другие участницы увидят анкету
 * после одобрения. Отклонённая анкета доступ закрывает.
 */
class RequireAccountAuth
{
    /** Страницы, которые нужны до тарифа: выбор тарифа, заявка, свой профиль, подключение Telegram, выход. */
    private const BEFORE_PLAN = [
        'account.subscription', 'account.subscription.*', 'account.plan-request.*', 'account.profile', 'account.profile.*',
        'account.telegram.*', 'account.logout', 'account.onboarding', 'account.onboarding.*', 'account.verification.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('participant');

        /** @var BotUser|null $user */
        $user = $guard->user();

        if ($user === null) {
            return redirect()->guest(route('account.login'));
        }

        if ($user->isRejected()) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('account.login')->with('error', __('participant.login.access_closed'));
        }

        view()->share('accountUser', $user);
        view()->share('accountTheme', SiteSetting::accountTheme());

        if (! $user->hasVerifiedEmail()) {
            return $request->routeIs('account.verification.*', 'account.logout')
                ? $next($request)
                : redirect()->route('account.verification.notice');
        }

        if (! $user->isProfileComplete()) {
            return $request->routeIs('account.onboarding', 'account.onboarding.*', 'account.logout', 'account.verification.*')
                ? $next($request)
                : redirect()->route('account.onboarding');
        }

        if ($user->currentPlan() === Plan::None && ! $request->routeIs(...self::BEFORE_PLAN)) {
            return redirect()->route('account.subscription');
        }

        return $next($request);
    }
}
