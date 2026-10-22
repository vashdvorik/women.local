<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BotHelp;
use App\Support\BotMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Инструкция AI-помощника в боте («Настройки кабинетов → Помощник бота»): единственный источник его знаний. */
class BotHelpController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (BotMessages::LOCALES as $locale) {
            $rules["texts.$locale"] = ['nullable', 'string', 'max:'.BotHelp::MAX_LENGTH];
        }

        $data = $request->validate($rules, [], [
            'texts.ru' => __('инструкция (русский)'),
            'texts.en' => __('инструкция (English)'),
            'texts.ro' => __('инструкция (Română)'),
        ]);

        BotHelp::save($data['texts'] ?? []);

        return redirect()->route('admin.cabinets.settings', ['tab' => 'bot-help'])
            ->with('success', __('Инструкция помощника сохранена.'));
    }

    /** «Вернуть исходный»: убирает правку, и помощник снова читает текст из resources/data/bot_help.php. */
    public function reset(Request $request): RedirectResponse
    {
        $locale = $request->string('locale')->toString();

        if (in_array($locale, BotMessages::LOCALES, true)) {
            $custom = [];
            foreach (BotMessages::LOCALES as $code) {
                $custom[$code] = $code === $locale ? '' : (BotHelp::isCustomized($code) ? BotHelp::template($code) : '');
            }
            BotHelp::save($custom);
        }

        return redirect()->route('admin.cabinets.settings', ['tab' => 'bot-help'])
            ->with('success', __('Исходная инструкция возвращена.'));
    }
}
