<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    public const LANDING_THEME_KEY = 'landing_theme';

    public const ACCOUNT_THEME_KEY = 'account_theme';

    public const GEMINI_EMBEDDING_KEY = 'gemini_embedding';

    public const DEEPSEEK_PROVIDER_KEY = 'deepseek_provider';

    public const OPENROUTER_PROVIDER_KEY = 'openrouter_provider';

    public const AI_FEATURES_KEY = 'ai_features';

    public const AI_ASSISTANT_KNOWLEDGE_KEY = 'ai_assistant_knowledge';

    public const LANDING_THEMES = [
        'miro'     => 'Miro',
        'fortun'   => 'Fortun',
        'platform' => 'Platform',
    ];

    /**
     * Themes for the authenticated participant cabinet.
     *
     * These are intentionally a separate catalogue from the public landing
     * themes: changing the landing template must not change the cabinet UI.
     */
    public const ACCOUNT_THEMES = [
        'miro'    => 'Miro',
        'fortun'  => 'Fortun',
        'classic' => 'Классическая фиолетовая',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function landingTheme(): string
    {
        return Cache::rememberForever(self::LANDING_THEME_KEY, function (): string {
            if (! Schema::hasTable('site_settings')) {
                return 'miro';
            }

            $setting = self::where('key', self::LANDING_THEME_KEY)->first();
            $value   = $setting?->value ?? [];
            $theme   = is_array($value) ? ($value['theme'] ?? 'miro') : 'miro';

            return array_key_exists($theme, self::LANDING_THEMES) ? $theme : 'miro';
        });
    }

    public static function setLandingTheme(string $theme): void
    {
        if (! array_key_exists($theme, self::LANDING_THEMES)) {
            $theme = 'miro';
        }

        self::updateOrCreate(
            ['key' => self::LANDING_THEME_KEY],
            ['value' => ['theme' => $theme]]
        );

        Cache::forget(self::LANDING_THEME_KEY);
    }

    public static function accountTheme(): string
    {
        return Cache::rememberForever(self::ACCOUNT_THEME_KEY, function (): string {
            if (! Schema::hasTable('site_settings')) {
                return 'classic';
            }

            $setting = self::where('key', self::ACCOUNT_THEME_KEY)->first();
            $value   = $setting?->value ?? [];
            $theme   = is_array($value) ? ($value['theme'] ?? 'classic') : 'classic';

            return array_key_exists($theme, self::ACCOUNT_THEMES) ? $theme : 'classic';
        });
    }

    public static function setAccountTheme(string $theme): void
    {
        if (! array_key_exists($theme, self::ACCOUNT_THEMES)) {
            $theme = 'classic';
        }

        self::updateOrCreate(
            ['key' => self::ACCOUNT_THEME_KEY],
            ['value' => ['theme' => $theme]]
        );

        Cache::forget(self::ACCOUNT_THEME_KEY);
    }

    /** @return array{base_url: string, model: string, timeout: int, api_key_configured: bool} */
    public static function geminiEmbeddingConfig(): array
    {
        $defaults = [
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'model' => 'gemini-embedding-001',
            'timeout' => 15,
        ];

        $value = self::settingValue(self::GEMINI_EMBEDDING_KEY);

        return [
            'base_url' => is_string($value['base_url'] ?? null) ? $value['base_url'] : $defaults['base_url'],
            'model' => is_string($value['model'] ?? null) ? $value['model'] : $defaults['model'],
            'timeout' => is_numeric($value['timeout'] ?? null) ? (int) $value['timeout'] : $defaults['timeout'],
            'api_key_configured' => self::encryptedKeyExists($value),
        ];
    }

    public static function geminiEmbeddingApiKey(): ?string
    {
        return self::decryptSettingKey(self::GEMINI_EMBEDDING_KEY);
    }

    /** @param array{base_url: string, model: string, timeout: int, api_key?: string} $config */
    public static function setGeminiEmbeddingConfig(array $config): void
    {
        self::storeProviderConfig(self::GEMINI_EMBEDDING_KEY, $config, ['base_url', 'model', 'timeout']);
    }

    /** @return array{base_url: string, timeout: int, api_key_configured: bool} */
    public static function openRouterProviderConfig(): array
    {
        $defaults = [
            'base_url' => 'https://openrouter.ai/api/v1',
            'timeout' => 30,
        ];
        $value = self::settingValue(self::OPENROUTER_PROVIDER_KEY);

        return [
            'base_url' => is_string($value['base_url'] ?? null) ? $value['base_url'] : $defaults['base_url'],
            'timeout' => is_numeric($value['timeout'] ?? null) ? (int) $value['timeout'] : $defaults['timeout'],
            'api_key_configured' => self::encryptedKeyExists($value),
        ];
    }

    public static function openRouterProviderApiKey(): ?string
    {
        return self::decryptSettingKey(self::OPENROUTER_PROVIDER_KEY);
    }

    /** @param array{base_url: string, timeout: int, api_key?: string} $config */
    public static function setOpenRouterProviderConfig(array $config): void
    {
        self::storeProviderConfig(self::OPENROUTER_PROVIDER_KEY, $config, ['base_url', 'timeout']);
    }

    /** @return array{provider: string, model: string, timeout: int} */
    public static function embeddingFeatureConfig(): array
    {
        $gemini = self::geminiEmbeddingConfig();
        $value = self::settingValue(self::AI_FEATURES_KEY);
        $provider = in_array($value['embedding_provider'] ?? null, ['gemini', 'openrouter'], true)
            ? $value['embedding_provider']
            : 'gemini';

        return [
            'provider' => $provider,
            'model' => is_string($value['embedding_model'] ?? null) && $value['embedding_model'] !== ''
                ? $value['embedding_model']
                : ($provider === 'openrouter' ? 'baai/bge-m3' : $gemini['model']),
            'timeout' => is_numeric($value['embedding_timeout'] ?? null)
                ? (int) $value['embedding_timeout']
                : ($provider === 'openrouter' ? self::openRouterProviderConfig()['timeout'] : $gemini['timeout']),
        ];
    }

    /** @param array{provider: string, model: string, timeout: int} $config */
    public static function setEmbeddingFeatureConfig(array $config): void
    {
        self::storeFeatureConfig([
            'embedding_provider' => $config['provider'],
            'embedding_model' => $config['model'],
            'embedding_timeout' => $config['timeout'],
        ]);
    }

    public static function searchMinScore(): float
    {
        $value = self::settingValue(self::AI_FEATURES_KEY)['search_min_score'] ?? config('ai.search_min_score', 0.65);

        return is_numeric($value) ? max(0.0, min(1.0, (float) $value)) : 0.65;
    }

    public static function setSearchMinScore(float $score): void
    {
        self::storeFeatureConfig(['search_min_score' => max(0.0, min(1.0, $score))]);
    }

    /** @return array{provider: string, model: string, temperature: float, max_tokens: int, timeout: int} */
    public static function agentFeatureConfig(): array
    {
        $deepSeek = self::deepSeekProviderConfig();
        $value = self::settingValue(self::AI_FEATURES_KEY);
        $provider = in_array($value['agent_provider'] ?? null, ['openrouter', 'deepseek'], true)
            ? $value['agent_provider']
            : 'deepseek';

        return [
            'provider' => $provider,
            'model' => is_string($value['agent_model'] ?? null) && $value['agent_model'] !== '' ? $value['agent_model'] : $deepSeek['model'],
            'temperature' => is_numeric($value['agent_temperature'] ?? null) ? (float) $value['agent_temperature'] : $deepSeek['temperature'],
            'max_tokens' => is_numeric($value['agent_max_tokens'] ?? null) ? (int) $value['agent_max_tokens'] : $deepSeek['max_tokens'],
            'timeout' => is_numeric($value['agent_timeout'] ?? null) ? (int) $value['agent_timeout'] : $deepSeek['timeout'],
        ];
    }

    /** @param array{provider: string, model: string, temperature: float, max_tokens: int, timeout: int} $config */
    public static function setAgentFeatureConfig(array $config): void
    {
        self::storeFeatureConfig([
            'agent_provider' => $config['provider'],
            'agent_model' => $config['model'],
            'agent_temperature' => $config['temperature'],
            'agent_max_tokens' => $config['max_tokens'],
            'agent_timeout' => $config['timeout'],
        ]);
    }

    /** @return array{rules: string, ru: string, en: string, ro: string} */
    public static function aiAssistantKnowledge(): array
    {
        $value = self::settingValue(self::AI_ASSISTANT_KNOWLEDGE_KEY);

        return [
            'rules' => is_string($value['rules'] ?? null) ? $value['rules'] : '',
            'ru' => is_string($value['ru'] ?? null) ? $value['ru'] : '',
            'en' => is_string($value['en'] ?? null) ? $value['en'] : '',
            'ro' => is_string($value['ro'] ?? null) ? $value['ro'] : '',
        ];
    }

    /** @param array{rules?: string, ru?: string, en?: string, ro?: string} $knowledge */
    public static function setAiAssistantKnowledge(array $knowledge): void
    {
        self::updateOrCreate(['key' => self::AI_ASSISTANT_KNOWLEDGE_KEY], ['value' => [
            'rules' => trim((string) ($knowledge['rules'] ?? '')),
            'ru' => trim((string) ($knowledge['ru'] ?? '')),
            'en' => trim((string) ($knowledge['en'] ?? '')),
            'ro' => trim((string) ($knowledge['ro'] ?? '')),
        ]]);
    }

    /** @return array{base_url: string, model: string, temperature: float, max_tokens: int, timeout: int, api_key_configured: bool} */
    public static function deepSeekProviderConfig(): array
    {
        $defaults = [
            'base_url' => 'https://api.deepseek.com',
            'model' => 'deepseek-v4-flash',
            'temperature' => 0.3,
            'max_tokens' => 1024,
            'timeout' => 30,
        ];

        $value = self::settingValue(self::DEEPSEEK_PROVIDER_KEY);

        return [
            'base_url' => is_string($value['base_url'] ?? null) ? $value['base_url'] : $defaults['base_url'],
            'model' => is_string($value['model'] ?? null) ? $value['model'] : $defaults['model'],
            'temperature' => is_numeric($value['temperature'] ?? null) ? (float) $value['temperature'] : $defaults['temperature'],
            'max_tokens' => is_numeric($value['max_tokens'] ?? null) ? (int) $value['max_tokens'] : $defaults['max_tokens'],
            'timeout' => is_numeric($value['timeout'] ?? null) ? (int) $value['timeout'] : $defaults['timeout'],
            'api_key_configured' => self::encryptedKeyExists($value),
        ];
    }

    public static function deepSeekProviderApiKey(): ?string
    {
        return self::decryptSettingKey(self::DEEPSEEK_PROVIDER_KEY);
    }

    /** @param array{base_url: string, model: string, temperature: float, max_tokens: int, timeout: int, api_key?: string} $config */
    public static function setDeepSeekProviderConfig(array $config): void
    {
        self::storeProviderConfig(self::DEEPSEEK_PROVIDER_KEY, $config, ['base_url', 'model', 'temperature', 'max_tokens', 'timeout']);
    }

    /** @return array<string, mixed> */
    private static function settingValue(string $key): array
    {
        if (! Schema::hasTable('site_settings')) {
            return [];
        }

        $value = self::query()->where('key', $key)->first()?->value;

        return is_array($value) ? $value : [];
    }

    /** @param array<string, mixed> $value */
    private static function encryptedKeyExists(array $value): bool
    {
        return is_string($value['api_key'] ?? null) && $value['api_key'] !== '';
    }

    private static function decryptSettingKey(string $key): ?string
    {
        $encryptedKey = self::settingValue($key)['api_key'] ?? null;

        if (! is_string($encryptedKey) || $encryptedKey === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedKey);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $config
     * @param list<string> $fields
     */
    private static function storeProviderConfig(string $key, array $config, array $fields): void
    {
        $current = self::settingValue($key);
        $value = [];

        foreach ($fields as $field) {
            $value[$field] = $config[$field];
        }

        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $value['api_key'] = $apiKey !== '' ? Crypt::encryptString($apiKey) : ($current['api_key'] ?? null);

        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** @param array<string, mixed> $changes */
    private static function storeFeatureConfig(array $changes): void
    {
        $value = array_merge(self::settingValue(self::AI_FEATURES_KEY), $changes);

        self::updateOrCreate(['key' => self::AI_FEATURES_KEY], ['value' => $value]);
    }
}
