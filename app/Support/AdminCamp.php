<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Админка делится на два «лагеря», чтобы администратор не путал их:
 *
 *  - «Внешний сайт» — то, что видят посетители: новости, публикации, возможности,
 *    эксперты, проекты, медиа, теги, подписчики, настройки сайта;
 *  - «Кабинеты участниц» — то, что относится к закрытому кабинету и Telegram-боту:
 *    профили, посты участниц, статистика, настройки кабинета (тема, ИИ, база знаний).
 *
 * Лагерь определяется по имени маршрута, поэтому новый раздел админки достаточно
 * либо назвать `admin.cabinets.*`, либо добавить сюда его префикс. Всё остальное
 * относится к внешнему сайту.
 */
final class AdminCamp
{
    public const SITE = 'site';

    public const CABINETS = 'cabinets';

    /** Маршруты лагеря «Кабинеты участниц». */
    private const CABINET_ROUTES = [
        'admin.cabinets.*',
        'admin.profiles.*',
        'admin.member-posts.*',
        'admin.statistics.*',
    ];

    public static function current(?Request $request = null): string
    {
        $request ??= request();

        return $request->routeIs(...self::CABINET_ROUTES) ? self::CABINETS : self::SITE;
    }

    public static function label(string $camp): string
    {
        return $camp === self::CABINETS ? 'Кабинеты участниц' : 'Внешний сайт';
    }
}
