<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicThemeRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cache::forget(SiteSetting::LANDING_THEME_KEY);

        parent::tearDown();
    }

    public function test_every_public_page_resolves_for_each_landing_theme(): void
    {
        $pages = ['/', '/about', '/members', '/events', '/partners', '/contact'];

        foreach (array_keys(SiteSetting::LANDING_THEMES) as $theme) {
            SiteSetting::setLandingTheme($theme);

            foreach ($pages as $page) {
                $response = $this->get($page);

                $response->assertOk();
                $response->assertSee("themes/public/{$theme}/", false);

                if ($theme === 'platform' && $page === '/') {
                    $response->assertSee('hero-gradient', false);
                    $response->assertSee('landing-docs/hero.jpg', false);
                }
            }
        }
    }
}
