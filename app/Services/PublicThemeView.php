<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

final class PublicThemeView
{
    /** Тема, в которой есть все страницы: к ней откатывается любая тема без нужной страницы. */
    private const FALLBACK_THEME = 'miro';

    public static function render(string $page, array $data = []): View
    {
        $theme = SiteSetting::landingTheme();

        // Страницы, подключённые к админке (эксперты, события, публикации, фото, видео,
        // проекты, возможности), есть только в теме miro. В остальных темах такая страница
        // выводится целиком в оформлении miro, вместе с её стилями и картинками, а не
        // «наполовину»: поэтому и `publicTheme` для неё — miro.
        if (! ViewFactory::exists("themes.public.{$theme}.{$page}")) {
            $theme = self::FALLBACK_THEME;
        }

        return view("themes.public.{$theme}.{$page}", array_merge([
            'landingTheme' => $theme,
            'publicTheme' => $theme,
        ], $data));
    }
}
