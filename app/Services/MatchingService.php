<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MatchingService
{
    /**
     * Return top-N matches for a user, from cache when possible.
     *
     * @return Collection<int, array{user: BotUser, score: float}>
     */
    public function topMatches(BotUser $user, int $n = null): Collection
    {
        $n ??= (int) config('ai.matches_count', 5);
        $ttl = (int) config('ai.matches_cache_ttl', 86400);

        $key = "matches:{$user->id}";
        $matches = Cache::remember($key, $ttl, fn () => $this->compute($user, $n));

        // Подбор лежит в кеше сутки, а за это время участница могла стать невидимкой, уйти на «Скрыта от других», быть
        // заблокированной или остаться без тарифа. Её страница другим уже закрыта (404), поэтому такие строки из кеша
        // убираем и подбор считаем заново: иначе «Открыть профиль» ведёт на «Страница не найдена».
        $ids = $matches->map(fn (array $match): int => (int) $match['user']->getKey())->all();
        if ($ids !== [] && BotUser::discoverable()->whereIn('id', $ids)->count() !== count($ids)) {
            Cache::forget($key);
            $matches = Cache::remember($key, $ttl, fn () => $this->compute($user, $n));
        }

        return $matches;
    }

    /**
     * Invalidate the cached matches for a user
     * (call this when their profile changes).
     */
    public function invalidate(BotUser $user): void
    {
        Cache::forget("matches:{$user->id}");
    }

    /**
     * Compute fresh matches (no cache).
     *
     * @return Collection<int, array{user: BotUser, score: float}>
     */
    public function compute(BotUser $user, int $n): Collection
    {
        if (! $user->embedding) {
            return collect();
        }

        $myVec = $user->embedding;

        // Load all other approved users that have an embedding (hidden members are never recommended)
        $candidates = BotUser::discoverable()
            ->whereKeyNot($user->getKey())
            ->whereNotNull('embedding')
            ->get(['id', 'phone', 'telegram_username', 'full_name',
                   'description', 'expectation', 'avatar_path', 'embedding']);

        return $candidates
            ->map(fn (BotUser $candidate) => [
                'user'  => $candidate,
                'score' => $this->cosine($myVec, $candidate->embedding),
            ])
            ->sortByDesc('score')
            ->take($n)
            ->values();
    }

    /**
     * Cosine similarity between two equal-length float vectors.
     * Returns value in [-1, 1]; higher means more similar.
     */
    private function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        $na  = 0.0;
        $nb  = 0.0;

        $len = count($a);
        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $na  += $a[$i] * $a[$i];
            $nb  += $b[$i] * $b[$i];
        }

        if ($na === 0.0 || $nb === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($na) * sqrt($nb));
    }

    /**
     * Search all approved users by an arbitrary query vector.
     *
     * @return Collection<int, array{user: BotUser, score: float}>
     */
    public function searchByQuery(array $queryVector, BotUser $exclude, int $n = 10): Collection
    {
        $minScore = SiteSetting::searchMinScore();

        $candidates = BotUser::discoverable()
            ->whereKeyNot($exclude->getKey())
            ->whereNotNull('embedding')
            ->get(['id', 'phone', 'telegram_username', 'full_name',
                   'description', 'expectation', 'avatar_path', 'embedding']);

        return $candidates
            ->map(fn (BotUser $candidate) => [
                'user'  => $candidate,
                'score' => $this->cosine($queryVector, $candidate->embedding),
            ])
            ->filter(fn (array $item) => $item['score'] >= $minScore)
            ->sortByDesc('score')
            ->take($n)
            ->values();
    }
}
