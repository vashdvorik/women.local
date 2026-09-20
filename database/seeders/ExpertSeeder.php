<?php

namespace Database\Seeders;

use App\Models\Expert;
use App\Support\CardTone;
use App\Support\Locales;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Переносит 12 экспертов из бывшего массива страницы «Эксперты» в БД. Идемпотентен:
 * если эксперты уже есть (заведены в админке), ничего не трогает.
 */
class ExpertSeeder extends Seeder
{
    use ImportsThemeImages;

    public function run(): void
    {
        if (Expert::query()->exists()) {
            $this->command?->info('Эксперты уже есть — импорт пропущен.');

            return;
        }

        /** @var list<array<string, mixed>> $profiles */
        $profiles = require database_path('seeders/data/experts.php');

        DB::transaction(function () use ($profiles) {
            foreach ($profiles as $index => $profile) {
                $expert = Expert::create([
                    'photo_path' => $this->importThemeImage($profile['photo'] ?? null, 'expert'),
                    'tone' => CardTone::normalize($profile['tone'] ?? null),
                    'is_published' => true,
                    'position' => $index + 1,
                ]);

                foreach (Locales::ALL as $locale) {
                    $expert->translations()->create([
                        'locale' => $locale,
                        'name' => $profile['name'][$locale] ?? null,
                        'role' => $profile['role'][$locale] ?? null,
                        'specialization' => $profile['specialization'][$locale] ?? null,
                        'description' => $profile['description'][$locale] ?? null,
                        'looking_for' => $profile['looking_for'][$locale] ?? null,
                        'can_offer' => $profile['can_offer'][$locale] ?? null,
                        'tags' => array_values(array_filter(array_map(
                            fn (array $tag) => $tag[$locale] ?? null,
                            $profile['tags'] ?? []
                        ))) ?: null,
                    ]);
                }
            }
        });

        $this->command?->info(count($profiles).' экспертов импортировано.');
    }
}
