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
 * Страница получает редирект на «Подписку» с пояснением, запрос с JSON (ИИ-помощник) — 403 с ссылкой на оплату.
 */
class RequirePlan
{
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

        return redirect()->route('account.subscription')
            ->with('error', __('subscription.locked.notice', ['plan' => $required->title()]));
    }
}
