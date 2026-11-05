<?php

/**
 * Готовит картинки для сайтов гала-премии (gala2024, gala2025, gala2026) из материалов основного сайта:
 *  - фотоальбомы public/themes/public/miro/images/albums/gala-premiya-2024|2025 (фото церемоний);
 *  - страницы каталогов «Женский журнал 2024» и «Женский журнал 2025» из public/uploads (по адресу страницы каталога);
 *  - логотипы платформы.
 * Картинки уменьшаются, чтобы папки сайтов весили в разы меньше исходников (сайты лежат в git и выкладываются целиком).
 * Запуск из корня проекта: php tools/gala-sites/prepare-media.php
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$root = realpath(__DIR__.'/../..');

/** Масштабирует WebP/JPEG до $maxW по ширине (без увеличения) и сохраняет как WebP. */
function resizeTo(string $from, string $to, int $maxW, int $quality, ?int $maxH = null): array
{
    $src = @imagecreatefromwebp($from) ?: @imagecreatefromjpeg($from) ?: @imagecreatefrompng($from);
    if (! $src) {
        throw new RuntimeException("Не открыть: {$from}");
    }
    [$w, $h] = [imagesx($src), imagesy($src)];
    $scale = min(1, $maxW / $w, $maxH ? $maxH / $h : 1);
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));
    $dst = imagecreatetruecolor($nw, $nh);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    @mkdir(dirname($to), 0777, true);
    imagewebp($dst, $to, $quality);

    return [$nw, $nh, filesize($to)];
}

/** Обложка для соцсетей: 1200x630, обрезка по центру, JPEG. */
function ogImage(string $from, string $to): void
{
    $src = imagecreatefromwebp($from);
    [$w, $h] = [imagesx($src), imagesy($src)];
    $target = 1200 / 630;
    if ($w / $h > $target) {
        $cw = (int) round($h * $target);
        $cx = (int) (($w - $cw) / 2);
        $cy = 0;
        $ch = $h;
    } else {
        $cw = $w;
        $ch = (int) round($w / $target);
        $cx = 0;
        $cy = (int) (($h - $ch) / 2.4);
    }
    $dst = imagecreatetruecolor(1200, 630);
    imagecopyresampled($dst, $src, 0, 0, $cx, $cy, 1200, 630, $cw, $ch);
    @mkdir(dirname($to), 0777, true);
    imagejpeg($dst, $to, 82);
}

$report = [];

// ---------------------------------------------------------------- фото церемоний
foreach ([2024, 2025] as $year) {
    $dir = "{$root}/public/themes/public/miro/images/albums/gala-premiya-{$year}";
    $files = glob("{$dir}/*.webp");
    sort($files);
    $n = 0;
    foreach ($files as $file) {
        $name = basename($file);
        resizeTo($file, "{$root}/gala{$year}/assets/img/photos/{$name}", 1600, 76);
        [,, $thumb] = resizeTo($file, "{$root}/gala{$year}/assets/img/thumbs/{$name}", 760, 72);
        $n++;
    }
    $report[] = "фото {$year}: {$n}";
}

// ---------------------------------------------------------------- страницы каталогов
$catalogs = [2024 => 'zhenskiy-zhurnal-2024', 2025 => 'zhenskiy-zhurnal-2025'];
foreach ($catalogs as $year => $slug) {
    $catalog = App\Models\Catalog::where('slug', $slug)->firstOrFail();
    $n = 0;
    $bytes = 0;
    foreach ($catalog->pagePaths() as $i => $path) {
        $num = str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
        [, , $size] = resizeTo("{$root}/public/uploads/{$path}", "{$root}/gala{$year}/assets/catalog/{$num}.webp", 860, 70);
        $bytes += $size;
        $n++;
    }
    // Обложка каталога для карточек на сайте 2026 и для превью
    resizeTo("{$root}/public/uploads/".$catalog->pagePaths()[0], "{$root}/gala2026/assets/img/cover-{$year}.webp", 520, 74);
    $report[] = "каталог {$year}: {$n} страниц, ".round($bytes / 1048576, 1).' МБ';
}

// ---------------------------------------------------------------- сайт 2026: полоса фото прошлых церемоний
foreach ([2025 => ['05', '01', '09', '08', '14', '17'], 2024 => ['07', '04', '02', '08']] as $year => $list) {
    foreach ($list as $name) {
        resizeTo("{$root}/public/themes/public/miro/images/albums/gala-premiya-{$year}/{$name}.webp", "{$root}/gala2026/assets/img/past-{$year}-{$name}.webp", 760, 72);
    }
}

// ---------------------------------------------------------------- карточки «другие церемонии» на сайтах прошлых лет
$cards = [
    2024 => "{$root}/public/themes/public/miro/images/albums/gala-premiya-2024/07.webp",
    2025 => "{$root}/public/themes/public/miro/images/albums/gala-premiya-2025/05.webp",
    2026 => "{$root}/public/themes/public/miro/images/albums/gala-premiya-2025/01.webp",
];
foreach ($cards as $cardYear => $source) {
    foreach ([2024, 2025] as $siteYear) {
        if ($siteYear !== $cardYear) {
            resizeTo($source, "{$root}/gala{$siteYear}/assets/img/card-{$cardYear}.webp", 900, 72);
        }
    }
}

// ---------------------------------------------------------------- картинки для соцсетей и логотипы
ogImage("{$root}/public/themes/public/miro/images/albums/gala-premiya-2024/07.webp", "{$root}/gala2024/assets/img/og.jpg");
ogImage("{$root}/public/themes/public/miro/images/albums/gala-premiya-2025/05.webp", "{$root}/gala2025/assets/img/og.jpg");
ogImage("{$root}/public/themes/public/miro/images/albums/gala-premiya-2025/01.webp", "{$root}/gala2026/assets/img/og.jpg");

foreach ([2024, 2025, 2026] as $year) {
    foreach (['favicon-w4w.png', 'apple-touch-icon.png'] as $logo) {
        @mkdir("{$root}/gala{$year}/assets/img", 0777, true);
        copy("{$root}/public/themes/public/miro/images/brand/{$logo}", "{$root}/gala{$year}/assets/img/{$logo}");
    }
}

echo implode("\n", $report), "\nГотово.\n";
