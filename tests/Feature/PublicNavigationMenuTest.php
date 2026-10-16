<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Выпадающие меню шапки сайта. Меню тёмное, и текст пунктов должен быть светлым на всех страницах, в том числе на главной:
 * там действовало старое правило «тёмный текст» с !important, и пункты Gala (текст прямо в ссылке, без вложенного span)
 * сливались с фоном.
 */
class PublicNavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    private function css(): string
    {
        return file_get_contents(public_path('themes/public/miro/css/navigation.css'));
    }

    public function test_the_gala_menu_lists_every_configured_year_on_every_page(): void
    {
        foreach (['/', '/events', '/media/photos'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            foreach (array_keys(config('site.gala')) as $year) {
                $this->assertStringContainsString("Gala {$year}", $html, "{$url}: Gala {$year}");
            }
        }
    }

    public function test_the_landing_menu_links_get_a_light_colour_after_the_legacy_dark_rule(): void
    {
        $css = $this->css();
        $selector = '.miro-page .miro-landing-page .miro-nav .miro-nav__dropdown-menu';

        // Старое правило красит тёмным всё в меню главной (ссылки, вложенные span и кнопки).
        $legacy = strpos($css, $selector.' :is(a, span, button) { color: var(--miro-text-dark) !important; }');
        $this->assertNotFalse($legacy, 'старое правило на месте: его специфичность задаёт, как поправка должна быть записана');

        // Поправка: тот же селектор (равная специфичность) стоит позже и возвращает светлый цвет ссылкам и их span.
        $fix = strpos($css, $selector." a,\n".$selector.' a span { color: #F8E8DF !important; }');
        $this->assertNotFalse($fix, 'светлый цвет у ссылок меню главной и их вложенных span');
        $this->assertGreaterThan($legacy, $fix, 'поправка записана после старого правила');

        // При наведении текст ещё светлее.
        $this->assertStringContainsString($selector." a:hover,\n".$selector.' a:hover span { color: #FFF9F5 !important; }', $css);
    }
}
