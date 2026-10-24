<?php

namespace App\Support;

/**
 * Адрес файла из public/ с меткой версии: `…/css/navigation.css?v=1760000000`. Метка — время изменения файла, поэтому
 * после правки стиля, скрипта или картинки темы адрес меняется, и браузер берёт новую версию, а не показывает файл из
 * кеша (имя файла у них постоянное). Файл не изменился — адрес прежний, и кеш работает. Файла нет (картинка из
 * внешнего источника, опечатка в пути) — адрес без метки, как у обычного asset().
 *
 * В шаблонах вызывается через asset_v() (app/Support/helpers.php). Скрипты и стили админки (public/build) версионируются
 * хешем в имени файла самим Vite, фото из админки — уникальным именем.
 */
class Assets
{
    /** @var array<string, string> уже посчитанные адреса за запрос: файл не опрашивается дважды */
    private static array $urls = [];

    public static function url(string $path): string
    {
        return self::$urls[$path] ??= self::build($path);
    }

    /** Забывает посчитанное (тестам, которые меняют файл на лету). */
    public static function flush(): void
    {
        self::$urls = [];
    }

    /** Тема, чьи картинки общие для всех тем сайта: у остальных тем своей папки images/ нет, пока там нечего заменять. */
    private const SHARED_IMAGES_THEME = 'miro';

    private static function build(string $path): string
    {
        $path = self::sharedImage($path);
        $url = asset($path);
        $file = public_path(ltrim($path, '/'));

        // Файл правили, пока работает процесс (долгоживущий воркер, тест): без этого PHP отдал бы старое время из кеша.
        clearstatcache(true, $file);

        if (! is_file($file) || ($time = @filemtime($file)) === false) {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').'v='.$time;
    }

    /**
     * Картинка (или папка картинок) другой темы сайта: если у темы своего файла нет, берётся такой же из темы miro.
     * Так вторая тема не копирует сотни мегабайт фото, но любой файл можно заменить, положив его в свою папку images/.
     */
    private static function sharedImage(string $path): string
    {
        if (! preg_match('#^(/?themes/public/)([^/]+)/(images(?:/.*)?)$#', $path, $m) || $m[2] === self::SHARED_IMAGES_THEME) {
            return $path;
        }

        if (file_exists(public_path(ltrim($path, '/')))) {
            return $path;
        }

        $shared = $m[1].self::SHARED_IMAGES_THEME.'/'.$m[3];

        return file_exists(public_path(ltrim($shared, '/'))) ? $shared : $path;
    }
}
