<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Models\Video;
use App\Support\Locales;
use App\Support\PublicCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Видео организации: девять роликов YouTube для страницы «Видео». Перечень, названия на трёх языках и порядок — в
 * database/seeders/data/videos_archive.php.
 *
 * На сервере достаточно одной команды, повторный запуск безопасен:
 *
 *   php artisan db:seed --class=VideoArchiveSeeder --force
 *
 * Что делает:
 *  - каждое видео заводит записью с названием на русском, английском и румынском; ролики встают в конец очереди в
 *    порядке списка (уже заведённые видео остаются выше), порядок потом меняется стрелками в админке;
 *  - картинки не создаёт: обложка — миниатюра YouTube (в админке можно загрузить свою);
 *  - запоминает загруженные ролики (site_settings: videos_archive): видео, удалённое в админке, повторный запуск не
 *    возвращает. Видео, которое уже есть в базе (тот же идентификатор YouTube), не дублируется и не меняется.
 */
class VideoArchiveSeeder extends Seeder
{
    /** Ключ настройки со списком загруженных видео (идентификаторы YouTube). */
    public const MARKER_KEY = 'videos_archive';

    /** Файл с перечнем видео в database/seeders/data. */
    protected const DATA_FILE = 'videos_archive.php';

    public function run(): void
    {
        $items = $this->items();
        $done = $this->doneKeys();
        $created = 0;

        DB::transaction(function () use ($items, &$done, &$created) {
            $position = (int) Video::max('position');

            foreach ($items as $item) {
                $id = $item['youtube_id'];

                if (! in_array($id, $done, true) && ! Video::where('youtube_id', $id)->exists()) {
                    $video = Video::create([
                        'youtube_id' => $id,
                        'youtube_url' => 'https://www.youtube.com/watch?v='.$id,
                        'event_date' => null,
                        'cover_path' => null,
                        'position' => ++$position,
                    ]);

                    foreach (Locales::ALL as $locale) {
                        $video->translations()->create(['locale' => $locale, 'title' => $item['title'][$locale]]);
                    }

                    $created++;
                }

                $done[] = $id;
            }

            SiteSetting::write(static::MARKER_KEY, ['keys' => array_values(array_unique($done))]);
        });

        // Под DatabaseSeeder события моделей отключены, и кеш публичных страниц сам не сбросился бы.
        PublicCache::flush();

        $this->command?->info("Видео: добавлено {$created}.");
    }

    /**
     * Видео из файла данных. Метод защищённый, чтобы тест мог взять одно-два видео.
     *
     * @return list<array{youtube_id: string, title: array<string, string>}>
     */
    protected function items(): array
    {
        return array_values((array) require database_path('seeders/data/'.static::DATA_FILE));
    }

    /** @return list<string> */
    private function doneKeys(): array
    {
        $stored = SiteSetting::read(static::MARKER_KEY, []);

        return is_array($stored) ? array_values(array_map('strval', (array) ($stored['keys'] ?? []))) : [];
    }
}
