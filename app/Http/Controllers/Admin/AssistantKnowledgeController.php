<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** База знаний AI-помощника (раньше — Filament AiAssistantKnowledge). */
class AssistantKnowledgeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rules' => ['nullable', 'string', 'max:20000'],
            'ru' => ['nullable', 'string', 'max:30000'],
            'en' => ['nullable', 'string', 'max:30000'],
            'ro' => ['nullable', 'string', 'max:30000'],
        ], [], [
            'rules' => 'общие правила',
            'ru' => 'информация (русский)',
            'en' => 'информация (English)',
            'ro' => 'информация (Română)',
        ]);

        SiteSetting::setAiAssistantKnowledge($data);

        return redirect()->route('admin.cabinets.settings', ['tab' => 'knowledge'])
            ->with('success', 'База знаний сохранена.');
    }
}
