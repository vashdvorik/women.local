<?php

namespace Database\Seeders;

use App\Models\Expert;
use Database\Seeders\Concerns\CreatesExperts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Переносит экспертов из database/seeders/data/experts.php в БД. Идемпотентен:
 * если эксперты уже есть (заведены в админке), ничего не трогает, кроме одного случая —
 * фото, файл которого пропал с диска, возвращается из исходника темы (сопоставление по
 * русскому имени).
 *
 * Эксперты, которых добавили в файл позже, и замену фото в базе, где эксперты уже есть, делает
 * ExpertRosterSeeder; на пустой базе этот сидер заводит всех сразу и сам отмечает это в его списке.
 */
class ExpertSeeder extends Seeder
{
    use CreatesExperts;

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
                $this->createExpert($profile, $index + 1);
            }

            ExpertRosterSeeder::remember($profiles);
        });

        $this->command?->info(count($profiles).' экспертов импортировано.');
    }

    /** @param  list<array<string, mixed>>  $profiles */
    private function restoreMissingPhotos(array $profiles): int
    {
        $restored = 0;

        foreach ($profiles as $profile) {
            $expert = $this->findExpertByRuName($profile['name']['ru'] ?? '');

            if ($expert && $this->restoreThemeImage($expert, 'photo_path', $profile['photo'] ?? null, 'expert', $profile['crop'] ?? null)) {
                $restored++;
            }
        }

        return $restored;
    }
}
