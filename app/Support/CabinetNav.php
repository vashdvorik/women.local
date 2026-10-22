<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Plan;
use App\Models\BotUser;

/**
 * Меню кабинета: один список разделов для боковой панели, нижней панели на телефоне и плиток на главной, одинаковый во
 * всех темах (тема только оформляет его). У каждого раздела — маршрут, подпись, иконка и тариф, с которого он открыт.
 *
 * Закрытые для Open разделы участницам Open не прячем: они ведут на страницу с объяснением, что откроет подписка
 * (RequirePlan), а на главной помечены замком.
 */
final class CabinetNav
{
    /** Иконки (контур SVG, viewBox 24): те же в боковой панели, нижней панели и плитках. */
    private const ICONS = [
        'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'assistant' => 'M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.83L3 20l1.4-3.74A7.6 7.6 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'matches' => 'M13 10V3L4 14h7v7l9-11h-7z',
        'search' => 'M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z',
        'people' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'opportunities' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'knowledge' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'profile' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'subscription' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'more' => 'M4 6h16M4 12h16M4 18h16',
    ];

    /**
     * Разделы кабинета по порядку меню.
     *
     * @return list<array{key: string, route: string, label: string, path: string, min: ?Plan, locked: bool}>
     */
    public static function items(?BotUser $user): array
    {
        $paid = $user !== null && $user->hasPlan(Plan::Community);

        $definitions = [
            ['home', 'account.index', 'account.nav.home', null],
            ['assistant', 'account.assistant', 'account.nav.assistant', Plan::Community],
            ['matches', 'account.matches', 'account.nav.matches', Plan::Community],
            ['search', 'account.search', 'account.nav.search', Plan::Community],
            ['people', 'account.people', 'account.nav.people', Plan::Community],
            ['opportunities', 'account.opportunities.index', 'account.nav.opportunities', Plan::Community],
            ['knowledge', 'account.knowledge', 'account.nav.knowledge', Plan::Community],
            ['profile', 'account.profile', 'account.nav.profile', null],
            ['subscription', 'account.subscription', 'subscription.nav.subscription', null],
        ];

        return array_map(fn (array $d): array => [
            'key' => $d[0],
            'route' => $d[1],
            'label' => __($d[2]),
            'path' => self::ICONS[$d[0]],
            'min' => $d[3],
            'locked' => $d[3] !== null && ! $paid,
        ], $definitions);
    }

    /** Иконка «Ещё» нижней панели (открывает полное меню). */
    public static function moreIcon(): string
    {
        return self::ICONS['more'];
    }
}
