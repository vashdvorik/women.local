<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила блоков. Поля провайдеров можно проверять по отдельности
     * (кнопка «Проверить подключение» шлёт всю форму, но валидирует только свой блок).
     *
     * @return array<string, array<int, string>>
     */
    public static function providerRules(string $provider): array
    {
        return match ($provider) {
            'gemini' => [
                'gemini_base_url' => ['required', 'url', 'max:255'],
                'gemini_model' => ['required', 'string', 'max:120'],
                'gemini_timeout' => ['required', 'integer', 'min:5', 'max:120'],
                'gemini_api_key' => ['nullable', 'string', 'max:500'],
            ],
            'openrouter' => [
                'openrouter_base_url' => ['required', 'url', 'max:255'],
                'openrouter_timeout' => ['required', 'integer', 'min:5', 'max:120'],
                'openrouter_api_key' => ['nullable', 'string', 'max:500'],
            ],
            'deepseek' => [
                'deepseek_base_url' => ['required', 'url', 'max:255'],
                'deepseek_model' => ['required', 'string', 'max:120'],
                'deepseek_temperature' => ['required', 'numeric', 'min:0', 'max:2'],
                'deepseek_max_tokens' => ['required', 'integer', 'min:64', 'max:32768'],
                'deepseek_timeout' => ['required', 'integer', 'min:5', 'max:120'],
                'deepseek_api_key' => ['nullable', 'string', 'max:500'],
            ],
        };
    }

    public function rules(): array
    {
        return array_merge(
            self::providerRules('gemini'),
            self::providerRules('openrouter'),
            self::providerRules('deepseek'),
            [
                'embedding_provider' => ['required', 'in:gemini,openrouter'],
                'embedding_model' => ['required', 'string', 'max:160'],
                'embedding_timeout' => ['required', 'integer', 'min:5', 'max:120'],
                'search_min_score' => ['required', 'numeric', 'min:0', 'max:1'],
                'agent_provider' => ['required', 'in:openrouter,deepseek'],
                'agent_model' => ['required', 'string', 'max:160'],
                'agent_temperature' => ['required', 'numeric', 'min:0', 'max:2'],
                'agent_max_tokens' => ['required', 'integer', 'min:64', 'max:32768'],
                'agent_timeout' => ['required', 'integer', 'min:5', 'max:120'],
            ],
        );
    }

    public function attributes(): array
    {
        return [
            'gemini_base_url' => 'Gemini: Base URL',
            'gemini_model' => 'Gemini: embedding-модель',
            'gemini_timeout' => 'Gemini: тайм-аут',
            'gemini_api_key' => 'Gemini: API-ключ',
            'openrouter_base_url' => 'OpenRouter: Base URL',
            'openrouter_timeout' => 'OpenRouter: тайм-аут',
            'openrouter_api_key' => 'OpenRouter: API-ключ',
            'deepseek_base_url' => 'DeepSeek: Base URL',
            'deepseek_model' => 'DeepSeek: модель',
            'deepseek_temperature' => 'DeepSeek: temperature',
            'deepseek_max_tokens' => 'DeepSeek: максимум токенов',
            'deepseek_timeout' => 'DeepSeek: тайм-аут',
            'deepseek_api_key' => 'DeepSeek: API-ключ',
            'embedding_provider' => 'embeddings: провайдер',
            'embedding_model' => 'embeddings: модель',
            'embedding_timeout' => 'embeddings: тайм-аут',
            'search_min_score' => 'минимальная релевантность',
            'agent_provider' => 'AI-помощник: провайдер',
            'agent_model' => 'AI-помощник: модель',
            'agent_temperature' => 'AI-помощник: temperature',
            'agent_max_tokens' => 'AI-помощник: максимум токенов',
            'agent_timeout' => 'AI-помощник: тайм-аут',
        ];
    }
}
