<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicAboutTest extends TestCase
{
    public function test_public_about_page_renders_miro_content(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk()
            ->assertSee('miro-about-page', false)
            ->assertSee('A place where women grow business together')
            ->assertSee('From profile to real collaboration')
            ->assertSee('500+');

        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }
}
