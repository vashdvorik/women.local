<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\PublicCache;
use Illuminate\Console\Command;

/**
 * Стирает кеш публичных страниц (новости, фото и другие разделы, см. App\Support\PublicCache). Запускается сам
 * в составе `php artisan optimize:clear`, то есть при обычной выкладке; отдельно нужен, если страница показывает
 * устаревшее и хочется сбросить только его.
 *
 *   php artisan public-cache:clear
 */
class ClearPublicCache extends Command
{
    protected $signature = 'public-cache:clear';

    protected $description = 'Стирает кеш публичных страниц (новости, фото, публикации и другие разделы)';

    public function handle(): int
    {
        PublicCache::flush();

        $this->info('Кеш публичных страниц очищен.');

        return self::SUCCESS;
    }
}
