<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Support\Locales;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Наполняет страницу «Проекты» подтверждёнными проектами и инициативами из архива новостей.
 * Повторный запуск не перезаписывает карточки, которые уже редактировались в админке.
 */
class ProjectSeeder extends Seeder
{
    use ImportsThemeImages;

    public function run(): void
    {
        /** @var list<array{image:string, translations:array<string, array{title:string, category:string, text:string}>}> $items */
        $items = require database_path('seeders/data/projects.php');
        $created = 0;
        $restored = 0;

        DB::transaction(function () use ($items, &$created, &$restored): void {
            foreach ($items as $position => $item) {
                $title = $item['translations'][Locales::PRIMARY]['title'];
                $project = Project::query()
                    ->whereHas('translations', fn ($query) => $query
                        ->where('locale', Locales::PRIMARY)
                        ->where('title', $title))
                    ->first();

                if ($project === null) {
                    $project = Project::create([
                        'image_path' => $this->importThemeImage($item['image'], 'project'),
                        'is_published' => true,
                        'position' => $position + 1,
                    ]);

                    foreach (Locales::ALL as $locale) {
                        $translation = $item['translations'][$locale];
                        $project->translations()->create([
                            'locale' => $locale,
                            'title' => $translation['title'],
                            'category' => $translation['category'],
                            'text' => $translation['text'],
                        ]);
                    }

                    $created++;

                    continue;
                }

                if (blank($project->image_path) || ! Storage::disk('uploads')->exists($project->image_path)) {
                    $image = $this->importThemeImage($item['image'], 'project');

                    if ($image !== null) {
                        $project->forceFill(['image_path' => $image])->save();
                        $restored++;
                    }
                }
            }
        });

        $this->command?->info("Проекты: добавлено {$created}, восстановлено обложек {$restored}.");
    }
}
