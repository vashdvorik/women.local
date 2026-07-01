<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BotUser;
use App\Services\EmbeddingService;
use App\Services\MatchingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ComputeUserEmbedding implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly BotUser $user,
    ) {}

    public function handle(EmbeddingService $embedder, MatchingService $matcher): void
    {
        $text = $embedder->textForUser(
            (string) ($this->user->description ?? ''),
            (string) ($this->user->expectation ?? ''),
        );

        if (trim($text) === '') {
            return;
        }

        $vector = $embedder->embed($text);

        $this->user->update([
            'embedding'            => $vector,
            'embedding_updated_at' => now(),
        ]);

        // Invalidate own match cache so fresh results are built next visit
        $matcher->invalidate($this->user);
    }

    public function failed(Throwable $e): void
    {
        // Embedding failure is non-critical — log and move on
        logger()->warning('ComputeUserEmbedding failed', [
            'telegram_id' => $this->user->telegram_id,
            'error'       => $e->getMessage(),
        ]);
    }
}
