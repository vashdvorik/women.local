<?php

namespace Database\Seeders\Concerns;

use App\Actions\StoreUploadedImage;
use Illuminate\Http\UploadedFile;

/**
 * Импорт картинок, лежавших в папке темы (`public/themes/public/miro/images`), в
 * `public/uploads` через тот же конвейер, что и загрузка в админке: кадрирование по
 * слоту → WebP. После импорта картинкой владеет админка; исходник в теме остаётся
 * резервной копией.
 */
trait ImportsThemeImages
{
    /**
     * @return string|null относительный путь вида «2026/09/abc123.webp» или null, если файла нет
     */
    protected function importThemeImage(?string $relativePath, string $slot): ?string
    {
        if (blank($relativePath)) {
            return null;
        }

        $absolute = public_path('themes/public/miro/images/'.ltrim($relativePath, '/'));

        if (! is_file($absolute)) {
            $this->command?->warn("Картинка не найдена, пропущена: {$relativePath}");

            return null;
        }

        // Последний аргумент true — «тестовый» режим: разрешает не-HTTP-загруженный файл.
        $file = new UploadedFile($absolute, basename($absolute), mime_content_type($absolute) ?: null, null, true);

        return app(StoreUploadedImage::class)->handle($file, $slot);
    }
}
