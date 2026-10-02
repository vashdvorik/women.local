<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AdminLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Переключатель языка админки RU | EN. Доступен и без входа: он же стоит на странице входа.
 * Выбор запоминается в cookie на год; на содержимое сайта и на язык бота он не влияет.
 */
class LanguageController extends Controller
{
    private const MINUTES_IN_YEAR = 60 * 24 * 365;

    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, AdminLocale::LOCALES), 404);

        return redirect($this->returnUrl($request))
            ->withCookie(cookie(AdminLocale::COOKIE, $locale, self::MINUTES_IN_YEAR, '/', null, $request->isSecure(), true, false, 'lax'));
    }

    /** Туда, откуда пришли: только на этом же сайте, и не на сам переключатель; иначе в панель. */
    private function returnUrl(Request $request): string
    {
        $previous = (string) $request->headers->get('referer', '');
        $host = parse_url($previous, PHP_URL_HOST);
        $path = (string) parse_url($previous, PHP_URL_PATH);

        $sameSite = is_string($host) && strcasecmp($host, $request->getHost()) === 0;

        if (! $sameSite || str_starts_with($path, '/admin/language/')) {
            return route('admin.dashboard');
        }

        return $previous;
    }
}
