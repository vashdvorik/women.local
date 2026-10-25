<?php

namespace Database\Seeders;

use App\Actions\PublishArticle;
use App\Actions\SaveArticle;
use App\Models\SiteOpportunity;
use App\Models\Tag;
use App\Support\Locales;
use App\Support\PublicCache;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * «Возможности» для сайта (/opportunities): гранты, стажировки, курсы. Тексты и картинки присланы заказчиком, лежат в
 * database/seeders/data/site_opportunities.php и public/themes/public/miro/images/opportunities.
 *
 * Что делает: заводит каждую возможность через те же действия, что админка (SaveArticle, PublishArticle): обложка проходит
 * конвейер загрузки (кадр 2,2:1 → WebP), на трёх языках, с тегом, сроком подачи заявок и сразу опубликована.
 * Идемпотентен: возможность с таким адресом (slug) уже есть — пропускается и ничего не перезаписывает, поэтому правки
 * в админке не затираются. Удалили возможность в админке и хотите вернуть — запустите сидер снова.
 *
 *   php artisan db:seed --class=SiteOpportunitySeeder
 */
class SiteOpportunitySeeder extends Seeder
{
    use ImportsThemeImages;

    public function run(SaveArticle $saver, PublishArticle $publisher): void
    {
        /** @var list<array<string, mixed>> $items */
        $items = require database_path('seeders/data/site_opportunities.php');

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (SiteOpportunity::query()->where('slug', $item['slug'])->exists()) {
                $skipped++;

                continue;
            }

            $cover = $this->importThemeImage($item['photo'] ?? null, 'cover', $item['crop'] ?? null);

            $uids = array_map(fn () => (string) Str::uuid(), $item['blocks']);

            $translations = [];
            foreach (Locales::ALL as $locale) {
                $translations[$locale] = [
                    'title' => $item['title'][$locale] ?? null,
                    'excerpt' => $item['excerpt'][$locale] ?? null,
                    'content' => $this->blocks($item['blocks'], $uids, $locale),
                ];
            }

            $opportunity = $saver->handle(SiteOpportunity::class, null, [
                'slug' => $item['slug'],
                'cover_path' => $cover,
                'deadline_at' => $item['deadline_at'] ?? null,
                'tag_id' => isset($item['tag']) ? $this->tag($item['tag'])->id : null,
                'translations' => $translations,
            ]);

            // Дата публикации задаёт порядок на сайте (новые выше); публикация ставит статус и не трогает уже заданную дату
            $opportunity->forceFill(['published_at' => Carbon::parse($item['published_at'])])->save();
            $publisher->publish($opportunity);

            $created++;
        }

        PublicCache::flush();

        $this->command?->info("Возможности: добавлено {$created}, уже были {$skipped}.");
    }

    /**
     * Блоки страницы на одном языке: структура и идентификаторы общие для всех языков, меняется только текст.
     *
     * @param  list<array<string, mixed>>  $blocks
     * @param  list<string>  $uids
     * @return list<array<string, mixed>>
     */
    private function blocks(array $blocks, array $uids, string $locale): array
    {
        return array_map(function (array $block, string $uid) use ($locale) {
            $text = $block[$locale] ?? $block['ru'];

            return $block['type'] === 'heading'
                ? ['uid' => $uid, 'type' => 'heading', 'data' => ['text' => $text, 'level' => $block['level'] ?? 'h2']]
                : ['uid' => $uid, 'type' => 'text', 'data' => ['html' => $text]];
        }, $blocks, $uids);
    }

    /** Тег по русскому названию: найти или завести (цвет плашки — из данных). @param  array<string, string>  $spec */
    private function tag(array $spec): Tag
    {
        $found = Tag::query()
            ->whereHas('translations', fn ($q) => $q->where('locale', Locales::PRIMARY)->where('name', $spec['ru']))
            ->first();

        if ($found) {
            return $found;
        }

        $tag = Tag::create(['color' => $spec['color']]);

        foreach (Locales::ALL as $locale) {
            $tag->translations()->create(['locale' => $locale, 'name' => $spec[$locale] ?? $spec['ru']]);
        }

        return $tag;
    }
}
