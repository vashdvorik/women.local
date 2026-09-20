<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\ImageSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * «Настройки сайта» — лагерь «Внешний сайт»: сжатие загружаемых изображений и тема публичного
 * сайта. Настройки кабинета участниц (тема кабинета, ИИ, база знаний) живут отдельно:
 * см. CabinetSettingController.
 */
class SettingController extends Controller
{
    private const TABS = ['images', 'theme'];

    public function edit(Request $request): View
    {
        $tab = $request->string('tab', 'images')->toString();

        return view('admin.settings.edit', [
            'tab' => in_array($tab, self::TABS, true) ? $tab : 'images',

            'maxSide' => ImageSettings::maxSide(),
            'quality' => ImageSettings::quality(),

            'landingThemes' => SiteSetting::LANDING_THEMES,
            'landingTheme' => SiteSetting::landingTheme(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image_max_side' => ['required', 'integer', 'between:'.ImageSettings::MAX_SIDE_MIN.','.ImageSettings::MAX_SIDE_MAX],
            'image_quality' => ['required', 'integer', 'between:'.ImageSettings::QUALITY_MIN.','.ImageSettings::QUALITY_MAX],
        ], [], [
            'image_max_side' => 'максимальная длинная сторона',
            'image_quality' => 'качество',
        ]);

        SiteSetting::write('image_max_side', (int) $validated['image_max_side']);
        SiteSetting::write('image_quality', (int) $validated['image_quality']);

        return redirect()->route('admin.settings.edit')->with('success', 'Настройки сохранены.');
    }
}
