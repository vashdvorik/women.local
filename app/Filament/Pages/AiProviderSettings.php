<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AiProviderSettings extends Page
{
    protected string $view = 'filament.pages.ai-provider-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'AI-провайдеры';

    protected static ?string $title = 'Настройки AI-провайдеров';

    protected static \UnitEnum|string|null $navigationGroup = 'Интеграции';

    protected static ?int $navigationSort = 5;

    public string $geminiBaseUrl = '';
    public string $geminiModel = '';
    public int $geminiTimeout = 15;
    public string $geminiApiKey = '';
    public bool $geminiApiKeyConfigured = false;

    public string $openRouterBaseUrl = '';
    public int $openRouterTimeout = 30;
    public string $openRouterApiKey = '';
    public bool $openRouterApiKeyConfigured = false;

    public string $deepSeekBaseUrl = '';
    public string $deepSeekModel = '';
    public float $deepSeekTemperature = 0.3;
    public int $deepSeekMaxTokens = 1024;
    public int $deepSeekTimeout = 30;
    public string $deepSeekApiKey = '';
    public bool $deepSeekApiKeyConfigured = false;

    public string $embeddingProvider = 'gemini';
    public string $embeddingModel = '';
    public int $embeddingTimeout = 15;
    public float $searchMinScore = 0.65;

    public string $agentProvider = 'deepseek';
    public string $agentModel = '';
    public float $agentTemperature = 0.3;
    public int $agentMaxTokens = 1024;
    public int $agentTimeout = 30;

    public function mount(): void
    {
        $gemini = SiteSetting::geminiEmbeddingConfig();
        $this->geminiBaseUrl = $gemini['base_url'];
        $this->geminiModel = $gemini['model'];
        $this->geminiTimeout = $gemini['timeout'];
        $this->geminiApiKeyConfigured = $gemini['api_key_configured'] || filled(config('ai.gemini.key'));

        $openRouter = SiteSetting::openRouterProviderConfig();
        $this->openRouterBaseUrl = $openRouter['base_url'];
        $this->openRouterTimeout = $openRouter['timeout'];
        $this->openRouterApiKeyConfigured = $openRouter['api_key_configured'];

        $deepSeek = SiteSetting::deepSeekProviderConfig();
        $this->deepSeekBaseUrl = $deepSeek['base_url'];
        $this->deepSeekModel = $deepSeek['model'];
        $this->deepSeekTemperature = $deepSeek['temperature'];
        $this->deepSeekMaxTokens = $deepSeek['max_tokens'];
        $this->deepSeekTimeout = $deepSeek['timeout'];
        $this->deepSeekApiKeyConfigured = $deepSeek['api_key_configured'];

        $embedding = SiteSetting::embeddingFeatureConfig();
        $this->embeddingProvider = $embedding['provider'];
        $this->embeddingModel = $embedding['model'];
        $this->embeddingTimeout = $embedding['timeout'];
        $this->searchMinScore = SiteSetting::searchMinScore();

        $agent = SiteSetting::agentFeatureConfig();
        $this->agentProvider = $agent['provider'];
        $this->agentModel = $agent['model'];
        $this->agentTemperature = $agent['temperature'];
        $this->agentMaxTokens = $agent['max_tokens'];
        $this->agentTimeout = $agent['timeout'];
    }

    public function save(): void
    {
        $gemini = $this->validatedGemini();
        $openRouter = $this->validatedOpenRouter();
        $deepSeek = $this->validatedDeepSeek();
        $embedding = $this->validatedEmbeddingFeature();
        $searchMinScore = $this->validatedSearchMinScore();
        $agent = $this->validatedAgentFeature();

        SiteSetting::setGeminiEmbeddingConfig($gemini);
        SiteSetting::setOpenRouterProviderConfig($openRouter);
        SiteSetting::setDeepSeekProviderConfig($deepSeek);
        SiteSetting::setEmbeddingFeatureConfig($embedding);
        SiteSetting::setSearchMinScore($searchMinScore);
        SiteSetting::setAgentFeatureConfig($agent);

        $this->geminiApiKey = '';
        $this->openRouterApiKey = '';
        $this->deepSeekApiKey = '';
        $this->geminiApiKeyConfigured = SiteSetting::geminiEmbeddingApiKey() !== null || filled(config('ai.gemini.key'));
        $this->deepSeekApiKeyConfigured = SiteSetting::deepSeekProviderApiKey() !== null;
        $this->openRouterApiKeyConfigured = SiteSetting::openRouterProviderApiKey() !== null;

        Notification::make()
            ->title('Настройки AI сохранены')
            ->body('Провайдеры и назначения моделей для функций сохранены независимо.')
            ->success()
            ->send();
    }

    public function testGeminiConnection(): void
    {
        $settings = $this->validatedGemini();
        $key = $this->keyForTest($settings['api_key'], SiteSetting::geminiEmbeddingApiKey() ?? config('ai.gemini.key'));

        if ($key === null) {
            $this->missingKeyNotification('Gemini');

            return;
        }

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $key])
                ->timeout($settings['timeout'])
                ->post(rtrim($settings['base_url'], '/') . "/models/{$settings['model']}:embedContent", [
                    'model' => "models/{$settings['model']}",
                    'content' => ['parts' => [['text' => 'Connection test']]],
                ]);

            if ($response->successful() && is_array($response->json('embedding.values'))) {
                Notification::make()->title('Gemini подключён')->body('Embedding-модель успешно вернула тестовый вектор.')->success()->send();

                return;
            }

            $this->failedConnectionNotification('Gemini', $response->status());
        } catch (ConnectionException) {
            $this->connectionExceptionNotification('Gemini');
        }
    }

    public function testDeepSeekConnection(): void
    {
        $settings = $this->validatedDeepSeek();
        $key = $this->keyForTest($settings['api_key'], SiteSetting::deepSeekProviderApiKey());

        if ($key === null) {
            $this->missingKeyNotification('DeepSeek');

            return;
        }

        try {
            $response = Http::acceptJson()
                ->withToken($key)
                ->timeout($settings['timeout'])
                ->post(rtrim($settings['base_url'], '/') . '/chat/completions', [
                    'model' => $settings['model'],
                    'messages' => [['role' => 'user', 'content' => 'Reply only with OK.']],
                    'temperature' => $settings['temperature'],
                    'max_tokens' => min(256, $settings['max_tokens']),
                    'thinking' => ['type' => 'disabled'],
                    'stream' => false,
                ]);

            if ($response->successful() && filled($response->json('choices.0.message.content'))) {
                Notification::make()->title('DeepSeek подключён')->body('Модель успешно ответила на тестовый запрос.')->success()->send();

                return;
            }

            $this->failedConnectionNotification('DeepSeek', $response->status());
        } catch (ConnectionException) {
            $this->connectionExceptionNotification('DeepSeek');
        }
    }

    public function testOpenRouterConnection(): void
    {
        $settings = $this->validatedOpenRouter();
        $key = $this->keyForTest($settings['api_key'], SiteSetting::openRouterProviderApiKey());

        if ($key === null) {
            $this->missingKeyNotification('OpenRouter');

            return;
        }

        $model = $this->embeddingProvider === 'openrouter' && trim($this->embeddingModel) !== ''
            ? trim($this->embeddingModel)
            : 'baai/bge-m3';

        try {
            $response = Http::acceptJson()
                ->withToken($key)
                ->timeout($settings['timeout'])
                ->post(rtrim($settings['base_url'], '/') . '/embeddings', [
                    'model' => $model,
                    'input' => 'Connection test',
                    'input_type' => 'search_document',
                ]);

            if ($response->successful() && is_array($response->json('data.0.embedding'))) {
                Notification::make()->title('OpenRouter подключён')->body("Embedding-модель {$model} успешно вернула тестовый вектор.")->success()->send();

                return;
            }

            $this->failedConnectionNotification('OpenRouter', $response->status());
        } catch (ConnectionException) {
            $this->connectionExceptionNotification('OpenRouter');
        }
    }

    /** @return array{base_url: string, model: string, timeout: int, api_key: string} */
    private function validatedGemini(): array
    {
        /** @var array{geminiBaseUrl: string, geminiModel: string, geminiTimeout: int, geminiApiKey: string} $data */
        $data = $this->validate([
            'geminiBaseUrl' => ['required', 'url', 'max:255'],
            'geminiModel' => ['required', 'string', 'max:120'],
            'geminiTimeout' => ['required', 'integer', 'min:5', 'max:120'],
            'geminiApiKey' => ['nullable', 'string', 'max:500'],
        ]);

        return [
            'base_url' => rtrim($data['geminiBaseUrl'], '/'),
            'model' => trim($data['geminiModel']),
            'timeout' => $data['geminiTimeout'],
            'api_key' => trim($data['geminiApiKey']),
        ];
    }

    /** @return array{base_url: string, model: string, temperature: float, max_tokens: int, timeout: int, api_key: string} */
    private function validatedDeepSeek(): array
    {
        /** @var array{deepSeekBaseUrl: string, deepSeekModel: string, deepSeekTemperature: float, deepSeekMaxTokens: int, deepSeekTimeout: int, deepSeekApiKey: string} $data */
        $data = $this->validate([
            'deepSeekBaseUrl' => ['required', 'url', 'max:255'],
            'deepSeekModel' => ['required', 'string', 'max:120'],
            'deepSeekTemperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'deepSeekMaxTokens' => ['required', 'integer', 'min:64', 'max:32768'],
            'deepSeekTimeout' => ['required', 'integer', 'min:5', 'max:120'],
            'deepSeekApiKey' => ['nullable', 'string', 'max:500'],
        ]);

        return [
            'base_url' => rtrim($data['deepSeekBaseUrl'], '/'),
            'model' => trim($data['deepSeekModel']),
            'temperature' => (float) $data['deepSeekTemperature'],
            'max_tokens' => $data['deepSeekMaxTokens'],
            'timeout' => $data['deepSeekTimeout'],
            'api_key' => trim($data['deepSeekApiKey']),
        ];
    }

    /** @return array{base_url: string, timeout: int, api_key: string} */
    private function validatedOpenRouter(): array
    {
        /** @var array{openRouterBaseUrl: string, openRouterTimeout: int, openRouterApiKey: string} $data */
        $data = $this->validate([
            'openRouterBaseUrl' => ['required', 'url', 'max:255'],
            'openRouterTimeout' => ['required', 'integer', 'min:5', 'max:120'],
            'openRouterApiKey' => ['nullable', 'string', 'max:500'],
        ]);

        return [
            'base_url' => rtrim($data['openRouterBaseUrl'], '/'),
            'timeout' => $data['openRouterTimeout'],
            'api_key' => trim($data['openRouterApiKey']),
        ];
    }

    /** @return array{provider: string, model: string, timeout: int} */
    private function validatedEmbeddingFeature(): array
    {
        /** @var array{embeddingProvider: string, embeddingModel: string, embeddingTimeout: int} $data */
        $data = $this->validate([
            'embeddingProvider' => ['required', 'in:gemini,openrouter'],
            'embeddingModel' => ['required', 'string', 'max:160'],
            'embeddingTimeout' => ['required', 'integer', 'min:5', 'max:120'],
        ]);

        return ['provider' => $data['embeddingProvider'], 'model' => trim($data['embeddingModel']), 'timeout' => $data['embeddingTimeout']];
    }

    private function validatedSearchMinScore(): float
    {
        /** @var array{searchMinScore: float} $data */
        $data = $this->validate([
            'searchMinScore' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        return (float) $data['searchMinScore'];
    }

    /** @return array{provider: string, model: string, temperature: float, max_tokens: int, timeout: int} */
    private function validatedAgentFeature(): array
    {
        /** @var array{agentProvider: string, agentModel: string, agentTemperature: float, agentMaxTokens: int, agentTimeout: int} $data */
        $data = $this->validate([
            'agentProvider' => ['required', 'in:openrouter,deepseek'],
            'agentModel' => ['required', 'string', 'max:160'],
            'agentTemperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'agentMaxTokens' => ['required', 'integer', 'min:64', 'max:32768'],
            'agentTimeout' => ['required', 'integer', 'min:5', 'max:120'],
        ]);

        return [
            'provider' => $data['agentProvider'],
            'model' => trim($data['agentModel']),
            'temperature' => (float) $data['agentTemperature'],
            'max_tokens' => $data['agentMaxTokens'],
            'timeout' => $data['agentTimeout'],
        ];
    }

    private function keyForTest(string $submittedKey, mixed $savedKey): ?string
    {
        $key = trim($submittedKey) !== '' ? trim($submittedKey) : $savedKey;

        return is_string($key) && $key !== '' ? $key : null;
    }

    private function missingKeyNotification(string $provider): void
    {
        Notification::make()->title("{$provider}: ключ не указан")->body('Введите ключ в соответствующем блоке или сначала сохраните его.')->danger()->send();
    }

    private function failedConnectionNotification(string $provider, int $status): void
    {
        Notification::make()->title("{$provider}: подключение не подтверждено")->body("API вернул HTTP {$status}. Проверьте ключ, URL и модель.")->danger()->send();
    }

    private function connectionExceptionNotification(string $provider): void
    {
        Notification::make()->title("{$provider}: нет соединения")->body('Проверьте URL API, доступ сервера в интернет и тайм-аут.')->danger()->send();
    }
}
