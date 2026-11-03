<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Models\BotUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Учёт активности участниц в кабинете для раздела «Статистика»: какой раздел или действие, кто и в какой день, сколько раз.
 * Хранится по строке на участницу, день и ключ (таблица cabinet_activity), поэтому растёт медленно. Сбой учёта страницу
 * не ломает: он только пишется в журнал.
 *
 * Ключи: разделы кабинета (home, matches, search, people, people_profile, assistant, manager, possibilities, knowledge,
 * initiatives, profile, subscription) и действия (assistant_question, manager_question, search_query, initiative_created,
 * profile_updated, checkout_started, plan_request, telegram_connect).
 */
final class CabinetActivity
{
    /** Как часто обновляется «последний раз в кабинете»: чаще не нужно, а запись в базу на каждый переход лишняя. */
    private const SEEN_EVERY_MINUTES = 5;

    /** Страницы (GET): имя маршрута → ключ раздела. */
    private const PAGES = [
        'account.index' => 'home',
        'account.matches' => 'matches',
        'account.search' => 'search',
        'account.people' => 'people',
        'account.people.show' => 'people_profile',
        'account.assistant' => 'assistant',
        'account.manager' => 'manager',
        'account.manager.history' => 'manager',
        'account.possibilities.index' => 'possibilities',
        'account.possibilities.show' => 'possibilities',
        'account.possibilities.post' => 'possibilities',
        'account.knowledge' => 'knowledge',
        'account.knowledge.show' => 'knowledge',
        'account.knowledge.post' => 'knowledge',
        'account.opportunities.index' => 'initiatives',
        'account.opportunities.create' => 'initiatives',
        'account.profile' => 'profile',
        'account.profile.edit' => 'profile',
        'account.subscription' => 'subscription',
    ];

    /** Действия (POST): имя маршрута → ключ действия. */
    private const ACTIONS = [
        'account.assistant.message' => 'assistant_question',
        'account.manager.message' => 'manager_question',
        'account.opportunities.store' => 'initiative_created',
        'account.profile.update' => 'profile_updated',
        'account.subscription.checkout' => 'checkout_started',
        'account.plan-request.store' => 'plan_request',
        'account.telegram.connect' => 'telegram_connect',
    ];

    /** @return list<string> ключи, которые стоит записать за этот запрос */
    public function keysFor(Request $request): array
    {
        $route = (string) $request->route()?->getName();
        $keys = [];

        if ($request->isMethod('GET') && isset(self::PAGES[$route])) {
            $keys[] = self::PAGES[$route];

            // Поиск с запросом — это уже действие, а не просто открытая страница
            if ($route === 'account.search' && filled($request->query('q'))) {
                $keys[] = 'search_query';
            }
        } elseif ($request->isMethod('POST') && isset(self::ACTIONS[$route])) {
            $keys[] = self::ACTIONS[$route];
        }

        return $keys;
    }

    /** Записывает ключи за сегодня и обновляет «последний раз в кабинете». */
    public function record(BotUser $user, array $keys): void
    {
        try {
            $now = now();

            foreach ($keys as $key) {
                DB::table('cabinet_activity')->upsert(
                    [['bot_user_id' => $user->getKey(), 'day' => $now->toDateString(), 'key' => $key, 'hits' => 1, 'created_at' => $now, 'updated_at' => $now]],
                    ['bot_user_id', 'day', 'key'],
                    ['hits' => DB::raw('hits + 1'), 'updated_at' => $now],
                );
            }

            if ($user->last_seen_at === null || $user->last_seen_at->lt($now->copy()->subMinutes(self::SEEN_EVERY_MINUTES))) {
                DB::table('bot_users')->where('id', $user->getKey())->update(['last_seen_at' => $now]);
                $user->last_seen_at = $now;
            }
        } catch (Throwable $e) {
            Log::warning('Не удалось записать активность участницы', ['user' => $user->getKey(), 'error' => $e->getMessage()]);
        }
    }
}
