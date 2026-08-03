<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EmbeddingService
{
    public function embed(string $text): array
    {
        return $this->createEmbedding($text, 'search_document');
    }

    public function embedQuery(string $text): array
    {
        return $this->createEmbedding($text, 'search_query');
    }

    private function createEmbedding(string $text, string $inputType): array
    {
        $feature = SiteSetting::embeddingFeatureConfig();

        return $feature['provider'] === 'openrouter'
            ? $this->embedWithOpenRouter($text, $feature['model'], $feature['timeout'], $inputType)
            : $this->embedWithGemini($text, $feature['model'], $feature['timeout']);
    }

    private function embedWithGemini(string $text, string $model, int $timeout): array
    {
        $settings = SiteSetting::geminiEmbeddingConfig();
        $key = SiteSetting::geminiEmbeddingApiKey() ?? config('ai.gemini.key');
        $url = rtrim($settings['base_url'], '/') . "/models/{$model}:embedContent";

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('Gemini embedding API key is not configured.');
        }

        $response = Http::withHeaders(['x-goog-api-key' => $key])->timeout($timeout)->post($url, [
            'model'   => "models/{$model}",
            'content' => [
                'parts' => [['text' => $text]],
            ],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Gemini embedding failed: ' . $response->status() . ' ' . $response->body()
            );
        }

        return $response->json('embedding.values') ?? throw new RuntimeException(
            'Unexpected Gemini response: ' . $response->body()
        );
    }

    private function embedWithOpenRouter(string $text, string $model, int $timeout, string $inputType): array
    {
        $settings = SiteSetting::openRouterProviderConfig();
        $key = SiteSetting::openRouterProviderApiKey();

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('OpenRouter embedding API key is not configured.');
        }

        $response = Http::acceptJson()
            ->withToken($key)
            ->timeout($timeout)
            ->post(rtrim($settings['base_url'], '/') . '/embeddings', [
                'model' => $model,
                'input' => $text,
                'input_type' => $inputType,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenRouter embedding failed: ' . $response->status() . ' ' . $response->body());
        }

        return $response->json('data.0.embedding') ?? throw new RuntimeException(
            'Unexpected OpenRouter embedding response: ' . $response->body()
        );
    }

    /**
     * Build the text to embed for a BotUser.
     * Combines description + expectation for richer semantic signal.
     */
    public function textForUser(string $description, string $expectation): string
    {
        $parts = array_filter([
            trim($description),
            trim($expectation),
        ]);

        return implode("\n\n", $parts);
    }
}
