<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Страницы «Фото» и «Видео» без большой шапки (подпись «Медиатека», крупный заголовок, описание, кружки): содержимое
 * начинается сразу под меню, а название страницы остаётся в разметке скрытым заголовком h1. У остальных списков
 * (публикации, проекты, возможности) шапка на месте.
 */
class PublicBareHeroTest extends TestCase
{
    use RefreshDatabase;

    private function album(): void
    {
        $album = Album::create([
            'slug' => 'nrc', 'status' => 'published', 'published_at' => now()->subDay(),
            'blocks' => [['uid' => 'g', 'type' => 'gallery_2', 'data' => ['images' => ['2026/10/a1.webp', '2026/10/a2.webp']]]],
        ]);
        $album->translations()->create(['locale' => 'ru', 'title' => 'NRC']);
    }

    private function video(): void
    {
        $video = Video::create(['youtube_id' => 'dQw4w9WgXcQ', 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ', 'position' => 1]);
        $video->translations()->create(['locale' => 'ru', 'title' => 'Открытие']);
    }

    /** @return array<string, array{0: string, 1: list<string>}> */
    public static function barePages(): array
    {
        return [
            'фото' => ['media.photos', ['Фото', 'Photos', 'Fotografii']],
            'видео' => ['media.videos', ['Видео', 'Videos', 'Video']],
        ];
    }

    /** @dataProvider barePages */
    public function test_the_page_has_no_big_hero_but_keeps_its_title_as_a_hidden_heading(string $route, array $titles): void
    {
        $this->album();
        $this->video();

        $document = $this->get(route($route))->assertOk()->getContent();
        $html = substr($document, (int) strpos($document, '<body'));

        // Шапки нет: ни секции, ни подписи «Медиатека», ни описания страницы, ни кружков. Описание остаётся только в
        // <head> (meta description) для поисковиков.
        $this->assertStringNotContainsString('miro-public-hero', $html);
        $this->assertStringNotContainsString('miro-eyebrow', $html);
        $this->assertStringNotContainsString('Фотографии с мероприятий, встреч', $html);
        $this->assertStringNotContainsString('Видео с мероприятий, интервью', $html);
        $this->assertStringContainsString('<meta name="description" content="', $document);

        // Содержимое начинается сразу, а заголовок h1 один, скрытый, на трёх языках.
        $this->assertStringContainsString('miro-public-content--bare', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertMatchesRegularExpression('#<h1 class="miro-visually-hidden"><span data-lang="ru">'.$titles[0].'</span><span data-lang="en">'.$titles[1].'</span><span data-lang="ro">'.$titles[2].'</span></h1>#', $html);
        $this->assertStringContainsString('<title>'.$titles[0].' — ', $document, 'название вкладки прежнее');
    }

    public function test_an_empty_section_still_has_its_heading_and_the_placeholder(): void
    {
        foreach (['media.photos', 'media.videos'] as $route) {
            $html = $this->get(route($route))->assertOk()->assertSee('Раздел готовится')->getContent();

            $this->assertStringNotContainsString('miro-public-hero', $html);
            $this->assertSame(1, substr_count($html, '<h1'), $route);
        }
    }

    public function test_other_list_pages_keep_their_hero(): void
    {
        foreach (['media.publications' => 'Публикации', 'projects' => 'Проекты', 'opportunities' => 'Возможности'] as $route => $title) {
            $html = $this->get(route($route))->assertOk()->getContent();

            $this->assertStringContainsString('class="miro-public-hero', $html, $route);
            // Надпись над заголовком есть только у «Публикаций»: на «Проектах» и «Возможностях» слово «Платформа» убрано как лишнее.
            $route === 'media.publications'
                ? $this->assertStringContainsString('miro-eyebrow', $html, $route)
                : $this->assertStringNotContainsString('miro-eyebrow', $html, $route);
            $this->assertStringContainsString($title, $html);
            $this->assertStringNotContainsString('miro-public-content--bare', $html);
        }
    }

    public function test_the_album_page_is_not_affected(): void
    {
        $this->album();

        $html = $this->get(route('media.photos.show', 'nrc'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('miro-article__title', $html);
        $this->assertStringNotContainsString('miro-visually-hidden', $html);
    }

    public function test_the_styles_are_in_place(): void
    {
        $css = file_get_contents(public_path('themes/public/miro/css/public-section.css'));

        $this->assertStringContainsString('.miro-public-content--bare { padding-top: 48px; }', $css);
        $this->assertStringContainsString('.miro-visually-hidden {', $css);
        $this->assertStringContainsString('clip: rect(0 0 0 0)', $css);
    }
}
