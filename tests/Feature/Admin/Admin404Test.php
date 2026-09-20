<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Admin404Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_custom_404_screen(): void
    {
        $this->actingAsAdmin()
            ->get('/admin/nowhere')
            ->assertNotFound()
            ->assertSee('Похоже, этот адрес больше не существует.');
    }
}
