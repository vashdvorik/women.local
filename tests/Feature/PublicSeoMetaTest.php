<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Метаданные публичных страниц (на русском): заголовок, описание, canonical и Open Graph (компонент <x-seo>).
 */
class PublicSeoMetaTest extends TestCase
{
    use RefreshDatabase;

    /** Middleware KeepNamesTogether склеивает «Women's Hub» неразрывным пробелом; для сравнения возвращаем обычный. */
    private function plain(string $html): string
    {
        return str_replace("\u{00A0}", ' ', $html);
    }

    public function test_every_public_page_has_russian_title_description_canonical_and_open_graph(): void
    {
        $pages = [
            '/' => "Women's Hub — платформа для женщин-предпринимательниц",
            '/about' => 'О платформе — Women',
            '/members' => 'Участницы — Women',
            '/experts' => 'Эксперты — Women',
            '/events' => 'Новости — Women',
            '/partners' => 'Партнёры — Women',
            '/contact' => 'Контакты — Women',
            '/projects' => 'Проекты и инициативы для женщин — Women',
            '/opportunities' => 'Возможности — Women',
            '/media/photos' => 'Фото — Women',
            '/media/videos' => 'Видео — Women',
            '/media/publications' => 'Публикации — Women',
        ];

        foreach ($pages as $path => $title) {
            $html = $this->plain($this->get($path)->assertOk()->getContent());

            $this->assertStringContainsString('<title>'.e($title), $html, $path);
            $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{20,}"/u', $html, $path);
            $this->assertStringContainsString('<link rel="canonical" href="'.url($path === '/' ? '' : $path).'">', $html, $path);
            $this->assertStringContainsString('<meta property="og:title"', $html, $path);
            $this->assertStringContainsString('<meta property="og:image" content="http', $html, $path);
        }
    }

    public function test_news_page_takes_title_and_description_from_the_record_and_draft_is_not_indexed(): void
    {
        $event = Event::create(['tone' => 'pink', 'slug' => 'forum', 'is_published' => true]);
        $event->translations()->create(['locale' => 'ru', 'title' => 'Форум лидеров', 'description' => 'Встреча женщин-лидеров.', 'content' => [['uid' => 'b', 'type' => 'text', 'data' => ['html' => '<p>Текст</p>']]]]);

        $html = $this->plain($this->get('/events/forum')->assertOk()->getContent());

        $this->assertStringContainsString('<title>Форум лидеров — Women', $html);
        $this->assertStringContainsString('Встреча женщин-лидеров.', $html);
        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
        $this->assertStringNotContainsString('noindex', $html);
    }
}
