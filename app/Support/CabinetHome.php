<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Opportunity;

/**
 * Данные главной страницы кабинета (account.index): человек сразу видит, где что лежит и с чего начать, а не упирается
 * в чат. Список «С чего начать» с галочками, плитки всех разделов с пояснением «что это и зачем» и «как пользоваться»
 * (дублируют меню), блок «Что нового». У тарифа Community те же плитки, закрытые разделы с замком, а вместо «Что нового» —
 * материалы публичного сайта (CommunityFeed) и приглашение подписаться.
 */
final class CabinetHome
{
    /**
     * @return array{
     *     name: string,
     *     paid: bool,
     *     tiles: list<array{key: string, route: string, label: string, path: string, locked: bool}>,
     *     opportunities: \Illuminate\Support\Collection,
     *     members: \Illuminate\Support\Collection,
     *     feed: ?array<string, mixed>,
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
            // Возможности участниц видны с тарифа Community (600), новые участницы и каталог — с Active.
            'opportunities' => self::latestOpportunities($user),
            'members' => $paid ? self::newestMembers($user) : collect(),
            'feed' => $paid ? null : CommunityFeed::build($locale),
        ];
    }

    private static function latestOpportunities(BotUser $user): \Illuminate\Support\Collection
    {
        return Opportunity::with('author:id,full_name')->visibleTo($user)->latest()->limit(3)->get();
    }

    /** Три последние вступившие участницы с действующим тарифом (каталог виден только им, см. AccountController::people). */
    private static function newestMembers(BotUser $user): \Illuminate\Support\Collection
    {
        return BotUser::discoverable()
            ->where('bot_users.id', '!=', $user->id)
            ->orderByDesc('approved_at')->orderByDesc('bot_users.id')
            ->limit(3)
            ->get(['bot_users.id', 'full_name', 'description', 'avatar_path']);
    }
}
