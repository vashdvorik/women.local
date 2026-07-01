<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Models\LoginToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAuthTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Login page
    // ──────────────────────────────────────────────

    public function test_login_page_returns_200(): void
    {
        $this->get(route('account.login'))->assertOk();
    }

    // ──────────────────────────────────────────────
    // Auth endpoint — error cases
    // ──────────────────────────────────────────────

    public function test_auth_without_token_redirects_to_login_with_error(): void
    {
        $this->get(route('account.auth'))
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error');
    }

    public function test_auth_with_nonexistent_token_redirects_to_login_with_error(): void
    {
        $this->get(route('account.auth') . '?token=' . str_repeat('a', 64))
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error');
    }

    public function test_auth_with_expired_token_redirects_to_login_with_error(): void
    {
        $user  = BotUser::factory()->approved()->create();
        $token = LoginToken::generateFor((int) $user->telegram_id);
        $token->update(['expires_at' => now()->subDay()]);

        $this->get(route('account.auth') . '?token=' . $token->token)
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error');
    }

    public function test_auth_with_valid_token_for_pending_user_redirects_to_login_with_error(): void
    {
        $user  = BotUser::factory()->pending()->create();
        $token = LoginToken::generateFor((int) $user->telegram_id);

        $this->get(route('account.auth') . '?token=' . $token->token)
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error');
    }

    public function test_auth_with_valid_token_for_rejected_user_redirects_to_login_with_error(): void
    {
        $user = BotUser::factory()->create(['status' => BotUser::STATUS_REJECTED]);
        $token = LoginToken::generateFor((int) $user->telegram_id);

        $this->get(route('account.auth') . '?token=' . $token->token)
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error');
    }

    // ──────────────────────────────────────────────
    // Auth endpoint — happy path
    // ──────────────────────────────────────────────

    public function test_auth_with_valid_token_creates_session_and_redirects_to_dashboard(): void
    {
        $user  = BotUser::factory()->approved()->create();
        $token = LoginToken::generateFor((int) $user->telegram_id);

        $this->get(route('account.auth') . '?token=' . $token->token)
            ->assertRedirect(route('account.index'));

        $this->assertEquals($user->telegram_id, session('account_telegram_id'));
    }

    public function test_valid_token_can_be_used_multiple_times(): void
    {
        $user  = BotUser::factory()->approved()->create();
        $token = LoginToken::generateFor((int) $user->telegram_id);

        // First use
        $this->get(route('account.auth') . '?token=' . $token->token)
            ->assertRedirect(route('account.index'));

        // Second use — same token still works
        $this->get(route('account.auth') . '?token=' . $token->token)
            ->assertRedirect(route('account.index'));
    }

    public function test_token_expires_after_24_hours(): void
    {
        $user  = BotUser::factory()->approved()->create();
        $token = LoginToken::generateFor((int) $user->telegram_id);

        $this->assertEqualsWithDelta(
            now()->addDay()->timestamp,
            $token->expires_at->timestamp,
            5,
            'Token must expire in 24 hours.'
        );
    }
};