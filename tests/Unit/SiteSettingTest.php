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
        SiteSetting::setLandingTheme('platform');
        SiteSetting::setAccountTheme('dark');

        $this->assertSame('platform', SiteSetting::landingTheme());
        $this->assertSame('dark', SiteSetting::accountTheme());

        SiteSetting::setLandingTheme('miro');

        $this->assertSame('miro', SiteSetting::landingTheme());
        $this->assertSame('dark', SiteSetting::accountTheme());
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

    public function test_miro_account_theme_is_available_and_applies_to_login(): void
    {
        $this->assertArrayHasKey('miro', SiteSetting::ACCOUNT_THEMES);

        SiteSetting::setAccountTheme('miro');

        $this->get(route('account.login'))
            ->assertOk()
            ->assertSee('account-theme-miro', false);
    }

    public function test_miro_theme_is_available_and_renders_as_landing_variant(): void
    {
        $this->assertArrayHasKey('miro', SiteSetting::LANDING_THEMES);

        SiteSetting::setLandingTheme('miro');

        $this->get('/')
            ->assertOk()
            ->assertSee('miro-page')
            ->assertSee('hero-community.webp')
            ->assertSee('ONLINE PLATFORM')
            ->assertDontSee('<div class="miro-board"', false);
    }
}
