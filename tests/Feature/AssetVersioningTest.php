<?php

namespace Tests\Feature;

use App\Support\Assets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * После обновления сайта у посетителя не должна оставаться старая версия: файлы темы (стили, скрипты, картинки) лежат
 * под постоянными именами, поэтому их адреса получают метку ?v=время изменения файла (asset_v()), а Apache отдаёт их
 * с проверкой при каждом заходе (public/.htaccess).
 */
class AssetVersioningTest extends TestCase
{
    use RefreshDatabase;

    private string $probe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->probe = public_path('themes/public/miro/css/_version-probe.css');
        Assets::flush();
    }

    protected function tearDown(): void
    {
        @unlink($this->probe);
        Assets::flush();

        parent::tearDown();
    }

    public function test_the_url_carries_the_file_modification_time_and_changes_with_the_file(): void
    {
        file_put_contents($this->probe, 'a{}');
        touch($this->probe, 1_700_000_000);

        $this->assertSame(asset('themes/public/miro/css/_version-probe.css').'?v=1700000000', asset_v('themes/public/miro/css/_version-probe.css'));

        // Файл правят: адрес другой, браузер возьмёт новую версию.
        touch($this->probe, 1_700_000_500);
        Assets::flush();

        $this->assertStringEndsWith('?v=1700000500', asset_v('themes/public/miro/css/_version-probe.css'));
    }

    public function test_a_missing_file_gets_a_plain_url(): void
    {
        $this->assertSame(asset('themes/public/miro/css/no-such-file.css'), asset_v('themes/public/miro/css/no-such-file.css'));
    }

    public function test_every_style_script_and_theme_image_on_the_public_pages_is_versioned(): void
    {
        foreach (['/', '/about', '/events', '/experts', '/media/photos', '/media/videos', '/about/regulations', '/members/join', '/partners', '/contact', '/gala'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            preg_match_all('#(?:href|src)="([^"]*/themes/[^"]+)"#', $html, $matches);
            $this->assertNotEmpty($matches[1], $path);

            foreach ($matches[1] as $url) {
                $this->assertMatchesRegularExpression('#\?v=\d+$#', $url, "{$path}: {$url} без метки версии");
            }
        }
    }

    public function test_the_cabinet_and_admin_pages_are_versioned_too(): void
    {
        $login = $this->get(route('login'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#/themes/public/miro/images/brand/logo-platform\.webp\?v=\d+#', $login);
        $this->assertMatchesRegularExpression('#/themes/public/miro/images/brand/favicon-w4w\.png\?v=\d+#', $login);

        $html = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#/themes/public/miro/images/brand/logo-wordmark\.webp\?v=\d+#', $html);
    }

    public function test_no_template_uses_the_plain_asset_helper_for_files_of_the_site(): void
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression('/(?<![\w>_])asset\(/', (string) file_get_contents($file->getPathname()), $file->getFilename().': вместо asset() нужен asset_v()');
        }
    }

    public function test_html_pages_are_not_cached_by_the_browser(): void
    {
        foreach (['/', '/events', '/experts'] as $path) {
            $cache = (string) $this->get($path)->assertOk()->headers->get('Cache-Control');

            $this->assertStringContainsString('no-cache', $cache, $path);
            $this->assertStringNotContainsString('max-age', $cache, $path);
        }
    }

    public function test_theme_files_are_revalidated_by_a_rule_in_their_own_folder(): void
    {
        $themes = (string) file_get_contents(public_path('themes/.htaccess'));

        $this->assertStringContainsString('<IfModule mod_headers.c>', $themes);
        $this->assertStringContainsString('Header set Cache-Control "no-cache"', $themes);
    }

    public function test_the_cache_rules_for_theme_files_are_not_added_to_the_root_htaccess(): void
    {
        // public/.htaccess на сервере дописывает cPanel (блок «PHP handler»), и любая наша правка в нём ломает git pull
        // там. Правила для /themes живут в public/themes/.htaccess.
        $root = (string) file_get_contents(public_path('.htaccess'));

        $this->assertStringNotContainsString('REVALIDATE_FILE', $root);
        $this->assertStringContainsString('"/(uploads|build)/" LONG_LIVED_FILE', $root);
    }
}
