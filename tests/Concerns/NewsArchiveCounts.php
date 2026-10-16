<?php

namespace Tests\Concerns;

/**
 * Сколько новостей даёт архив постов (database/seeders/data/news_archive), для тестов, которые считают новости
 * после полного запуска сидеров. Число берётся из самих файлов данных, поэтому добавленная новость не ломает счёт.
 */
trait NewsArchiveCounts
{
    /** Новостей, заведённых карточками до архива (EventSeeder). */
    private const CARDS_BEFORE_ARCHIVE = 9;

    /** @return list<array<string, mixed>> */
    protected function archiveItems(): array
    {
        return array_map(fn (string $file): array => require $file, glob(database_path('seeders/data/news_archive/*.php')));
    }

    /** Сколько новостей архив добавляет: все, кроме тех, что дополняют уже заведённые карточки. */
    protected function archiveNewCount(): int
    {
        return count(array_filter($this->archiveItems(), fn (array $item) => ($item['merge'] ?? null) === null));
    }

    /** Сколько новостей на сайте после EventSeeder и NewsArchiveSeeder. */
    protected function eventsTotal(): int
    {
        return self::CARDS_BEFORE_ARCHIVE + $this->archiveNewCount();
    }

    /** Сколько новостей получают настоящий текст из архива (новые и дополненные). */
    protected function archiveNewsWithBody(): int
    {
        return count($this->archiveItems());
    }
}
