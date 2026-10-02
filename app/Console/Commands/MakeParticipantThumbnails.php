<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Participants;
use Illuminate\Console\Command;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class MakeParticipantThumbnails extends Command
{
    protected $signature = 'participants:thumbnails
                            {--force : Пересоздать и те миниатюры, что уже есть}';

    protected $description = 'Миниатюры аватаров участниц (WebP, квадрат) для карусели на главной';

    public function handle(): int
    {
        $base = public_path('themes/public/miro/images');
        $size = Participants::THUMB_SIZE;
        $manager = ImageManager::usingDriver(new Driver());

        $made = 0;
        $kept = 0;
        $failed = 0;

        $photos = array_unique(array_filter(array_column(Participants::all(), 'photo')));

        foreach ($photos as $photo) {
            $source = $base.'/'.$photo;
            $target = $base.'/'.Participants::thumb($photo);

            if (! $this->option('force') && is_file($target)) {
                $kept++;

                continue;
            }

            if (! is_file($source)) {
                $this->error("Нет исходного фото: {$photo}");
                $failed++;

                continue;
            }

            if (! is_dir(dirname($target))) {
                mkdir(dirname($target), 0775, true);
            }

            // Кадр режется от верхнего края: на портретах лицо сверху, как и в CSS (object-position: center top).
            $manager->decode($source)
                ->cover($size, $size, 'top')
                ->encode(new WebpEncoder(quality: 80))
                ->save($target);

            $made++;
        }

        $this->info("Миниатюры: создано {$made}, уже были {$kept}".($failed ? ", ошибок {$failed}" : '').'.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
