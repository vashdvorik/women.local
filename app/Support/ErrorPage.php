<?php

namespace App\Support;

use Illuminate\Http\Request;
use Throwable;

/**
 * Язык страниц ошибок. Ошибка часто случается до того, как middleware SetLocale успел выбрать язык (несуществующий адрес,
 * просроченный токен формы), поэтому язык определяется здесь сам: ?lang=, сохранённый выбор, язык браузера, иначе русский.
 */
final class ErrorPage
{
    private const SUPPORTED = ['ru', 'en', 'ro'];

    public static function locale(Request $request): string
    {
        $query = (string) $request->query('lang', '');

        if (in_array($query, self::SUPPORTED, true)) {
            return $query;
        }

        try {
            $stored = $request->hasSession() ? $request->session()->get('locale') : null;
        } catch (Throwable) {
            $stored = null;
        }

        if (is_string($stored) && in_array($stored, self::SUPPORTED, true)) {
            return $stored;
        }

        $cookie = $request->cookie('locale');

        if (is_string($cookie) && in_array($cookie, self::SUPPORTED, true)) {
            return $cookie;
        }

        // Язык браузера: первый из поддерживаемых в порядке перечисления («ro-RO,ro;q=0.9,en;q=0.8»).
        foreach (explode(',', (string) $request->header('Accept-Language', '')) as $part) {
            $code = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));

            if (in_array($code, self::SUPPORTED, true)) {
                return $code;
            }
        }

        return 'ru';
    }
}
