<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EmbeddingService
{
    public function embed(string $text): array
    {
        $key   = config('ai.gemini.key');
        $model = config('ai.gemini.embedding_model');
        $url   = config('ai.gemini.base_url') . "/models/{$model}:embedContent?key={$key}";

        $response = Http::timeout(15)->post($url, [
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
