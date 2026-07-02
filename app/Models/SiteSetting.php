<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    public const LANDING_THEME_KEY = 'landing_theme';

    public const LANDING_THEMES = [
        'classic' => 'Классическая зелёная',
        'warm'    => 'Тёплая гранатовая',
        'dark'    => 'Премиальная тёмная',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function landingTheme(): string
    {
        return Cache::rememberForever(self::LANDING_THEME_KEY, function (): string {
            if (! Schema::hasTable('site_settings')) {
                return 'classic';
            }

            $setting = self::where('key', self::LANDING_THEME_KEY)->first();
            $value   = $setting?->value ?? [];
            $theme   = is_array($value) ? ($value['theme'] ?? 'classic') : 'classic';

            return array_key_exists($theme, self::LANDING_THEMES) ? $theme : 'classic';
        });
    }

    public static function setLandingTheme(string $theme): void
    {
        if (! array_key_exists($theme, self::LANDING_THEMES)) {
            $theme = 'classic';
        }

        self::updateOrCreate(
            ['key' => self::LANDING_THEME_KEY],
            ['value' => ['theme' => $theme]]
        );

        Cache::forget(self::LANDING_THEME_KEY);
    }
}
