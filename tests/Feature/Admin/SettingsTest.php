<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function aiPayload(array $overrides = []): array
    {
        return array_merge([
            'gemini_base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'gemini_model' => 'gemini-embedding-001',
            'gemini_timeout' => 15,
            'gemini_api_key' => '',
            'openrouter_base_url' => 'https://openrouter.ai/api/v1',
            'openrouter_timeout' => 30,
            'openrouter_api_key' => '',
            'deepseek_base_url' => 'https://api.deepseek.com',
            'deepseek_model' => 'deepseek-chat',
            'deepseek_temperature' => 0.3,
            'deepseek_max_tokens' => 1024,
            'deepseek_timeout' => 30,
            'deepseek_api_key' => '',
            'embedding_provider' => 'gemini',
            'embedding_model' => 'gemini-embedding-001',
            'embedding_timeout' => 15,
            'search_min_score' => 0.55,
            'agent_provider' => 'deepseek',
            'agent_model' => 'deepseek-chat',
            'agent_temperature' => 0.3,
            'agent_max_tokens' => 1024,
            'agent_timeout' => 30,
        ], $overrides);
    }

    public function test_site_settings_page_has_only_site_tabs(): void
    {
        foreach (['images', 'theme'] as $tab) {
            $this->actingAsAdmin()
                ->get(route('admin.settings.edit', ['tab' => $tab]))
                ->assertOk()
                ->assertSee('Сжатие изображений')
                ->assertSee('Тема сайта')
                // Настройки кабинета участниц живут в другом лагере админки.
                ->assertDontSee('ИИ-провайдеры')
                ->assertDontSee('База знаний ассистента')
                ->assertDontSee('Тема кабинета участницы');
        }
    }

    public function test_cabinet_settings_page_has_only_cabinet_tabs(): void
    {
        foreach (['theme', 'ai', 'knowledge'] as $tab) {
            $this->actingAsAdmin()
                ->get(route('admin.cabinets.settings', ['tab' => $tab]))
                ->assertOk()
                ->assertSee('Тема кабинета')
                ->assertSee('ИИ-провайдеры')
                ->assertSee('База знаний ассистента')
                ->assertDontSee('Сжатие изображений')
                ->assertDontSee('Тема публичного сайта');
        }
    }

    public function test_unknown_tab_falls_back_to_the_first_one(): void
    {
        $this->actingAsAdmin()->get(route('admin.settings.edit', ['tab' => 'ai']))->assertOk()->assertSee('Максимальная длинная сторона');
        $this->actingAsAdmin()->get(route('admin.cabinets.settings', ['tab' => 'images']))->assertOk()->assertSee('Тема кабинета участницы');
    }

    public function test_image_settings_are_validated_and_saved(): void
    {
        $this->actingAsAdmin()
            ->put(route('admin.settings.update'), ['image_max_side' => 10, 'image_quality' => 200])
            ->assertSessionHasErrors(['image_max_side', 'image_quality']);

        $this->actingAsAdmin()
            ->put(route('admin.settings.update'), ['image_max_side' => 1600, 'image_quality' => 80])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame(1600, SiteSetting::read('image_max_side'));
        $this->assertSame(80, SiteSetting::read('image_quality'));
    }

    public function test_site_theme_is_saved_from_site_settings_and_rejects_unknown_values(): void
    {
        SiteSetting::setAccountTheme('classic');

        $this->actingAsAdmin()
            ->put(route('admin.settings.theme'), ['landing_theme' => 'nope'])
            ->assertSessionHasErrors('landing_theme');

        $this->actingAsAdmin()
            ->put(route('admin.settings.theme'), ['landing_theme' => 'fortun'])
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'theme']));

        $this->assertSame('fortun', SiteSetting::landingTheme());
        // Тема кабинета от этой формы не меняется, даже если её прислать.
        $this->actingAsAdmin()->put(route('admin.settings.theme'), ['landing_theme' => 'miro', 'account_theme' => 'fortun']);
        $this->assertSame('classic', SiteSetting::accountTheme());
    }

    public function test_cabinet_theme_is_saved_from_cabinet_settings_and_rejects_unknown_values(): void
    {
        SiteSetting::setLandingTheme('fortun');

        $this->actingAsAdmin()
            ->put(route('admin.cabinets.settings.theme'), ['account_theme' => 'nope'])
            ->assertSessionHasErrors('account_theme');

        $this->actingAsAdmin()
            ->put(route('admin.cabinets.settings.theme'), ['account_theme' => 'miro'])
            ->assertRedirect(route('admin.cabinets.settings', ['tab' => 'theme']));

        $this->assertSame('miro', SiteSetting::accountTheme());
        // Публичный сайт от этой формы не меняется.
        $this->assertSame('fortun', SiteSetting::landingTheme());
    }

    public function test_assistant_knowledge_is_saved(): void
    {
        $this->actingAsAdmin()
            ->put(route('admin.cabinets.settings.knowledge'), ['rules' => 'Будь вежлива', 'ru' => 'О платформе', 'en' => '', 'ro' => ''])
            ->assertRedirect(route('admin.cabinets.settings', ['tab' => 'knowledge']));

        $this->assertSame('Будь вежлива', SiteSetting::aiAssistantKnowledge()['rules']);
        $this->assertSame('О платформе', SiteSetting::aiAssistantKnowledge()['ru']);
    }

    public function test_ai_settings_are_saved_and_keys_are_stored_encrypted(): void
    {
        $this->actingAsAdmin()
            ->put(route('admin.cabinets.settings.ai.update'), $this->aiPayload([
                'deepseek_api_key' => 'sk-secret-deepseek',
                'agent_model' => 'deepseek-reasoner',
            ]))
            ->assertRedirect(route('admin.cabinets.settings', ['tab' => 'ai']));

        $this->assertSame('sk-secret-deepseek', SiteSetting::deepSeekProviderApiKey());
        $this->assertSame('deepseek-reasoner', SiteSetting::agentFeatureConfig()['model']);

        $raw = SiteSetting::query()->where('key', SiteSetting::DEEPSEEK_PROVIDER_KEY)->first()->value;
        $this->assertStringNotContainsString('sk-secret-deepseek', json_encode($raw));

        // Пустое поле ключа оставляет сохранённый ключ.
        $this->actingAsAdmin()->put(route('admin.cabinets.settings.ai.update'), $this->aiPayload());
        $this->assertSame('sk-secret-deepseek', SiteSetting::deepSeekProviderApiKey());
    }

    public function test_saved_key_is_never_rendered_back(): void
    {
        SiteSetting::setDeepSeekProviderConfig([
            'base_url' => 'https://api.deepseek.com',
            'model' => 'deepseek-chat',
            'temperature' => 0.3,
            'max_tokens' => 1024,
            'timeout' => 30,
            'api_key' => 'sk-secret-deepseek',
        ]);

        $this->actingAsAdmin()
            ->get(route('admin.cabinets.settings', ['tab' => 'ai']))
            ->assertOk()
            ->assertDontSee('sk-secret-deepseek')
            ->assertSee('Ключ сохранён');
    }

    public function test_invalid_ai_settings_are_rejected(): void
    {
        $this->actingAsAdmin()
            ->put(route('admin.cabinets.settings.ai.update'), $this->aiPayload(['embedding_provider' => 'unknown', 'search_min_score' => 4]))
            ->assertSessionHasErrors(['embedding_provider', 'search_min_score']);
    }

    public function test_connection_test_reports_success_without_saving(): void
    {
        Http::fake([
            'api.deepseek.com/*' => Http::response(['choices' => [['message' => ['content' => 'OK']]]]),
        ]);

        $this->actingAsAdmin()
            ->postJson(route('admin.cabinets.settings.ai.test', 'deepseek'), $this->aiPayload(['deepseek_api_key' => 'sk-test']))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertNull(SiteSetting::deepSeekProviderApiKey());
    }

    public function test_connection_test_reports_missing_key_and_http_failure(): void
    {
        $this->actingAsAdmin()
            ->postJson(route('admin.cabinets.settings.ai.test', 'deepseek'), $this->aiPayload())
            ->assertOk()
            ->assertJson(['ok' => false]);

        Http::fake(['openrouter.ai/*' => Http::response([], 401)]);

        $this->actingAsAdmin()
            ->postJson(route('admin.cabinets.settings.ai.test', 'openrouter'), $this->aiPayload(['openrouter_api_key' => 'sk-bad']))
            ->assertOk()
            ->assertJson(['ok' => false]);
    }

    public function test_connection_test_rejects_unknown_provider_and_invalid_input(): void
    {
        $this->actingAsAdmin()->postJson(route('admin.cabinets.settings.ai.test', 'unknown'), [])->assertNotFound();

        $this->actingAsAdmin()
            ->postJson(route('admin.cabinets.settings.ai.test', 'gemini'), $this->aiPayload(['gemini_base_url' => 'not-a-url']))
            ->assertOk()
            ->assertJson(['ok' => false]);
    }

    public function test_settings_endpoints_are_closed_to_non_admins(): void
    {
        config(['admin.email' => 'someone-else@example.test']);
        $user = \App\Models\User::factory()->create(['email' => 'intruder@example.test']);

        $this->actingAs($user)->put(route('admin.cabinets.settings.ai.update'), $this->aiPayload())->assertForbidden();
        $this->actingAs($user)->postJson(route('admin.cabinets.settings.ai.test', 'gemini'), $this->aiPayload())->assertForbidden();
    }
}
