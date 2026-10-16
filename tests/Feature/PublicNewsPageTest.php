<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Страница новости (/events/{slug}): обложка, тип и дата, текст из блоков на трёх языках и возврат к списку
 * (кнопки со ссылкой на сайт организации нет). Карточка ведёт на страницу, когда у неё есть текст.
 */
class PublicNewsPageTest extends TestCase
{
    use RefreshDatabase;

    private const BLOCKS = [
        ['uid' => 'h1', 'type' => 'heading', 'data' => ['text' => 'О событии', 'level' => 'h2']],
        ['uid' => 't1', 'type' => 'text', 'data' => ['html' => '<p>Мы встретились.</p><script>alert(1)</script>']],
        ['uid' => 'i1', 'type' => 'image', 'data' => ['path' => '2026/09/inside.webp']],
        ['uid' => 'g1', 'type' => 'gallery_3', 'data' => ['images' => ['2026/09/a.webp', '2026/09/b.webp', '2026/09/c.webp']]],
    ];

    private function news(array $attributes = [], ?array $blocks = self::BLOCKS, array $ro = []): Event
    {
        $event = Event::create(array_merge([
            'slug' => 'vstrecha',
            'tone' => 'rose',
            'position' => 1,
            'starts_at' => '2026-05-20',
            'image_path' => '2026/09/cover.webp',
            'url' => 'https://example.test/original',
        ], $attributes));

        $event->translations()->create([
            'locale' => 'ru', 'title' => 'Встреча', 'type' => 'Конференция', 'description' => 'Коротко о встрече.', 'content' => $blocks,
        ]);

        if ($ro) {
            $event->translations()->create(['locale' => 'ro', ...$ro]);
        }

        return $event;
    }

    public function test_news_page_renders_cover_meta_text_and_photos(): void
    {
        $this->news(ro: [
            'title' => 'Întâlnire', 'type' => 'Conferință', 'description' => 'Pe scurt.',
            'content' => [
                ['uid' => 'h1', 'type' => 'heading', 'data' => ['text' => 'Despre eveniment', 'level' => 'h2']],
                ['uid' => 't1', 'type' => 'text', 'data' => ['html' => '<p>Ne-am întâlnit.</p>']],
            ],
        ]);

        $response = $this->get(route('events.show', ['event' => 'vstrecha']));

        $response->assertOk()
            ->assertSee('Встреча')
            ->assertSee('Întâlnire')
            ->assertSee('Конференция')
            ->assertSee('20 мая')
            ->assertSee('/uploads/2026/09/cover.webp', false)
            // Блоки: заголовок, очищенный текст, картинка и галерея.
            ->assertSee('О событии')
            ->assertSee('Despre eveniment')
            ->assertSee('Мы встретились.')
            ->assertSee('Ne-am întâlnit.')
            ->assertSee('/uploads/2026/09/inside.webp', false)
            ->assertSee('/uploads/2026/09/b.webp', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            // Только возврат к списку: кнопки «Читать на сайте организации» на странице нет, даже когда у новости есть внешняя ссылка.
            ->assertDontSee('https://example.test/original', false)
            ->assertDontSee('Читать на сайте организации')
            ->assertDontSee('Read on the organisation')
            ->assertSee(route('events'), false);

        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }

    public function test_article_page_has_no_big_hero_and_the_title_stands_above_the_text(): void
    {
        $this->news();

        $html = $this->get(route('events.show', ['event' => 'vstrecha']))->assertOk()->getContent();

        // Большой шапки нет; единственный h1 — название внутри статьи, обычным жирным шрифтом (класс задаёт стиль).
        $this->assertStringNotContainsString('class="miro-public-hero"', $html);
        $this->assertSame(1, substr_count($html, '<h1'));

        // Порядок в разметке: название → обложка → текст → боковая колонка (тип и дата). На экране от 1024 px боковая
        // колонка справа, на узком тип и дата поднимаются под название (CSS). Краткого описания между ними нет.
        $order = ['miro-article__title', 'miro-article__cover', 'miro-prose', 'miro-article__meta'];
        $positions = array_map(fn (string $marker) => strpos($html, $marker), $order);
        $this->assertNotContains(false, $positions, 'не хватает части страницы');
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'порядок частей страницы нарушен');

        // Кружки-украшения шапки стоят в блоке с текстом, а не в шапке.
        $this->assertMatchesRegularExpression('#miro-public-content--article.*?miro-public-hero__accent#s', $html);
        $this->assertSame(1, substr_count($html, 'miro-public-hero__accent"'));
    }

