<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Админка и форма входа всегда по-русски. Публичный SetLocale подхватывает язык
 * из сессии или cookie посетителя сайта; здесь он принудительно сбрасывается,
 * иначе сообщения валидации и даты в панели зависели бы от того, на каком
 * языке редактор смотрел сайт.
 */
class AdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('ru');

        return $next($request);
    }
}
