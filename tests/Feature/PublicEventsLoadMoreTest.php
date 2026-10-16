<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicSite\EventsController;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Список новостей порциями: девять карточек и кнопка «Смотреть ещё». Без скриптов это ссылка на ?page=N, со скриптом
 * (js/load-more.js) следующая порция подгружается в сетку (?partial=1, ответ JSON).
 */
class PublicEventsLoadMoreTest extends TestCase
{
    use RefreshDatabase;

    /** Новости «Новость 1» … «Новость N» в порядке списка. */
    private function news(int $count, array $unpublished = []): void
    {
        foreach (range(1, $count) as $i) {
            $event = Event::create([
                'slug' => "novost-{$i}", 'tone' => 'rose', 'position' => $i, 'starts_at' => '2026-05-20',
                'is_published' => ! in_array($i, $unpublished, true),
            ]);
            $event->translations()->create(['locale' => 'ru', 'title' => "Новость {$i}", 'type' => 'Событие', 'description' => 'Коротко.']);
        }
    }

    private function cards(string $html): int
    {
        return substr_count($html, '<article class="miro-event-card');
    }

    public function test_the_first_batch_is_nine_cards_with_a_load_more_button(): void
    {
        $this->news(20);

        $html = $this->get(route('events'))->assertOk()->getContent();

        $this->assertSame(9, EventsController::PER_PAGE);
        $this->assertSame(9, $this->cards($html));
        $this->assertStringContainsString('Новость 1<', $html);
        $this->assertStringContainsString('Новость 9<', $html);
        $this->assertStringNotContainsString('Новость 10<', $html);

        // Кнопка на трёх языках и ссылка на вторую порцию (так она работает и без скриптов).
        $this->assertMatchesRegularExpression('#<div class="miro-events-more" data-load-more data-target="\.miro-events-grid">\s*<a href="[^"]*[?&]page=2" rel="next"#', $html);
        foreach (['Смотреть ещё', 'Load more', 'Vezi mai multe'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertStringContainsString('/themes/public/miro/js/load-more.js', $html);
    }

    public function test_every_next_page_has_the_next_nine_and_the_last_one_has_no_button(): void
    {
        $this->news(20);

        $second = $this->get(route('events', ['page' => 2]))->assertOk()->getContent();
        $this->assertSame(9, $this->cards($second));
        $this->assertStringContainsString('Новость 10<', $second);
        $this->assertStringContainsString('Новость 18<', $second);
        $this->assertStringNotContainsString('Новость 9<', $second);
        $this->assertStringContainsString('page=3', $second);

        $third = $this->get(route('events', ['page' => 3]))->assertOk()->getContent();
        $this->assertSame(2, $this->cards($third));
        $this->assertStringContainsString('Новость 19<', $third);
        $this->assertStringContainsString('Новость 20<', $third);
        $this->assertStringNotContainsString('data-load-more', $third, 'новостей больше нет: кнопки нет');

        // За последней порцией список открывается с начала.
        $this->get(route('events', ['page' => 4]))->assertRedirect(route('events'));
        $this->get(route('events', ['page' => 'abc']))->assertOk();
    }

    public function test_a_short_list_has_no_button(): void
    {
        $this->news(9);

        $html = $this->get(route('events'))->assertOk()->getContent();

        $this->assertSame(9, $this->cards($html));
        $this->assertStringNotContainsString('data-load-more', $html);

        $this->get(route('events', ['page' => 2]))->assertRedirect(route('events'));
    }

    public function test_unpublished_news_are_not_counted_in_the_batches(): void
    {
        $this->news(12, unpublished: [2, 5, 7]);

        $first = $this->get(route('events'))->assertOk()->getContent();
        $this->assertSame(9, $this->cards($first));
        $this->assertStringNotContainsString('Новость 2<', $first);
        $this->assertStringNotContainsString('data-load-more', $first, 'опубликовано ровно девять: больше нечего показывать');
    }

    public function test_the_script_asks_for_the_next_batch_as_json_with_ready_cards_and_the_next_address(): void
    {
        $this->news(20);

        $response = $this->getJson(route('events', ['page' => 2, 'partial' => 1]))->assertOk();
        $data = $response->json();

        // Только карточки, без шапки и подвала страницы.
        $this->assertSame(9, $this->cards($data['html']));
        $this->assertStringContainsString('Новость 10<', $data['html']);
        $this->assertStringNotContainsString('<html', $data['html']);
        $this->assertStringNotContainsString('miro-nav', $data['html']);
        $this->assertStringContainsString('data-lang="en"', $data['html'], 'три языка: язык переключается стилями, как у первых карточек');

        // Адрес следующей порции без служебного partial: он же ссылка для браузера без скриптов.
        $this->assertStringEndsWith('/events?page=3', $data['next']);

        $last = $this->getJson(route('events', ['page' => 3, 'partial' => 1]))->assertOk()->json();
        $this->assertSame(2, $this->cards($last['html']));
        $this->assertNull($last['next']);

        $beyond = $this->getJson(route('events', ['page' => 9, 'partial' => 1]))->assertOk()->json();
        $this->assertSame('', trim($beyond['html']));
        $this->assertNull($beyond['next']);
    }

    public function test_every_batch_comes_from_the_cache_after_the_first_visit(): void
    {
        $this->news(20);

        foreach ([1, 2, 3] as $page) {
            $this->get(route('events', ['page' => $page]))->assertOk();
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        foreach ([1, 2, 3] as $page) {
            $this->get(route('events', ['page' => $page]))->assertOk();
            $this->getJson(route('events', ['page' => $page, 'partial' => 1]))->assertOk();
        }
        $queries = array_filter(array_column(DB::getQueryLog(), 'query'), fn (string $sql) => preg_match('/\b(?:from|join)\s+["`]?(?:events|event_translations)\b/i', $sql) === 1);
        DB::disableQueryLog();

        $this->assertSame([], array_values($queries), 'порции из кеша: '.implode(' | ', $queries));
    }

    public function test_a_new_news_item_appears_at_the_top_after_saving(): void
    {
        $this->news(12);
        $this->get(route('events'))->assertOk();
        $this->get(route('events', ['page' => 2]))->assertOk();

        $event = Event::create(['slug' => 'samaya-novaya', 'tone' => 'rose', 'position' => 0, 'starts_at' => '2026-06-01', 'is_published' => true]);
        $event->translations()->create(['locale' => 'ru', 'title' => 'Самая новая', 'type' => 'Событие', 'description' => 'Коротко.']);

        $first = $this->get(route('events'))->getContent();
        $this->assertStringContainsString('Самая новая<', $first);
        $this->assertStringNotContainsString('Новость 9<', $first, 'порции сдвинулись: девятая ушла на вторую');
        $this->assertStringContainsString('Новость 9<', $this->get(route('events', ['page' => 2]))->getContent());
    }

    public function test_the_script_and_the_styles_are_in_place(): void
    {
        $script = file_get_contents(public_path('themes/public/miro/js/load-more.js'));

        foreach (['data-load-more', "'partial'", 'fetch(', 'response.json()', 'data-target', 'ctrlKey', 'preventDefault', 'window.location.href = link.href'] as $needle) {
            $this->assertStringContainsString($needle, $script, $needle);
        }

        $css = file_get_contents(public_path('themes/public/miro/css/events.css'));
        $this->assertStringContainsString('.miro-events-more', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);

        // Карточки лежат в одном общем файле: первые девять и подгруженные устроены одинаково.
        $this->assertFileExists(resource_path('views/themes/public/miro/partials/event-cards.blade.php'));
        $page = file_get_contents(resource_path('views/themes/public/miro/events.blade.php'));
        $this->assertStringContainsString("themes.public.miro.partials.event-cards", $page);
    }
}
