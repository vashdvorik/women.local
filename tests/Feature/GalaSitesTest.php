<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Сайты гала-премии (gala2024, gala2025, gala2026 в корне проекта) собраны скриптом tools/gala-sites/build.mjs и выкладываются
 * на хостинг папками. Тест следит, чтобы сборка была целой: страницы на трёх языках, адреса, картинки и страницы журнала на месте.
 */
class GalaSitesTest extends TestCase
{
    private const SITES = [
        2024 => ['domain' => 'gala2024.innovation.md', 'past' => true, 'pages' => 84, 'photos' => 9, 'video' => true],
        2025 => ['domain' => 'gala2025.innovation.md', 'past' => true, 'pages' => 74, 'photos' => 17, 'video' => true],
        2026 => ['domain' => 'gala2026.innovation.md', 'past' => false, 'pages' => 0, 'photos' => 0, 'video' => true],
    ];

    private function dir(int $year): string
    {
        return base_path("gala{$year}");
    }

    private function html(int $year): string
    {
        return (string) file_get_contents($this->dir($year).'/index.html');
    }

    public function test_every_site_has_its_files_and_the_right_domain(): void
    {
        foreach (self::SITES as $year => $site) {
            foreach (['index.html', 'index.php', '.htaccess', 'robots.txt', 'sitemap.xml', 'README.txt', 'assets/style.css', 'assets/script.js', 'assets/img/og.jpg'] as $file) {
                $this->assertFileExists($this->dir($year).'/'.$file, "gala{$year}/{$file}");
            }

            $html = $this->html($year);
            $this->assertStringContainsString('<link rel="canonical" href="https://'.$site['domain'].'/">', $html);
            $this->assertStringContainsString('https://'.$site['domain'].'/assets/img/og.jpg', $html);
            $this->assertStringContainsString("Woman of the Year {$year}", $html, 'английский текст');
            $this->assertStringContainsString("Femeia Anului {$year}", $html, 'румынский текст');
            $this->assertStringContainsString("Женщина Года {$year}", $html);
            $this->assertStringContainsString('https://'.$site['domain'].'/sitemap.xml', (string) file_get_contents($this->dir($year).'/robots.txt'));
        }
    }

    public function test_every_local_reference_of_the_pages_points_to_an_existing_file(): void
    {
        foreach (array_keys(self::SITES) as $year) {
            preg_match_all('#(?:src|href|data-shot|data-pages)="(assets/[^"?]+)#', $this->html($year), $m);
            $this->assertNotEmpty($m[1]);

            foreach (array_unique($m[1]) as $path) {
                $this->assertFileExists($this->dir($year).'/'.$path, "gala{$year}: {$path}");
            }
        }
    }

    public function test_past_sites_have_photos_video_where_there_is_one_and_a_flip_magazine(): void
    {
        foreach (self::SITES as $year => $site) {
            if (! $site['past']) {
                continue;
            }

            $html = $this->html($year);
            $this->assertSame($site['photos'], substr_count($html, 'data-shot="'), "фото {$year}");
            $this->assertSame($site['video'], str_contains($html, 'data-video="'), "видео {$year}");

            $this->assertMatchesRegularExpression('/data-pages="([^"]+)"/', $html);
            preg_match('/data-pages="([^"]+)"/', $html, $pages);
            $list = json_decode(html_entity_decode($pages[1]), true);
            $this->assertCount($site['pages'], $list, "страниц журнала {$year}");
            foreach ($list as $page) {
                $this->assertFileExists($this->dir($year).'/'.$page);
            }

            foreach (['assets/flip.js', 'assets/page-flip.browser.js'] as $file) {
                $this->assertFileExists($this->dir($year).'/'.$file);
            }
        }
    }

    public function test_the_2026_site_is_an_announcement_that_leads_to_the_registration_and_the_past_ceremonies(): void
    {
        $html = $this->html(2026);

        $this->assertStringContainsString('https://womenhub.innovation.md/app/account/register', $html);
        $this->assertStringContainsString('https://womenhub.innovation.md/gala/regulations', $html);
        $this->assertStringContainsString('https://gala2025.innovation.md/', $html);
        $this->assertStringContainsString('https://gala2024.innovation.md/', $html);
        $this->assertStringContainsString('data-video="AcesUVGyAkU"', $html);
        // У анонса нет журнала и лайтбокса: церемония ещё не прошла
        $this->assertStringNotContainsString('data-flip', $html);
        $this->assertFileDoesNotExist($this->dir(2026).'/assets/flip.js');
    }

    public function test_the_domains_match_the_menu_of_the_main_site(): void
    {
        foreach (self::SITES as $year => $site) {
            $this->assertSame('https://'.$site['domain'].'/', config("site.gala.{$year}"));
        }
    }
}
