<?php

namespace App\Actions;

use App\Support\AspectRatio;
use App\Support\ImageSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Путь каждого загруженного файла: уменьшение → кадрирование по центру →
 * сохранение в WebP. Оригинал не сохраняется. Формат выбран под ограничения
 * shared hosting и не меняется по инициативе агента (SETUP.md §7).
 *
 * Возвращается относительный путь вида «2026/09/abc123.webp» — именно он ложится
 * в базу, никогда не полный URL.
 *
 * Если передан прямоугольник `$crop` (из инструмента кадрирования в форме),
 * сначала вырезается ровно он, а не центр кадра — тогда увиденное в форме и
 * оказавшееся на сайте совпадают буквально (AGENTS.md §14).
 *
 * @param  ?array{x:int|string,y:int|string,width:int|string,height:int|string}  $crop
 */
class StoreUploadedImage
{
    public function handle(UploadedFile $file, string $slot, ?array $crop = null): string
    {
        [$targetWidth, $targetHeight] = AspectRatio::dimensions($slot);

        $maxSide = ImageSettings::maxSide();
        $quality = ImageSettings::quality();

        $manager = ImageManager::usingDriver(new Driver());

        $image = $manager->decode($file->getRealPath());

        if ($crop !== null) {
            $x = max(0, (int) $crop['x']);
            $y = max(0, (int) $crop['y']);
            $w = min((int) $crop['width'], $image->width() - $x);
            $h = min((int) $crop['height'], $image->height() - $y);

            if ($w > 0 && $h > 0) {
                $image->crop($w, $h, $x, $y);
            }
        }

        // Кадрирование по центру (`cover`) уже совпадает с выбранным
        // пользователем прямоугольником, если он был, — здесь только приведение
        // к точному размеру слота.
        $image->scaleDown(width: $maxSide, height: $maxSide)
            ->cover($targetWidth, $targetHeight);

        $binary = (string) $image->encode(new WebpEncoder(quality: $quality));

        $relativePath = now()->format('Y/m').'/'.Str::lower(Str::random(12)).'.webp';

        Storage::disk('uploads')->put($relativePath, $binary);

        return $relativePath;
    }
}
