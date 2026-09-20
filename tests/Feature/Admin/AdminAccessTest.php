<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function adminRoutes(): array
    {
        return [
            ['admin.dashboard'],
            ['admin.posts.index'],
            ['admin.posts.create'],
            ['admin.opportunities.index'],
            ['admin.events.index'],
            ['admin.experts.index'],
            ['admin.tags.index'],
            ['admin.albums.index'],
            ['admin.videos.index'],
            ['admin.profiles.index'],
            ['admin.member-posts.index'],
            ['admin.statistics.index'],
            ['admin.cabinets.dashboard'],
            ['admin.cabinets.settings'],
            ['admin.subscribers.index'],
            ['admin.settings.edit'],
        ];
    }

    #[DataProvider('adminRoutes')]
    public function test_guest_is_redirected_to_login(string $route): void
    {
        $this->get(route($route))->assertRedirect(route('login'));
    }

    #[DataProvider('adminRoutes')]
    public function test_authenticated_non_admin_is_forbidden(string $route): void
    {
        config(['admin.email' => 'someone-else@example.test']);

        $this->actingAs(User::factory()->create(['email' => 'intruder@example.test']))
            ->get(route($route))
            ->assertForbidden();
    }

    #[DataProvider('adminRoutes')]
    public function test_admin_can_open_every_section(string $route): void
    {
        $this->actingAsAdmin()->get(route($route))->assertOk();
    }
}
