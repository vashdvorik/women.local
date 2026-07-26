<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicMembersTest extends TestCase
{
    public function test_public_members_catalog_renders_twelve_miro_profiles(): void
    {
        $response = $this->get(route('members'));

        $response->assertOk()
            ->assertSee('miro-members-page', false)
            ->assertSee('miro-public-member', false)
            ->assertSee('Carolina Bugaiyan')
            ->assertSee('Irina Pleshkova')
            ->assertSee('Register to make the connection');

        $this->assertSame(12, substr_count($response->getContent(), 'class="miro-public-member"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }
}
