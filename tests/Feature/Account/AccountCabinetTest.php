<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Models\SiteSetting;
use App\Jobs\ComputeUserEmbedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AccountCabinetTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Middleware: unauthenticated
    // ──────────────────────────────────────────────

    /** @dataProvider protectedRouteProvider */
    #[\PHPUnit\Framework\Attributes\DataProvider('protectedRouteProvider')]
    public function test_protected_route_redirects_when_no_session(string $method, string $route): void
    {
        $this->$method(route($route))
            ->assertRedirect(route('account.login'));
    }

    public static function protectedRouteProvider(): array
    {
        return [
            'dashboard'  => ['get', 'account.index'],
            'profile'    => ['get', 'account.profile'],
            'matches'    => ['get', 'account.matches'],
            'people'     => ['get', 'account.people'],
            'knowledge'  => ['get', 'account.knowledge'],
        ];
    }

    public function test_protected_route_redirects_when_session_has_no_valid_user(): void
    {
        // Session exists but user is pending (not approved)
        $user = BotUser::factory()->pending()->create();

        $this->withSession($this->participantSession($user))
            ->get(route('account.index'))
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error');
    }

    // ──────────────────────────────────────────────
    // Authenticated access
    // ──────────────────────────────────────────────

    /** Build a valid session array for the given approved user. */
    private function sessionFor(BotUser $user): array
    {
        return [
            ...$this->participantSession($user),
'_account_expires'    => now()->addDays(7)->timestamp,
        ];
    }

    private function actingAsApproved(): BotUser
    {
        $user = BotUser::factory()->active()->create();
        session($this->participantSession($user));
        session(['_account_expires' => now()->addDays(7)->timestamp]);

        return $user;
    }

    public function test_dashboard_returns_200_when_authenticated(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->get(route('account.index'))
            ->assertOk();
    }

    /** @dataProvider accountThemeProvider */
    #[\PHPUnit\Framework\Attributes\DataProvider('accountThemeProvider')]
    public function test_sidebar_has_the_same_logo_as_the_public_header_in_every_theme(string $theme): void
    {
        SiteSetting::setAccountTheme($theme);
        $user = BotUser::factory()->active()->create();

        $html = $this->withSession($this->sessionFor($user))
            ->get(route('account.index'))
            ->assertOk()
            ->getContent();

        // Основной логотип платформы, тот же рисунок, что в шапке публичного сайта, в цветном исполнении: боковое меню светлое, тёмной плашки нет.
        $this->assertStringContainsString('themes/public/miro/images/brand/logo-platform.webp', $html);
        $this->assertStringContainsString('class="account-brand-logo', $html);
        $this->assertStringNotContainsString('logo-platform-light', $html);
        $this->assertStringNotContainsString('background:#21140F', $html);
        $this->assertStringContainsString('href="'.route('account.index').'" aria-label="Women\'s hub"', $html);
    }

    /** @dataProvider accountThemeProvider */
    #[\PHPUnit\Framework\Attributes\DataProvider('accountThemeProvider')]
    public function test_profile_page_has_no_delete_profile_block_in_any_theme(string $theme): void
    {
        SiteSetting::setAccountTheme($theme);
        $user = BotUser::factory()->active()->create();

        $html = $this->withSession($this->sessionFor($user))
            ->get(route('account.profile'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('delete-profile-form', $html);
        $this->assertStringNotContainsString('name="_method" value="DELETE"', $html);
        $this->assertStringNotContainsString('miro-danger-zone', $html);
        $this->assertStringNotContainsString('Удалить профиль', $html);
        $this->assertStringNotContainsString('Удалить мой профиль', $html);
    }

    public static function accountThemeProvider(): array
    {
        // Все темы из каталога: добавленная позже тема попадёт под эти проверки сама.
        $themes = array_keys(SiteSetting::ACCOUNT_THEMES);

        return array_combine($themes, array_map(fn (string $theme): array => [$theme], $themes));
    }

    public function test_all_cabinet_get_pages_render_when_authenticated(): void
    {
        $user = BotUser::factory()->active()->create();
        $person = BotUser::factory()->active()->create();

        $pages = [
            ['account.index'],
            ['account.profile'],
            ['account.profile.edit'],
            ['account.matches'],
            ['account.people'],
            ['account.people.show', $person],
            ['account.search'],
            ['account.knowledge'],
            ['account.opportunities.index'],
            ['account.opportunities.create'],
        ];

        foreach ($pages as $page) {
            $route = array_shift($page);

            $this->withSession($this->sessionFor($user))
                ->get(route($route, $page))
                ->assertOk();
        }
    }

    public function test_profile_page_returns_200_when_authenticated(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->get(route('account.profile'))
            ->assertOk();
    }

    public function test_person_profile_renders_telegram_username_value(): void
    {
        $user = BotUser::factory()->active()->create();
        $person = BotUser::factory()->active()->create([
            'full_name' => 'Test Member',
            'telegram_username' => 'test_member',
        ]);

        $this->withSession($this->sessionFor($user))
            ->get(route('account.people.show', $person))
            ->assertOk()
            ->assertSee('@test_member')
            ->assertDontSee('{{ $person->telegram_username }}', false);
    }

    public function test_profile_update_saves_data(): void
    {
        Bus::fake([ComputeUserEmbedding::class]);

        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->post(route('account.profile.update'), [
                'full_name'   => 'Иван Иванов',
                'description' => 'Предприниматель',
                'expectation' => 'Партнёрство',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bot_users', [
            'id'          => $user->id,
            'full_name'   => 'Иван Иванов',
            'description' => 'Предприниматель',
            'expectation' => 'Партнёрство',
        ]);

        Bus::assertDispatched(ComputeUserEmbedding::class);
    }

    public function test_profile_update_validates_required_full_name(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->post(route('account.profile.update'), ['full_name' => ''])
            ->assertSessionHasErrors('full_name');
    }

    public function test_profile_update_validates_max_lengths(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->post(route('account.profile.update'), [
                'full_name'   => str_repeat('a', 121),
                'description' => str_repeat('a', 1001),
                'expectation' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors(['full_name', 'description', 'expectation']);
    }

    // ──────────────────────────────────────────────
    // People page
    // ──────────────────────────────────────────────

    public function test_people_page_shows_approved_members(): void
    {
        $currentUser = BotUser::factory()->active()->create();
        $other1      = BotUser::factory()->active()->create();
        $other2      = BotUser::factory()->active()->create();
        $pending     = BotUser::factory()->pending()->create();

        $response = $this->withSession($this->sessionFor($currentUser))
            ->get(route('account.people'));

        $response->assertOk()
            ->assertSee('miro-directory-page', false)
            ->assertSee('miro-directory-card__tags', false)
            ->assertDontSee('/app/account/people/' . $other1->id, false)
            ->assertViewHas('people', function ($people) use ($other1, $other2, $currentUser, $pending) {
                $ids = $people->pluck('id');

                return $ids->contains($other1->id)
                    && $ids->contains($other2->id)
                    && ! $ids->contains($currentUser->id)     // current user excluded
                    && ! $ids->contains($pending->id);        // pending excluded
            });
    }

    // ──────────────────────────────────────────────
    // Logout
    // ──────────────────────────────────────────────

    public function test_logout_destroys_session_and_redirects_to_home(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->post(route('account.logout'))
            ->assertRedirect(route('account.login'));

        $this->assertGuest('participant');
    }

    // ──────────────────────────────────────────────
    // Matches + knowledge placeholders
    // ──────────────────────────────────────────────

    public function test_matches_page_returns_200(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->get(route('account.matches'))
            ->assertOk();
    }

    public function test_knowledge_page_returns_200(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->get(route('account.knowledge'))
            ->assertOk();
    }

    // ──────────────────────────────────────────────
    // Logout: session cleared, redirect to /
    // ──────────────────────────────────────────────

    public function test_logout_clears_session_and_redirects_to_root(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->sessionFor($user))
            ->post(route('account.logout'))
            ->assertRedirect(route('account.login'));

        // Both session keys must be removed
        $this->assertGuest('participant');
    }

    public function test_logout_blocks_subsequent_protected_requests(): void
    {
        $user = BotUser::factory()->active()->create();

        // Log out
        $this->withSession($this->sessionFor($user))
            ->post(route('account.logout'));

        // Subsequent request without session must redirect to login
        $this->get(route('account.index'))
            ->assertRedirect(route('account.login'));
    }

    public function test_logout_without_session_returns_redirect(): void
    {
        // Calling logout when not authenticated: middleware intercepts → redirects to login
        $this->post(route('account.logout'))
            ->assertRedirect(route('account.login'));
    }
}