    public function test_article_follows_the_site_grid_with_a_side_column_and_has_no_width_of_its_own(): void
    {
        $this->news();

        $html = $this->get(route('events.show', ['event' => 'vstrecha']))->assertOk()->getContent();

        // Название сверху, дальше два блока сетки: слева обложка и текст, справа боковая колонка с типом и датой.
        $this->assertMatchesRegularExpression('#miro-article__title.*miro-article__layout.*miro-article__main.*miro-article__aside#s', $html);
        $this->assertMatchesRegularExpression('#miro-article__main.*miro-article__cover.*miro-prose.*miro-article__aside.*miro-article__meta.*miro-article__back#s', $html);

        // Обложка и текст лежат в основной колонке, а тип, дата и «все новости» — в боковой.
        $aside = substr($html, strpos($html, '<aside class="miro-article__aside"'));
        $this->assertStringContainsString('miro-tag', $aside);
        $this->assertStringContainsString('miro-article__date', $aside);
        $this->assertStringContainsString(route('events'), $aside);
        $this->assertStringNotContainsString('miro-prose', $aside);

        // У статьи нет собственной ширины: ни max-width, ни центрирования, она идёт по контейнеру сайта.
        $css = file_get_contents(public_path('themes/public/miro/css/public-section.css'));
        $article = preg_match('/^\.miro-article \{[^}]*\}/m', $css, $m) ? $m[0] : '';
        $this->assertNotSame('', $article, 'правило .miro-article найдено');
        $this->assertStringNotContainsString('max-width', $article);
        $this->assertStringNotContainsString('margin: 0 auto', $article);
        $this->assertStringContainsString('grid-template-columns: repeat(12', $css);
        $this->assertMatchesRegularExpression('/\.miro-article__main \{[^}]*grid-column: 1 \/ span 8/', $css);
        $this->assertMatchesRegularExpression('/\.miro-article__aside \{[^}]*grid-column: 9 \/ span 4/', $css);

        // Обложку CSS больше не обрезает (раньше новостные 16:9 обрезались до 2,2:1).
        $this->assertDoesNotMatchRegularExpression('/\.miro-article__cover \{[^}]*aspect-ratio/', $css);
    }

    public function test_photos_open_by_click_cover_images_and_gallery_cells_alike(): void
    {
        $this->news();

        $html = $this->get(route('events.show', ['event' => 'vstrecha']))->assertOk()->getContent();

        // Обложка, картинка в тексте и каждая ячейка галереи — ссылка на сам файл: без скриптов она открывает его,
        // а lightbox.js перехватывает клик и показывает фото поверх страницы.
        foreach (['/uploads/2026/09/cover.webp', '/uploads/2026/09/inside.webp', '/uploads/2026/09/a.webp', '/uploads/2026/09/b.webp', '/uploads/2026/09/c.webp'] as $file) {
            $this->assertMatchesRegularExpression('#<a class="miro-zoom[^"]*" href="'.preg_quote($file, '#').'" data-lightbox><img [^>]*src="'.preg_quote($file, '#').'"#', $html, $file);
        }

        // Скрипт просмотра подключён и лежит на месте; в его тексте есть закрытие по Esc и листание клавишами.
        $this->assertStringContainsString('/themes/public/miro/js/lightbox.js', $html);
        $script = file_get_contents(public_path('themes/public/miro/js/lightbox.js'));
        foreach (['Escape', 'ArrowLeft', 'ArrowRight', 'aria-modal', 'touchend'] as $needle) {
            $this->assertStringContainsString($needle, $script, $needle);
        }
    }

    public function test_list_pages_keep_their_hero(): void
    {
        $this->get(route('media.publications'))->assertOk()->assertSee('class="miro-public-hero"', false);
    }

    public function test_untranslated_text_falls_back_to_russian(): void
    {
        $this->news(ro: ['title' => 'Întâlnire', 'content' => [
            ['uid' => 't1', 'type' => 'text', 'data' => ['html' => '']],
        ]]);

        $this->get(route('events.show', ['event' => 'vstrecha']))->assertOk()->assertSee('Мы встретились.');
    }

