<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Сжатое фото целиком и рамка, из которых получен готовый кадр картинки на сайте (App\Actions\StoreUploadedImage).
 * Нужна, чтобы кадр можно было изменить после загрузки. Подробности — в миграции create_image_sources_table.
 */
class ImageSource extends Model
{
    protected $fillable = [
        'path',
        'source_path',
        'slot',
        'source_width',
        'source_height',
        'crop_x',
        'crop_y',
        'crop_width',
        'crop_height',
    ];

    protected function casts(): array
    {
        return [
            'source_width' => 'integer',
            'source_height' => 'integer',
            'crop_x' => 'integer',
            'crop_y' => 'integer',
            'crop_width' => 'integer',
            'crop_height' => 'integer',
        ];
    }

    /** Рамка в пикселях сохранённого фото. @return array{x: int, y: int, width: int, height: int} */
    public function cropRect(): array
    {
        return [
            'x' => $this->crop_x,
            'y' => $this->crop_y,
            'width' => $this->crop_width,
            'height' => $this->crop_height,
        ];
    }
}
