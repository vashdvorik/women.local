<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Темы независимы: смена оформления публичного сайта не меняет кабинет участницы, и наоборот.
 * Поэтому у каждой темы свой обработчик и своё место в админке: тема сайта — «Настройки сайта»
 * (лагерь «Внешний сайт»), тема кабинета — «Настройки кабинетов» (лагерь «Кабинеты участниц»).
 */
class ThemeSettingController extends Controller
{
    public function site(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'landing_theme' => ['required', Rule::in(array_keys(SiteSetting::LANDING_THEMES))],
        ], [], ['landing_theme' => 'тема сайта']);

        SiteSetting::setLandingTheme($data['landing_theme']);

        return redirect()->route('admin.settings.edit', ['tab' => 'theme'])
            ->with('success', 'Тема сайта сохранена и уже применяется.');
    }

    public function cabinet(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_theme' => ['required', Rule::in(array_keys(SiteSetting::ACCOUNT_THEMES))],
        ], [], ['account_theme' => 'тема кабинета']);

        SiteSetting::setAccountTheme($data['account_theme']);

        return redirect()->route('admin.cabinets.settings', ['tab' => 'theme'])
            ->with('success', 'Тема кабинета сохранена и уже применяется.');
    }
}
