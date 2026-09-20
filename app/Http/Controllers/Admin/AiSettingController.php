<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiSettingsRequest;
use App\Models\SiteSetting;
use App\Services\AiConnectionTester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * ИИ-провайдеры и назначение моделей функциям платформы (раньше — Filament AiProviderSettings).
 * Ключи хранятся зашифрованными (SiteSetting) и в панели больше не показываются.
 */
class AiSettingController extends Controller
{
    public function update(AiSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        SiteSetting::setGeminiEmbeddingConfig([
            'base_url' => rtrim($data['gemini_base_url'], '/'),
            'model' => trim($data['gemini_model']),
            'timeout' => (int) $data['gemini_timeout'],
            'api_key' => trim((string) ($data['gemini_api_key'] ?? '')),
        ]);

        SiteSetting::setOpenRouterProviderConfig([
            'base_url' => rtrim($data['openrouter_base_url'], '/'),
            'timeout' => (int) $data['openrouter_timeout'],
            'api_key' => trim((string) ($data['openrouter_api_key'] ?? '')),
        ]);

        SiteSetting::setDeepSeekProviderConfig([
            'base_url' => rtrim($data['deepseek_base_url'], '/'),
            'model' => trim($data['deepseek_model']),
            'temperature' => (float) $data['deepseek_temperature'],
            'max_tokens' => (int) $data['deepseek_max_tokens'],
            'timeout' => (int) $data['deepseek_timeout'],
            'api_key' => trim((string) ($data['deepseek_api_key'] ?? '')),
        ]);

        SiteSetting::setEmbeddingFeatureConfig([
            'provider' => $data['embedding_provider'],
            'model' => trim($data['embedding_model']),
            'timeout' => (int) $data['embedding_timeout'],
        ]);
        SiteSetting::setSearchMinScore((float) $data['search_min_score']);

        SiteSetting::setAgentFeatureConfig([
            'provider' => $data['agent_provider'],
            'model' => trim($data['agent_model']),
            'temperature' => (float) $data['agent_temperature'],
            'max_tokens' => (int) $data['agent_max_tokens'],
            'timeout' => (int) $data['agent_timeout'],
        ]);

        return redirect()->route('admin.cabinets.settings', ['tab' => 'ai'])
            ->with('success', 'Настройки ИИ сохранены.');
    }

    /** Пробный запрос к провайдеру по значениям формы; ничего не сохраняет. */
    public function test(Request $request, string $provider, AiConnectionTester $tester): JsonResponse
    {
        abort_unless(in_array($provider, ['gemini', 'openrouter', 'deepseek'], true), 404);

        $validator = Validator::make($request->all(), AiSettingsRequest::providerRules($provider));

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => $validator->errors()->first()]);
        }

        $v = $validator->validated();

        $result = match ($provider) {
            'gemini' => $tester->gemini([
                'base_url' => $v['gemini_base_url'],
                'model' => trim($v['gemini_model']),
                'timeout' => (int) $v['gemini_timeout'],
            ], $v['gemini_api_key'] ?? null),
            'deepseek' => $tester->deepSeek([
                'base_url' => $v['deepseek_base_url'],
                'model' => trim($v['deepseek_model']),
                'temperature' => (float) $v['deepseek_temperature'],
                'max_tokens' => (int) $v['deepseek_max_tokens'],
                'timeout' => (int) $v['deepseek_timeout'],
            ], $v['deepseek_api_key'] ?? null),
            'openrouter' => $tester->openRouter([
                'base_url' => $v['openrouter_base_url'],
                'timeout' => (int) $v['openrouter_timeout'],
            ], $v['openrouter_api_key'] ?? null, $request->input('embedding_provider') === 'openrouter' ? $request->input('embedding_model') : null),
        };

        return response()->json($result);
    }
}
