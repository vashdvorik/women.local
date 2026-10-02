<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Блок «Файл (PDF)» на публичной странице: ссылка на скачивание, название и размер.
 *
 * Файл лежит в `public/uploads/files/ГГГГ/ММ/`, а эта папка не хранится в git, поэтому файл
 * может пропасть (очистили папку, свежий клон). Ссылка на несуществующий файл посетителю не
 * нужна, поэтому в таком случае блок просто не выводится. Размер берётся с диска, а не из
 * данных блока: пришедшему из формы числу доверять нечего.
 */
class FileBlock
{
    /** Подписи кнопки по языкам. */
    private const DOWNLOAD = ['ru' => 'Скачать', 'en' => 'Download', 'ro' => 'Descarcă'];

    /** Название по умолчанию, если редактор его не задал и имя файла неизвестно. */
    private const FALLBACK_TITLE = ['ru' => 'Документ', 'en' => 'Document', 'ro' => 'Document'];

    /** Единицы размера по языкам: килобайт, мегабайт. */
    private const UNITS = ['ru' => ['КБ', 'МБ'], 'en' => ['KB', 'MB'], 'ro' => ['KB', 'MB']];

    /**
     * @param  array<string, mixed>  $data  data блока `file`
     * @return array{url: string, title: string, size: ?string, download: string, button: string}|null
     *         null — файл не загружен или его нет на диске
     */
    public static function info(array $data, string $lang = Locales::PRIMARY): ?array
    {
        $path = Blocks::normalizeFilePath($data['path'] ?? null);
        $disk = Storage::disk('uploads');

        if ($path === null || ! $disk->exists($path)) {
            return null;
        }

        $lang = array_key_exists($lang, self::DOWNLOAD) ? $lang : Locales::PRIMARY;

        $title = Blocks::fileTitle($data['title'] ?? null);

        if ($title === '') {
            $name = Blocks::fileName($data['name'] ?? null);
            $title = $name !== null
                ? preg_replace('/\.pdf$/i', '', $name)
                : self::FALLBACK_TITLE[$lang];
        }

        return [
            'url' => '/uploads/'.$path,
            'title' => $title,
            // Имя, под которым браузер сохранит файл: на диске он лежит под случайным именем.
            'download' => (Str::slug($title) ?: 'document').'.pdf',
            'size' => self::sizeLabel($disk->size($path), $lang),
            'button' => self::DOWNLOAD[$lang],
        ];
    }

    /** «340 КБ», «2,4 МБ» / «2.4 MB»; десятичный разделитель — по языку. */
    public static function sizeLabel(int $bytes, string $lang = Locales::PRIMARY): ?string
    {
        if ($bytes <= 0) {
            return null;
        }

        [$kb, $mb] = self::UNITS[$lang] ?? self::UNITS[Locales::PRIMARY];

        if ($bytes < 1024 * 1024) {
            return max(1, (int) round($bytes / 1024)).' '.$kb;
        }

        $value = number_format($bytes / (1024 * 1024), 1, $lang === 'en' ? '.' : ',', '');

        return $value.' '.$mb;
    }
}
