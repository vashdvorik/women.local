<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BotUser;
use App\Services\EmbeddingService;
use App\Services\MatchingService;
use Illuminate\Console\Command;

class RecomputeEmbeddings extends Command
{
    protected $signature = 'ai:recompute-embeddings
                            {--force : Recompute even if embedding already exists}';

    protected $description = 'Compute Gemini embeddings for all approved BotUsers without one';

    public function handle(EmbeddingService $embedder, MatchingService $matcher): int
    {
        $query = BotUser::approved();

        if (! $this->option('force')) {
            $query->whereNull('embedding');
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('Нет пользователей для обработки.');
            return self::SUCCESS;
        }

        $this->info("Обрабатываю {$users->count()} пользователей...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $done  = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                $text = $embedder->textForUser(
                    (string) ($user->description ?? ''),
                    (string) ($user->expectation ?? ''),
                );

                if (trim($text) === '') {
                    $bar->advance();
                    continue;
                }

                $vector = $embedder->embed($text);
                $user->update([
                    'embedding'            => $vector,
                    'embedding_updated_at' => now(),
                ]);
                $matcher->invalidate($user);
                $done++;
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->warn("  Ошибка для {$user->telegram_id}: " . $e->getMessage());
            }

            $bar->advance();

            // Небольшая пауза чтобы не превысить лимит Gemini API
            usleep(200_000); // 200ms
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Готово: {$done} обработано, {$errors} ошибок.");

        return self::SUCCESS;
    }
}
