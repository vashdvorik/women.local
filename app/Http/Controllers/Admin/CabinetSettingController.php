<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * «Настройки кабинетов» — лагерь «Кабинеты участниц»: тема кабинета, ИИ-провайдеры (поиск,
 * подбор контактов и AI-помощник в кабинете) и база знаний помощника. Каждая вкладка — своя
 * форма со своим обработчиком: ThemeSettingController, AiSettingController,
 * AssistantKnowledgeController.
 */
class CabinetSettingController extends Controller
{
    private const TABS = ['theme', 'ai', 'knowledge'];

    public function edit(Request $request): View
    {
        $tab = $request->string('tab', 'theme')->toString();

        $gemini = SiteSetting::geminiEmbeddingConfig();
        $openRouter = SiteSetting::openRouterProviderConfig();
        $deepSeek = SiteSetting::deepSeekProviderConfig();
        $embedding = SiteSetting::embeddingFeatureConfig();
        $agent = SiteSetting::agentFeatureConfig();

        $geminiKeySet = $gemini['api_key_configured'] || filled(config('ai.gemini.key'));

        return view('admin.cabinets.settings', [
            'tab' => in_array($tab, self::TABS, true) ? $tab : 'theme',

            'accountThemes' => SiteSetting::ACCOUNT_THEMES,
            'accountTheme' => SiteSetting::accountTheme(),

            'gemini' => $gemini,
            'geminiKeySet' => $geminiKeySet,
            'openRouter' => $openRouter,
            'deepSeek' => $deepSeek,
            'embedding' => $embedding,
            'searchMinScore' => SiteSetting::searchMinScore(),
            'agent' => $agent,
            'embeddingKeySet' => $embedding['provider'] === 'gemini' ? $geminiKeySet : $openRouter['api_key_configured'],
            'agentKeySet' => $agent['provider'] === 'openrouter' ? $openRouter['api_key_configured'] : $deepSeek['api_key_configured'],

            'knowledge' => SiteSetting::aiAssistantKnowledge(),
        ]);
    }
}
