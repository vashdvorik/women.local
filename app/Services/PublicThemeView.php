<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\View\View;

final class PublicThemeView
{
    public static function render(string $page, array $data = []): View
    {
        $theme = SiteSetting::landingTheme();

        return view("themes.public.{$theme}.{$page}", array_merge([
            'landingTheme' => $theme,
            'publicTheme' => $theme,
        ], $data));
    }
}
