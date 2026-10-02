<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Expert;
use Database\Seeders\ContentSeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\ExpertSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Папка public/uploads не хранится в git и может опустеть, а в БД остаются пути без файлов
 * («битые» картинки на сайте). Повторный запуск сидеров возвращает такие картинки из исходников
 * темы и больше ничего не трогает.
 */
class ContentSeederRestoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Подменный диск: тест не должен писать в реальный public/uploads.
        Storage::fake('uploads');
    }

    private function firstExpertName(): string
    {
        return (require database_path('seeders/data/experts.php'))[0]['name']['ru'];
    }

    private function firstEventTitle(): string
    {
        return (require database_path('seeders/data/events.php'))[0]['title']['ru'];
    }

    private function expert(string $ruName, ?string $photo): Expert
    {
        $expert = Expert::create(['photo_path' => $photo, 'position' => 1]);
        $expert->translations()->create(['locale' => 'ru', 'name' => $ruName]);

        return $expert;
    }

    public function test_missing_expert_photo_is_restored_from_the_theme_source(): void
    {
        $expert = $this->expert($this->firstExpertName(), '2026/09/lost.webp');

        $this->seed(ExpertSeeder::class);

        $expert->refresh();
        $this->assertNotSame('2026/09/lost.webp', $expert->photo_path);
        Storage::disk('uploads')->assertExists($expert->photo_path);
        $this->assertSame(1, Expert::count(), 'сидер не создаёт дубликатов');
    }

    public function test_existing_photo_is_left_alone(): void
    {
        Storage::disk('uploads')->put('2026/09/kept.webp', 'binary');
        $expert = $this->expert($this->firstExpertName(), '2026/09/kept.webp');

        $this->seed(ExpertSeeder::class);

        $this->assertSame('2026/09/kept.webp', $expert->fresh()->photo_path);
    }

    public function test_expert_without_a_photo_is_not_given_one_back(): void
    {
        // Редактор мог убрать фото сознательно — сидер этого не отменяет.
        $expert = $this->expert($this->firstExpertName(), null);

        $this->seed(ExpertSeeder::class);

        $this->assertNull($expert->fresh()->photo_path);
    }

    public function test_expert_that_no_longer_matches_a_source_record_is_skipped(): void
    {
        // Эксперта переименовали или завели вручную: сопоставить с исходником нельзя.
        $expert = $this->expert('Совсем другая эксперт', '2026/09/lost.webp');

        $this->seed(ExpertSeeder::class);

        $this->assertSame('2026/09/lost.webp', $expert->fresh()->photo_path);
    }

    public function test_missing_news_cover_is_restored_from_the_theme_source(): void
    {
        $event = Event::create(['image_path' => '2026/09/lost.webp', 'position' => 1]);
        $event->translations()->create(['locale' => 'ru', 'title' => $this->firstEventTitle()]);

        $this->seed(EventSeeder::class);

        $event->refresh();
        $this->assertNotSame('2026/09/lost.webp', $event->image_path);
        Storage::disk('uploads')->assertExists($event->image_path);
        $this->assertSame(1, Event::count());
    }

    public function test_content_seeder_on_an_empty_database_imports_everything_with_files_on_disk(): void
    {
        $this->seed(ContentSeeder::class);

        $this->assertSame(11, Expert::count());
        $this->assertSame(9, Event::count());

        foreach (Expert::pluck('photo_path') as $path) {
            Storage::disk('uploads')->assertExists($path);
        }
        foreach (Event::pluck('image_path') as $path) {
            Storage::disk('uploads')->assertExists($path);
        }

        // Повторный запуск ничего не дублирует и ничего не пересоздаёт.
        $before = Expert::pluck('photo_path')->all();
        $this->seed(ContentSeeder::class);

        $this->assertSame(11, Expert::count());
        $this->assertSame($before, Expert::pluck('photo_path')->all());
    }
}
