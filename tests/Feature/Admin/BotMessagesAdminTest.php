<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Services\BotCommandSync;
use App\Support\BotMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SergiX44\Nutgram\Nutgram;
use Tests\TestCase;

/**
 * «Кабинеты участниц → Сообщения бота»: администратор правит все тексты бота по языкам. Эталон лежит в
 * resources/data/bot_messages.php, правки — в базе поверх него; текст, который Telegram не примет, не сохраняется.
 */
class BotMessagesAdminTest extends TestCase
{
    use RefreshDatabase;

    private function save(array $messages, string $lang = 'ru')
    {
        return $this->actingAsAdmin()->put(route('admin.cabinets.bot-messages.update'), ['lang' => $lang, 'messages' => $messages]);
    }

    /** Сколько раз «бот» вызвал этот метод Telegram API. */
    private function calls(string $method): array
    {
        $bodies = [];

        foreach (app(Nutgram::class)->getRequestHistory() as $item) {
            $request = $item['request'] ?? array_values($item)[0];

            if ($request->getUri()->getPath() === $method) {
                $bodies[] = json_decode((string) $request->getBody(), true);
            }
        }

        return $bodies;
    }

    public function test_guests_cannot_open_or_change_the_messages(): void
    {
        $this->get(route('admin.cabinets.bot-messages'))->assertRedirect(route('login'));
        $this->put(route('admin.cabinets.bot-messages.update'), ['messages' => ['menu_chat_stub' => 'x']])->assertRedirect(route('login'));

        $this->assertNull(SiteSetting::read(BotMessages::SETTING_KEY));
    }

    public function test_the_page_lists_every_message_grouped_and_is_reachable_from_the_menu(): void
    {
        $html = $this->actingAsAdmin()->get(route('admin.cabinets.bot-messages'))->assertOk()->getContent();

        foreach (BotMessages::registry()['groups'] as $group) {
            $this->assertStringContainsString($group['title'], $html);
        }

        // Поле на каждое сообщение файла, с эталонным текстом.
        foreach (BotMessages::keys() as $key) {
            $this->assertStringContainsString('name="messages['.$key.']"', $html, $key);
        }
        $this->assertStringContainsString('Как вас зовут? Укажите имя и фамилию.', $html);

        // Подсказки переменных, вкладки языков и пункт меню лагеря «Кабинеты участниц».
        $this->assertStringContainsString('{name}', $html);
        $this->assertStringContainsString('обязательна', $html);
        $this->assertStringContainsString(route('admin.cabinets.bot-messages', ['lang' => 'en']), $html);
        $this->assertStringContainsString(route('admin.cabinets.bot-messages'), $html);
        $this->assertStringContainsString('Сообщения бота', $html);
        $this->assertStringContainsString('Не используется', $html, 'вход с сайта сейчас не отправляется — так и помечено');
    }

    public function test_each_language_tab_shows_its_own_texts_and_unknown_languages_fall_back_to_russian(): void
    {
        $en = $this->actingAsAdmin()->get(route('admin.cabinets.bot-messages', ['lang' => 'en']))->assertOk()->getContent();
        $this->assertStringContainsString('What is your name? Please enter your first and last name.', $en);
        $this->assertStringNotContainsString('Как вас зовут? Укажите имя и фамилию.</textarea>', $en);

        $ro = $this->actingAsAdmin()->get(route('admin.cabinets.bot-messages', ['lang' => 'ro']))->assertOk()->getContent();
        $this->assertStringContainsString('Cum vă numiți?', $ro);

        $this->actingAsAdmin()->get(route('admin.cabinets.bot-messages', ['lang' => 'de']))->assertOk()
            ->assertSee('Как вас зовут? Укажите имя и фамилию.');
    }

