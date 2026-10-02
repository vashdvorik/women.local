<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Support\Blocks;
use App\Support\Locales;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Демонстрационный текст и фото для страниц новостей: заголовки, абзацы, список, цитата, картинка и
 * галерея из трёх фотографий, на трёх языках. Нужен, чтобы в разработке сразу видно было готовую страницу.
 *
 * Только для разработки. На боевом сервере ничего не создаёт даже при прямом запуске: демо-текст под видом
 * настоящей новости там недопустим (как и тестовые участницы). Там текст новостей вносят в админке.
 *
 * Фотографии берутся из уже загруженных обложек других новостей, поэтому картинок новых он не обрабатывает.
 * Идемпотентен и ничего не перезаписывает: у новости, где текст уже есть, страницу не трогает.
 *
 *   php artisan db:seed --class=DemoNewsSeeder
 */
class DemoNewsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Демо-текст новостей на боевом сервере не создаётся (APP_ENV=production): тексты вносят в админке.');

            return;
        }

        /** @var array<string, array<string, mixed>> $texts */
        $texts = require database_path('seeders/data/demo_news.php');

        $events = Event::query()->with('translations')->ordered()->get();
        $covers = $events->pluck('image_path')->filter()->values()->all();

        $filled = 0;

        foreach ($events as $index => $event) {
            if ($event->hasBody()) {
                continue;
            }

            $photos = $this->photosFor($index, $event->image_path, $covers);
            $uids = array_map(fn () => (string) Str::uuid(), range(0, 9));

            foreach (Locales::ALL as $locale) {
                $row = $event->translations->firstWhere('locale', $locale);

                // Текст кладём к переводам, которые у новости есть: карточка на языке — основа страницы.
                $row?->update(['content' => $this->blocks($texts[$locale], $uids, $photos)]);
            }

            $filled++;
        }

        $this->command?->info($filled > 0
            ? "Демо-текст добавлен новостям: {$filled}."
            : 'У всех новостей уже есть текст — демо пропущено.');
    }

    /**
     * Четыре фото для страницы: одно в тексте и три в галерее. Берутся обложки соседних новостей, чтобы
     * не повторять обложку самой страницы.
     *
     * @param  list<string>  $covers
     * @return array{main: ?string, gallery: list<?string>}
     */
    private function photosFor(int $index, ?string $own, array $covers): array
    {
        $pool = array_values(array_filter($covers, fn (string $path) => $path !== $own));

        if ($pool === []) {
            return ['main' => null, 'gallery' => [null, null, null]];
        }

        $pick = fn (int $offset): string => $pool[($index + $offset) % count($pool)];

        return ['main' => $pick(0), 'gallery' => [$pick(1), $pick(2), $pick(3)]];
    }

    /**
     * Блоки страницы на одном языке. Идентификаторы и картинки одинаковы на всех языках: структура общая,
     * переводится только текст.
     *
     * @param  array<string, mixed>  $t     тексты языка (data/demo_news.php)
     * @param  list<string>  $uids
     * @param  array{main: ?string, gallery: list<?string>}  $photos
     * @return list<array<string, mixed>>
     */
    private function blocks(array $t, array $uids, array $photos): array
    {
        $list = '<ul>'.collect($t['items'])->map(fn (string $item) => '<li>'.e($item).'</li>')->implode('').'</ul>';

        $blocks = [
            ['uid' => $uids[0], 'type' => 'heading', 'data' => ['text' => $t['about'], 'level' => 'h2']],
            ['uid' => $uids[1], 'type' => 'text', 'data' => ['html' => $t['lead']]],
            ['uid' => $uids[2], 'type' => 'text', 'data' => ['html' => $t['body']]],
            ['uid' => $uids[3], 'type' => 'image', 'data' => ['path' => $photos['main']]],
            ['uid' => $uids[4], 'type' => 'heading', 'data' => ['text' => $t['program'], 'level' => 'h2']],
            ['uid' => $uids[5], 'type' => 'text', 'data' => ['html' => $list]],
            ['uid' => $uids[6], 'type' => 'text', 'data' => ['html' => '<blockquote><p>'.e($t['quote']).'</p></blockquote>']],
            ['uid' => $uids[7], 'type' => 'gallery_3', 'data' => ['images' => $photos['gallery']]],
            ['uid' => $uids[8], 'type' => 'heading', 'data' => ['text' => $t['results'], 'level' => 'h2']],
            ['uid' => $uids[9], 'type' => 'text', 'data' => ['html' => $t['results_text']]],
        ];

        return Blocks::canonical($blocks, Blocks::ARTICLE_KINDS);
    }
}
