<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['ru', 'en', 'ro'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->query('lang', '');

        if (in_array($locale, self::SUPPORTED, true)) {
            session(['locale' => $locale]);
            cookie()->queue(cookie('locale', $locale, 60 * 24 * 365));
        }

        $locale = session('locale', $request->cookie('locale', 'ru'));
        $locale = in_array($locale, self::SUPPORTED, true) ? $locale : 'ru';

        App::setLocale($locale);

        return $next($request);
    }
}
