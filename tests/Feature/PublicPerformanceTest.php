<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/** Нагрузка на устройство посетителя: без размытия под шапкой, с паузой невидимых анимаций, режимом lite и отложенной отрисовкой секций. */
class PublicPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cache::forget(SiteSetting::LANDING_THEME_KEY);

        parent::tearDown();
    }

    /** @return list<string> */
    private static function themes(): array
    {
        return ['miro', 'miro2'];
    }

    public function test_the_sticky_header_has_no_backdrop_blur_in_any_theme(): void
    {
        foreach (self::themes() as $theme) {
            foreach (glob(public_path("themes/public/{$theme}/css/*.css")) as $file) {
                $this->assertStringNotContainsString('backdrop-filter', (string) file_get_contents($file), "{$theme}/".basename($file));
            }
        }
    }

    public function test_every_page_loads_the_performance_script_and_the_styles_know_its_classes(): void
    {
        foreach (self::themes() as $theme) {
            SiteSetting::setLandingTheme($theme);

            foreach (['/', '/about', '/contact', '/events'] as $page) {
                $this->get($page)->assertOk()->assertSee("themes/public/{$theme}/js/perf.js?v=", false);
            }

            $css = (string) file_get_contents(public_path("themes/public/{$theme}/css/navigation.css"));
            foreach (['.is-offscreen', 'html.tab-hidden', 'html.lite', 'animation-play-state: paused'] as $needle) {
                $this->assertStringContainsString($needle, $css, "{$theme}: {$needle}");
            }

            $this->assertStringContainsString('content-visibility: auto', (string) file_get_contents(public_path("themes/public/{$theme}/css/landing.css")), $theme);
        }

        $this->assertFileEquals(public_path('themes/public/miro/js/perf.js'), public_path('themes/public/miro2/js/perf.js'));
    }

    public function test_the_light_theme_has_no_endless_decorative_animations_left_on_the_landing(): void
    {
        $light = (string) file_get_contents(public_path('themes/public/miro2/css/light.css'));

        foreach (['.miro-direction-card', '.miro-direction-card__icon', '.miro-proof', '.miro-floating-accents span'] as $selector) {
            $this->assertStringContainsString($selector, $light, $selector);
        }
        $this->assertMatchesRegularExpression('/animation: none !important; will-change: auto !important;/', $light);

        // Живого фона с точками и пятнами в светлой теме нет совсем (убран по просьбе заказчика)
        SiteSetting::setLandingTheme('miro2');
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('miro-hero__ambient', $html);
        $this->assertStringNotContainsString('miro-hero__sparkles', $html);
        $this->assertStringNotContainsString('miro-hero__dust', $html);
        $this->assertStringNotContainsString('miro-hero__glow', $html);
    }

    public function test_the_hero_photo_of_the_light_theme_is_baked_with_its_fade_and_has_no_mask_or_filter(): void
    {
        $file = public_path('themes/public/miro2/images/bannerhero-light.webp');
        $this->assertFileExists($file);
        $this->assertLessThan(300 * 1024, filesize($file), 'готовая картинка должна быть лёгкой');

        // Левый край прозрачный, правый непрозрачный: растворение запечено в файле
        $image = imagecreatefromwebp($file);
        $this->assertSame(127, (imagecolorat($image, 0, 400) >> 24) & 127, 'слева прозрачно');
        $this->assertSame(0, (imagecolorat($image, imagesx($image) - 1, 400) >> 24) & 127, 'справа непрозрачно');

        $light = (string) file_get_contents(public_path('themes/public/miro2/css/light.css'));
        $this->assertStringContainsString('.miro-hero__image { -webkit-mask-image: none; mask-image: none; }', $light);
        $this->assertStringContainsString('.miro-hero__image img { filter: none; }', $light);
    }
}
