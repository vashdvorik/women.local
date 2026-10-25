<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\Assets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/** Тема сайта miro2 — копия miro: свои шаблоны, стили и скрипты, а картинки общие (берутся из miro). */
class PublicSecondThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cache::forget(SiteSetting::LANDING_THEME_KEY);
        Assets::flush();

        parent::tearDown();
    }

    /** @return list<string> пути файлов внутри папки, относительно неё */
    private function files(string $dir): array
    {
        $found = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $found[] = str_replace('\\', '/', substr($file->getPathname(), strlen($dir) + 1));
        }
        sort($found);

        return $found;
    }

    public function test_the_theme_is_listed_and_has_the_same_pages_styles_and_scripts_as_miro(): void
    {
        $this->assertArrayHasKey('miro2', SiteSetting::LANDING_THEMES);

        $this->assertSame(
            $this->files(resource_path('views/themes/public/miro')),
            $this->files(resource_path('views/themes/public/miro2')),
        );

        foreach (['css', 'js'] as $kind) {
            $miro = $this->files(public_path("themes/public/miro/{$kind}"));
            $miro2 = $this->files(public_path("themes/public/miro2/{$kind}"));

            // miro2 — те же файлы плюс свои: light.css (светлый слой поверх тёмной палитры miro)
            $expected = $kind === 'css' ? [...$miro, 'light.css'] : $miro;
            sort($expected);
            $this->assertSame($expected, $miro2, $kind);
        }
    }

    public function test_every_page_loads_the_light_layer_right_after_the_navigation_styles(): void
    {
        foreach ($this->files(resource_path('views/themes/public/miro2')) as $file) {
            $source = (string) file_get_contents(resource_path('views/themes/public/miro2/'.$file));

            if (str_contains($source, '/css/navigation.css')) {
                $this->assertMatchesRegularExpression('#/css/navigation\.css\'\) \}\}">\s*<link rel="stylesheet" href="\{\{ asset_v\([^\n]*/css/light\.css\'\) \}\}">#', $source, $file);
            }
        }

        SiteSetting::setLandingTheme('miro2');
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'miro2/css/light.css'), strpos($html, 'miro2/css/navigation.css'), 'light.css идёт после navigation.css');
        // Светлая тема: шапка и подвал с цветным логотипом, не со светлым вариантом для тёмного фона
        $this->assertStringContainsString('images/brand/logo-platform.webp', $html);
        $this->assertStringNotContainsString('logo-platform-light.webp', $html);
    }
    public function test_the_copy_does_not_point_back_at_the_original_theme(): void
    {
        foreach ($this->files(resource_path('views/themes/public/miro2')) as $file) {
            if (! str_ends_with($file, '.blade.php')) {
                continue;
            }

            $source = (string) file_get_contents(resource_path('views/themes/public/miro2/'.$file));
            $this->assertDoesNotMatchRegularExpression('#themes[./]public[./]miro[./]#', $source, $file);
        }
    }

    public function test_the_landing_uses_its_own_styles_and_scripts_and_shared_pictures(): void
    {
        SiteSetting::setLandingTheme('miro2');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#themes/public/miro2/css/landing\.css\?v=\d+#', $html);
        $this->assertMatchesRegularExpression('#themes/public/miro2/js/impact\.js\?v=\d+#', $html);
        // Фото первого экрана у темы своё (запечённое: растворение слева и цвет уже в файле), остальные картинки общие с miro
        $this->assertMatchesRegularExpression('#themes/public/miro2/images/bannerhero-light\.webp\?v=\d+#', $html);
        $this->assertMatchesRegularExpression('#themes/public/miro/images/director.webp\?v=\d+#', $html);
        $this->assertDoesNotMatchRegularExpression('#themes/public/miro2/images/(?!bannerhero-light)#', $html);
        $this->assertStringNotContainsString('themes/public/miro/css/', $html);
    }

    public function test_every_public_page_renders_on_the_theme_without_touching_the_original_styles(): void
    {
        SiteSetting::setLandingTheme('miro2');

        foreach (['/about', '/members', '/events', '/partners', '/contact', '/experts', '/projects'] as $page) {
            $html = $this->get($page)->assertOk()->getContent();

            $this->assertStringContainsString('themes/public/miro2/css/', $html, $page);
            $this->assertStringNotContainsString('themes/public/miro/css/', $html, $page);
        }
    }

    public function test_an_own_picture_of_the_theme_wins_over_the_shared_one(): void
    {
        $this->assertDirectoryDoesNotExist(public_path('themes/public/miro2/images/participants'), 'у темы нет своей папки с фото участниц');
        // Папка картинок (адрес-основа для скриптов) ведёт на общую
        $this->assertStringContainsString('themes/public/miro/images/participants', Assets::url('themes/public/miro2/images/participants'));
        $this->assertStringNotContainsString('miro2', Assets::url('themes/public/miro2/images/participants'));

        $own = public_path('themes/public/miro2/images/_probe.webp');
        @mkdir(dirname($own), 0777, true);
        file_put_contents($own, 'x');

        try {
            Assets::flush();
            $this->assertStringContainsString('themes/public/miro2/images/_probe.webp', Assets::url('themes/public/miro2/images/_probe.webp'));
            $this->assertStringContainsString('themes/public/miro/images/bannerhero.webp', Assets::url('themes/public/miro2/images/bannerhero.webp'));
            // Несуществующее нигде остаётся как есть
            $this->assertStringContainsString('themes/public/miro2/images/nope.webp', Assets::url('themes/public/miro2/images/nope.webp'));
        } finally {
            @unlink($own);
            @rmdir(dirname($own));
            Assets::flush();
        }
    }
}
