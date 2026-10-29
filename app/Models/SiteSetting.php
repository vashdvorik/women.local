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

    public const SUBSCRIPTION_PRICES_KEY = 'subscription_prices';

    /** Тарифы, покупка которых закрыта: вместо «Купить» стоит «Оставить заявку» (менеджер откроет покупку лично). */
    public const SUBSCRIPTION_CLOSED_KEY = 'subscription_closed';

    /** Почта менеджера, на которую приходят заявки на закрытые тарифы. */
    public const PLAN_REQUEST_EMAIL_KEY = 'plan_request_email';

    /** Самая большая цена подписки, которую принимает админка (в рублях): защита от лишнего нуля. */
    public const SUBSCRIPTION_PRICE_MAX = 1_000_000;

    /** Тема сайта по умолчанию: к ней возвращается любое неизвестное значение (например, тема, которую удалили). */
    public const DEFAULT_LANDING_THEME = 'miro';

    /** Тема кабинета по умолчанию, по тому же правилу. */
    public const DEFAULT_ACCOUNT_THEME = 'fortuntwo';

    /**
     * Темы публичного сайта: ключ — имя папок resources/views/themes/public/{ключ} и public/themes/public/{ключ}.
     * Чтобы добавить тему, заведите её папки и строку здесь: выбор в админке появится сам (docs/PROJECT_MAP.md, «Как добавить тему»).
     */
    public const LANDING_THEMES = [
        'miro' => 'Miro',
        'miro2' => 'Miro 2',
    ];

    /**
     * Темы кабинета участницы: ключ — имя папок resources/views/themes/account/{ключ} и public/themes/account/{ключ}.
     *
     * Каталог намеренно отдельный от тем сайта: смена оформления сайта не должна менять кабинет.
     */
    public const ACCOUNT_THEMES = [
        'fortuntwo' => 'Fortun Two',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /** Простое значение настройки: сжатие изображений, флаги и т. п. */
    public static function read(string $key, mixed $default = null): mixed
    {
        $stored = self::query()->where('key', $key)->first()?->value;

        return $stored ?? $default;
    }

    public static function write(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function landingTheme(): string
    {
        return self::knownTheme(
            Cache::rememberForever(self::LANDING_THEME_KEY, fn (): string => self::storedTheme(self::LANDING_THEME_KEY)),
            self::LANDING_THEMES,
            self::DEFAULT_LANDING_THEME,
        );
    }

    public static function setLandingTheme(string $theme): void
    {
        self::updateOrCreate(
            ['key' => self::LANDING_THEME_KEY],
            ['value' => ['theme' => self::knownTheme($theme, self::LANDING_THEMES, self::DEFAULT_LANDING_THEME)]]
        );

        Cache::forget(self::LANDING_THEME_KEY);
    }

    public static function accountTheme(): string
    {
        return self::knownTheme(
            Cache::rememberForever(self::ACCOUNT_THEME_KEY, fn (): string => self::storedTheme(self::ACCOUNT_THEME_KEY)),
            self::ACCOUNT_THEMES,
            self::DEFAULT_ACCOUNT_THEME,
        );
    }

    public static function setAccountTheme(string $theme): void
    {
        self::updateOrCreate(
            ['key' => self::ACCOUNT_THEME_KEY],
            ['value' => ['theme' => self::knownTheme($theme, self::ACCOUNT_THEMES, self::DEFAULT_ACCOUNT_THEME)]]
        );

        Cache::forget(self::ACCOUNT_THEME_KEY);
    }

    /**
     * Имя темы из настройки как есть, без проверки каталога. Проверяет knownTheme(): тему могли удалить,
     * а в базе и в кеше она осталась.
     */
    private static function storedTheme(string $key): string
    {
        if (! Schema::hasTable('site_settings')) {
            return '';
        }

        $value = self::where('key', $key)->first()?->value;

        return is_array($value) && is_string($value['theme'] ?? null) ? $value['theme'] : '';
    }

    /**
     * Тема из каталога, а если такой нет (удалена или опечатка), тема по умолчанию. Проверяется и значение из кеша:
     * после удаления темы в кеше сервера ещё лежит её имя, и без проверки сайт искал бы папки, которых нет.
     *
     * @param  array<string, string>  $catalogue
     */
    private static function knownTheme(mixed $theme, array $catalogue, string $default): string
    {
        return is_string($theme) && array_key_exists($theme, $catalogue) ? $theme : $default;
    }

    /**
     * Цены подписки, заданные в админке, в целых рублях: ['community' => 600, 'active' => 5000, 'private' => 19000]. Чего здесь нет,
     * берётся из config/subscription.php (см. Plan::price()). Читается на каждой странице тарифов, поэтому кешируется.
     *
     * @return array<string, int>
     */
    public static function subscriptionPrices(): array
    {
        return Cache::rememberForever(self::SUBSCRIPTION_PRICES_KEY, function (): array {
            $stored = self::settingValue(self::SUBSCRIPTION_PRICES_KEY);
            $prices = [];

            foreach (['community', 'active', 'private'] as $plan) {
                $price = $stored[$plan] ?? null;

                if (is_int($price) && $price >= 1 && $price <= self::SUBSCRIPTION_PRICE_MAX) {
                    $prices[$plan] = $price;
                }
            }

            return $prices;
        });
    }

    /** @param array{community: int, active: int, private: int} $prices */
    public static function setSubscriptionPrices(array $prices): void
    {
        self::updateOrCreate(['key' => self::SUBSCRIPTION_PRICES_KEY], ['value' => [
            'community' => (int) $prices['community'],
            'active' => (int) $prices['active'],
            'private' => (int) $prices['private'],
        ]]);

        Cache::forget(self::SUBSCRIPTION_PRICES_KEY);
    }

    /**
     * Тарифы, покупка которых закрыта («Оставить заявку» вместо «Купить»): ключи 'community', 'active', 'private'.
     * По умолчанию все три открыты. Читается на каждой странице тарифов, поэтому кешируется.
     *
     * @return list<string>
     */
    public static function closedPlans(): array
    {
        return Cache::rememberForever(self::SUBSCRIPTION_CLOSED_KEY, function (): array {
            $stored = self::settingValue(self::SUBSCRIPTION_CLOSED_KEY);

            return array_values(array_intersect(['community', 'active', 'private'], is_array($stored) ? $stored : []));
        });
    }

    /** @param list<string> $plans */
    public static function setClosedPlans(array $plans): void
    {
        self::updateOrCreate(['key' => self::SUBSCRIPTION_CLOSED_KEY], ['value' => array_values(array_intersect(['community', 'active', 'private'], $plans))]);

        Cache::forget(self::SUBSCRIPTION_CLOSED_KEY);
    }

    /** Почта менеджера для заявок на закрытые тарифы; пока не задана, пусто. */
    public static function planRequestEmail(): string
    {
        $stored = self::settingValue(self::PLAN_REQUEST_EMAIL_KEY)['email'] ?? '';

        return is_string($stored) ? trim($stored) : '';
    }

    public static function setPlanRequestEmail(string $email): void
    {
        self::updateOrCreate(['key' => self::PLAN_REQUEST_EMAIL_KEY], ['value' => ['email' => trim($email)]]);
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
