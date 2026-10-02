<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Главная (тема miro): секции стоят не в порядке разметки, а в порядке CSS `order`. Поэтому «Новости»
 * оказались прямо перед «Преимуществами», и у обеих был один и тот же белый фон: граница между ними
 * не читалась. Соседние секции должны различаться фоном.
 */
class PublicLandingSectionsTest extends TestCase
{
    use RefreshDatabase;

    /** Фон секции по её классам: у секции без модификатора фона — белый (холст страницы). */
    private function background(string $html, string $id): string
    {
        $this->assertMatchesRegularExpression('#<section class="([^"]*)" id="'.$id.'"#', $html, "секции #{$id} нет на главной");
        preg_match('#<section class="([^"]*)" id="'.$id.'"#', $html, $m);

        return preg_match('/miro-section--(surface|soft|tint)/', $m[1], $bg) ? $bg[1] : 'canvas';
    }

    /** Порядок секций на странице: их номера `order` в CSS главной. */
    private function visualOrder(): array
    {
        $css = (string) file_get_contents(public_path('themes/public/miro/css/landing.css'));
        preg_match_all('/main > (?:#|\.)([a-z-]+) \{ order: (\d+); \}/', $css, $m, PREG_SET_ORDER);

        $order = [];
        foreach ($m as [, $id, $n]) {
            $order[(int) $n] = $id;
        }
        ksort($order);

        return array_values($order);
    }

    public function test_news_and_benefits_sit_side_by_side_and_have_different_backgrounds(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $order = $this->visualOrder();

        // Обе секции стоят рядом: без этого проверка теряет смысл.
        $this->assertSame(1, array_search('benefits', $order) - array_search('events', $order), 'после «Новостей» идут «Преимущества»');

        $this->assertNotSame($this->background($html, 'events'), $this->background($html, 'benefits'));
    }

    public function test_neighbouring_content_sections_never_share_a_background(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Секции с заголовком и фоном по модификатору; hero, «направления» и слово директора устроены иначе.
        $sections = array_values(array_filter($this->visualOrder(), fn ($id) => preg_match('#<section class="miro-section[^"]*" id="'.$id.'"#', $html)));
        $this->assertGreaterThanOrEqual(7, count($sections));

        foreach ($sections as $i => $id) {
            if ($i === 0) {
                continue;
            }
            $this->assertNotSame(
                $this->background($html, $sections[$i - 1]),
                $this->background($html, $id),
                "«{$sections[$i - 1]}» и «{$id}» идут подряд и имеют одинаковый фон"
            );
        }
    }
}
