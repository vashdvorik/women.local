<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\PublicCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Любое изменение в админке (отправка формы, удаление, сдвиг карточки стрелкой, загрузка картинки) сбрасывает кеш
 * публичных страниц. Сброс по моделям (PublicCache::watch) не видит изменений, сделанных одним запросом к таблице
 * без модели, например смены порядка; здесь они учтены. Ненужный сброс безвреден: страница просто соберётся заново.
 */
class FlushPublicCacheAfterAdminChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodSafe() && $response->getStatusCode() < 500) {
            PublicCache::flush();
        }

        return $response;
    }
}
