<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Параметры сжатия. Применяются только к новым загрузкам — массового
 * пересчёта нет (AGENTS.md §14, §18).
 */
class ImageSettings
{
    public const MAX_SIDE_MIN = 320;
    public const MAX_SIDE_MAX = 5000;
    public const MAX_SIDE_DEFAULT = 2400;

    public const QUALITY_MIN = 20;
    public const QUALITY_MAX = 100;
    public const QUALITY_DEFAULT = 60;

    public static function maxSide(): int
    {
        return (int) SiteSetting::read('image_max_side', self::MAX_SIDE_DEFAULT);
    }

    public static function quality(): int
    {
        return (int) SiteSetting::read('image_quality', self::QUALITY_DEFAULT);
    }
}
