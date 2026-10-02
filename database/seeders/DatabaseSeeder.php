<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Полная начальная загрузка: всё, что сайт показывает из БД, воспроизводится отсюда.
 * Безопасно запускать повторно: каждый сидер добавляет только недостающее.
 *
 *   php artisan db:seed
 *   php artisan migrate:fresh --seed --force   (перезапись БД с нуля)
 *
 * Везде: администратор из .env, тема сайта и кабинета, эксперты и новости.
 * Только вне боевого сервера (APP_ENV не production): тестовые участницы, демо-тексты новостей
 * и настройки ИИ.
 * Настоящим участницам на боевом сервере фиктивные профили не нужны, а выбор провайдера ИИ
 * зависит от того, какие ключи там внесены, поэтому сидер не должен подменять его сам.
 * Тестовые участницы (BotUserSeeder, CommunitySeeder) и демо-тексты новостей (DemoNewsSeeder) на
 * боевом сервере не создаются вообще, даже при прямом запуске. Настройки ИИ там запускают отдельно:
 * db:seed --class=AiSettingSeeder.
 *
 * Сидеры не используют фабрики: Faker стоит только в dev-зависимостях, на боевом сервере
 * (composer install --no-dev) его нет, и запуск падал бы на первой же фабрике.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Администратор берётся из .env. Если его данные не подошли (нет пароля, пароль шаблонный),
        // остальное содержимое всё равно загружается, а ошибка выбрасывается в самом конце. Иначе при
        // migrate:fresh --seed таблицы уже стёрты, а сайт остался бы пустым: без экспертов и новостей.
        $adminError = null;

        if (filled(config('admin.email')) && filled(config('admin.password'))) {
            try {
                $this->call(AdminUserSeeder::class);
            } catch (RuntimeException $e) {
                $adminError = $e;
            }
        } else {
            $this->command?->warn('ADMIN_EMAIL и ADMIN_PASSWORD не заданы в .env: администратор не создан (php artisan db:seed --class=AdminUserSeeder).');
        }

        $this->call([
            SiteSettingSeeder::class, // тема сайта и кабинета
            ContentSeeder::class,     // эксперты и новости публичного сайта
        ]);

        if (app()->isProduction()) {
            $this->command?->warn('Боевой сервер: тестовые участницы и демо-тексты новостей не создаются (демо-данные только для разработки), настройки ИИ пропущены (AiSettingSeeder запускайте отдельно, после внесения ключей).');
        } else {
            $this->call([
                DemoNewsSeeder::class,    // демо-текст и фото на страницах новостей
                AiSettingSeeder::class,   // провайдеры и модели ИИ (без ключей API)
                BotUserSeeder::class,     // тестовые участницы для разработки
                CommunitySeeder::class,
            ]);
        }

        if ($adminError !== null) {
            $this->command?->error('Содержимое сайта загружено, но администратор НЕ создан. Исправьте .env и запустите: php artisan db:seed --class=AdminUserSeeder --force');

            throw $adminError;
        }
    }
}
