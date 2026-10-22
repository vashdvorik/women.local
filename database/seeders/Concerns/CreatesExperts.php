<?php

namespace Database\Seeders\Concerns;

use App\Models\Expert;
use App\Support\CardTone;
use App\Support\Locales;

/**
 * Заведение эксперта из записи database/seeders/data/experts.php: общее для ExpertSeeder (пустая база) и
 * ExpertRosterSeeder (дополнение базы, где эксперты уже есть), чтобы карточка везде собиралась одинаково.
 */
trait CreatesExperts
{
    use ImportsThemeImages;

    /** @param  array<string, mixed>  $profile */
    protected function createExpert(array $profile, int $position): Expert
    {
        $expert = Expert::create([
            'photo_path' => $this->importThemeImage($profile['photo'] ?? null, 'expert', $profile['crop'] ?? null),
            'tone' => CardTone::normalize($profile['tone'] ?? null),
            'is_published' => true,
            'position' => $position,
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

        return $expert;
    }

    /** Эксперт с таким именем по-русски (имя — единственный ключ записи в файле данных). */
    protected function findExpertByRuName(string $name): ?Expert
    {
        return Expert::query()
            ->whereHas('translations', fn ($q) => $q->where('locale', 'ru')->where('name', $name))
            ->first();
    }
}
