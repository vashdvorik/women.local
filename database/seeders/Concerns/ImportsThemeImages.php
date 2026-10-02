<?php

namespace Database\Seeders\Concerns;

use App\Actions\StoreUploadedImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Импорт картинок, лежавших в папке темы (`public/themes/public/miro/images`), в
 * `public/uploads` через тот же конвейер, что и загрузка в админке: кадрирование по
 * слоту → WebP. После импорта картинкой владеет админка; исходник в теме остаётся
 * резервной копией.
 *
 * Папка `public/uploads` не хранится в git, поэтому файлы из неё могут пропасть (очистили
 * папку, свежий клон с базой из резервной копии). Тогда в БД остаются пути без файлов, а на
 * сайте — «битые» картинки. `restoreThemeImage()` возвращает такие картинки из исходников темы.
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

    /**
     * Если запись ссылается на файл, которого нет на диске, заново импортирует исходник из
     * темы и обновляет путь. Запись без картинки (путь пуст) не трогается: редактор мог убрать
     * её сознательно. Если файл на месте, ничего не меняется.
     *
     * @return bool была ли картинка восстановлена
     */
    protected function restoreThemeImage(Model $model, string $column, ?string $themePath, string $slot): bool
    {
        $current = $model->{$column};

        if (blank($current) || Storage::disk('uploads')->exists($current)) {
            return false;
        }

        $restored = $this->importThemeImage($themePath, $slot);

        if ($restored === null) {
            return false;
        }

        $model->forceFill([$column => $restored])->save();

        return true;
    }
}
