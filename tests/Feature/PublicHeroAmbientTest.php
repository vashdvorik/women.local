<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Живой фон первого экрана: световые пятна и золотистые пылинки (только CSS), не мешающие тексту. */
class PublicHeroAmbientTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_hero_has_a_decorative_ambient_layer_between_the_photo_and_the_text(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Слой чисто декоративный: скрыт от читалок экрана; пылинок 18, световых пятен 3.
        $this->assertMatchesRegularExpression('#<div class="miro-hero__ambient" aria-hidden="true">#', $html);
        $this->assertSame(3, substr_count($html, 'class="miro-hero__glow '));
        $this->assertSame(18, substr_count($html, 'class="miro-hero__dust"'));

        // Слой стоит в разметке после фото (иначе фото закрыло бы его) и до нижней волны.
        $visual = strpos($html, 'class="miro-hero__visual"');
        $ambient = strpos($html, 'class="miro-hero__ambient"');
        $curve = strpos($html, 'class="miro-hero__bottom-curve"');
        $this->assertTrue($visual < $ambient && $ambient < $curve);
    }

    public function test_the_buttons_and_the_proof_card_form_one_block(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Кнопки и плашка «500+» лежат в одном блоке (на широких экранах у них одна ширина).
        $this->assertSame(1, substr_count($html, 'class="miro-hero__cta"'));
        $this->assertMatchesRegularExpression('#class="miro-hero__cta">\s*<div class="miro-hero__actions">.*?class="miro-proof">#s', $html);

        // Число в плашке статичное: без счётчика и без отдельного скрипта.
        $this->assertStringContainsString('<strong class="miro-proof__value">500<span class="miro-proof__plus">+</span></strong>', $html);
        $this->assertStringNotContainsString('data-proof-count', $html);
        $this->assertStringNotContainsString('hero-proof.js', $html);
    }

    public function test_the_layer_never_catches_clicks_and_respects_reduced_motion(): void
    {
        $css = (string) file_get_contents(public_path('themes/public/miro/css/landing.css'));

        $this->assertMatchesRegularExpression('/\.miro-hero__ambient\s*\{[^}]*pointer-events:\s*none/', $css);
        // Текст (z-index 2) выше слоя (z-index 1).
        $this->assertMatchesRegularExpression('/\.miro-hero__ambient\s*\{[^}]*z-index:\s*1;/', $css);
        $this->assertMatchesRegularExpression('/\.miro-hero__content\s*\{[^}]*z-index:\s*2;/', $css);
        // «Уменьшить анимацию»: пылинки скрыты, пятна неподвижны.
        $this->assertMatchesRegularExpression('/prefers-reduced-motion:\s*reduce\)\s*\{\s*\.miro-hero__glow\s*\{\s*animation:\s*none;[^}]*\}\s*\.miro-hero__dust\s*\{\s*display:\s*none;/', $css);
    }
}