    public function test_an_edit_is_saved_for_that_language_only_and_shown_back(): void
    {
        $this->save(['registration_ask_name' => "Как к вам обращаться?\r\nИмя и фамилия."])
            ->assertRedirect(route('admin.cabinets.bot-messages', ['lang' => 'ru']))
            ->assertSessionHas('success');

        $this->assertSame("Как к вам обращаться?\nИмя и фамилия.", BotMessages::text('registration_ask_name', 'ru'));
        $this->assertSame(BotMessages::default('registration_ask_name', 'en'), BotMessages::text('registration_ask_name', 'en'));

        // Страница показывает правку в поле и рядом эталон, с которым её можно сравнить и к которому можно вернуться.
        $html = $this->actingAsAdmin()->get(route('admin.cabinets.bot-messages'))->assertOk()->getContent();
        $this->assertStringContainsString("Как к вам обращаться?\nИмя и фамилия.</textarea>", $html);
        $this->assertStringContainsString('Как вас зовут? Укажите имя и фамилию.', $html);
    }

    public function test_an_english_edit_does_not_touch_russian(): void
    {
        $this->save(['status_rejected' => 'Closed.'], 'en')->assertSessionHas('success');

        $this->assertSame('Closed.', BotMessages::text('status_rejected', 'en'));
        $this->assertSame(BotMessages::default('status_rejected', 'ru'), BotMessages::text('status_rejected', 'ru'));
    }

    public function test_the_reference_text_or_an_empty_field_returns_the_message_to_the_reference(): void
    {
        $this->save(['status_rejected' => 'Иначе.', 'menu_chat_stub' => 'Скоро.']);
        $this->assertTrue(BotMessages::isCustomized('status_rejected', 'ru'));

        $this->save([
            'status_rejected' => BotMessages::default('status_rejected', 'ru'),
            'menu_chat_stub' => '',
        ])->assertSessionHas('success');

        $this->assertFalse(BotMessages::isCustomized('status_rejected', 'ru'));
        $this->assertFalse(BotMessages::isCustomized('menu_chat_stub', 'ru'));
        $this->assertSame([], SiteSetting::read(BotMessages::SETTING_KEY));
    }

    public function test_messages_missing_from_the_form_stay_as_they_were(): void
    {
        $this->save(['status_rejected' => 'Иначе.']);
        $this->save(['menu_chat_stub' => 'Скоро.']); // в форме нет status_rejected — правка не должна пропасть

        $this->assertSame('Иначе.', BotMessages::text('status_rejected', 'ru'));
        $this->assertSame('Скоро.', BotMessages::text('menu_chat_stub', 'ru'));
    }

    public function test_broken_html_is_not_saved_and_the_error_names_the_message(): void
    {
        $response = $this->save([
            'registration_ask_name' => 'Всё хорошо, но это сохраниться не должно.',
            'registration_welcome' => '<b>Здравствуйте!',
        ]);

        $response->assertSessionHasErrors('messages.registration_welcome');
        $this->assertStringContainsString('«Приветствие»', session('errors')->first('messages.registration_welcome'));
        $this->assertStringContainsString('не закрыт', session('errors')->first('messages.registration_welcome'));

        // Ничего не сохранено, даже исправное: администратор поправляет всё разом и видит, что осталось как было.
        $this->assertNull(SiteSetting::read(BotMessages::SETTING_KEY));
        $this->assertSame(BotMessages::default('registration_ask_name', 'ru'), BotMessages::text('registration_ask_name', 'ru'));
    }

    public function test_the_form_is_redisplayed_with_what_the_administrator_typed(): void
    {
        $this->actingAsAdmin()->from(route('admin.cabinets.bot-messages'))
            ->put(route('admin.cabinets.bot-messages.update'), ['lang' => 'ru', 'messages' => ['registration_welcome' => '<b>Привет!']]);

        $html = $this->actingAsAdmin()->get(route('admin.cabinets.bot-messages'))->assertOk()->getContent();

        $this->assertStringContainsString('&lt;b&gt;Привет!</textarea>', $html, 'введённое не потерялось');
        $this->assertStringContainsString('Ничего не сохранено', $html);
    }

