<?php

namespace Tests\Feature\Account;

use App\Mail\VerifyParticipantEmailMail;
use App\Models\BotUser;
use App\Models\Payment;
use App\Support\EmailTypo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Ошибки с почтой: опечатка в адресе при регистрации (подсказка и исправление без нового аккаунта), мёртвые регистрации,
 * мягкие лимиты форм и подсказка на входе для тех, кто регистрировался через Google.
 */
class EmailMistakesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'ru');
    }

    private function unverified(string $email = 'anna@gmial.com'): BotUser
    {
        $user = BotUser::factory()->create(['email' => $email]);
        // Регистрация по почте: Telegram не подключён, анкеты и тарифа нет.
        $user->forceFill(['email_verified_at' => null, 'profile_completed_at' => null, 'telegram_id' => null, 'password' => 'secret-password-1'])->save();

        return $user->fresh();
    }

    // ------------------------------------------------------------------ подсказка об опечатке

    public function test_typo_suggestions_for_popular_domains(): void
    {
        $this->assertSame('anna@gmail.com', EmailTypo::suggest('anna@gmial.com'));
        $this->assertSame('anna@gmail.com', EmailTypo::suggest('Anna@Gmai.com'));
        $this->assertSame('anna@gmail.com', EmailTypo::suggest('anna@gmail.con'));
        $this->assertSame('anna@mail.ru', EmailTypo::suggest('anna@mial.ru'));
        $this->assertSame('x@yahoo.com', EmailTypo::suggest('x@yahho.com'));
    }

    public function test_correct_and_unusual_domains_get_no_suggestion(): void
    {
        $this->assertNull(EmailTypo::suggest('anna@gmail.com'));
        $this->assertNull(EmailTypo::suggest('anna@mail.ru'));
        $this->assertNull(EmailTypo::suggest('anna@ya.ru'));
        $this->assertNull(EmailTypo::suggest('anna@mycompany.md'));
        $this->assertNull(EmailTypo::suggest('anna@innovation.md'));
        $this->assertNull(EmailTypo::suggest('not-an-email'));
    }

    public function test_registration_asks_to_check_a_suspicious_address_once_and_then_accepts_it(): void
    {
        Mail::fake();
        $payload = ['email' => 'anna@gmial.com', 'password' => 'secret-password-1', 'password_confirmation' => 'secret-password-1', 'consent' => '1'];

        $this->from(route('account.register'))->post(route('account.register.submit'), $payload)
            ->assertRedirect(route('account.register'))
            ->assertSessionHasErrors(['email' => 'Вы имели в виду anna@gmail.com? Исправьте адрес или нажмите кнопку ещё раз, если всё верно.']);
        $this->assertDatabaseMissing('bot_users', ['email' => 'anna@gmial.com']);

        // Та же почта второй раз: человек подтвердил, что адрес верный.
        $this->post(route('account.register.submit'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bot_users', ['email' => 'anna@gmial.com']);
    }

    public function test_a_normal_address_registers_without_a_question(): void
    {
        Mail::fake();

        $this->post(route('account.register.submit'), ['email' => 'anna@gmail.com', 'password' => 'secret-password-1', 'password_confirmation' => 'secret-password-1', 'consent' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('bot_users', ['email' => 'anna@gmail.com']);
    }

    // ------------------------------------------------------------------ смена почты

    public function test_the_verify_page_offers_to_fix_the_address(): void
    {
        $user = $this->unverified();

        $this->withSession($this->participantSession($user))->get(route('account.verification.notice'))->assertOk()
            ->assertSee('Указали не ту почту? Исправить адрес')
            ->assertSee(route('account.verification.change'), false);
    }

    public function test_the_address_is_changed_and_the_letter_goes_to_the_new_one(): void
    {
        Mail::fake();
        $user = $this->unverified();
        $oldHash = sha1('anna@gmial.com');

        $this->withSession($this->participantSession($user))->from(route('account.verification.notice'))
            ->post(route('account.verification.change'), ['email' => 'Anna@Gmail.com'])
            ->assertRedirect(route('account.verification.notice'))
            ->assertSessionHas('success', 'Готово. Мы отправили письмо на anna@gmail.com.');

        $this->assertSame('anna@gmail.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
        Mail::assertSent(VerifyParticipantEmailMail::class, fn (VerifyParticipantEmailMail $mail) => $mail->hasTo('anna@gmail.com'));

        // Старая ссылка (на опечатанный адрес) больше ничего не подтверждает.
        $this->get(route('account.verification.verify', ['id' => $user->id, 'hash' => $oldHash]))->assertRedirect(route('account.login'));
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_a_taken_address_cannot_be_used(): void
    {
        BotUser::factory()->create(['email' => 'taken@example.test']);
        $user = $this->unverified();

        $this->withSession($this->participantSession($user))->from(route('account.verification.notice'))
            ->post(route('account.verification.change'), ['email' => 'taken@example.test'])
            ->assertSessionHasErrors('email');

        $this->assertSame('anna@gmial.com', $user->fresh()->email);
    }

    public function test_fixing_to_another_suspicious_address_also_asks_once(): void
    {
        Mail::fake();
        $user = $this->unverified('someone@example.test');

        $this->withSession($this->participantSession($user))->from(route('account.verification.notice'))
            ->post(route('account.verification.change'), ['email' => 'anna@gmial.com'])
            ->assertSessionHasErrors('email');
        $this->assertSame('someone@example.test', $user->fresh()->email);
    }

    public function test_a_verified_participant_cannot_change_the_address_here(): void
    {
        $user = BotUser::factory()->create(['email' => 'ok@example.test']);

        $this->withSession($this->participantSession($user))->post(route('account.verification.change'), ['email' => 'other@example.test'])
            ->assertRedirect(route('account.onboarding'));

        $this->assertSame('ok@example.test', $user->fresh()->email);
    }

    // ------------------------------------------------------------------ мёртвые регистрации

    public function test_dead_registrations_are_removed_after_thirty_days_and_everything_else_is_kept(): void
    {
        $dead = $this->unverified('dead@example.test');
        BotUser::whereKey($dead->id)->update(['created_at' => now()->subDays(31)]);

        $recent = $this->unverified('recent@example.test');
        BotUser::whereKey($recent->id)->update(['created_at' => now()->subDays(5)]);

        $verified = BotUser::factory()->create(['email' => 'verified@example.test']);
        BotUser::whereKey($verified->id)->update(['created_at' => now()->subDays(90)]);

        $google = $this->unverified('google@example.test');
        BotUser::whereKey($google->id)->update(['google_id' => 'g-1', 'created_at' => now()->subDays(90)]);

        $this->artisan('accounts:purge-unverified', ['--dry-run' => true])->expectsOutput('Будет удалено регистраций: 1.')->assertSuccessful();
        $this->assertDatabaseHas('bot_users', ['email' => 'dead@example.test']);

        $this->artisan('accounts:purge-unverified')->assertSuccessful();

        $this->assertDatabaseMissing('bot_users', ['email' => 'dead@example.test']);
        foreach (['recent@example.test', 'verified@example.test', 'google@example.test'] as $email) {
            $this->assertDatabaseHas('bot_users', ['email' => $email]);
        }
    }

    // ------------------------------------------------------------------ вход и лимиты

    public function test_a_failed_login_hints_at_google_without_revealing_whether_the_account_exists(): void
    {
        BotUser::factory()->create(['email' => 'exists@example.test']);

        $existing = $this->from(route('account.login'))->post(route('account.login.submit'), ['email' => 'exists@example.test', 'password' => 'wrong-password'])->getSession()->get('error');
        $missing = $this->from(route('account.login'))->post(route('account.login.submit'), ['email' => 'nobody@example.test', 'password' => 'wrong-password'])->getSession()->get('error');

        $this->assertSame($existing, $missing, 'сообщение одно и то же: по нему нельзя узнать, есть ли такая почта');
        $this->assertStringContainsString('через Google', $existing);
    }

    public function test_many_people_behind_one_address_are_not_blocked_by_each_other(): void
    {
        // Десять разных людей с одного IP (мобильный оператор, офис): раньше после десятой попытки блокировалась вся сеть.
        foreach (range(1, 12) as $i) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.40'])->post(route('account.register.submit'), [
                'email' => "person{$i}@example.test", 'password' => 'secret-password-1', 'password_confirmation' => 'secret-password-1', 'consent' => '1',
            ])->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('bot_users', 12);
    }

    public function test_one_address_cannot_be_flooded_with_letters(): void
    {
        Mail::fake();
        $payload = ['email' => 'victim@example.test', 'password' => 'secret-password-1', 'password_confirmation' => 'secret-password-1', 'consent' => '1'];

        foreach (range(1, 6) as $i) {
            $this->post(route('account.password.email'), ['email' => 'victim@example.test']);
        }

        $this->from(route('account.password.forgot'))->post(route('account.password.email'), ['email' => 'victim@example.test'])
            ->assertRedirect(route('account.password.forgot'))
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'Слишком много попыток'));
    }
}
