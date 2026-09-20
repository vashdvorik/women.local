<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Страница «Новости» (/events) читает карточки из БД (раздел «Новости» админки).
 */
class PublicEventsTest extends TestCase
{
    use RefreshDatabase;

    private function event(array $attributes, array $translations): Event
    {
        $event = Event::create($attributes);

        foreach ($translations as $locale => $fields) {
            $event->translations()->create(['locale' => $locale, ...$fields]);
        }

        return $event;
    }

    public function test_public_events_page_renders_published_cards_from_the_database(): void
    {
        $this->event(['tone' => 'pink', 'position' => 1, 'url' => 'https://example.test/one', 'image_path' => '2026/09/one.webp'], [
            'ru' => ['title' => 'Бизнес-лаборатория', 'type' => 'Обучение', 'date_label' => 'Открыт набор', 'description' => 'Разберите задачу.'],
            'en' => ['title' => 'Business lab', 'type' => 'Learning', 'date_label' => 'Applications open', 'description' => 'Break it into steps.'],
        ]);
        $this->event(['tone' => 'teal', 'position' => 2, 'starts_at' => '2026-07-09'], [
            'ru' => ['title' => 'ESG для малого бизнеса'],
        ]);
        $this->event(['tone' => 'blue', 'position' => 3, 'is_published' => false], [
            'ru' => ['title' => 'Скрытое событие'],
        ]);

        $response = $this->get(route('events'));

        $response->assertOk()
            ->assertSee('miro-events-page', false)
            ->assertSee('Бизнес-лаборатория')
            ->assertSee('Business lab')
            ->assertSee('Applications open')
            ->assertSee('ESG для малого бизнеса')
            ->assertSee('9 июля')
            ->assertSee('https://example.test/one', false)
            ->assertSee('/uploads/2026/09/one.webp', false)
            ->assertDontSee('Скрытое событие')
            ->assertSee('Don’t miss the next opportunity');

        $this->assertSame(2, substr_count($response->getContent(), 'class="miro-event-card"'));
        // «Подробнее» есть только у карточки со ссылкой.
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-event-card__link"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }

    public function test_cards_follow_the_order_set_in_the_admin(): void
    {
        $this->event(['position' => 2], ['ru' => ['title' => 'Второе событие']]);
        $this->event(['position' => 1], ['ru' => ['title' => 'Первое событие']]);

        $content = $this->get(route('events'))->getContent();

        $this->assertLessThan(strpos($content, 'Второе событие'), strpos($content, 'Первое событие'));
    }

    public function test_missing_translation_falls_back_to_russian_text(): void
    {
        $this->event(['position' => 1], ['ru' => ['title' => 'Только по-русски', 'type' => 'Форум']]);

        // Английский и румынский span присутствуют в разметке и содержат русский текст.
        $this->assertSame(3, substr_count($this->get(route('events'))->getContent(), 'Только по-русски'));
    }

    public function test_page_renders_when_there_are_no_events(): void
    {
        $this->get(route('events'))
            ->assertOk()
            ->assertDontSee('class="miro-event-card"', false)
            ->assertSee('Don’t miss the next opportunity');
    }
}
