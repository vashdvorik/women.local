<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicEventsTest extends TestCase
{
    public function test_public_events_page_renders_miro_events_catalog(): void
    {
        $response = $this->get(route('events'));

        $response->assertOk()
            ->assertSee('miro-events-page', false)
            ->assertSee('miro-event-card', false)
            ->assertSee('White Noise — where creativity meets entrepreneurship')
            ->assertSee('Women’s business forum: growth, digitalisation, sustainability')
            ->assertSee('Don’t miss the next opportunity');

        $this->assertSame(9, substr_count($response->getContent(), 'class="miro-event-card"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }
}
