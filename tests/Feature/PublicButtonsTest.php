<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Светлые кнопки на тёмных блоках (CTA, подвал): у части страниц в их собственном CSS не было
 * заливки «pink», а общее правило «светлый текст в тёмных блоках» делало надпись на светлой кнопке
 * светлой. Итог: кнопка «Открыть Telegram» на странице «События» была видна как простой текст,
 * а «Стать партнёром» и «Начать регистрацию» читались с трудом.
 *
 * Раскраску здесь не проверить (тест не рисует страницу), поэтому проверяется то, на чём она держится:
 * общий файл стилей задаёт заливку и тёмный текст, подключается на каждой странице последним, а страницы
 * не подменяют цвет инлайн-стилем.
 */
class PublicButtonsTest extends TestCase
{
    use RefreshDatabase;

    private function navigationCss(): string
    {
        return (string) file_get_contents(public_path('themes/public/miro/css/navigation.css'));
    }

    public function test_shared_stylesheet_defines_the_light_button_fill_and_dark_label(): void
    {
        $css = $this->navigationCss();

        // Заливка «pink» задана в общем файле, а не только в стилях отдельных страниц.
        $this->assertMatchesRegularExpression('/\.miro-button--pink[^{]*\{[^}]*background:\s*var\(--miro-pink\)/', $css);

        // Надпись внутри кнопки (span) тёмная: иначе её перекрашивает правило для тёмных блоков.
        $this->assertMatchesRegularExpression('/\.miro-page \.miro-button\.miro-button--pink span[^{]*\{[^}]*color:\s*var\(--miro-primary\)/s', $css);
        $this->assertMatchesRegularExpression('/\.miro-page \.miro-public-cta \.miro-button span[^{]*\{[^}]*color:\s*var\(--miro-primary\)/s', $css);
    }

    public function test_pages_with_light_buttons_load_the_shared_stylesheet_last(): void
    {
        foreach (['/events', '/partners', '/members/join', '/experts', '/members', '/about/priorities'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            preg_match_all('#<link rel="stylesheet" href="[^"]*/css/([a-z-]+)\.css"#', $html, $m);
            $this->assertSame('navigation', end($m[1]), "$url: navigation.css должен идти последним");
        }
    }

    public function test_light_buttons_do_not_override_colours_inline(): void
    {
        // Инлайн-цвет на кнопке не доходит до надписи внутри неё, поэтому светлую кнопку задают классом.
        $html = $this->get('/partners')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#class="miro-button miro-button--pink"[^>]*><span data-lang="ru">Стать партнёром#u', $html);
        $this->assertDoesNotMatchRegularExpression('#miro-button--primary"[^>]*style="background:var\(--miro-pink\)#', $html);
    }

    public function test_landing_secondary_buttons_are_light_on_dark_only_in_the_nav_and_hero(): void
    {
        // «Светлая рамка, прозрачная заливка» — вид для тёмных блоков. Правило без уточнения «.miro-nav» красило
        // все вторичные кнопки главной, и «Все участницы» на светлой секции сливалась с фоном: кнопки не было видно.
        $css = (string) file_get_contents(public_path('themes/public/miro/css/landing.css'));

        $this->assertDoesNotMatchRegularExpression('/\.miro-landing-page \.miro-button--secondary[^{]*\{/', $css);
        $this->assertMatchesRegularExpression('/\.miro-landing-page \.miro-nav \.miro-button--secondary\s*\{[^}]*rgba\(255, 249, 245/', $css);

        // У светлой кнопки hero свой hover: базовый hover даёт светлую заливку, и светлая надпись пропала бы.
        $this->assertMatchesRegularExpression('/\.miro-hero--image \.miro-button--secondary\s*\{[^}]*background: transparent/', $css);
        $this->assertMatchesRegularExpression('/\.miro-hero--image \.miro-button--secondary:hover\s*\{[^}]*rgba\(255, 249, 245/', $css);

        // Обычная вторичная кнопка: белая заливка и заметная рамка — читается на светлых секциях.
        $this->assertMatchesRegularExpression('/^\s*\.miro-button--secondary \{[^}]*border: 1px solid var\(--miro-hairline-strong\);[^}]*background: #fff/m', $css);

        // «Все участницы» — как раз такая кнопка на светлой секции.
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#class="miro-participants__cta">\s*<a class="miro-button miro-button--secondary"#', $html);
    }
}
