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

    public function test_each_catalogue_contains_its_default_theme_and_the_choice_is_stored(): void
    {
        $this->assertArrayHasKey(SiteSetting::DEFAULT_LANDING_THEME, SiteSetting::LANDING_THEMES);
        $this->assertArrayHasKey(SiteSetting::DEFAULT_ACCOUNT_THEME, SiteSetting::ACCOUNT_THEMES);
        $this->assertSame('miro', SiteSetting::DEFAULT_LANDING_THEME);
        $this->assertSame('fortuntwo', SiteSetting::DEFAULT_ACCOUNT_THEME);

        // Без записи в базе действуют темы по умолчанию.
        $this->assertSame('miro', SiteSetting::landingTheme());
        $this->assertSame('fortuntwo', SiteSetting::accountTheme());

        foreach (array_keys(SiteSetting::LANDING_THEMES) as $theme) {
            SiteSetting::setLandingTheme($theme);
            $this->assertSame($theme, SiteSetting::landingTheme());
        }

        foreach (array_keys(SiteSetting::ACCOUNT_THEMES) as $theme) {
            SiteSetting::setAccountTheme($theme);
            $this->assertSame($theme, SiteSetting::accountTheme());
        }
    }

    public function test_an_unknown_or_removed_theme_falls_back_to_the_default(): void
    {
        // Темы fortun, classic и другие удалены, но в базе боевого сайта их имя могло остаться.
        foreach (['not-a-theme', 'classic', 'fortun', 'platform', ''] as $name) {
            Cache::forget(SiteSetting::LANDING_THEME_KEY);
            Cache::forget(SiteSetting::ACCOUNT_THEME_KEY);
            SiteSetting::query()->delete();
            SiteSetting::create(['key' => SiteSetting::LANDING_THEME_KEY, 'value' => ['theme' => $name]]);
            SiteSetting::create(['key' => SiteSetting::ACCOUNT_THEME_KEY, 'value' => ['theme' => $name]]);

            $this->assertSame(SiteSetting::DEFAULT_LANDING_THEME, SiteSetting::landingTheme(), "сайт, база: «{$name}»");
            $this->assertSame(SiteSetting::DEFAULT_ACCOUNT_THEME, SiteSetting::accountTheme(), "кабинет, база: «{$name}»");
        }

        // Имя удалённой темы осталось в кеше сервера с прошлого запуска: оно тоже не должно дойти до шаблонов.
        Cache::forever(SiteSetting::LANDING_THEME_KEY, 'platform');
        Cache::forever(SiteSetting::ACCOUNT_THEME_KEY, 'classic');

        $this->assertSame(SiteSetting::DEFAULT_LANDING_THEME, SiteSetting::landingTheme());
        $this->assertSame(SiteSetting::DEFAULT_ACCOUNT_THEME, SiteSetting::accountTheme());

        // Сохранить несуществующую тему нельзя: запишется тема по умолчанию.
        SiteSetting::setLandingTheme('platform');
        SiteSetting::setAccountTheme('classic');

        $this->assertSame(['theme' => 'miro'], SiteSetting::where('key', SiteSetting::LANDING_THEME_KEY)->firstOrFail()->value);
        $this->assertSame(['theme' => 'fortuntwo'], SiteSetting::where('key', SiteSetting::ACCOUNT_THEME_KEY)->firstOrFail()->value);
    }

    public function test_pages_of_the_removed_themes_do_not_break_when_the_old_theme_name_is_still_stored(): void
    {
        Cache::forever(SiteSetting::LANDING_THEME_KEY, 'fortun');
        Cache::forever(SiteSetting::ACCOUNT_THEME_KEY, 'miro');

        $this->get('/')->assertOk()->assertSee('themes/public/miro/', false);
        $this->get(route('account.login'))->assertOk();
    }

    public function test_landing_theme_applies_to_login_instead_of_account_theme(): void
    {
        SiteSetting::setLandingTheme('miro');
        SiteSetting::setAccountTheme('fortuntwo');

        $this->get(route('account.login'))
            ->assertOk()
            ->assertSee('public-theme-miro', false)
            // Название уже написано заголовком, поэтому логотипа на странице входа нет: иначе оно читалось бы дважды.
            ->assertSee('<h1', false)
            ->assertSee(__('account.brand'))
            ->assertDontSee('/images/brand/logo', false)
            ->assertDontSee('account-login-logo', false)
            ->assertDontSee('account-theme-fortuntwo', false);
    }

    public function test_miro_theme_is_available_and_renders_as_landing_variant(): void
    {
        $this->assertArrayHasKey('miro', SiteSetting::LANDING_THEMES);

        SiteSetting::setLandingTheme('miro');

        $this->get('/')
            ->assertOk()
            ->assertSee('miro-page')
            ->assertSee('bannerhero.webp')
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
