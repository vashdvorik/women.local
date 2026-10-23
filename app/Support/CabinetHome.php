<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Opportunity;

/**
 * Данные главной страницы кабинета (account.index): человек сразу видит, где что лежит и с чего начать, а не упирается
 * в чат. Список «С чего начать» с галочками, плитки всех разделов с пояснением «что это и зачем» и «как пользоваться»
 * (дублируют меню), блок «Что нового». У тарифа Open те же плитки, закрытые разделы с замком, а вместо «Что нового» —
 * материалы публичного сайта (OpenFeed) и приглашение подписаться.
 */
final class CabinetHome
{
    /**
     * @return array{
     *     name: string,
     *     paid: bool,
     *     steps: list<array{key: string, route: string, done: bool}>,
     *     doneCount: int,
     *     tiles: list<array{key: string, route: string, label: string, path: string, locked: bool}>,
     *     opportunities: \Illuminate\Support\Collection,
     *     members: \Illuminate\Support\Collection,
     *     feed: ?array<string, mixed>,
     * }
     */
    public static function build(BotUser $user, string $locale): array
    {
        $paid = $user->hasPlan(Plan::Community);
        $steps = self::steps($user, $paid);

        return [
            'name' => BotMessages::firstName($user->full_name),
            'paid' => $paid,
            'steps' => $steps,
            'doneCount' => count(array_filter($steps, fn (array $step): bool => $step['done'])),
            // Плитки — те же разделы, что в меню, кроме самой главной.
            'tiles' => array_values(array_filter(CabinetNav::items($user), fn (array $item): bool => $item['key'] !== 'home')),
            'opportunities' => $paid ? self::latestOpportunities($user) : collect(),
            'members' => $paid ? self::newestMembers($user) : collect(),
            'feed' => $paid ? null : OpenFeed::build($locale),
        ];
    }

    /**
     * Шаги «С чего начать». Профиль и подписка отмечаются сами по данным; остальные — когда участница открыла раздел
     * (BotUser::markSeen). У Open шагов два: профиль и подписка, остальные разделы ей закрыты.
     *
     * @return list<array{key: string, route: string, done: bool}>
     */
    private static function steps(BotUser $user, bool $paid): array
    {
        $profileDone = filled($user->description) && filled($user->expectation);

        if (! $paid) {
            return [
                ['key' => 'profile', 'route' => 'account.profile.edit', 'done' => $profileDone],
                ['key' => 'subscription', 'route' => 'account.subscription', 'done' => false],
            ];
        }

        return [
            ['key' => 'profile', 'route' => 'account.profile.edit', 'done' => $profileDone],
            ['key' => 'matches', 'route' => 'account.matches', 'done' => $user->hasSeen('matches')],
            ['key' => 'search', 'route' => 'account.search', 'done' => $user->hasSeen('search')],
            ['key' => 'opportunities', 'route' => 'account.opportunities.index', 'done' => $user->hasSeen('opportunities')],
            ['key' => 'assistant', 'route' => 'account.assistant', 'done' => $user->hasSeen('assistant')],
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
