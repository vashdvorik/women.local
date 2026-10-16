<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Plan;
use App\Models\BotUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Закрывает раздел кабинета для тех, у кого тариф ниже нужного: `plan:community` пускает Community и Private.
 *
 * Стоит ПОСЛЕ RequireAccountAuth (он кладёт участницу в общие данные представлений), поэтому вешается на маршруты
 * внутри группы кабинета. Тариф проверяется на каждом запросе по сохранённой дате окончания, так что доступ
 * закрывается в ту же минуту, когда подписка закончилась.
 *
 * Страница не уводит участницу в другой раздел: на месте раздела (меню кабинета остаётся, раздел подсвечен) показывается
 * сообщение «Этот раздел входит в тариф … и выше» и кнопка «Купить подписку», которая ведёт на страницу «Подписка».
 * Запрос с JSON (ИИ-помощник) получает 403 со ссылкой на оплату, запись (POST и т. п.) — редирект на «Подписку».
 */
class RequirePlan
{
    /** Раздел кабинета → ключ его названия в меню (account.nav.* в lang/*). */
    private const SECTIONS = [
        'account.matches' => 'matches',
        'account.search' => 'search',
        'account.people' => 'people',
        'account.people.show' => 'people',
        'account.opportunities.*' => 'opportunities',
        'account.knowledge' => 'knowledge',
    ];

    public function handle(Request $request, Closure $next, string $plan = 'community'): Response
    {
        $required = Plan::tryFrom($plan) ?? Plan::Community;

        /** @var BotUser|null $user */
        $user = view()->shared('accountUser');

        if ($user instanceof BotUser && $user->hasPlan($required)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('subscription.locked.json'),
                'upgrade_url' => route('account.subscription'),
            ], 403);
        }

        if ($request->isMethod('GET')) {
            return response()->view('account.subscription.locked', [
                'plan' => $required,
                'section' => $this->sectionTitle($request),
            ]);
        }

        return redirect()->route('account.subscription')
            ->with('error', __('subscription.locked.notice', ['plan' => $required->title()]));
    }

    /** Название закрытого раздела так, как оно написано в меню кабинета; null, если раздел не из списка. */
    private function sectionTitle(Request $request): ?string
    {
        foreach (self::SECTIONS as $route => $key) {
            if ($request->routeIs($route)) {
                return __('account.nav.'.$key);
            }
        }

        return null;
    }
}
