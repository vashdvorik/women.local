<?php

namespace Tests\Feature\Admin;

use App\Models\Album;
use App\Models\AlbumTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ручной порядок фотоальбомов: перетаскивание и стрелки в админке, тот же порядок на сайте. */
class AlbumOrderTest extends TestCase
{
    use RefreshDatabase;

    private function album(string $title, int $position, string $date = '2026-01-01'): Album
    {
        $album = Album::create(['slug' => str($title)->slug()->toString(), 'status' => 'published', 'published_at' => $date, 'position' => $position]);
        AlbumTranslation::create(['album_id' => $album->id, 'locale' => 'ru', 'title' => $title]);

        return $album;
    }

    /** @return list<string> названия альбомов в том порядке, в каком они выведены в тексте страницы */
    private function titlesInOrder(string $html, array $titles): array
    {
        $positions = [];
        foreach ($titles as $title) {
            $positions[$title] = (int) strpos($html, $title);
        }
        asort($positions);

        return array_keys($positions);
    }

    public function test_the_list_follows_the_manual_order_and_shows_the_drag_handles(): void
    {
        $this->album('Альфа', 3, '2026-03-01');
        $this->album('Бета', 1, '2024-01-01');
        $this->album('Гамма', 2, '2025-06-01');

        $html = $this->actingAsAdmin()->get(route('admin.albums.index'))->assertOk()->getContent();

        $this->assertSame(['Бета', 'Гамма', 'Альфа'], $this->titlesInOrder($html, ['Альфа', 'Бета', 'Гамма']));
        $this->assertStringContainsString('x-data="rowReorder(', $html);
        $this->assertStringContainsString(route('admin.albums.reorder'), str_replace('\/', '/', $html));
        $this->assertSame(3, substr_count($html, 'data-move="up"'));
    }

    public function test_search_and_sorting_turn_the_dragging_off(): void
    {
        $this->album('Альфа', 1);
        $this->album('Бета', 2);

        $searched = $this->actingAsAdmin()->get(route('admin.albums.index', ['q' => 'Альфа']))->assertOk()->getContent();
        $this->assertStringNotContainsString('rowReorder', $searched);
        $this->assertStringContainsString('Показать весь список', $searched);

        $sorted = $this->actingAsAdmin()->get(route('admin.albums.index', ['sort' => 'title']))->assertOk()->getContent();
        $this->assertStringNotContainsString('rowReorder', $sorted);
    }

    public function test_the_new_order_is_saved_and_unlisted_albums_follow_in_their_old_order(): void
    {
        $a = $this->album('Альфа', 1);
        $b = $this->album('Бета', 2);
        $c = $this->album('Гамма', 3);
        $d = $this->album('Дельта', 4);

        $this->actingAsAdmin()->postJson(route('admin.albums.reorder'), ['ids' => [$c->id, $a->id]])
            ->assertOk()->assertJson(['ok' => true]);

        // Гамма и Альфа поднялись, Бета и Дельта (их не было в списке) встали следом в прежнем порядке.
        $this->assertSame([$c->id, $a->id, $b->id, $d->id], Album::query()->ordered()->pluck('id')->all());
        $this->assertSame([1, 2, 3, 4], Album::query()->ordered()->pluck('position')->all());
    }

    public function test_the_site_shows_the_albums_in_the_manual_order(): void
    {
        $old = $this->album('Старый альбом', 1, '2023-01-01');
        $new = $this->album('Новый альбом', 2, '2026-05-01');

        // По умолчанию (позиции как после миграции): порядок по позиции, а не по дате.
        $html = $this->get(route('media.photos'))->assertOk()->getContent();
        $this->assertSame(['Старый альбом', 'Новый альбом'], $this->titlesInOrder($html, ['Новый альбом', 'Старый альбом']));

        // Администратор поменял порядок: страница на сайте обновляется сразу (кеш сброшен).
        $this->actingAsAdmin()->postJson(route('admin.albums.reorder'), ['ids' => [$new->id, $old->id]])->assertOk();

        $html = $this->get(route('media.photos'))->assertOk()->getContent();
        $this->assertSame(['Новый альбом', 'Старый альбом'], $this->titlesInOrder($html, ['Новый альбом', 'Старый альбом']));
    }

    public function test_a_new_album_is_placed_first(): void
    {
        $this->album('Первый', 1);
        $this->album('Второй', 2);

        $this->actingAsAdmin()->post(route('admin.albums.store'), [
            'intent' => 'publish',
            'translations' => ['ru' => ['title' => 'Самый новый']],
            'blocks' => '[]',
        ])->assertRedirect();

        $titles = Album::query()->ordered()->with('translations')->get()->map(fn (Album $a) => $a->rawTranslation('ru')?->title)->all();
        $this->assertSame(['Самый новый', 'Первый', 'Второй'], $titles);
    }

    public function test_the_order_request_is_validated_and_closed_to_guests(): void
    {
        $this->album('Альфа', 1);

        $this->actingAsAdmin()->postJson(route('admin.albums.reorder'), ['ids' => []])->assertStatus(422);
        $this->actingAsAdmin()->postJson(route('admin.albums.reorder'), ['ids' => [9999]])->assertStatus(422);
    }

    public function test_guests_cannot_change_the_order(): void
    {
        $album = $this->album('Альфа', 1);

        $this->postJson(route('admin.albums.reorder'), ['ids' => [$album->id]])->assertUnauthorized();
    }
}
