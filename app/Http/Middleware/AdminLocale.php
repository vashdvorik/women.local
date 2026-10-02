<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Язык админки и формы входа: русский или английский. Выбор лежит в cookie `admin_lang` (ставит
 * AdminLanguageController по клику на RU | EN), по умолчанию — русский. Публичный SetLocale подхватывает язык
 * из сессии или cookie посетителя сайта; здесь он принудительно заменяется, иначе сообщения валидации
 * и даты в панели зависели бы от того, на каком языке редактор смотрел сайт.
 *
 * Язык интерфейса — не язык материалов: вкладки ru / ro / en в формах новостей, публикаций и экспертов
 * по-прежнему редактируют содержимое сайта на всех трёх языках.
 */
class AdminLocale
{
    public const COOKIE = 'admin_lang';

    /** Языки интерфейса админки: код → подпись на переключателе. */
    public const LOCALES = ['ru' => 'RU', 'en' => 'EN'];

    public const DEFAULT = 'ru';

    /** Язык этого запроса: из cookie, если он допустим, иначе русский. */
    public static function resolve(Request $request): string
    {
        $stored = $request->cookie(self::COOKIE);

        return is_string($stored) && array_key_exists($stored, self::LOCALES) ? $stored : self::DEFAULT;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $locale = self::resolve($request);

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