    public function test_page_without_text_still_opens_with_the_card_details(): void
    {
        $this->news(blocks: null);

        $this->get(route('events.show', ['event' => 'vstrecha']))
            ->assertOk()
            ->assertSee('Встреча')
            ->assertSee('/uploads/2026/09/cover.webp', false);
    }

    public function test_short_description_is_for_cards_and_page_meta_only_not_for_the_article(): void
    {
        $this->news();

        $html = $this->get(route('events.show', ['event' => 'vstrecha']))->assertOk()->getContent();

        // В самой статье описания нет.
        preg_match('#<article class="miro-article">.*?</article>#s', $html, $article);
        $this->assertNotEmpty($article, 'статья не найдена');
        $this->assertStringNotContainsString('Коротко о встрече.', $article[0]);

        // Для поисковиков оно остаётся в описании страницы.
        $this->assertStringContainsString('<meta name="description" content="Коротко о встрече.">', $html);

        // А карточка новости в списке его показывает.
        $this->get(route('events'))->assertOk()->assertSee('Коротко о встрече.');
    }

    public function test_news_page_opens_under_every_landing_theme(): void
    {
        // Страницы из админки есть в теме miro; тема без такой страницы получает её версию (PublicThemeView).
        $this->news();

        foreach (array_keys(\App\Models\SiteSetting::LANDING_THEMES) as $theme) {
            \App\Models\SiteSetting::setLandingTheme($theme);

            $this->get(route('events.show', ['event' => 'vstrecha']))->assertOk()->assertSee('Мы встретились.');
        }
    }

    public function test_unknown_address_is_not_found(): void
    {
        $this->get('/events/net-takoy-novosti')->assertNotFound();
    }

    public function test_unpublished_news_is_hidden_from_guests_and_previewable_by_the_admin(): void
    {
        $this->news(['is_published' => false]);

        $this->get(route('events.show', ['event' => 'vstrecha']))->assertNotFound();

        $this->actingAsAdmin()->get(route('events.show', ['event' => 'vstrecha']))
            ->assertOk()
            ->assertSee('Черновик: эту страницу видит только администратор');
    }

    public function test_card_leads_to_the_page_when_it_has_text_and_to_the_external_link_otherwise(): void
    {
        $this->news(['slug' => 's-tekstom', 'position' => 1, 'url' => 'https://example.test/one']);
        $withoutText = Event::create(['slug' => 'bez-teksta', 'tone' => 'pink', 'position' => 2, 'url' => 'https://example.test/two']);
        $withoutText->translations()->create(['locale' => 'ru', 'title' => 'Без текста']);
        $noLink = Event::create(['slug' => 'bez-nichego', 'tone' => 'teal', 'position' => 3]);
        $noLink->translations()->create(['locale' => 'ru', 'title' => 'Без ссылки']);

        foreach ([route('events'), url('/')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            // С текстом: своя страница, в этой же вкладке.
            $this->assertMatchesRegularExpression('#<a href="'.preg_quote(route('events.show', ['event' => 's-tekstom']), '#').'" class="miro-event-card__title-link"#', $html);
            // Без текста: прежняя внешняя ссылка в новой вкладке.
            $this->assertMatchesRegularExpression('#<a href="https://example.test/two" target="_blank" rel="noopener" class="miro-event-card__title-link"#', $html);
            // Ни того ни другого: ссылки и подсказки «Подробнее» нет.
            $this->assertSame(2, substr_count($html, 'class="miro-event-card__link"'), $url);
            $this->assertSame(2, substr_count($html, 'class="miro-event-card__title-link"'), $url);
        }
    }

    public function test_has_body_ignores_empty_blocks(): void
    {
        $this->assertFalse($this->news(blocks: [['uid' => 't', 'type' => 'text', 'data' => ['html' => '<p> </p>']]])->hasBody());
        $this->assertTrue(Event::create(['slug' => 'foto', 'tone' => 'pink'])->translations()->create([
            'locale' => 'ru', 'title' => 'Фото', 'content' => [['uid' => 'i', 'type' => 'image', 'data' => ['path' => '2026/09/x.webp']]],
        ])->event->fresh('translations')->hasBody());
    }
}
