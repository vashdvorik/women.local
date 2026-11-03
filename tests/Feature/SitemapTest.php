<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\SiteOpportunity;
use App\Support\PublicCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /sitemap.xml: только открытые страницы публичного сайта; /robots.txt закрывает кабинет и админку и указывает на карту.
 */
class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PublicCache::flush();
    }

    public function test_sitemap_lists_sections_and_published_materials_only(): void
    {
        SiteOpportunity::create(['slug' => 'open-grant', 'status' => 'published', 'published_at' => now()->subDay(), 'show_on_site' => true]);
        SiteOpportunity::create(['slug' => 'draft-grant', 'status' => 'draft', 'show_on_site' => true]);
        SiteOpportunity::create(['slug' => 'cabinet-only', 'status' => 'published', 'published_at' => now()->subDay(), 'show_on_site' => false]);
        Project::create(['slug' => 'hidden-project', 'is_published' => false]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $xml = $response->getContent();
        $this->assertStringContainsString('<loc>'.url('/events').'</loc>', $xml);
        $this->assertStringContainsString('<loc>'.url('/opportunities/open-grant').'</loc>', $xml);
        $this->assertStringNotContainsString('draft-grant', $xml);
        $this->assertStringNotContainsString('cabinet-only', $xml);
        $this->assertStringNotContainsString('hidden-project', $xml);
        $this->assertStringNotContainsString('/app/account', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_robots_hides_private_areas_and_points_to_the_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $body = $response->getContent();
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /app/', $body);
        $this->assertStringContainsString('Sitemap: '.url('/sitemap.xml'), $body);
    }
}
