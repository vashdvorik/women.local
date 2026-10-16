<?php

namespace App\Actions;

use App\Models\ImageSource;
use App\Support\AspectRatio;
use App\Support\ImageSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\ImageManager;

/**
 * Путь каждого загруженного файла. Две копии:
 *
 *  1. Готовый кадр для сайта: вырезанная рамка → уменьшение → точный размер слота → WebP. Лежит в public/uploads, и
 *     только он попадает на публичные страницы. Режется из полного снимка (не из уменьшенной копии), поэтому
 *     качество кадра такое же, как если бы фото вообще не сохранялось.
 *  2. Сжатое фото целиком, без обрезки: длинная сторона не больше «Максимальной стороны» из настроек, WebP.
 *     Лежит на закрытом диске originals, рядом в таблице image_sources хранится рамка. По нему кадр можно
 *     изменить после загрузки (recrop()), не загружая фото заново. Исходный файл на 5–8 МБ не хранится.
 *
 * Формат (WebP) выбран под ограничения shared hosting и не меняется по инициативе агента (SETUP.md §7).
 *
 * Возвращается относительный путь готового кадра вида «2026/09/abc123.webp» — именно он ложится в базу,
 * никогда не полный URL.
 *
 * Если передан прямоугольник `$crop` (из инструмента кадрирования в форме), вырезается ровно он, а не центр
 * кадра: увиденное в форме и оказавшееся на сайте совпадают буквально (AGENTS.md §14). Координаты — в пикселях
 * загруженного файла.
 *
 * @param  ?array{x:int|string,y:int|string,width:int|string,height:int|string}  $crop
 */
class StoreUploadedImage
{
    /** Качество сжатого фото целиком. Выше, чем у кадра: из него кадр режется ещё раз, и потери не должны копиться. */
    public const SOURCE_QUALITY = 82;

    public function handle(UploadedFile $file, string $slot, ?array $crop = null): string
    {
        [$targetWidth, $targetHeight] = AspectRatio::dimensions($slot);
        $manager = ImageManager::usingDriver(new Driver());

        // Готовый кадр: из полного снимка, по рамке в координатах загруженного файла.
        $full = $manager->decode($file->getRealPath());
        $originalWidth = $full->width();
        $originalHeight = $full->height();
        $rect = $this->rect($crop, $originalWidth, $originalHeight, $targetWidth / $targetHeight);

        $path = $this->bake($full, $rect, $slot);
        unset($full);

        // Сжатое фото целиком и рамка в его координатах.
        $source = $manager->decode($file->getRealPath())->scaleDown(width: ImageSettings::maxSide(), height: ImageSettings::maxSide());
        $factor = $source->width() / $originalWidth;
        $sourceRect = $this->rect([
            'x' => $rect['x'] * $factor,
            'y' => $rect['y'] * $factor,
            'width' => $rect['width'] * $factor,
            'height' => $rect['height'] * $factor,
        ], $source->width(), $source->height(), $targetWidth / $targetHeight);

        $sourcePath = $this->newPath();
        Storage::disk('originals')->put($sourcePath, (string) $source->encode(new WebpEncoder(quality: self::SOURCE_QUALITY)));

        ImageSource::create([
            'path' => $path,
            'source_path' => $sourcePath,
            'slot' => $slot,
            'source_width' => $source->width(),
            'source_height' => $source->height(),
            'crop_x' => $sourceRect['x'],
            'crop_y' => $sourceRect['y'],
            'crop_width' => $sourceRect['width'],
            'crop_height' => $sourceRect['height'],
        ]);

        return $path;
    }

    /**
     * Новый кадр из уже сохранённого фото целиком. Рамка — в пикселях этого фото. Старый кадр и его запись остаются:
     * форма, где его заменили, могла не сохраниться, и прежняя картинка должна по-прежнему открываться и меняться.
     *
     * @param  array{x:int|string,y:int|string,width:int|string,height:int|string}  $crop
     * @return string путь нового кадра
     */
    public function recrop(ImageSource $source, array $crop): string
    {
        [$targetWidth, $targetHeight] = AspectRatio::dimensions($source->slot);
        $manager = ImageManager::usingDriver(new Driver());

        $image = $manager->decode(Storage::disk('originals')->get($source->source_path));
        $rect = $this->rect($crop, $image->width(), $image->height(), $targetWidth / $targetHeight);

        $path = $this->bake($image, $rect, $source->slot);

        ImageSource::create([
            'path' => $path,
            'source_path' => $source->source_path,
            'slot' => $source->slot,
            'source_width' => $source->source_width,
            'source_height' => $source->source_height,
            'crop_x' => $rect['x'],
            'crop_y' => $rect['y'],
            'crop_width' => $rect['width'],
            'crop_height' => $rect['height'],
        ]);

        return $path;
    }

    /**
     * Вырезает рамку, уменьшает, приводит к размеру слота и сохраняет WebP на публичный диск.
     * Рамка, чьи пропорции чуть отличаются от слота, подгоняется по центру (cover): кадр всегда ровно по слоту.
     *
     * @param  array{x:int,y:int,width:int,height:int}  $rect
     */
    private function bake(ImageInterface $image, array $rect, string $slot): string
    {
        [$targetWidth, $targetHeight] = AspectRatio::dimensions($slot);
        $maxSide = ImageSettings::maxSide();

        $image->crop($rect['width'], $rect['height'], $rect['x'], $rect['y'])
            ->scaleDown(width: $maxSide, height: $maxSide)
            ->cover($targetWidth, $targetHeight);

        $path = $this->newPath();
        Storage::disk('uploads')->put($path, (string) $image->encode(new WebpEncoder(quality: ImageSettings::quality())));

        return $path;
    }

    /**
     * Рамка внутри снимка: целые числа, не выходит за края. Без рамки — самая большая по пропорциям слота, по центру.
     *
     * @param  ?array<string, int|float|string>  $crop
     * @return array{x:int,y:int,width:int,height:int}
     */
    private function rect(?array $crop, int $imageWidth, int $imageHeight, float $ratio): array
    {
        if ($crop === null) {
            $width = min($imageWidth, (int) round($imageHeight * $ratio));
            $height = min($imageHeight, (int) round($width / $ratio));

            return [
                'x' => intdiv($imageWidth - $width, 2),
                'y' => intdiv($imageHeight - $height, 2),
                'width' => $width,
                'height' => $height,
            ];
        }

        $x = max(0, min((int) round((float) $crop['x']), $imageWidth - 1));
        $y = max(0, min((int) round((float) $crop['y']), $imageHeight - 1));

        return [
            'x' => $x,
            'y' => $y,
            'width' => max(1, min((int) round((float) $crop['width']), $imageWidth - $x)),
            'height' => max(1, min((int) round((float) $crop['height']), $imageHeight - $y)),
        ];
    }

    private function newPath(): string
    {
        return now()->format('Y/m').'/'.Str::lower(Str::random(12)).'.webp';
    }
}
