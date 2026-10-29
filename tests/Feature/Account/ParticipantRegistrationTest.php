<?php

namespace Tests\Feature\Account;

use App\Enums\Plan;
use App\Mail\VerifyParticipantEmailMail;
use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Регистрация по почте и шаги до кабинета: подтверждение почты → анкета (с телефоном) → выбор тарифа. Без тарифа
 * кабинета нет. Вход через Telegram не существует: бот только присылает уведомления.
 */
class ParticipantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Mail::fake();
    }

    private function register(array $override = []): void
    {
        $this->post(route('account.register.submit'), array_merge([
            'email' => 'New.Member@Example.test',
            'password' => 'a-long-password',
            'password_confirmation' => 'a-long-password',
            'consent' => '1',
        ], $override));
    }

    public function test_registration_creates_a_participant_signs_her_in_and_sends_the_confirmation_letter(): void
    {
        $this->register();

        $user = BotUser::where('email', 'new.member@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user, 'participant');
        $this->assertNull($user->telegram_id, 'Telegram при регистрации не нужен');
        $this->assertNull($user->email_verified_at);
        $this->assertSame(Plan::None, $user->currentPlan());
        $this->assertTrue(Hash::check('a-long-password', $user->password));
        $this->assertNotSame('a-long-password', $user->getRawOriginal('password'));

        Mail::assertSent(VerifyParticipantEmailMail::class, fn ($mail) => $mail->hasTo('new.member@example.test'));
    }

    public function test_registration_is_refused_without_consent_with_a_short_password_or_a_taken_email(): void
    {
        BotUser::factory()->create(['email' => 'taken@example.test']);

        $this->register(['consent' => null]);
        $this->register(['password' => 'short', 'password_confirmation' => 'short']);
        $this->register(['email' => 'taken@example.test']);

        $this->assertSame(1, BotUser::count(), 'ни одной новой участницы');
        $this->assertGuest('participant');
    }

    public function test_an_unverified_participant_is_kept_on_the_confirmation_page(): void
    {
        $user = BotUser::factory()->registered()->create(['email_verified_at' => null]);

        $this->withSession($this->participantSession($user))->get(route('account.index'))
            ->assertRedirect(route('account.verification.notice'));

        $this->withSession($this->participantSession($user))->get(route('account.onboarding'))
            ->assertRedirect(route('account.verification.notice'));
    }

    public function test_the_signed_link_from_the_letter_confirms_the_email_and_leads_to_the_profile_form(): void
    {
        $user = BotUser::factory()->registered()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute('account.verification.verify', now()->addDay(), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->withSession($this->participantSession($user))->get($url)->assertRedirect(route('account.onboarding'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_the_confirmation_link_does_not_work_with_a_wrong_hash_or_a_forged_signature(): void
    {
        $user = BotUser::factory()->registered()->create(['email_verified_at' => null]);

        $wrongHash = URL::temporarySignedRoute('account.verification.verify', now()->addDay(), ['id' => $user->id, 'hash' => sha1('other@example.test')]);
        $this->get($wrongHash)->assertRedirect(route('account.login'));

        $this->get(route('account.verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_the_profile_form_needs_a_name_a_phone_and_both_questions_and_then_leads_to_the_plans(): void
    {
        $user = BotUser::factory()->registered()->create();

        $this->withSession($this->participantSession($user))->get(route('account.index'))
            ->assertRedirect(route('account.onboarding'));

        $this->withSession($this->participantSession($user))->post(route('account.onboarding.store'), [
            'full_name' => 'Анна Лупу',
            'phone' => 'abc',
            'description' => 'Делаю мыло',
            'expectation' => '',
        ])->assertSessionHasErrors(['phone', 'expectation']);

        $this->assertNull($user->fresh()->profile_completed_at);

        $this->withSession($this->participantSession($user))->post(route('account.onboarding.store'), [
            'full_name' => 'Анна Лупу',
            'phone' => '+373 69 123 456',
            'description' => 'Делаю мыло',
            'expectation' => 'Ищу партнёров',
        ])->assertRedirect(route('account.subscription'));

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->profile_completed_at);
        $this->assertSame('+373 69 123 456', $fresh->phone);
        $this->assertTrue($fresh->isPending(), 'анкета ждёт проверки администратора');
    }

    public function test_without_a_plan_only_the_plans_page_and_the_profile_are_open(): void
    {
        $user = BotUser::factory()->approved()->create();

        $this->withSession($this->participantSession($user))->get(route('account.index'))->assertRedirect(route('account.subscription'));
        $this->withSession($this->participantSession($user))->get(route('account.people'))->assertRedirect(route('account.subscription'));
        $this->withSession($this->participantSession($user))->get(route('account.opportunities.index'))->assertRedirect(route('account.subscription'));

        $this->withSession($this->participantSession($user))->get(route('account.subscription'))->assertOk()
            ->assertSee('600')->assertSee('5 000')->assertSee('19 000');
        $this->withSession($this->participantSession($user))->get(route('account.profile'))->assertOk();
    }

    public function test_after_paying_for_a_plan_the_cabinet_opens(): void
    {
        $user = BotUser::factory()->community()->create();

        $this->withSession($this->participantSession($user))->get(route('account.index'))->assertOk();
    }

    public function test_login_logout_and_a_wrong_password(): void
    {
        $user = BotUser::factory()->community()->create(['email' => 'member@example.test']);
        $user->forceFill(['password' => 'correct-horse'])->save();

        $this->post(route('account.login.submit'), ['email' => 'member@example.test', 'password' => 'wrong'])
            ->assertSessionHas('error');
        $this->assertGuest('participant');

        $this->post(route('account.login.submit'), ['email' => 'Member@Example.test', 'password' => 'correct-horse'])
            ->assertRedirect();
        $this->assertAuthenticatedAs($user, 'participant');

        $this->post(route('account.logout'))->assertRedirect(route('account.login'));
        $this->assertGuest('participant');
    }

    public function test_the_login_form_locks_after_five_wrong_passwords(): void
    {
        $user = BotUser::factory()->community()->create(['email' => 'member@example.test']);
        $user->forceFill(['password' => 'correct-horse'])->save();

        foreach (range(1, 5) as $_) {
            $this->post(route('account.login.submit'), ['email' => 'member@example.test', 'password' => 'wrong']);
        }

        $this->post(route('account.login.submit'), ['email' => 'member@example.test', 'password' => 'correct-horse']);

        $this->assertGuest('participant');
    }

    public function test_a_rejected_participant_cannot_sign_in(): void
    {
        $user = BotUser::factory()->create(['email' => 'no@example.test', 'status' => BotUser::STATUS_REJECTED]);
        $user->forceFill(['password' => 'correct-horse'])->save();

        $this->post(route('account.login.submit'), ['email' => 'no@example.test', 'password' => 'correct-horse'])
            ->assertRedirect(route('account.login'));

        $this->assertGuest('participant');
    }

    public function test_the_password_reset_letter_gives_the_same_answer_for_any_email(): void
    {
        BotUser::factory()->create(['email' => 'member@example.test']);

        $known = $this->post(route('account.password.email'), ['email' => 'member@example.test']);
        $unknown = $this->post(route('account.password.email'), ['email' => 'nobody@example.test']);

        $known->assertSessionHas('success', __('participant.password.forgot_sent'));
        $unknown->assertSessionHas('success', __('participant.password.forgot_sent'));
    }

    public function test_google_sign_in_is_hidden_and_closed_until_the_keys_are_set(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get(route('account.login'))->assertOk()->assertDontSee(route('account.google'));
        $this->get(route('account.google'))->assertNotFound();

        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);

        $this->get(route('account.login'))->assertSee(route('account.google'));
        $this->get(route('account.google'))->assertRedirectContains('accounts.google.com');
    }

    public function test_the_old_telegram_login_addresses_are_gone(): void
    {
        $this->get('/app/account/auth?token=abc')->assertNotFound();
        $this->post('/app/account/tma-auth')->assertNotFound();
        $this->get('/go/abcdef12')->assertNotFound();
    }
}
