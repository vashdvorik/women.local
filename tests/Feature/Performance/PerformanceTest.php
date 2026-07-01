<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Models\BotUser;
use App\Models\LoginToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Тесты производительности при нагрузке ~300 пользователей.
 *
 * Цель каждого теста — зафиксировать узкое место и установить
 * чёткий порог (query count, время), выход за который = регрессия.
 *
 * Запуск: php artisan test --filter=PerformanceTest
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────────────
    // УЗКОЕ МЕСТО №1
    // RequireAccountAuth: DB-запрос на КАЖДЫЙ защищённый запрос
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Каждый защищённый запрос делает SELECT в bot_users.
     * При 300 одновременных пользователях это 300 * (кол-во запросов/сессию) SELECT-ов.
     * Ожидаемый минимум: 1 запрос на запрос (без кеша).
     * Рекомендация: кешировать результат в Redis на 60 сек.
     */
    public function test_middleware_executes_exactly_one_db_query_per_request(): void
    {
        $user = BotUser::factory()->approved()->create();

        DB::enableQueryLog();

        $this->withSession(['account_telegram_id' => $user->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
            ->get(route('account.index'))
            ->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Фильтруем только SELECT по bot_users (запрос middleware)
        $authQueries = array_filter(
            $queries,
            static fn (array $q) => str_contains((string) $q['query'], 'bot_users')
                && str_contains((string) $q['query'], 'select')
        );

        // ТЕКУЩЕЕ ПОВЕДЕНИЕ: 1 SELECT на каждый запрос — допустимо сейчас,
        // но становится проблемой при 300 пользователей × N запросов в сессии.
        $this->assertCount(
            1,
            $authQueries,
            'ПРОБЛЕМА: Middleware делает SELECT bot_users на каждый запрос. '
            . 'При 300 пользователях и 10 запросах/сессию = 3000 SELECT/мин. '
            . 'РЕШЕНИЕ: Cache::remember("bot_user_{$telegramId}", 60, fn() => BotUser::...) в middleware.'
        );
    }

    /**
     * Проверяем общее кол-во запросов на dashboard при аутентификации.
     * Бюджет: не более 3 запросов (auth + session + страница).
     */
    public function test_dashboard_query_budget(): void
    {
        $user = BotUser::factory()->approved()->create();

        DB::enableQueryLog();

        $this->withSession(['account_telegram_id' => $user->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
            ->get(route('account.index'))
            ->assertOk();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            3,
            $count,
            "ПРОБЛЕМА: Dashboard делает {$count} DB-запросов вместо ≤3. "
            . 'Каждый лишний запрос умножается на 300 пользователей.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // УЗКОЕ МЕСТО №2
    // people(): ->get() без пагинации — полная таблица в память
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * При 300 пользователях people() загружает все 299 строк разом.
     * Проверяем, что кол-во запросов не зависит от числа пользователей (нет N+1).
     */
    public function test_people_page_has_no_n_plus_one_queries(): void
    {
        $user = BotUser::factory()->approved()->create();

        // Создаём 50 пользователей — достаточно для выявления N+1
        BotUser::factory()->approved()->count(50)->create();

        DB::enableQueryLog();

        $this->withSession(['account_telegram_id' => $user->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
            ->get(route('account.people'))
            ->assertOk();

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            3,
            $queryCount,
            "ПРОБЛЕМА N+1: people() делает {$queryCount} запросов при 50 пользователях. "
            . 'Если кол-во запросов растёт с числом пользователей — это N+1.'
        );
    }

    /**
     * С 300 пользователями people() возвращает полный список одним запросом.
     * Измеряем время — должно укладываться в 200 мс на тестовой БД.
     * Проблема: отсутствует пагинация. При 1000+ пользователей = проблема памяти.
     */
    public function test_people_page_response_time_with_300_users(): void
    {
        $user = BotUser::factory()->approved()->create();
        BotUser::factory()->approved()->count(299)->create();

        $start = microtime(true);

        $response = $this->withSession(['account_telegram_id' => $user->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
            ->get(route('account.people'));

        $elapsed = (microtime(true) - $start) * 1000; // в мс

        $response->assertOk();

        // 300 мс — мягкий порог для тестовой SQLite, 100 мс — для prod MySQL
        $this->assertLessThan(
            300.0,
            $elapsed,
            sprintf(
                'ПРОБЛЕМА ПРОИЗВОДИТЕЛЬНОСТИ: people() с 300 пользователями выполняется %.1f мс. '
                . 'РЕШЕНИЕ: добавить пагинацию paginate(20) и индекс на (status, full_name).',
                $elapsed
            )
        );
    }

    /**
     * Проверяем, что people() делает ровно 1 SELECT (не N запросов по пользователю).
     * Эта проверка документирует текущее состояние: нет N+1, но нет и пагинации.
     */
    public function test_people_page_uses_exactly_one_select_query(): void
    {
        $user = BotUser::factory()->approved()->create();
        BotUser::factory()->approved()->count(20)->create();

        DB::enableQueryLog();

        $this->withSession(['account_telegram_id' => $user->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
            ->get(route('account.people'))
            ->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $peopleQueries = array_filter(
            $queries,
            static fn (array $q) => str_contains((string) $q['query'], 'bot_users')
                && str_contains((string) $q['query'], 'select')
        );

        $this->assertCount(
            2,
            $peopleQueries,
            'ОЖИДАЕТСЯ: 2 SELECT на bot_users (1 — middleware auth, 1 — список людей). '
            . 'Если больше — появился N+1.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // УЗКОЕ МЕСТО №3
    // /go/{code}: LIKE-запрос по токену вместо точного match
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Маршрут /go/{code} использует LIKE 'xxxx%' вместо точного поиска.
     * Хотя MySQL может использовать индекс для prefix-LIKE, это неочевидно
     * и зависит от collation. Лучше хранить short_code отдельно.
     *
     * Тест фиксирует, что запрос выполняется и возвращает правильный редирект.
     */
    public function test_short_link_redirect_executes_one_db_query(): void
    {
        $user  = BotUser::factory()->approved()->create();
        $token = LoginToken::generateFor((int) $user->telegram_id);
        $code  = substr($token->token, 0, 8);

        DB::enableQueryLog();

        $this->get(route('account.go', ['code' => $code]))
            ->assertRedirect();

        $queries   = DB::getQueryLog();
        $likeQuery = array_filter(
            $queries,
            static fn (array $q) => str_contains((string) $q['query'], 'LIKE')
                || str_contains((string) $q['query'], 'like')
        );
        DB::disableQueryLog();

        $this->assertCount(
            1,
            $likeQuery,
            'ПРОБЛЕМА: /go/{code} использует LIKE-запрос. '
            . 'РЕШЕНИЕ: добавить колонку short_code VARCHAR(8) с уникальным индексом '
            . 'и искать по точному совпадению WHERE short_code = :code.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // УЗКОЕ МЕСТО №4
    // Множественные запросы подряд от одного пользователя (имитация навигации)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Пользователь обходит 5 страниц кабинета подряд.
     * Без кеша middleware = 5 SELECT на bot_users.
     * Тест документирует накопленную стоимость одной сессии.
     */
    public function test_session_navigation_query_accumulation(): void
    {
        $user = BotUser::factory()->approved()->create();

        $routes = [
            route('account.index'),
            route('account.profile'),
            route('account.matches'),
            route('account.knowledge'),
        ];

        $totalQueries = 0;

        foreach ($routes as $url) {
            DB::enableQueryLog();
            $this->withSession(['account_telegram_id' => $user->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
                ->get($url)
                ->assertOk();
            $totalQueries += count(DB::getQueryLog());
            DB::disableQueryLog();
        }

        // Максимальный бюджет: 4 маршрута × 3 запроса = 12
        $this->assertLessThanOrEqual(
            12,
            $totalQueries,
            sprintf(
                'ПРОБЛЕМА: 4 страницы кабинета = %d DB-запросов на одного пользователя. '
                . 'При 300 пользователях = %d запросов только на навигацию. '
                . 'РЕШЕНИЕ: кешировать BotUser в сессии или Redis.',
                $totalQueries,
                $totalQueries * 300
            )
        );
    }

    /**
     * Имитируем 10 одновременных пользователей, каждый делает запрос к dashboard.
     * Проверяем, что суммарное число DB-запросов линейно (нет shared state / N+1 между пользователями).
     */
    public function test_concurrent_users_query_count_is_linear(): void
    {
        $users = BotUser::factory()->approved()->count(10)->create();

        $totalQueries = 0;
        $queriesPerUser = [];

        foreach ($users as $user) {
            DB::enableQueryLog();
            DB::flushQueryLog();

            $this->withSession(['account_telegram_id' => $user->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
                ->get(route('account.index'))
                ->assertOk();

            $count = count(DB::getQueryLog());
            $queriesPerUser[] = $count;
            $totalQueries += $count;
            DB::disableQueryLog();
        }

        // Все пользователи должны генерировать одинаковое кол-во запросов
        $unique = array_unique($queriesPerUser);

        $this->assertCount(
            1,
            $unique,
            sprintf(
                'ПРОБЛЕМА: разные пользователи генерируют разное кол-во запросов: [%s]. '
                . 'Это может указывать на зависимость от состояния БД.',
                implode(', ', $queriesPerUser)
            )
        );

        // Не более 3 запросов на пользователя
        $this->assertLessThanOrEqual(
            3,
            (int) $unique[0],
            sprintf(
                'МАСШТАБИРОВАНИЕ: каждый пользователь генерирует %d запросов на dashboard. '
                . 'При 300 пользователях/мин = %d запросов/мин к БД.',
                (int) $unique[0],
                (int) $unique[0] * 300
            )
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // УЗКОЕ МЕСТО №5
    // LoginToken: генерация токена = 2 запроса (DELETE + INSERT)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * LoginToken::generateFor() удаляет старые токены и создаёт новый — 2 запроса.
     * При 300 запросах логина одновременно — нормально, но документируем.
     */
    public function test_login_token_generation_uses_two_queries(): void
    {
        $user = BotUser::factory()->approved()->create();

        DB::enableQueryLog();
        LoginToken::generateFor((int) $user->telegram_id);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(
            2,
            $queries,
            'LoginToken::generateFor() должен выполнять ровно 2 запроса: DELETE старых + INSERT нового.'
        );

        $this->assertStringContainsStringIgnoringCase(
            'delete',
            $queries[0]['query'],
            'Первый запрос должен удалять старые токены пользователя.'
        );

        $this->assertStringContainsStringIgnoringCase(
            'insert',
            $queries[1]['query'],
            'Второй запрос должен вставлять новый токен.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // УЗКОЕ МЕСТО №6
    // people(): память — размер данных при полной загрузке
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * При 300 пользователях people() возвращает 299 строк.
     * Если description и expectation заполнены, это сотни KB в памяти.
     * Тест проверяет, что запрос выбирает только нужные колонки (не SELECT *).
     */
    public function test_people_page_selects_only_required_columns(): void
    {
        $user = BotUser::factory()->approved()->create();
        BotUser::factory()->approved()->count(10)->create();

        DB::enableQueryLog();

        $this->withSession(['account_telegram_id' => $user->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
            ->get(route('account.people'))
            ->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Ищем запрос к people (не middleware auth-запрос)
        $peopleQuery = collect($queries)->first(
            static fn (array $q) => str_contains((string) $q['query'], 'bot_users')
                && str_contains((string) $q['query'], 'order by')
        );

        $this->assertNotNull($peopleQuery, 'Запрос к people должен содержать ORDER BY.');

        // Убеждаемся, что это не SELECT * (должны быть конкретные поля)
        $this->assertStringNotContainsString(
            'select *',
            strtolower((string) $peopleQuery['query']),
            'ПРОБЛЕМА: people() использует SELECT *. '
            . 'Это передаёт тяжёлые поля description/expectation без нужды. '
            . 'ТЕКУЩИЙ КОД использует ->get([\'id\', \'full_name\', \'telegram_username\', \'description\']) — OK. '
            . 'Убедитесь, что поле expectation не включено.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // СВОДНЫЙ ТЕСТ: узкие места при 300 пользователях
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Итоговый тест — ключевые метрики одним проходом.
     * Создаём 300 пользователей и замеряем people() — самый тяжёлый эндпоинт.
     */
    public function test_bottleneck_summary_with_300_users(): void
    {
        $currentUser = BotUser::factory()->approved()->create();
        BotUser::factory()->approved()->count(299)->create();

        // --- Замер people() ---
        DB::enableQueryLog();
        $start = microtime(true);

        $response = $this->withSession(['account_telegram_id' => $currentUser->telegram_id, '_account_expires' => now()->addDays(7)->timestamp])
            ->get(route('account.people'));

        $elapsedMs  = (microtime(true) - $start) * 1000;
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();

        // Собираем отчёт для вывода при падении
        $memoryPeakMb = memory_get_peak_usage(true) / 1024 / 1024;

        $report = sprintf(
            "\n\n=== СВОДКА УЗКИХ МЕСТ (300 пользователей) ===\n"
            . "Маршрут:           GET /app/account/people\n"
            . "DB-запросы:        %d\n"
            . "Время ответа:      %.1f мс\n"
            . "Пик памяти (тест): %.1f MB\n\n"
            . "КРИТИЧЕСКИЕ ПРОБЛЕМЫ:\n"
            . "  1. Middleware делает SELECT bot_users на КАЖДЫЙ запрос (нет кеша)\n"
            . "     → При 300 юзеров × 10 запросов/сессию = 3000 SELECT/мин\n"
            . "     → РЕШЕНИЕ: Cache::remember('bot_user_'.\$id, 60, fn()=>...)\n\n"
            . "  2. people() загружает ВСЕ 299 строк без пагинации\n"
            . "     → При 1000+ пользователей = сотни KB в памяти на запрос\n"
            . "     → РЕШЕНИЕ: ->paginate(20) + индекс (status, full_name)\n\n"
            . "  3. sendLink() делает синхронный HTTP-вызов к Telegram API\n"
            . "     → Блокирует PHP-воркер на ~200-500 мс\n"
            . "     → РЕШЕНИЕ: SendMagicLink::dispatch(\$user) (Job в очереди)\n\n"
            . "  4. /go/{code} использует LIKE-запрос\n"
            . "     → РЕШЕНИЕ: добавить колонку short_code с unique index\n\n"
            . "  5. Файловые сессии под нагрузкой — блокировки файлов\n"
            . "     → РЕШЕНИЕ: SESSION_DRIVER=redis в .env\n",
            $queryCount,
            $elapsedMs,
            $memoryPeakMb
        );

        $this->assertLessThan(300.0, $elapsedMs, $report);
        $this->assertLessThanOrEqual(3, $queryCount, $report);
    }
}
