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

    // ------------------------------------------------------------------ страница новости

    private function blocks(array $extra = []): string
    {
        return json_encode(array_merge([
            ['uid' => 'h1', 'type' => 'heading', 'data' => ['text' => 'О событии', 'level' => 'h2']],
            ['uid' => 't1', 'type' => 'text', 'data' => ['html' => '<p>Мы встретились.</p>']],
            ['uid' => 'i1', 'type' => 'image', 'data' => ['path' => '2026/09/inside.webp']],
        ], $extra));
    }

    public function test_news_text_is_saved_per_language_and_the_address_comes_from_the_title(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload([
            'translations' => [
                'ru' => ['content' => $this->blocks()],
                'ro' => ['title' => 'Întâlnire', 'content' => json_encode([
                    ['uid' => 'h1', 'type' => 'heading', 'data' => ['text' => 'Despre eveniment']],
                    ['uid' => 't1', 'type' => 'text', 'data' => ['html' => '<p>Ne-am întâlnit.</p>']],
                    // Румынская вкладка не может подменить картинку: она берётся из русской версии.
                    ['uid' => 'i1', 'type' => 'image', 'data' => ['path' => '2026/09/evil.webp']],
                ])],
            ],
        ]))->assertRedirect(route('admin.events.index'));

        $event = Event::with('translations')->firstOrFail();

        $this->assertSame('biznes-laboratoriya', $event->slug);

        $ru = $event->rawTranslation('ru')->content;
        $this->assertSame('h2', $ru[0]['data']['level']);
        $this->assertSame('2026/09/inside.webp', $ru[2]['data']['path']);

        $ro = $event->rawTranslation('ro')->content;
        $this->assertSame('Despre eveniment', $ro[0]['data']['text']);
        $this->assertSame('h2', $ro[0]['data']['level']); // уровень заголовка из русской версии
        $this->assertSame('2026/09/inside.webp', $ro[2]['data']['path']);
        $this->assertTrue($event->hasBody());
    }

    public function test_a_translation_with_only_text_blocks_is_kept(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload([
            'translations' => [
                'ru' => ['content' => $this->blocks()],
                'en' => ['content' => json_encode([['uid' => 't1', 'type' => 'text', 'data' => ['html' => '<p>We met.</p>']]])],
            ],
        ]));

        $en = Event::firstOrFail()->rawTranslation('en');

        $this->assertNotNull($en, 'текст, набранный на английской вкладке, не должен пропасть');
        $this->assertStringContainsString('We met.', $en->content[1]['data']['html']);
    }

    public function test_news_without_text_saves_no_content(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload());

        $event = Event::firstOrFail();

        $this->assertNull($event->rawTranslation('ru')->content);
        $this->assertFalse($event->hasBody());
        $this->assertSame('https://example.test/lab', $event->cardLink()['href'], 'без текста карточка ведёт по внешней ссылке');
    }

    public function test_address_stays_when_the_title_changes_and_is_unique(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload());
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload()); // то же название

        [$first, $second] = Event::orderBy('id')->get()->all();
        $this->assertSame('biznes-laboratoriya', $first->slug);
        $this->assertSame('biznes-laboratoriya-2', $second->slug);

        // Название поменяли: осмысленный адрес не перезаписывается (ссылки на страницу не ломаются).
        $this->actingAsAdmin()->put(route('admin.events.update', $first), $this->payload([
            'translations' => ['ru' => ['title' => 'Совсем другое название']],
        ]));

        $this->assertSame('biznes-laboratoriya', $first->fresh()->slug);
    }

    public function test_manual_address_is_normalised_and_cannot_take_another_news_address(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload(['slug' => 'Моя Новость!']));
        $this->assertSame('moya-novost', Event::firstOrFail()->slug);

        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload(['slug' => 'moya-novost']))
            ->assertSessionHasErrors('slug');

        $this->assertSame(1, Event::count());
    }

    public function test_very_long_title_still_gets_a_valid_address(): void
    {
        $title = str_repeat('Длинное название новости ', 7); // ~175 знаков, после транслитерации заметно длиннее

        $this->actingAsAdmin()->post(route('admin.events.store'), $this->payload(['translations' => ['ru' => ['title' => trim($title)]]]))
            ->assertRedirect(route('admin.events.index'));

        $this->assertLessThanOrEqual(191, strlen(Event::firstOrFail()->slug));
    }

    public function test_form_offers_the_block_editor_and_the_page_address(): void
    {
        $event = Event::create(['slug' => 'nash-vecher', 'tone' => 'pink', 'position' => 1]);
        $event->translations()->create(['locale' => 'ru', 'title' => 'Наш вечер', 'content' => json_decode($this->blocks(), true)]);

        $this->actingAsAdmin()->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('eventEditor', false)
            ->assertSee("addBlock('gallery_3')", false)
            ->assertSee('name="slug"', false)
            ->assertSee('/events/nash-vecher')
            ->assertSee('inside.webp', false);

        $this->actingAsAdmin()->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('eventEditor', false)
            ->assertSee('Текст страницы новости');
    }

    public function test_typed_blocks_survive_a_validation_error(): void
    {
        // Название не заполнено: форма вернётся с ошибкой, а набранный текст должен остаться в редакторе.
        $this->actingAsAdmin()->from(route('admin.events.create'))
            ->post(route('admin.events.store'), $this->payload([
                'translations' => ['ru' => ['title' => '', 'content' => $this->blocks()]],
            ]))
            ->assertRedirect(route('admin.events.create'))
            ->assertSessionHasErrors('translations.ru.title');

        // Данные редактора вставляются в страницу как JSON (кириллица в нём экранирована), поэтому проверяем
        // латинскую часть набранного: путь картинки из блока.
        $this->actingAsAdmin()->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('inside.webp', false);
    }

    public function test_index_links_to_the_page_only_when_the_news_has_text(): void
    {
        $with = Event::create(['slug' => 's-tekstom', 'tone' => 'pink', 'position' => 1]);
        $with->translations()->create(['locale' => 'ru', 'title' => 'С текстом', 'content' => json_decode($this->blocks(), true)]);
        $without = Event::create(['slug' => 'bez-teksta', 'tone' => 'pink', 'position' => 2]);
        $without->translations()->create(['locale' => 'ru', 'title' => 'Без текста']);

        $html = $this->actingAsAdmin()->get(route('admin.events.index'))->assertOk()->getContent();

        $this->assertStringContainsString('/events/s-tekstom', $html);
        $this->assertStringNotContainsString('/events/bez-teksta', $html);
    }
}
