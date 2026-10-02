<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Внешний вид, который действует в рабочей БД: тема публичного сайта и тема кабинета участниц.
 * Значения снимаются с работающей БД, поэтому свежая установка выглядит так же.
 *
 * Идемпотентен и ничего не перезаписывает: тема, которую уже выбрали в админке, остаётся как есть,
 * добавляются только недостающие.
 *
 *   php artisan db:seed --class=SiteSettingSeeder
 *
 * Параметры ИИ вынесены в AiSettingSeeder: они зависят от окружения.
 */
class SiteSettingSeeder extends Seeder
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function settings(): array
    {
        return [
            SiteSetting::LANDING_THEME_KEY => ['theme' => 'miro'],
            SiteSetting::ACCOUNT_THEME_KEY => ['theme' => 'fortuntwo'],
        ];
    }

    public function run(): void
    {
        $created = 0;

        foreach (self::settings() as $key => $value) {
            $setting = SiteSetting::query()->firstOrCreate(['key' => $key], ['value' => $value]);

            if ($setting->wasRecentlyCreated) {
                $created++;
            }
        }

        // Темы кешируются навсегда; без этого свежая строка не подхватится до сброса кеша.
        Cache::forget(SiteSetting::LANDING_THEME_KEY);
        Cache::forget(SiteSetting::ACCOUNT_THEME_KEY);

        $this->command?->info($created > 0
            ? "Тем оформления добавлено: {$created}."
            : 'Темы оформления уже заданы — импорт пропущен.');
    }
}
