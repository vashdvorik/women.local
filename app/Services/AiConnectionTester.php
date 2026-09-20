<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Пробные запросы к ИИ-провайдерам из настроек админки («Проверить подключение»).
 * Проверка ничего не сохраняет: используются значения формы, а если ключ в форме
 * пуст — уже сохранённый. Раньше жила внутри Filament-страницы AiProviderSettings.
 *
 * @phpstan-type Result array{ok: bool, message: string}
 */
class AiConnectionTester
{
    /**
     * @param  array{base_url: string, model: string, timeout: int}  $settings
     * @return array{ok: bool, message: string}
     */
    public function gemini(array $settings, ?string $submittedKey): array
    {
        $key = $this->key($submittedKey, SiteSetting::geminiEmbeddingApiKey() ?? config('ai.gemini.key'));

        if ($key === null) {
            return $this->missingKey('Gemini');
        }

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $key])
                ->timeout($settings['timeout'])
                ->post(rtrim($settings['base_url'], '/')."/models/{$settings['model']}:embedContent", [
                    'model' => "models/{$settings['model']}",
                    'content' => ['parts' => [['text' => 'Connection test']]],
                ]);

            if ($response->successful() && is_array($response->json('embedding.values'))) {
                return $this->ok('Gemini подключён: embedding-модель вернула тестовый вектор.');
            }

            return $this->failed('Gemini', $response->status());
        } catch (ConnectionException) {
            return $this->noConnection('Gemini');
        }
    }

    /**
     * @param  array{base_url: string, model: string, temperature: float, max_tokens: int, timeout: int}  $settings
     * @return array{ok: bool, message: string}
     */
    public function deepSeek(array $settings, ?string $submittedKey): array
    {
        $key = $this->key($submittedKey, SiteSetting::deepSeekProviderApiKey());

        if ($key === null) {
            return $this->missingKey('DeepSeek');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($key)
                ->timeout($settings['timeout'])
                ->post(rtrim($settings['base_url'], '/').'/chat/completions', [
                    'model' => $settings['model'],
                    'messages' => [['role' => 'user', 'content' => 'Reply only with OK.']],
                    'temperature' => $settings['temperature'],
                    'max_tokens' => min(256, $settings['max_tokens']),
                    'thinking' => ['type' => 'disabled'],
                    'stream' => false,
                ]);

            if ($response->successful() && filled($response->json('choices.0.message.content'))) {
                return $this->ok('DeepSeek подключён: модель ответила на тестовый запрос.');
            }

            return $this->failed('DeepSeek', $response->status());
        } catch (ConnectionException) {
            return $this->noConnection('DeepSeek');
        }
    }

    /**
     * @param  array{base_url: string, timeout: int}  $settings
     * @return array{ok: bool, message: string}
     */
    public function openRouter(array $settings, ?string $submittedKey, ?string $embeddingModel = null): array
    {
        $key = $this->key($submittedKey, SiteSetting::openRouterProviderApiKey());

        if ($key === null) {
            return $this->missingKey('OpenRouter');
        }

        $model = filled($embeddingModel) ? trim((string) $embeddingModel) : 'baai/bge-m3';

        try {
            $response = Http::acceptJson()
                ->withToken($key)
                ->timeout($settings['timeout'])
                ->post(rtrim($settings['base_url'], '/').'/embeddings', [
                    'model' => $model,
                    'input' => 'Connection test',
                    'input_type' => 'search_document',
                ]);

            if ($response->successful() && is_array($response->json('data.0.embedding'))) {
                return $this->ok("OpenRouter подключён: embedding-модель {$model} вернула тестовый вектор.");
            }

            return $this->failed('OpenRouter', $response->status());
        } catch (ConnectionException) {
            return $this->noConnection('OpenRouter');
        }
    }

    private function key(?string $submitted, mixed $saved): ?string
    {
        $key = trim((string) $submitted) !== '' ? trim((string) $submitted) : $saved;

        return is_string($key) && $key !== '' ? $key : null;
    }

    /** @return array{ok: bool, message: string} */
    private function ok(string $message): array
    {
        return ['ok' => true, 'message' => $message];
    }

    /** @return array{ok: bool, message: string} */
    private function missingKey(string $provider): array
    {
        return ['ok' => false, 'message' => "{$provider}: ключ не указан. Введите ключ в этом блоке или сначала сохраните его."];
    }

    /** @return array{ok: bool, message: string} */
    private function failed(string $provider, int $status): array
    {
        return ['ok' => false, 'message' => "{$provider}: подключение не подтверждено, API вернул HTTP {$status}. Проверьте ключ, URL и модель."];
    }

    /** @return array{ok: bool, message: string} */
    private function noConnection(string $provider): array
    {
        return ['ok' => false, 'message' => "{$provider}: нет соединения. Проверьте URL API, доступ сервера в интернет и тайм-аут."];
    }
}
