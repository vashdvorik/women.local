<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cache::forget(SiteSetting::LANDING_THEME_KEY);
        Cache::forget(SiteSetting::ACCOUNT_THEME_KEY);

        parent::tearDown();
    }

    public function test_landing_and_account_themes_are_independent(): void
    {
        $this->assertArrayHasKey('fortun', SiteSetting::ACCOUNT_THEMES);

        SiteSetting::setLandingTheme('platform');
        SiteSetting::setAccountTheme('classic');

        $this->assertSame('platform', SiteSetting::landingTheme());
        $this->assertSame('classic', SiteSetting::accountTheme());

        SiteSetting::setLandingTheme('miro');

        $this->assertSame('miro', SiteSetting::landingTheme());
        $this->assertSame('classic', SiteSetting::accountTheme());
    }

    public function test_invalid_account_theme_falls_back_to_classic(): void
    {
        Cache::forget(SiteSetting::ACCOUNT_THEME_KEY);
        SiteSetting::create([
            'key' => SiteSetting::ACCOUNT_THEME_KEY,
            'value' => ['theme' => 'not-a-theme'],
        ]);

        $this->assertSame('classic', SiteSetting::accountTheme());
    }

    public function test_landing_theme_applies_to_login_instead_of_account_theme(): void
    {
        $this->assertArrayHasKey('fortun', SiteSetting::LANDING_THEMES);

        SiteSetting::setLandingTheme('fortun');
        SiteSetting::setAccountTheme('classic');

        $this->get(route('account.login'))
            ->assertOk()
            ->assertSee('public-theme-fortun', false)
            ->assertSee('/themes/public/fortun/images/brand/logo.png', false)
            ->assertDontSee('account-theme-classic', false);
    }

    public function test_miro_theme_is_available_and_renders_as_landing_variant(): void
    {
        $this->assertArrayHasKey('miro', SiteSetting::LANDING_THEMES);

        SiteSetting::setLandingTheme('miro');

        $this->get('/')
            ->assertOk()
            ->assertSee('miro-page')
            ->assertSee('bannerhero.webp')
            ->assertSee('PLATFORM<br>FOR WOMEN', false)
            ->assertDontSee('<div class="miro-board"', false);
    }

    public function test_gemini_embedding_settings_store_the_key_encrypted(): void
    {
        SiteSetting::setGeminiEmbeddingConfig([
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'model' => 'gemini-embedding-001',
            'timeout' => 20,
            'api_key' => 'gemini-test-key',
        ]);

        $stored = SiteSetting::query()->where('key', SiteSetting::GEMINI_EMBEDDING_KEY)->firstOrFail();

        $this->assertNotSame('gemini-test-key', $stored->value['api_key']);
        $this->assertSame('gemini-test-key', SiteSetting::geminiEmbeddingApiKey());
        $this->assertSame(20, SiteSetting::geminiEmbeddingConfig()['timeout']);
    }

    public function test_deepseek_settings_are_independent_from_gemini_settings(): void
    {
        SiteSetting::setDeepSeekProviderConfig([
            'base_url' => 'https://api.deepseek.com',
            'model' => 'deepseek-v4-flash',
            'temperature' => 0.2,
            'max_tokens' => 1200,
            'timeout' => 25,
            'api_key' => 'deepseek-test-key',
        ]);

        $this->assertSame('deepseek-test-key', SiteSetting::deepSeekProviderApiKey());
        $this->assertSame('deepseek-v4-flash', SiteSetting::deepSeekProviderConfig()['model']);
        $this->assertNull(SiteSetting::geminiEmbeddingApiKey());
    }

    public function test_openrouter_and_feature_models_are_stored_independently(): void
    {
        SiteSetting::setOpenRouterProviderConfig([
            'base_url' => 'https://openrouter.ai/api/v1',
            'timeout' => 40,
            'api_key' => 'openrouter-test-key',
        ]);
        SiteSetting::setEmbeddingFeatureConfig([
            'provider' => 'openrouter',
            'model' => 'baai/bge-m3',
            'timeout' => 35,
        ]);
        SiteSetting::setAgentFeatureConfig([
            'provider' => 'deepseek',
            'model' => 'deepseek-v4-flash',
            'temperature' => 0.2,
            'max_tokens' => 1200,
            'timeout' => 25,
        ]);

        $this->assertSame('openrouter-test-key', SiteSetting::openRouterProviderApiKey());
        $this->assertSame('openrouter', SiteSetting::embeddingFeatureConfig()['provider']);
        $this->assertSame('baai/bge-m3', SiteSetting::embeddingFeatureConfig()['model']);
        $this->assertSame('deepseek', SiteSetting::agentFeatureConfig()['provider']);
        $this->assertSame('deepseek-v4-flash', SiteSetting::agentFeatureConfig()['model']);
    }

    public function test_search_threshold_is_stored_as_an_ai_feature_setting(): void
    {
        SiteSetting::setSearchMinScore(0.55);

        $this->assertSame(0.55, SiteSetting::searchMinScore());
    }
}
