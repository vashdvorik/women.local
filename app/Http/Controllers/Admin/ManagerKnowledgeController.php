<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Знания AI-менеджера кабинета: общие правила и текст о платформе на трёх языках («Настройки кабинетов → AI-менеджер»). */
class ManagerKnowledgeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rules' => ['nullable', 'string', 'max:20000'],
            'ru' => ['nullable', 'string', 'max:30000'],
            'en' => ['nullable', 'string', 'max:30000'],
            'ro' => ['nullable', 'string', 'max:30000'],
        ], [], [
            'rules' => __('общие правила'),
            'ru' => __('информация (русский)'),
            'en' => __('информация (English)'),
            'ro' => __('информация (Română)'),
        ]);

        SiteSetting::setManagerKnowledge($data);

        return redirect()->route('admin.cabinets.settings', ['tab' => 'manager'])
            ->with('success', __('Знания AI-менеджера сохранены.'));
    }
}
