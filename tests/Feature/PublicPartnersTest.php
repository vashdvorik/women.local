<?php

namespace Tests\Feature;

use App\Support\PartnerLogos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPartnersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_partners_page_renders_both_groups_of_logos_without_captions_or_links(): void
    {
        $response = $this->get(route('partners'));

        $response->assertOk()
            ->assertSee('miro-partners-page', false)
            ->assertSeeInOrder(['Международные партнёры', 'Партнёры и вдохновители'])
            ->assertDontSee('Platform coordinators')
            ->assertDontSee('Growth is easier with strong partners by your side')
            ->assertDontSee('miro-partner-link', false)
            ->assertDontSee('https://innovation.md/', false)
            ->assertSee('images/partners/home/intl-un-women.webp', false)
            ->assertSee('images/partners/home/p15-startup-moldova.webp', false)
            ->assertSee('images/brand/logo-platform-light.webp', false)
            ->assertSee('images/brand/favicon-w4w.png', false);

        $this->assertSame(20, substr_count($response->getContent(), 'class="miro-partner-card miro-partner-card--'));
        $this->assertSame(0, substr_count($response->getContent(), 'class="miro-partner-card__bottom"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }

    public function test_landing_shows_the_same_two_groups_of_logos(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSeeInOrder(['Международные партнёры', 'Партнёры и вдохновители']);
        $this->assertSame(20, substr_count($response->getContent(), 'class="miro-partner-chip"'));
    }

    public function test_every_logo_file_exists(): void
    {
        foreach (PartnerLogos::groups() as $group) {
            foreach ($group['logos'] as $logo) {
                $this->assertFileExists(public_path('themes/public/miro/images/partners/home/'.$logo['file'].'.webp'));
            }
        }
    }
}
