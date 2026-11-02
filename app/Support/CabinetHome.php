<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Opportunity;

/**
 * Данные главной страницы кабинета (account.index): имя, тариф и плитки разделов с пояснением «что это» и «как пользоваться»
 * (дублируют меню; закрытые для тарифа разделы с замком). Подсказки при первом входе: CabinetTour.
 */
final class CabinetHome
{
    /**
     * @return array{
     *     name: string,
     *     paid: bool,
     *     tiles: list<array{key: string, route: string, label: string, path: string, locked: bool}>,
     * }
     */
    public static function build(BotUser $user, string $locale): array
    {
        $paid = $user->hasPlan(Plan::Active);

        return [
            'name' => BotMessages::firstName($user->full_name),
            'paid' => $paid,
            // Плитки — те же разделы, что в меню, кроме самой главной.
            'tiles' => array_values(array_filter(CabinetNav::items($user), fn (array $item): bool => $item['key'] !== 'home')),
        ];
    }
}
