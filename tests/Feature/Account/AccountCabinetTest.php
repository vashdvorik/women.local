<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Models\LoginToken;
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

        $this->withSession(['account_telegram_id' => $user->telegram_id])
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
            'account_telegram_id' => $user->telegram_id,
            '_account_expires'    => now()->addDays(7)->timestamp,
        ];
    }

    private function actingAsApproved(): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        session(['account_telegram_id' => $user->telegram_id]);
        session(['_account_expires' => now()->addDays(7)->timestamp]);

        return $user;
    }

    public function test_dashboard_returns_200_when_authenticated(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->withSession($this->sessionFor($user))
            ->get(route('account.index'))
            ->assertOk();
    }

    public function test_profile_page_returns_200_when_authenticated(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->withSession($this->sessionFor($user))
            ->get(route('account.profile'))
            ->assertOk();
    }

    public function test_profile_update_saves_data(): void
    {
        Bus::fake([ComputeUserEmbedding::class]);

        $user = BotUser::factory()->approved()->create();

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
        $user = BotUser::factory()->approved()->create();

        $this->withSession($this->sessionFor($user))
            ->post(route('account.profile.update'), ['full_name' => ''])
            ->assertSessionHasErrors('full_name');
    }

    public function test_profile_update_validates_max_lengths(): void
    {
        $user = BotUser::factory()->approved()->create();

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
        $currentUser = BotUser::factory()->approved()->create();
        $other1      = BotUser::factory()->approved()->create();
        $other2      = BotUser::factory()->approved()->create();
        $pending     = BotUser::factory()->pending()->create();

        $response = $this->withSession($this->sessionFor($currentUser))
            ->get(route('account.people'));

        $response->assertOk()
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
        $user = BotUser::factory()->approved()->create();

        $this->withSession($this->sessionFor($user))
            ->post(route('account.logout'))
            ->assertRedirect(route('account.login', ['logout' => '1']));

        $this->assertNull(session('account_telegram_id'));
    }

    // ──────────────────────────────────────────────
    // Matches + knowledge placeholders
    // ──────────────────────────────────────────────

    public function test_matches_page_returns_200(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->withSession($this->sessionFor($user))
            ->get(route('account.matches'))
            ->assertOk();
    }

    public function test_knowledge_page_returns_200(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->withSession($this->sessionFor($user))
            ->get(route('account.knowledge'))
            ->assertOk();
    }

    // ──────────────────────────────────────────────
    // Logout: session cleared, redirect to /
    // ──────────────────────────────────────────────

    public function test_logout_clears_session_and_redirects_to_root(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->withSession($this->sessionFor($user))
            ->post(route('account.logout'))
            ->assertRedirect(route('account.login', ['logout' => '1']));

        // Both session keys must be removed
        $this->assertNull(session('account_telegram_id'));
        $this->assertNull(session('_account_expires'));
    }

    public function test_logout_blocks_subsequent_protected_requests(): void
    {
        $user = BotUser::factory()->approved()->create();

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
