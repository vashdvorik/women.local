<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Plan;
use App\Models\BotUser;

/**
 * Дополнительные пункты меню кабинета, одинаковые во всех четырёх темах: «Подписка» (всегда) и «Private»
 * (особая вкладка, появляется у участниц с действующим Community или Private). Первый пункт у Open-участниц
 * называется «Главная» вместо «ИИ-помощник».
 *
 * Тема строит меню сама массивом $navItems; сюда она отдаёт его целиком и получает обратно с новыми пунктами.
 * Остальные пункты Open-участницам не прячем: они ведут на страницу «Подписка» с объяснением, что откроет оплата.
 */
final class CabinetNav
{
    /**
     * @param  list<array{route: string, label: string, path: string}>  $items
     * @return list<array{route: string, label: string, path: string}>
     */
    public static function extend(array $items, ?BotUser $user): array
    {
        // Для Open первая страница — не ИИ-помощник (он закрыт), а обычная «Главная» с материалами сайта.
        if ($user === null || ! $user->hasPlan(Plan::Community)) {
            foreach ($items as &$item) {
                if ($item['route'] === 'account.index') {
                    $item['label'] = __('subscription.open_home.title');
                }
            }
            unset($item);
        }

        $items[] = [
            'route' => 'account.subscription',
            'label' => __('subscription.nav.subscription'),
            'path' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        ];

        if ($user !== null && $user->hasPlan(Plan::Community)) {
            $items[] = [
                'route' => 'account.private',
                'label' => __('subscription.nav.private'),
                'path' => 'M5 16L3 6l5.5 4L12 4l3.5 6L21 6l-2 10H5zm14 3H5v2h14v-2z',
            ];
        }

        return $items;
    }
}
