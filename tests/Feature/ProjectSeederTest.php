<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\SiteSetting;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Проекты от заказчика: десять карточек со страницами и фото, прежние проекты старого сидера снимаются один раз. */
class ProjectSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    private function legacy(string $title): Project
    {
        $project = Project::create(['is_published' => true, 'position' => 1]);
        $project->translations()->create(['locale' => 'ru', 'title' => $title, 'category' => 'Проект', 'text' => 'Старый текст']);

        return $project;
    }

    public function test_it_creates_ten_published_projects_with_pages_photos_and_three_languages(): void
    {
        $this->seed(ProjectSeeder::class);

        $this->assertSame(10, Project::count());
        $this->assertSame(10, Project::where('is_published', true)->count());

        foreach (Project::with('translations')->orderBy('position')->get() as $project) {
            $this->assertNotEmpty($project->slug);
            $this->assertTrue($project->hasPage(), $project->slug);
            Storage::disk('uploads')->assertExists($project->image_path);

            foreach (['ru', 'en', 'ro'] as $locale) {
                $row = $project->translations->firstWhere('locale', $locale);
                $this->assertNotNull($row, "{$project->slug} {$locale}");
                $this->assertNotSame('', trim((string) $row->title));
                $this->assertNotSame('', trim((string) $row->category));
                $this->assertLessThanOrEqual(2000, mb_strlen((string) $row->text), 'карточка влезает в поле админки');
                $this->assertNotEmpty($row->content);
            }

            // одни и те же блоки на всех языках, и в них есть фотографии, лежащие на диске
            $uids = fn (string $l) => array_column($project->rawTranslation($l)->content, 'uid');
            $this->assertSame($uids('ru'), $uids('en'));
            $this->assertSame($uids('ru'), $uids('ro'));

            $photos = collect($project->rawTranslation('ru')->content)
                ->flatMap(fn (array $b) => $b['type'] === 'image' ? [$b['data']['path']] : ($b['data']['images'] ?? []));
            $this->assertGreaterThan(0, $photos->count(), $project->slug);
            $photos->each(fn (string $path) => Storage::disk('uploads')->assertExists($path));
        }
    }

    public function test_it_removes_the_old_seeded_projects_once_but_keeps_hand_made_ones(): void
    {
        $this->legacy('Женское лидерство');
        $this->legacy('Tech Women Moldova');
        $mine = $this->legacy('Мой собственный проект');

        $this->seed(ProjectSeeder::class);

        $this->assertSame(0, Project::whereNull('slug')->whereHas('translations', fn ($q) => $q->where('title', 'Tech Women Moldova'))->count());
        $this->assertTrue(Project::whereNull('slug')->whereHas('translations', fn ($q) => $q->where('title', 'Женское лидерство'))->doesntExist(), 'прежняя карточка снята');
        $this->assertNotNull(Project::find($mine->id), 'ручной проект остался');
        $this->assertSame(11, Project::count());
        $this->assertTrue((bool) SiteSetting::read(ProjectSeeder::MARKER_KEY)['legacy_removed']);

        // Повторный запуск ничего не удаляет и не дублирует, даже если старая запись снова появилась
        $this->legacy('Женское лидерство');
        $this->seed(ProjectSeeder::class);

        $this->assertSame(12, Project::count());
    }

    public function test_it_never_overwrites_edits_made_in_the_admin(): void
    {
        $this->seed(ProjectSeeder::class);

        $project = Project::where('slug', 'zhenskoe-liderstvo')->first();
        $project->translations()->where('locale', 'ru')->update(['title' => 'Правка из админки']);

        $this->seed(ProjectSeeder::class);

        $this->assertSame(10, Project::count());
        $this->assertSame('Правка из админки', $project->fresh()->translations->firstWhere('locale', 'ru')->title);
    }

    public function test_the_card_leads_to_the_project_page_which_shows_the_story_and_photo_rows(): void
    {
        $this->seed(ProjectSeeder::class);

        $html = $this->get('/projects')->assertOk()->getContent();
        $this->assertStringContainsString('/projects/zhenskoe-liderstvo', $html);
        $this->assertSame(10, substr_count($html, 'class="miro-event-card'));

        $page = $this->get('/projects/zhenskoe-liderstvo')->assertOk()->getContent();
        $this->assertStringContainsString('Школа лидерства для женщин', $page);
        $this->assertStringContainsString('School of Leadership for Women', $page);
        $this->assertStringContainsString('Școala de leadership pentru femei', $page);
        $this->assertStringContainsString('miro-prose__gallery', $page);
        $this->assertStringContainsString(route('projects'), $page, 'есть ссылка «Все проекты»');

        $this->get('/projects/net-takogo-proekta')->assertNotFound();
    }

    public function test_an_unpublished_project_page_is_hidden_from_visitors(): void
    {
        $this->seed(ProjectSeeder::class);
        Project::where('slug', 'fashion-tiras-day')->update(['is_published' => false]);
        \App\Support\PublicCache::flush();

        $this->get('/projects/fashion-tiras-day')->assertNotFound();
    }

    public function test_the_source_photos_and_data_are_in_place_for_every_project(): void
    {
        $items = (function () {
            return require database_path('seeders/data/projects.php');
        })();

        $this->assertCount(10, $items);

        foreach ($items as $item) {
            $files = glob(public_path("themes/public/miro/images/projects/{$item['folder']}/*.webp"));
            $this->assertNotEmpty($files, $item['slug']);

            foreach (['ru', 'en', 'ro'] as $locale) {
                $this->assertNotSame('', trim($item['title'][$locale]));
                $this->assertNotSame('', trim($item['text'][$locale]));
            }

            foreach ($item['blocks'] as $block) {
                $this->assertContains($block[0], ['p', 'h', 'ul']);
                $this->assertCount(4, $block, 'вид и три языка');
            }
        }
    }
}
