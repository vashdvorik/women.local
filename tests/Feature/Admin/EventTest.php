<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'is_published' => '1',
            'tone' => 'blue',
            'starts_at' => '2026-06-18',
            'url' => 'https://example.test/lab',
            'translations' => [
                'ru' => ['title' => 'Бизнес-лаборатория', 'type' => 'Обучение', 'description' => 'Разберите задачу на шаги.'],
            ],
        ], $overrides);
    }

    public function test_index_and_forms_render(): void
    {
        $event = Event::create(['tone' => 'pink', 'position' => 1, 'starts_at' => '2026-06-18']);
        $event->translations()->create(['locale' => 'ru', 'title' => 'Нетворкинг-вечер']);

        $this->actingAsAdmin()->get(route('admin.events.index'))->assertOk()->assertSee('Нетворкинг-вечер')->assertSee('18 июня');
        $this->actingAsAdmin()->get(route('admin.events.create'))->assertOk();
        $this->actingAsAdmin()->get(route('admin.events.edit', $event))->assertOk()->assertSee('Нетворкинг-вечер');
    }

    public function test_event_is_created_with_russian_only(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload())
            ->assertRedirect(route('admin.events.index'));

        $event = Event::firstOrFail();
        $this->assertSame('blue', $event->tone);
        $this->assertSame('2026-06-18', $event->starts_at->format('Y-m-d'));
        $this->assertSame('https://example.test/lab', $event->url);
        $this->assertSame('Бизнес-лаборатория', $event->rawTranslation('ru')->title);
        $this->assertNull($event->rawTranslation('ro'));
    }

    public function test_russian_title_is_required_and_url_must_be_valid(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload([
            'translations' => ['ru' => ['title' => '']],
        ]))->assertSessionHasErrors('translations.ru.title');

        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload(['url' => 'not-a-url']))
            ->assertSessionHasErrors('url');

        $this->assertSame(0, Event::count());
    }

    public function test_date_is_formatted_for_each_language(): void
    {
        $event = Event::create(['starts_at' => '2026-07-09']);
        $event->translations()->create(['locale' => 'ru', 'title' => 'ESG']);
        $event->load('translations');

        $this->assertSame('9 июля', $event->dateLabel('ru'));
        $this->assertSame('July 9', $event->dateLabel('en'));
        $this->assertSame('9 iulie', $event->dateLabel('ro'));
    }

    public function test_custom_label_replaces_the_date_and_falls_back_to_russian(): void
    {
        $event = Event::create(['starts_at' => '2026-07-09']);
        $event->translations()->create(['locale' => 'ru', 'title' => 'Набор', 'date_label' => 'Открыт набор']);
        $event->translations()->create(['locale' => 'en', 'title' => 'Intake', 'date_label' => 'Applications open']);
        $event->load('translations');

        $this->assertSame('Открыт набор', $event->dateLabel('ru'));
        $this->assertSame('Applications open', $event->dateLabel('en'));
        // В румынском подписи нет — как и у любого поля, подставляется русская.
        $this->assertSame('Открыт набор', $event->dateLabel('ro'));
    }

    public function test_event_without_date_and_label_shows_nothing(): void
    {
        $event = Event::create([]);
        $event->translations()->create(['locale' => 'ru', 'title' => 'Без даты']);
        $event->load('translations');

        $this->assertSame('', $event->dateLabel('ru'));
    }

    public function test_move_reorders_and_delete_removes_translations(): void
    {
        $first = Event::create(['position' => 1]);
        $second = Event::create(['position' => 2]);
        $second->translations()->create(['locale' => 'ru', 'title' => 'B']);

        $this->actingAsAdmin()->post(route('admin.events.move', $second), ['direction' => 'up']);
        $this->assertSame([$second->id, $first->id], Event::ordered()->pluck('id')->all());

        $this->actingAsAdmin()->delete(route('admin.events.destroy', $second))
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseCount('event_translations', 0);
    }

    public function test_unpublished_events_are_excluded_from_the_published_scope(): void
    {
        $shown = Event::create(['is_published' => true, 'position' => 1]);
        $hidden = Event::create(['is_published' => false, 'position' => 2]);

        $ids = Event::published()->pluck('id');
        $this->assertTrue($ids->contains($shown->id));
        $this->assertFalse($ids->contains($hidden->id));
    }
}
