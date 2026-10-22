<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventTranslation;
use Database\Seeders\ContentSeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\NewsArchiveSeeder;
use Database\Seeders\NewsTagsClearSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\FakesImageProcessing;
use Tests\TestCase;

/**
 * Плашки на карточках новостей: загрузка новостей сидерами плашек не ставит, а NewsTagsClearSeeder убирает плашки,
 * которые уже есть в базе (загруженные прежней версией сидеров).
 */
class NewsTagsClearSeederTest extends TestCase
{
    use FakesImageProcessing;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        $this->fakeImageProcessing();
    }

    public function test_the_news_seeders_load_the_news_without_tags(): void
    {
        $this->seed(EventSeeder::class);
        $this->seed(NewsArchiveSeeder::class);

        $this->assertGreaterThan(0, EventTranslation::count());
        $this->assertSame(0, EventTranslation::whereNotNull('type')->count());
        $this->get(route('events'))->assertOk()->assertDontSee('miro-event-card__type', false);
        $this->get('/')->assertOk()->assertDontSee('<span class="miro-tag"', false);
    }

    public function test_the_clear_seeder_removes_every_tag_and_touches_nothing_else(): void
    {
        $this->seed(EventSeeder::class);
        $this->seed(NewsArchiveSeeder::class);
        // Как на сервере, куда новости загрузили раньше: у переводов проставлены плашки.
        EventTranslation::query()->where('locale', 'ru')->update(['type' => 'Конференция']);
        EventTranslation::query()->where('locale', 'en')->update(['type' => 'Conference']);
        $before = Event::with('translations')->get()->mapWithKeys(fn (Event $e) => [$e->id => [
            $e->tone, $e->slug, $e->image_path, $e->translations->sortBy('locale')->map(fn ($t) => [$t->title, $t->description, $t->date_label])->values()->all(),
        ]])->all();
        $this->get(route('events'))->assertSee('miro-event-card__type', false);

        $this->seed(NewsTagsClearSeeder::class);

        $this->assertSame(0, EventTranslation::whereNotNull('type')->count());
        $after = Event::with('translations')->get()->mapWithKeys(fn (Event $e) => [$e->id => [
            $e->tone, $e->slug, $e->image_path, $e->translations->sortBy('locale')->map(fn ($t) => [$t->title, $t->description, $t->date_label])->values()->all(),
        ]])->all();
        $this->assertSame($before, $after, 'тексты, даты, адреса, фото и цвета не тронуты');
        $this->get(route('events'))->assertOk()->assertDontSee('miro-event-card__type', false)->assertDontSee('Конференция');

        $this->seed(NewsTagsClearSeeder::class); // повторный запуск безопасен
        $this->assertSame(0, EventTranslation::whereNotNull('type')->count());
    }

    public function test_the_content_seeder_does_not_wipe_a_tag_set_in_the_admin(): void
    {
        $this->seed(ContentSeeder::class);
        $translation = EventTranslation::where('locale', 'ru')->firstOrFail();
        $translation->update(['type' => 'Выставка']);

        $this->seed(ContentSeeder::class); // как повторный деплой

        $this->assertSame('Выставка', $translation->fresh()->type);
    }
}
