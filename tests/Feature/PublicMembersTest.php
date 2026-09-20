<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicMembersTest extends TestCase
{
    public function test_public_members_catalog_renders_the_participants_grid(): void
    {
        $response = $this->get(route('members'));

        $response->assertOk()
            ->assertSee('miro-members-page', false)
            ->assertSee('miro-participants-grid', false)
            ->assertSee('Register to make the connection');

        // Первые 24 карточки видны сразу, остальные подгружаются кнопкой «Показать ещё».
        $this->assertSame(24, substr_count($response->getContent(), 'class="miro-participant-card"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-participants-loadmore"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }
}