    public function test_unknown_and_missing_variables_are_rejected(): void
    {
        $this->save(['login_link' => 'Здравствуйте, {name}! Ссылка придёт позже.'])
            ->assertSessionHasErrors('messages.login_link');
        $this->assertStringContainsString('{url}', session('errors')->first('messages.login_link'));

        $this->save(['status_pending' => 'Привет, {imya}!'])->assertSessionHasErrors('messages.status_pending');
        $this->save(['status_rejected' => 'Закрыто {name}'])->assertSessionHasErrors('messages.status_rejected');

        $this->assertNull(SiteSetting::read(BotMessages::SETTING_KEY));
    }

    public function test_menu_buttons_cannot_share_a_caption(): void
    {
        // «Чат сообщества» с подписью «Найти контакты»: бот не смог бы понять, какую кнопку нажали.
        $this->save(['menu_chat_button' => BotMessages::default('menu_matches_button', 'ru')])
            ->assertSessionHasErrors('messages.menu_chat_button');

        // Даже если пара — эталонная подпись другого языка: бот узнаёт кнопки на всех языках.
        $this->save(['menu_chat_button' => BotMessages::default('menu_matches_button', 'en')])
            ->assertSessionHasErrors('messages.menu_chat_button');

        $this->assertNull(SiteSetting::read(BotMessages::SETTING_KEY));

        $this->save(['menu_chat_button' => '💬 Общий чат'])->assertSessionHasNoErrors();
        $this->assertSame('💬 Общий чат', BotMessages::text('menu_chat_button', 'ru'));
    }

    public function test_changing_a_command_description_updates_the_telegram_menu_for_every_language(): void
    {
        config(['nutgram.token' => 'TESTTOKEN']);

        $this->save(['menu_command_start' => 'Заявка или вход'])
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'Меню команд в Telegram обновлено'));

        $calls = $this->calls('setMyCommands');
        $this->assertCount(4, $calls, 'по умолчанию + ru, en, ro');

        $byLanguage = [];
        foreach ($calls as $call) {
            $byLanguage[$call['language_code'] ?? 'default'] = json_decode($call['commands'], true);
        }

        $this->assertSame(['command' => 'start', 'description' => 'Заявка или вход'], $byLanguage['ru'][0]);
        $this->assertSame(['command' => 'start', 'description' => 'Заявка или вход'], $byLanguage['default'][0]);
        $this->assertSame(['command' => 'start', 'description' => BotMessages::default('menu_command_start', 'en')], $byLanguage['en'][0]);
        $this->assertSame(['login', BotMessages::default('menu_command_login', 'ro')], [$byLanguage['ro'][1]['command'], $byLanguage['ro'][1]['description']]);
    }

    public function test_the_telegram_menu_is_left_alone_when_no_command_changed(): void
    {
        config(['nutgram.token' => 'TESTTOKEN']);

        $this->save(['status_rejected' => 'Иначе.']);

        $this->assertSame([], $this->calls('setMyCommands'));
    }

    public function test_without_a_bot_token_the_texts_are_saved_and_the_administrator_is_told_about_the_menu(): void
    {
        config(['nutgram.token' => null]);

        $this->save(['menu_command_login' => 'Открыть кабинет'])
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'не задан TELEGRAM_TOKEN'));

        $this->assertSame('Открыть кабинет', BotMessages::text('menu_command_login', 'ru'));
        $this->assertSame([], $this->calls('setMyCommands'));
    }

    public function test_a_telegram_failure_does_not_undo_the_saved_texts(): void
    {
        $this->mock(BotCommandSync::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('sync')->andThrow(new \RuntimeException('Telegram is down'));
        });

        $this->save(['menu_command_login' => 'Открыть кабинет'])
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'php artisan bot:sync-commands'));

        $this->assertSame('Открыть кабинет', BotMessages::text('menu_command_login', 'ru'));
    }

    public function test_the_console_command_pushes_the_menu_or_explains_why_it_cannot(): void
    {
        config(['nutgram.token' => null]);
        $this->artisan('bot:sync-commands')->expectsOutputToContain('TELEGRAM_TOKEN')->assertFailed();

        config(['nutgram.token' => 'TESTTOKEN']);
        $this->artisan('bot:sync-commands')->expectsOutputToContain('Меню команд обновлено')->assertSuccessful();
        $this->assertCount(4, $this->calls('setMyCommands'));
    }
}
