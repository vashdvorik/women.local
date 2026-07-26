<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPartnersTest extends TestCase
{
    public function test_public_partners_page_renders_all_partner_logos(): void
    {
        $response = $this->get(route('partners'));

        $response->assertOk()
            ->assertSee('miro-partners-page', false)
            ->assertSee('Platform partners')
            ->assertSee('Agency for Innovation and Development')
            ->assertSee('UN Women')
            ->assertSee('https://innovation.md/', false)
            ->assertSee('https://moldova.unwomen.org/', false)
            ->assertSee('images/brand/logo.webp', false)
            ->assertSee('images/brand/favicon.png', false);

        $this->assertSame(10, substr_count($response->getContent(), 'class="miro-partner-card miro-partner-card--'));
        $this->assertSame(10, substr_count($response->getContent(), 'class="miro-partner-link"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }
}
