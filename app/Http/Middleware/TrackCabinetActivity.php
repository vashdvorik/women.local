<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\BotUser;
use App\Services\Activity\CabinetActivity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Записывает, какой раздел кабинета открыла участница и какое действие сделала (см. CabinetActivity). Работает после
 * ответа (terminate), поэтому страницу не замедляет; учитываются только успешные ответы и переадресации после действий.
 */
class TrackCabinetActivity
{
    public function __construct(private readonly CabinetActivity $activity) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($response->getStatusCode() >= 400) {
            return;
        }

        $user = $request->user('participant');

        if (! $user instanceof BotUser) {
            return;
        }

        $keys = $this->activity->keysFor($request);

        // Страница, которая сразу перенаправила (например, на «Подписку»), открытой не считается: считаем только действия
        if ($response->isRedirection() && $request->isMethod('GET')) {
            return;
        }

        if ($keys !== []) {
            $this->activity->record($user, $keys);
        }
    }
}
