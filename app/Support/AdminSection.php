<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Раздел админки, в котором находится страница. Меню админки одно и сгруппировано по задачам администратора
 * (участницы, пакеты и оплата, контент, общение, настройки); название группы стоит над заголовком страницы, чтобы было
 * видно, где вы. Раздел определяется по имени маршрута: новую страницу достаточно добавить в нужный список.
 * У «Инфопанели» группы нет.
 */
final class AdminSection
{
    /** @var array<string, array{0: string, 1: list<string>}> ключ группы меню → [название, маршруты, которые к ней относятся] */
    private const GROUPS = [
        'people' => ['Участницы', ['admin.profiles.*', 'admin.member-posts.*', 'admin.manager-questions.*', 'admin.statistics.*']],
        'money' => ['Пакеты и оплата', ['admin.subscriptions.*', 'admin.plan-requests.*', 'admin.payments.*']],
        'content' => ['Контент', [
            'admin.events.*', 'admin.opportunities.*', 'admin.learning-videos.*', 'admin.projects.*', 'admin.experts.*',
            'admin.albums.*', 'admin.videos.*', 'admin.posts.*', 'admin.catalogs.*',
        ]],
        'comm' => ['Общение', ['admin.broadcasts.*', 'admin.contact-messages.*', 'admin.subscribers.*', 'admin.cabinets.bot-messages*']],
        'settings' => ['Настройки', ['admin.settings.*', 'admin.cabinets.settings*', 'admin.tags.*']],
    ];

    /** Ключ группы меню текущей страницы (people, money, content, comm, settings); null — страница вне групп (инфопанель). */
    public static function key(?Request $request = null): ?string
    {
        $request ??= request();

        foreach (self::GROUPS as $key => [, $routes]) {
            if ($request->routeIs(...$routes)) {
                return $key;
            }
        }

        return null;
    }

    /** Название группы текущей страницы на языке админки; null — страница вне групп (инфопанель). */
    public static function label(?Request $request = null): ?string
    {
        $key = self::key($request);

        return $key === null ? null : __(self::GROUPS[$key][0]);
    }
}
