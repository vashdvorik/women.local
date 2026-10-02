<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Post;
use App\Models\Video;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Карточки новостей и материалов: кликабельна вся карточка, а не только маленькая ссылка «Подробнее».
 * Ссылка стоит в заголовке и растягивается на карточку стилем; «Подробнее →» внизу — подсказка, не ссылка.
 * Так экранные дикторы читают название, у карточки одна остановка при обходе клавишей Tab, а клик по фото,
 * тексту и пустому месту открывает материал.
 */
class PublicCardsTest extends TestCase
{
    use RefreshDatabase;

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    private function news(string $slug, array $attributes, string $title, ?array $content = null): Event
    {
        $event = Event::create(array_merge(['slug' => $slug, 'tone' => 'pink', 'position' => Event::count() + 1], $attributes));
        $event->translations()->create(['locale' => 'ru', 'title' => $title, 'description' => 'Кратко', 'content' => $content]);

        return $event;
    }

    private const BODY = [['uid' => 't', 'type' => 'text', 'data' => ['html' => '<p>Текст.</p>']]];

    public function test_a_news_card_has_exactly_one_link_and_it_is_on_the_title(): void
    {
        $this->news('s-tekstom', [], 'С текстом', self::BODY);
        $this->news('vneshnyaya', ['url' => 'https://example.test/out'], 'Внешняя');
        $this->news('bez-ssylki', [], 'Без ссылки');

        foreach ([route('events'), url('/')] as $url) {
            $xp = $this->xpath($this->get($url)->assertOk()->getContent());
            $cards = $xp->query('//article[contains(@class,"miro-event-card")]');
            $this->assertSame(3, $cards->length, $url);

            $byTitle = [];
            foreach ($cards as $card) {
                $title = trim($xp->evaluate('string(.//h3//span[@data-lang="ru"])', $card));
                $byTitle[$title] = $card;
            }

            // С текстом: одна ссылка на свою страницу, в той же вкладке; карточка помечена как ссылочная.
            $card = $byTitle['С текстом'];
            $links = $xp->query('.//a', $card);
            $this->assertSame(1, $links->length, "$url: у карточки одна ссылка");
            $this->assertSame(route('events.show', ['event' => 's-tekstom']), $links->item(0)->getAttribute('href'));
            $this->assertSame('', $links->item(0)->getAttribute('target'));
            $this->assertStringContainsString('miro-event-card--linked', $card->getAttribute('class'));
            $this->assertSame(1, $xp->query('.//h3//a[contains(@class,"miro-event-card__title-link")]', $card)->length, 'ссылка стоит в заголовке');

            // «Подробнее →» — подсказка, а не ссылка: скрыта от экранных дикторов, чтобы название не дублировалось.
            $cue = $xp->query('.//span[contains(@class,"miro-event-card__link")]', $card);
            $this->assertSame(1, $cue->length);
            $this->assertSame('true', $cue->item(0)->getAttribute('aria-hidden'));
            $this->assertStringContainsString('→', $cue->item(0)->textContent);

            // Внешняя: своя вкладка и стрелка ↗ вместо →.
            $card = $byTitle['Внешняя'];
            $link = $xp->query('.//a', $card)->item(0);
            $this->assertSame('https://example.test/out', $link->getAttribute('href'));
            $this->assertSame('_blank', $link->getAttribute('target'));
            $this->assertStringContainsString('noopener', $link->getAttribute('rel'));
            $this->assertStringContainsString('↗', $xp->query('.//span[contains(@class,"miro-event-card__link")]', $card)->item(0)->textContent);

            // Вести некуда: ни ссылки, ни подсказки, ни «ссылочного» вида (курсор-рука, подъём при наведении).
            $card = $byTitle['Без ссылки'];
            $this->assertSame(0, $xp->query('.//a', $card)->length);
            $this->assertSame(0, $xp->query('.//span[contains(@class,"miro-event-card__link")]', $card)->length);
            $this->assertStringNotContainsString('miro-event-card--linked', $card->getAttribute('class'));
        }
    }

    public function test_material_cards_in_lists_follow_the_same_pattern(): void
    {
        $post = Post::create(['slug' => 'katalog', 'status' => 'published', 'published_at' => now()->subDay()]);
        $post->translations()->create(['locale' => 'ru', 'title' => 'Каталог участниц', 'excerpt' => 'Кратко']);

        $video = Video::create(['youtube_id' => 'dQw4w9WgXcQ', 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ', 'position' => 1]);
        $video->translations()->create(['locale' => 'ru', 'title' => 'Ролик']);

        $xp = $this->xpath($this->get(route('media.publications'))->assertOk()->getContent());
        $links = $xp->query('//article[contains(@class,"miro-event-card--linked")]//a');
        $this->assertSame(1, $links->length);
        $this->assertSame(url('media/publications/katalog'), $links->item(0)->getAttribute('href'));
        $this->assertSame(1, $xp->query('//article//h3//a[contains(@class,"miro-event-card__title-link")]')->length);
        $this->assertStringContainsString('→', $xp->query('//article//span[contains(@class,"miro-event-card__link")]')->item(0)->textContent);

        // Видео: подсказка «Смотреть», ссылка в новой вкладке (ролик на YouTube).
        $xp = $this->xpath($this->get(route('media.videos'))->assertOk()->getContent());
        $link = $xp->query('//article[contains(@class,"miro-event-card--linked")]//a')->item(0);
        $this->assertNotNull($link, 'у карточки видео есть ссылка');
        $this->assertSame('_blank', $link->getAttribute('target'));
        $cue = $xp->query('//article//span[contains(@class,"miro-event-card__link")]')->item(0);
        $this->assertStringContainsString('Смотреть', $cue->textContent);
        $this->assertStringContainsString('↗', $cue->textContent);
    }

    public function test_stylesheet_stretches_the_link_over_the_card_and_shows_keyboard_focus(): void
    {
        $css = (string) file_get_contents(public_path('themes/public/miro/css/navigation.css'));

        // Ссылка растягивается на всю карточку: клик по любому месту открывает материал.
        $this->assertMatchesRegularExpression('/\.miro-event-card__title-link::after\s*\{[^}]*position:\s*absolute[^}]*inset:\s*0/', $css);
        $this->assertMatchesRegularExpression('/\.miro-event-card\s*\{[^}]*position:\s*relative/', $css);

        // Наведение и клавиатура: подъём карточки, курсор-рука, рамка вокруг всей карточки при фокусе.
        $this->assertMatchesRegularExpression('/\.miro-event-card--linked\s*\{[^}]*cursor:\s*pointer/', $css);
        $this->assertMatchesRegularExpression('/\.miro-event-card--linked:hover\s*\{[^}]*translateY/', $css);
        $this->assertStringContainsString('.miro-event-card--linked:has(.miro-event-card__title-link:focus-visible)', $css);

        // Название при наведении меняет цвет, а не подчёркивается: подчёркивание выглядело грубо.
        $this->assertMatchesRegularExpression('/\.miro-event-card--linked:hover \.miro-event-card__title-link\s*\{[^}]*color:/', $css);
        $this->assertDoesNotMatchRegularExpression('/\.miro-event-card[^{]*__title-link[^{]*\{[^}]*underline/', $css);
    }
}
