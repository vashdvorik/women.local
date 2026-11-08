<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\ImageSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * «Настройки сайта» — лагерь «Внешний сайт»: сжатие загружаемых изображений и тема публичного
 * сайта. Настройки кабинета участниц (тема кабинета, AI, база знаний) живут отдельно:
 * см. CabinetSettingController.
 */
class SettingController extends Controller
{
    private const TABS = ['images', 'theme', 'contacts'];

    public function edit(Request $request): View
    {
        $tab = $request->string('tab', 'images')->toString();

        return view('admin.settings.edit', [
            'tab' => in_array($tab, self::TABS, true) ? $tab : 'images',

            'maxSide' => ImageSettings::maxSide(),
            'quality' => ImageSettings::quality(),

            'landingThemes' => SiteSetting::LANDING_THEMES,
            'landingTheme' => SiteSetting::landingTheme(),

            'contactEmail' => SiteSetting::contactEmail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image_max_side' => ['required', 'integer', 'between:'.ImageSettings::MAX_SIDE_MIN.','.ImageSettings::MAX_SIDE_MAX],
            'image_quality' => ['required', 'integer', 'between:'.ImageSettings::QUALITY_MIN.','.ImageSettings::QUALITY_MAX],
        ], [], [
            'image_max_side' => __('максимальная длинная сторона'),
            'image_quality' => __('качество'),
        ]);

        SiteSetting::write('image_max_side', (int) $validated['image_max_side']);
        SiteSetting::write('image_quality', (int) $validated['image_quality']);

        return redirect()->route('admin.settings.edit')->with('success', __('Настройки сохранены.'));
    }

    /** Почта команды: куда уходят письма из формы «Контакты» (и что показано на сайте). */
    public function updateContacts(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_email' => ['required', 'string', 'email', 'max:191'],
        ], [], ['contact_email' => __('почта команды')]);

        SiteSetting::setContactEmail($validated['contact_email']);

        return redirect()->route('admin.settings.edit', ['tab' => 'contacts'])
            ->with('success', __('Почта сохранена: письма из формы теперь идут на :email.', ['email' => SiteSetting::contactEmail()]));
    }

    /** Тестовое письмо на сохранённую почту: показывает, принял ли письмо почтовый сервер, или причину отказа. */
    public function testContacts(): RedirectResponse
    {
        $to = SiteSetting::contactEmail();
        $back = redirect()->route('admin.settings.edit', ['tab' => 'contacts']);

        if ($to === '') {
            return $back->with('error', __('Сначала сохраните почту команды.'));
        }

        try {
            Mail::raw(__('Проверка почты сайта: если вы читаете это письмо, отправка работает.'), fn ($message) => $message->to($to)->subject(__('Проверка почты сайта')));
        } catch (Throwable $e) {
            report($e);

            return $back->with('error', __('Письмо не отправилось: :reason', ['reason' => mb_substr($e->getMessage(), 0, 300)]));
        }

        return $back->with('success', __('Письмо передано почтовому серверу и отправлено на :email. Проверьте почту и папку «Спам».', ['email' => $to]));
    }
}
