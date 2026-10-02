<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Параметры ИИ, которые действуют в рабочей БД: какие провайдеры и модели отвечают за
 * агента-ассистента и за поиск по смыслу, адреса провайдеров, порог поиска.
 *
 * Только несекретное. Ключей API здесь нет и быть не должно: сидер лежит в git. Ключи вносят
 * в админке («Кабинеты участниц → Настройки»), там они шифруются.
 *
 * DatabaseSeeder не запускает его на боевом сервере. Причина: выбор провайдера зависит от того,
 * какие ключи там внесены. Пока строки `ai_features` нет, эмбеддинги идут через Gemini с ключом из
 * .env; строка с провайдером OpenRouter без ключа в настройках сломала бы поиск и подбор.
 * На боевом сервере его запускают осознанно и после внесения ключей.
 *
 * Идемпотентен и ничего не перезаписывает: настройка, которая уже есть (её сохранили в админке),
 * остаётся как есть, добавляются только недостающие.
 *
 *   php artisan db:seed --class=AiSettingSeeder
 */
class AiSettingSeeder extends Seeder
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function settings(): array
    {
        return [
            // Какие провайдеры и модели отвечают за агента-ассистента и за поиск по смыслу.
            SiteSetting::AI_FEATURES_KEY => [
                'agent_provider' => 'openrouter',
                'agent_model' => 'deepseek/deepseek-chat-v3.1',
                'agent_timeout' => 30,
                'agent_max_tokens' => 1024,
                'agent_temperature' => 0.3,
                'embedding_provider' => 'openrouter',
                'embedding_model' => 'baai/bge-m3',
                'embedding_timeout' => 15,
                'search_min_score' => 0.45,
            ],

            // Адреса и параметры провайдеров. Без api_key: его вносят в админке.
            SiteSetting::OPENROUTER_PROVIDER_KEY => [
                'base_url' => 'https://openrouter.ai/api/v1',
                'timeout' => 30,
            ],
            SiteSetting::DEEPSEEK_PROVIDER_KEY => [
                'base_url' => 'https://api.deepseek.com',
                'model' => 'deepseek-v4-flash',
                'timeout' => 30,
                'max_tokens' => 1024,
                'temperature' => 0.3,
            ],
            SiteSetting::GEMINI_EMBEDDING_KEY => [
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'model' => 'gemini-embedding-001',
                'timeout' => 15,
            ],
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

        $this->command?->info($created > 0
            ? "Настроек ИИ добавлено: {$created}."
            : 'Настройки ИИ уже есть — импорт пропущен.');
    }
}
