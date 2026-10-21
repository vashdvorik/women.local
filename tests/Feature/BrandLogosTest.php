<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Набор логотипов от заказчика (dizayn/newlogo): основной горизонтальный (W4W + Women's Hub + слоган), надпись
 * «Women's Hub» и монограмма W4W. Исходники были на белом фоне, поэтому на сайте лежат прозрачные WebP, обрезанные по
 * рисунку, в двух исполнениях: цветное — для светлых мест, светлое — для тёмных шапки и подвала.
 */
class BrandLogosTest extends TestCase
{
    use RefreshDatabase;

    private const BRAND = 'themes/public/miro/images/brand/';

    /** Рисунок => допустимые ширина/высота обрезанного файла: горизонтальный и надпись ≈ 4.3–4.6:1, монограмма ≈ 2:1. */
    private const RATIOS = [
        'platform' => [4.0, 4.8],
        'wordmark' => [4.2, 4.9],
        'mark' => [1.8, 2.2],
    ];

    public function test_every_logo_variant_is_a_trimmed_transparent_webp_with_the_expected_proportions(): void
    {
        foreach (self::RATIOS as $name => [$min, $max]) {
            foreach (["logo-$name.webp", "logo-$name-light.webp"] as $file) {
                $path = public_path(self::BRAND.$file);
                $this->assertFileExists($path, $file);

                [$width, $height, $type] = getimagesize($path);
                $this->assertSame(IMAGETYPE_WEBP, $type, $file);
                $this->assertGreaterThanOrEqual(800, $width, "$file: хватает для экранов с высокой плотностью");
                $this->assertGreaterThan($min, $width / $height, "$file: обрезан по рисунку, без полей");
                $this->assertLessThan($max, $width / $height, "$file: обрезан по рисунку, без полей");

                // Прозрачность: углы пустые, белого фона исходника нет.
                $image = imagecreatefromstring((string) file_get_contents($path));
                foreach ([[0, 0], [$width - 1, 0], [0, $height - 1], [$width - 1, $height - 1]] as [$x, $y]) {
                    $this->assertSame(127, (imagecolorat($image, $x, $y) >> 24) & 127, "$file: угол ($x,$y) прозрачный");
                }
            }
        }
    }

    public function test_the_light_variants_read_on_the_dark_header_colour(): void
    {
        foreach (array_keys(self::RATIOS) as $name) {
            $colour = $this->meanLuminance(public_path(self::BRAND."logo-$name.webp"));
            $light = $this->meanLuminance(public_path(self::BRAND."logo-$name-light.webp"));

            $this->assertGreaterThan(150, $light, "$name: светлый вариант заметно светлее фона шапки (#21140F)");
            $this->assertGreaterThan($colour + 50, $light, "$name: светлый вариант светлее цветного");
        }
    }

    public function test_the_icons_are_square_png_and_the_ico_has_three_sizes(): void
    {
        foreach (['favicon-w4w.png' => 256, 'apple-touch-icon.png' => 180] as $file => $size) {
            [$width, $height, $type] = getimagesize(public_path(self::BRAND.$file));
            $this->assertSame([$size, $size, IMAGETYPE_PNG], [$width, $height, $type], $file);
        }
        $this->assertFileExists(public_path('themes/account/fortuntwo/images/brand/favicon-w4w.png'));

        $ico = (string) file_get_contents(public_path('favicon.ico'));
        $this->assertSame([0, 1, 3], array_values(unpack('v3', substr($ico, 0, 6))), 'favicon.ico: 16, 32 и 48 px');
    }

    public function test_the_public_header_and_footer_use_the_light_platform_logo(): void
    {
        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, self::BRAND.'logo-platform-light.webp'), 'шапка и подвал');
        $this->assertStringContainsString(self::BRAND.'favicon-w4w.png', $html);
        $this->assertStringContainsString(self::BRAND.'apple-touch-icon.png', $html);
    }

    public function test_every_public_page_template_points_at_the_new_icons(): void
    {
        foreach (glob(resource_path('views/themes/public/miro/*.blade.php')) as $template) {
            $source = (string) file_get_contents($template);
            if (! str_contains($source, 'rel="icon"')) {
                continue;
            }

            $this->assertStringContainsString('brand/favicon-w4w.png', $source, basename($template));
            $this->assertStringContainsString('rel="apple-touch-icon"', $source, basename($template));
        }
    }

    public function test_nothing_refers_to_the_replaced_logo_files(): void
    {
        $stale = ['logo-white.webp', 'logo-dark.webp', 'logo-ink.webp', 'brand/logo.png', 'brand/logo.webp', 'brand/favicon.png'];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            $source = (string) file_get_contents($file->getPathname());
            foreach ($stale as $name) {
                $this->assertStringNotContainsString($name, $source, $file->getFilename());
            }
        }

        foreach ($stale as $name) {
            $this->assertFileDoesNotExist(public_path(self::BRAND.basename($name)), $name);
        }
    }

    /** Средняя яркость непрозрачных пикселей (0–255). */
    private function meanLuminance(string $path): float
    {
        $image = imagecreatefromstring((string) file_get_contents($path));
        $width = imagesx($image);
        $height = imagesy($image);
        $sum = 0.0;
        $count = 0;

        for ($y = 0; $y < $height; $y += 3) {
            for ($x = 0; $x < $width; $x += 3) {
                $color = imagecolorat($image, $x, $y);
                if ((($color >> 24) & 127) > 20) {
                    continue; // почти прозрачный пиксель
                }
                $sum += 0.299 * (($color >> 16) & 255) + 0.587 * (($color >> 8) & 255) + 0.114 * ($color & 255);
                $count++;
            }
        }

        return $count > 0 ? $sum / $count : 0.0;
    }
}
