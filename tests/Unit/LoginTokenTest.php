<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\BotUser;
use App\Models\LoginToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_valid_returns_true_for_fresh_token(): void
    {
        $user = BotUser::factory()->approved()->create();

        $token = LoginToken::generateFor((int) $user->telegram_id);

        $this->assertTrue($token->isValid());
    }

    public function test_is_valid_returns_false_when_token_is_expired(): void
    {
        $user = BotUser::factory()->approved()->create();

        $token = LoginToken::generateFor((int) $user->telegram_id);
        $token->update(['expires_at' => now()->subMinute()]);
        $token->refresh();

        $this->assertFalse($token->isValid());
    }

    public function test_generate_for_creates_token_with_24_hour_expiry(): void
    {
        $user = BotUser::factory()->approved()->create();

        $token = LoginToken::generateFor((int) $user->telegram_id);

        $this->assertEqualsWithDelta(
            now()->addDay()->timestamp,
            $token->expires_at->timestamp,
            5 // seconds tolerance
        );
    }

    public function test_generate_for_deletes_previous_tokens(): void
    {
        $user = BotUser::factory()->approved()->create();

        $first  = LoginToken::generateFor((int) $user->telegram_id);
        $second = LoginToken::generateFor((int) $user->telegram_id);

        $this->assertDatabaseMissing('login_tokens', ['id' => $first->id]);
        $this->assertDatabaseHas('login_tokens', ['id' => $second->id]);
    }

    public function test_generate_for_produces_64_char_hex_token(): void
    {
        $user  = BotUser::factory()->approved()->create();
        $token = LoginToken::generateFor((int) $user->telegram_id);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token->token);
    }

    public function test_bot_user_relation_returns_correct_user(): void
    {
        $user  = BotUser::factory()->approved()->create();
        $token = LoginToken::generateFor((int) $user->telegram_id);

        $this->assertTrue($token->botUser->is($user));
    }
}
