<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Support\CardTone;
use App\Support\Locales;
use Carbon\Carbon;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Переносит карточки страницы «Новости» (/events) из бывшего массива в БД. Идемпотентен:
 * если новости уже есть, ничего не трогает.
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
        if (Event::query()->exists()) {
            $this->command?->info('Новости уже есть — импорт пропущен.');

            return;
        }

        /** @var list<array<string, mixed>> $events */
        $events = require database_path('seeders/data/events.php');

        DB::transaction(function () use ($events) {
            foreach ($events as $index => $item) {
                $event = Event::create([
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

    private function parseDate(?string $label): ?Carbon
    {
        if ($label !== null && preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $label, $m)) {
            return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
        }

        return null;
    }
}
