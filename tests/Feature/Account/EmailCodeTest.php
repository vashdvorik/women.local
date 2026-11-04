<?php

namespace Tests\Feature\Account;

use App\Mail\VerifyParticipantEmailMail;
use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Подтверждение почты 5-значным кодом из письма (рядом со ссылкой): человек вводит его там же, где регистрировался.
 */
class EmailCodeTest extends TestCase
{
    use RefreshDatabase;

    private function unverified(): BotUser
    {
        $user = BotUser::factory()->create(['email' => 'anna@example.test']);
        $user->forceFill(['email_verified_at' => null])->save();

        return $user;
    }

    /** Отправляет письмо и возвращает код из него. */
    private function sentCode(BotUser $user): string
    {
        Mail::fake();
        $user->sendEmailVerification();

        $code = null;
        Mail::assertSent(VerifyParticipantEmailMail::class, function (VerifyParticipantEmailMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return (string) $code;
    }

    private function as(BotUser $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->flushSession()->actingAs($user, 'participant');
    }

    public function test_the_email_has_a_five_digit_code_next_to_the_link_and_the_database_keeps_only_its_hash(): void
    {
        $user = $this->unverified();
        $code = $this->sentCode($user);

        $this->assertMatchesRegularExpression('/^[0-9]{5}$/', $code);

        $row = $user->fresh();
        $this->assertNotNull($row->email_code_hash);
        $this->assertStringNotContainsString($code, (string) $row->email_code_hash);
        $this->assertTrue($row->email_code_expires_at->between(now()->addMinutes(14), now()->addMinutes(16)));

        $html = (new VerifyParticipantEmailMail($user, 'https://example.test/link', 'ru', $code))->render();
        $this->assertStringContainsString($code, $html);
        $this->assertStringContainsString('https://example.test/link', $html);
    }

    public function test_the_right_code_confirms_the_email_and_leads_to_the_profile(): void
    {
        $user = $this->unverified();
        $code = $this->sentCode($user);

        $this->as($user)->post(route('account.verification.code'), ['code' => $code])
            ->assertRedirect(route('account.onboarding'))
            ->assertSessionHas('success');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNull($user->fresh()->email_code_hash);
    }

    public function test_spaces_in_the_code_are_ignored(): void
    {
        $user = $this->unverified();
        $code = $this->sentCode($user);

        $this->as($user)->post(route('account.verification.code'), ['code' => substr($code, 0, 2).' '.substr($code, 2)])
            ->assertRedirect(route('account.onboarding'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_a_wrong_code_is_counted_and_five_mistakes_burn_the_code(): void
    {
        $user = $this->unverified();
        $code = $this->sentCode($user);
        $wrong = $code === '00000' ? '11111' : '00000';

        $this->as($user)->post(route('account.verification.code'), ['code' => $wrong])
            ->assertSessionHasErrors('code');
        $this->assertSame(4, $user->fresh()->emailCodeAttemptsLeft());

        for ($i = 0; $i < 4; $i++) {
            $this->as($user)->post(route('account.verification.code'), ['code' => $wrong]);
        }

        // Правильный код после пяти ошибок уже не принимается: нужен новый
        $this->as($user)->post(route('account.verification.code'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_an_old_code_does_not_work_after_the_time_is_up_and_a_new_email_issues_a_new_code(): void
    {
        $user = $this->unverified();
        $code = $this->sentCode($user);

        $this->travel(16)->minutes();
        $this->as($user)->post(route('account.verification.code'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);

        $fresh = $this->sentCode($user->fresh());
        $this->as($user->fresh())->post(route('account.verification.code'), ['code' => $fresh])->assertRedirect(route('account.onboarding'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_a_code_for_the_old_address_stops_working_when_the_email_is_changed(): void
    {
        $user = $this->unverified();
        $code = $this->sentCode($user);

        $user->forceFill(['email' => 'new@example.test'])->save();

        $this->as($user->fresh())->post(route('account.verification.code'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_garbage_instead_of_the_code_is_refused_and_the_page_shows_the_field(): void
    {
        $user = $this->unverified();

        $this->as($user)->post(route('account.verification.code'), ['code' => 'abc'])->assertSessionHasErrors('code');

        $this->as($user)->get(route('account.verification.notice'))
            ->assertOk()
            ->assertSee('Код из письма')
            ->assertSee('autocomplete="one-time-code"', false);
    }
}
