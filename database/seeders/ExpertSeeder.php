<?php

namespace Database\Seeders;

use App\Models\Expert;
use App\Support\CardTone;
use App\Support\Locales;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Переносит 11 экспертов из бывшего массива страницы «Эксперты» в БД. Идемпотентен:
 * если эксперты уже есть (заведены в админке), ничего не трогает, кроме одного случая —
 * фото, файл которого пропал с диска, возвращается из исходника темы (сопоставление по
 * русскому имени).
 */
class ExpertSeeder extends Seeder
{
    use ImportsThemeImages;

    public function run(): void
    {
        /** @var list<array<string, mixed>> $profiles */
        $profiles = require database_path('seeders/data/experts.php');

        if (Expert::query()->exists()) {
            $restored = $this->restoreMissingPhotos($profiles);

            $this->command?->info($restored > 0
                ? "Эксперты уже есть. Восстановлено фото: {$restored}."
                : 'Эксперты уже есть — импорт пропущен.');

            return;
        }

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

    /** @param  list<array<string, mixed>>  $profiles */
    private function restoreMissingPhotos(array $profiles): int
    {
        $restored = 0;

        foreach ($profiles as $profile) {
            $expert = Expert::query()
                ->whereHas('translations', fn ($q) => $q
                    ->where('locale', 'ru')
                    ->where('name', $profile['name']['ru'] ?? ''))
                ->first();

            if ($expert && $this->restoreThemeImage($expert, 'photo_path', $profile['photo'] ?? null, 'expert')) {
                $restored++;
            }
        }

        return $restored;
    }
}
