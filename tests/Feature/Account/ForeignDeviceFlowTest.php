<?php

namespace Tests\Feature\Account;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Кабинет создали на компьютере, а ссылку из письма открыли на телефоне: в этом браузере сессии нет. Человеку нужно
 * понятно сказать «просто войдите», подставить почту и не предлагать регистрироваться заново.
 */
class ForeignDeviceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_link_opened_in_another_browser_asks_to_sign_in_with_the_email_filled_in(): void
    {
        $user = BotUser::factory()->create(['email' => 'anna@example.test']);
        $user->forceFill(['email_verified_at' => null])->save();

        $link = URL::temporarySignedRoute('account.verification.verify', now()->addDay(), ['id' => $user->id, 'hash' => sha1('anna@example.test')]);

        $this->get($link)->assertRedirect(route('account.login'));
        $this->assertNotNull($user->fresh()->email_verified_at);

        $page = $this->followingRedirects()->get($link);

        $page->assertOk()
            ->assertSee('Теперь войдите')
            ->assertSee('value="anna@example.test"', false)
            // Блок «Я здесь впервые» спрятан: регистрироваться заново не нужно
            ->assertDontSee('Я здесь впервые')
            ->assertSee('Я уже участница');
    }

    public function test_a_plain_visit_of_the_sign_in_page_still_offers_the_registration_first(): void
    {
        $this->get(route('account.login'))
            ->assertOk()
            ->assertSee('Я здесь впервые')
            ->assertSee('Я уже участница');
    }

    public function test_registering_with_an_existing_email_explains_and_offers_the_sign_in_button(): void
    {
        BotUser::factory()->create(['email' => 'anna@example.test']);

        $this->from(route('account.register'))->post(route('account.register.submit'), [
            'email' => 'Anna@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'consent' => 1,
        ])->assertRedirect(route('account.register'));

        $this->get(route('account.register'))
            ->assertOk()
            ->assertSee('Вы уже регистрировались.')
            ->assertSee('href="'.route('account.login').'"', false);

        // Кнопка «Войти» подставляет почту в форму входа (один раз)
        $this->get(route('account.login'))->assertSee('value="anna@example.test"', false);
        $this->get(route('account.login'))->assertDontSee('value="anna@example.test"', false);
    }
}
