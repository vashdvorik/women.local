<?php

namespace Tests\Feature\Account;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Защита от захвата аккаунта: кто-то заранее занял чужую почту паролем (не подтвердив её), а настоящая владелица потом
 * вошла через Google. Прежний пароль и открытые входы под ним должны перестать работать.
 */
class GoogleTakeoverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
    }

    private function googleCallback(string $email, string $sub = 'google-sub-1'): \Illuminate\Testing\TestResponse
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token']),
            'openidconnect.googleapis.com/*' => Http::response(['sub' => $sub, 'email' => $email, 'email_verified' => true, 'name' => 'Анна']),
        ]);

        return $this->withSession(['google_oauth_state' => 'state-1'])
            ->get(route('account.google.callback', ['state' => 'state-1', 'code' => 'code-1']));
    }

    private function squatter(string $email, bool $verified = false): BotUser
    {
        $user = BotUser::factory()->create(['email' => $email]);
        $user->forceFill(['password' => 'attacker-password-1', 'email_verified_at' => $verified ? now() : null])->save();

        return $user->fresh();
    }

    public function test_google_sign_in_wipes_the_password_set_on_an_unverified_account(): void
    {
        $user = $this->squatter('anna@example.test');
        $oldHash = $user->password;

        $this->googleCallback('anna@example.test')->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->email_verified_at, 'Google подтвердил почту');
        $this->assertSame('google-sub-1', $user->google_id);
        $this->assertNotSame($oldHash, $user->password);
        $this->assertFalse(Hash::check('attacker-password-1', $user->password), 'прежний пароль больше не подходит');
    }

    public function test_the_old_password_no_longer_signs_in(): void
    {
        $this->squatter('anna@example.test');
        $this->googleCallback('anna@example.test');
        Auth::guard('participant')->logout();

        $this->post(route('account.login.submit'), ['email' => 'anna@example.test', 'password' => 'attacker-password-1'])
            ->assertSessionHas('error');
        $this->assertGuest('participant');
    }

    public function test_a_session_opened_under_the_old_password_is_closed(): void
    {
        $user = $this->squatter('anna@example.test');

        // Злоумышленник уже вошёл под своим паролем и побывал в кабинете: сессия получила отпечаток пароля.
        $this->actingAs($user, 'participant')->get(route('account.verification.notice'))->assertOk();

        // Настоящая владелица входит через Google: пароль меняется.
        BotUser::whereKey($user->id)->update(['password' => Hash::make('something-else-123'), 'email_verified_at' => now()]);

        $this->actingAs($user->fresh(), 'participant')->get(route('account.index'))
            ->assertRedirect(route('account.login'));
    }

    public function test_the_remember_me_token_is_rotated(): void
    {
        $user = $this->squatter('anna@example.test');
        $user->forceFill(['remember_token' => 'attacker-remember-token'])->save();

        $this->googleCallback('anna@example.test');

        $this->assertNotSame('attacker-remember-token', $user->fresh()->remember_token);
    }

    public function test_a_verified_account_keeps_its_password_when_signing_in_with_google(): void
    {
        $user = $this->squatter('maria@example.test', verified: true);
        $hash = $user->password;

        $this->googleCallback('maria@example.test', 'google-sub-2')->assertRedirect();

        $user->refresh();
        $this->assertSame($hash, $user->password, 'почта была подтверждена: пароль владелицы не трогаем');
        $this->assertTrue(Hash::check('attacker-password-1', $user->password));
        $this->assertSame('google-sub-2', $user->google_id);
    }

    public function test_a_brand_new_google_account_gets_no_password_changes(): void
    {
        $this->googleCallback('newbie@example.test', 'google-sub-3')->assertRedirect();

        $user = BotUser::where('email', 'newbie@example.test')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->password);
    }

    public function test_an_ordinary_session_is_not_closed_while_the_password_stays_the_same(): void
    {
        $user = BotUser::factory()->create();

        // Страница тарифов открыта и без тарифа: проверяем именно то, что вход не сброшен.
        $this->actingAs($user, 'participant')->get(route('account.subscription'))->assertOk();
        $this->actingAs($user->fresh(), 'participant')->get(route('account.subscription'))->assertOk();
    }
}
