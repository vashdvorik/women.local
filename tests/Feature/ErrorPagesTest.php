<?php

namespace Tests\Feature;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Понятные страницы ошибок на трёх языках вместо английских заглушек, и возврат на форму (с сообщением) там, где человеку
 * лучше повторить действие: просроченная сессия, слишком частые отправки, просроченная ссылка из письма.
 */
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Тестовый клиент по умолчанию «говорит по-английски» (en-us): здесь по умолчанию русский, как у посетителя из Кишинёва.
        $this->withHeader('Accept-Language', 'ru');
    }

    public function test_404_is_in_the_visitors_language_with_a_way_out(): void
    {
        $this->get('/no-such-page', ['Accept-Language' => 'ru-RU,ru;q=0.9'])->assertNotFound()
            ->assertSee('Страница не найдена')->assertSee('На главную');

        $this->get('/no-such-page?lang=en')->assertNotFound()->assertSee('Page not found')->assertSee('Home');
        $this->get('/no-such-page?lang=ro')->assertNotFound()->assertSee('Pagina nu a fost găsită')->assertSee('Pagina principală');
    }

    public function test_the_browser_language_is_used_when_nothing_was_chosen(): void
    {
        $this->get('/no-such-page', ['Accept-Language' => 'ro-RO,ro;q=0.9,en;q=0.8'])->assertNotFound()->assertSee('Pagina nu a fost găsită');
        $this->get('/no-such-page', ['Accept-Language' => 'fr-FR,fr;q=0.9'])->assertNotFound()->assertSee('Страница не найдена');
    }

    public function test_inside_the_cabinet_the_button_leads_back_to_the_cabinet(): void
    {
        $this->get('/app/account/no-such-page')->assertNotFound()->assertSee('В кабинет')->assertDontSee('На главную');
    }

    public function test_the_page_has_a_language_switch_and_is_hidden_from_search(): void
    {
        $this->get('/no-such-page')->assertNotFound()
            ->assertSee('name="robots" content="noindex,nofollow"', false)
            ->assertSee('?lang=en', false)->assertSee('?lang=ro', false);
    }

    public function test_403_500_503_and_other_codes_use_the_same_friendly_page(): void
    {
        Route::get('/_e403', fn () => abort(403));
        Route::get('/_e500', fn () => abort(500));
        Route::get('/_e503', fn () => abort(503));
        Route::post('/_post-only', fn () => 'ok');

        $this->get('/_e403')->assertForbidden()->assertSee('Доступ закрыт');
        $this->get('/_e500')->assertStatus(500)->assertSee('Что-то пошло не так')->assertSee('Обновить страницу');
        $this->get('/_e503')->assertStatus(503)->assertSee('Идут технические работы');
        $this->get('/_post-only')->assertStatus(405)->assertSee('Не получилось открыть страницу')->assertSee('405');
    }

    public function test_a_json_request_still_gets_json(): void
    {
        $this->getJson('/no-such-page')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_an_expired_session_on_a_form_returns_to_the_form_with_a_message_and_keeps_the_input(): void
    {
        Route::post('/app/account/_expired', fn () => throw new TokenMismatchException('CSRF token mismatch.'))->middleware('web');

        $this->from(route('account.login'))
            ->post('/app/account/_expired', ['email' => 'anna@example.test', 'password' => 'secret-password', '_token' => 'old'])
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error', 'Страница слишком долго была открыта, и сессия закончилась. Ничего не потеряно: повторите, пожалуйста, действие.')
            ->assertSessionHasInput('email', 'anna@example.test')
            ->assertSessionMissing('_old_input.password');
    }

    public function test_the_expired_session_message_follows_the_language(): void
    {
        Route::post('/app/account/_expired2', fn () => throw new TokenMismatchException('CSRF token mismatch.'))->middleware('web');

        $this->from(route('account.login'))->post('/app/account/_expired2?lang=en', ['email' => 'a@b.test'])
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'session ended'));
    }

    public function test_too_many_attempts_on_a_form_return_to_it_with_the_wait_time(): void
    {
        Route::post('/app/account/_limited', fn () => 'ok')->middleware(['web', 'throttle:1,1']);

        $this->from(route('account.login'))->post('/app/account/_limited', ['email' => 'a@b.test'])->assertOk();

        $this->from(route('account.login'))->post('/app/account/_limited', ['email' => 'a@b.test'])
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'Слишком много попыток') && preg_match('/\d+ с/u', $message) === 1);
    }

    public function test_public_forms_keep_the_error_page_because_they_show_their_own_messages(): void
    {
        Route::post('/_public-form', fn () => 'ok')->middleware(['web', 'throttle:1,1']);
        Route::post('/_public-expired', fn () => throw new TokenMismatchException('CSRF token mismatch.'))->middleware('web');

        $this->post('/_public-form')->assertOk();
        $this->from('/')->post('/_public-form')->assertStatus(429)->assertSee('Слишком много попыток');
        $this->from('/')->post('/_public-expired')->assertStatus(419)->assertSee('Сессия закончилась');
    }

    public function test_too_many_requests_to_a_page_show_the_page_with_the_wait_time(): void
    {
        Route::get('/_limited-page', fn () => 'ok')->middleware(['web', 'throttle:1,1']);

        $this->get('/_limited-page')->assertOk();
        $this->get('/_limited-page')->assertStatus(429)->assertSee('Слишком много попыток')->assertSee('Подождите');
    }

    public function test_an_expired_email_link_leads_to_the_login_page_with_a_clear_message(): void
    {
        $user = BotUser::factory()->create();
        $user->forceFill(['email_verified_at' => null])->save();

        $expired = URL::temporarySignedRoute('account.verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1((string) $user->email)]);

        $this->get($expired)->assertRedirect(route('account.login'))
            ->assertSessionHas('error', 'Ссылка недействительна или устарела. Запросите новое письмо.');
        $this->assertNull($user->fresh()->email_verified_at, 'просроченная ссылка почту не подтверждает');

        // Ссылка без подписи ведёт себя так же.
        $this->get(route('account.verification.verify', ['id' => $user->id, 'hash' => sha1((string) $user->email)]))
            ->assertRedirect(route('account.login'));
    }

    public function test_a_valid_email_link_still_works(): void
    {
        $user = BotUser::factory()->create();
        $user->forceFill(['email_verified_at' => null])->save();

        $link = URL::temporarySignedRoute('account.verification.verify', now()->addDay(), ['id' => $user->id, 'hash' => sha1((string) $user->email)]);

        $this->get($link)->assertRedirect(route('account.login'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
