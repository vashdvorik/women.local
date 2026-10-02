<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Support\CardTone;
use App\Support\Locales;
use Carbon\Carbon;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Переносит карточки страницы «Новости» (/events) из бывшего массива в БД. Идемпотентен:
 * если новости уже есть, ничего не трогает, кроме одного случая — обложка, файл которой
 * пропал с диска, возвращается из исходника темы (сопоставление по русскому заголовку).
 *
 * Подпись даты сохраняется дословно (`date_label`), как её показывала страница:
 * «20.05.2026» / «May 20, 2026». Заодно из русской подписи разбирается настоящая
 * дата начала, если она записана как ДД.ММ.ГГГГ.
 */
class EventSeeder extends Seeder
{
    use ImportsThemeImages;

    public function run(): void
    {
        /** @var list<array<string, mixed>> $events */
        $events = require database_path('seeders/data/events.php');

        if (Event::query()->exists()) {
            $restored = $this->restoreMissingCovers($events);

            $this->command?->info($restored > 0
                ? "Новости уже есть. Восстановлено обложек: {$restored}."
                : 'Новости уже есть — импорт пропущен.');

            return;
        }

        DB::transaction(function () use ($events) {
            $slugs = [];

            foreach ($events as $index => $item) {
                $event = Event::create([
                    'slug' => $this->slug($item['title']['ru'] ?? '', $slugs, $index),
                    'image_path' => $this->importThemeImage($item['photo'] ?? null, 'event'),
                    'tone' => CardTone::normalize($item['tone'] ?? null),
                    'starts_at' => $this->parseDate($item['date']['ru'] ?? null),
                    'url' => $item['url'] ?? null,
                    'is_published' => true,
                    'position' => $index + 1,
                ]);

                foreach (Locales::ALL as $locale) {
                    $event->translations()->create([
                        'locale' => $locale,
                        'type' => $item['type'][$locale] ?? null,
                        'date_label' => $item['date'][$locale] ?? null,
                        'title' => $item['title'][$locale] ?? null,
                        'description' => $item['description'][$locale] ?? null,
                    ]);
                }
            }
        });

        $this->command?->info(count($events).' новостей импортировано.');
    }

    /** @param  list<array<string, mixed>>  $events */
    private function restoreMissingCovers(array $events): int
    {
        $restored = 0;

        foreach ($events as $item) {
            $event = Event::query()
                ->whereHas('translations', fn ($q) => $q
                    ->where('locale', 'ru')
                    ->where('title', $item['title']['ru'] ?? ''))
                ->first();

            if ($event && $this->restoreThemeImage($event, 'image_path', $item['photo'] ?? null, 'event')) {
                $restored++;
            }
        }

        return $restored;
    }

    /**
     * Адрес страницы новости из русского названия: транслитерация, уникальный, не длиннее 100 знаков
     * (после транслитерации название заметно длиннее). Так же строит адреса миграция для готовых новостей.
     *
     * @param  list<string>  $used
     */
    private function slug(string $title, array &$used, int $index): string
    {
        $base = trim(mb_substr(Str::slug($title, '-', 'ru'), 0, 100), '-') ?: 'news-'.($index + 1);
        $slug = $base;

        for ($i = 2; in_array($slug, $used, true); $i++) {
            $slug = $base.'-'.$i;
        }

        return $used[] = $slug;
    }

    private function parseDate(?string $label): ?Carbon
    {
        if ($label !== null && preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $label, $m)) {
            return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
        }

        return null;
    }
}
