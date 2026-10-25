<?php

namespace Database\Seeders;

/**
 * Ещё девять видео YouTube для страницы «Видео»: инфосессии, тренинги и арт-выставка «Белый шум» (часть роликов —
 * вертикальные shorts). Перечень и названия на трёх языках — в database/seeders/data/videos_creative.php.
 *
 * Работает так же, как VideoArchiveSeeder (новые ролики встают в конец очереди, уже заведённые и удалённые в админке
 * не возвращаются и не дублируются), но запоминает свои ролики отдельно (site_settings: videos_creative):
 *
 *   php artisan db:seed --class=VideoCreativeSeeder --force
 */
class VideoCreativeSeeder extends VideoArchiveSeeder
{
    public const MARKER_KEY = 'videos_creative';

    protected const DATA_FILE = 'videos_creative.php';
}
