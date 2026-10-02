<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\BotMessages;
use App\Support\TelegramHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Все тексты бота лежат в одном файле (resources/data/bot_messages.php), а код берёт их через BotMessages.
 * Здесь проверяются: целостность файла, выбор языка, подстановка переменных (с защитой от чужой разметки),
 * проверка Telegram-HTML, правки администратора поверх эталона и узнавание кнопок меню.
 */
class BotMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_message_is_complete_in_all_three_languages(): void
    {
        $registry = BotMessages::registry();
        $this->assertNotEmpty($registry['messages']);

        foreach ($registry['messages'] as $key => $definition) {
            $this->assertMatchesRegularExpression('/^[a-z][a-z0-9_]*$/', $key, 'ключ без точек: он же имя поля формы');
            $this->assertArrayHasKey($definition['group'], $registry['groups'], "{$key}: нет раздела {$definition['group']}");
            $this->assertContains($definition['kind'], ['text', 'button', 'command'], $key);
            $this->assertNotSame('', trim($definition['title']), $key);
            $this->assertNotSame('', trim($definition['when']), $key);

            foreach (BotMessages::LOCALES as $locale) {
                $this->assertNotSame('', trim($definition['text'][$locale] ?? ''), "{$key}: нет текста {$locale}");
            }

            foreach (array_merge(array_keys($definition['vars']), $definition['raw'] ?? [], $definition['required'] ?? []) as $var) {
                $this->assertMatchesRegularExpression('/^[a-z_]+$/', $var, "{$key}: переменная {$var}");
            }
            foreach (array_merge($definition['raw'] ?? [], $definition['required'] ?? []) as $var) {
                $this->assertArrayHasKey($var, $definition['vars'], "{$key}: {$var} не объявлена в vars");
            }
            if ($definition['trigger'] ?? false) {
                $this->assertSame('button', $definition['kind'], "{$key}: триггером может быть только кнопка");
            }
        }
    }

    public function test_the_reference_texts_pass_their_own_checks_unchanged(): void
    {
        foreach (BotMessages::keys() as $key) {
            foreach (BotMessages::LOCALES as $locale) {
                $default = BotMessages::default($key, $locale);
                [$text, $errors] = BotMessages::check($key, $default);

                $this->assertSame([], $errors, "{$key}/{$locale}: ".implode(' ', $errors));
                $this->assertSame($default, $text, "{$key}/{$locale}: проверка изменила эталонный текст");
            }
        }
    }

    public function test_language_follows_the_telegram_language_code_and_defaults_to_russian(): void
    {
        $this->assertSame('ru', BotMessages::locale('ru'));
        $this->assertSame('en', BotMessages::locale('en'));
        $this->assertSame('ro', BotMessages::locale('ro'));
        $this->assertSame('en', BotMessages::locale('en-GB'));
        $this->assertSame('ro', BotMessages::locale('RO-md'));
        $this->assertSame('ru', BotMessages::locale('uk'));
        $this->assertSame('ru', BotMessages::locale(''));
        $this->assertSame('ru', BotMessages::locale(null));
    }

    public function test_variables_are_substituted_and_the_participants_own_markup_is_neutralised(): void
    {
        // Имя приходит от участницы: тег в нём не должен ни сломать сообщение, ни оформить его.
        $text = BotMessages::text('status_pending', 'ru', ['name' => 'Анна<b>&"\'']);

        $this->assertStringContainsString('Анна&lt;b&gt;&amp;&quot;&#039;, ваша заявка уже на рассмотрении.', $text);
        $this->assertStringNotContainsString('<b>', $text);

        // Одна подстановка за проход: то, что участница написала в запросе, повторно не разбирается.
        $text = BotMessages::text('search_no_results', 'ru', ['query' => 'нужна {query} и {name}']);
        $this->assertStringContainsString('«нужна {query} и {name}»', $text);

        // Переменная, которую код не передал, превращается в пустоту, а не остаётся «{name}» в сообщении.
        $this->assertStringStartsWith(', ваша заявка', BotMessages::text('status_pending', 'ru'));

        // Собранный кодом HTML (raw-переменная) не экранируется, а всё остальное — да.
        $text = BotMessages::text('broadcast_opportunity', 'ru', ['title' => 'A & B', 'details' => "\n\n📅 01.02.2027", 'body' => 'x', 'type' => 'Проект', 'emoji' => '💼', 'author' => 'Анна']);
        $this->assertStringContainsString('A &amp; B', $text);
        $this->assertStringContainsString("x\n\n📅 01.02.2027\n\n👤", $text);
    }

    public function test_button_captions_are_plain_text_and_are_not_escaped(): void
    {
        $this->assertSame('Написать @a_b&c', BotMessages::text('search_write_button', 'ru', ['username' => 'a_b&c']));
        $this->assertSame('Show 2 more →', BotMessages::text('search_more_button', 'en', ['count' => 2]));
    }

    public function test_a_missing_language_falls_back_to_russian(): void
    {
        $this->assertSame(BotMessages::text('menu_chat_stub', 'ru'), BotMessages::text('menu_chat_stub', 'xx'));
    }

    public function test_administrator_edits_override_the_reference_only_for_their_language(): void
    {
        BotMessages::save('en', ['registration_ask_name' => 'Your name, please?']);

        $this->assertSame('Your name, please?', BotMessages::text('registration_ask_name', 'en'));
        $this->assertTrue(BotMessages::isCustomized('registration_ask_name', 'en'));

        // Другие языки и другие сообщения не тронуты.
        $this->assertSame(BotMessages::default('registration_ask_name', 'ru'), BotMessages::text('registration_ask_name', 'ru'));
        $this->assertFalse(BotMessages::isCustomized('registration_ask_name', 'ro'));
        $this->assertSame(BotMessages::default('registration_ask_description', 'en'), BotMessages::text('registration_ask_description', 'en'));

        // В базе лежит только отличие от эталона.
        $this->assertSame(['en' => ['registration_ask_name' => 'Your name, please?']], SiteSetting::read(BotMessages::SETTING_KEY));
    }

    public function test_saving_the_reference_text_or_an_empty_one_returns_the_message_to_the_reference(): void
    {
        BotMessages::save('ru', ['registration_ask_name' => 'Иначе?', 'status_rejected' => 'Закрыто.']);
        $this->assertTrue(BotMessages::isCustomized('status_rejected', 'ru'));

        BotMessages::save('ru', [
            'registration_ask_name' => BotMessages::default('registration_ask_name', 'ru'),
            'status_rejected' => '   ',
        ]);

        $this->assertFalse(BotMessages::isCustomized('registration_ask_name', 'ru'));
        $this->assertFalse(BotMessages::isCustomized('status_rejected', 'ru'));
        $this->assertSame([], SiteSetting::read(BotMessages::SETTING_KEY));
    }

    public function test_changes_apply_immediately_despite_the_cache(): void
    {
        $this->assertSame(BotMessages::default('menu_chat_stub', 'ru'), BotMessages::text('menu_chat_stub', 'ru')); // прогрев кэша

        BotMessages::save('ru', ['menu_chat_stub' => 'Скоро.']);

        $this->assertSame('Скоро.', BotMessages::text('menu_chat_stub', 'ru'));
    }

    public function test_menu_buttons_are_recognised_by_any_language_and_by_the_previous_caption(): void
    {
        $key = 'menu_matches_button';

        foreach (BotMessages::LOCALES as $locale) {
            $this->assertTrue(BotMessages::matches($key, BotMessages::default($key, $locale)), $locale);
        }

        // Администратор переименовал кнопку: новая работает, а прежняя (она ещё на телефонах) — тоже.
        BotMessages::save('ru', [$key => '🔍 Подобрать контакты']);

        $this->assertTrue(BotMessages::matches($key, '🔍 Подобрать контакты'));
        $this->assertTrue(BotMessages::matches($key, BotMessages::default($key, 'ru')));
        $this->assertTrue(BotMessages::matches($key, '  🔍 Подобрать контакты  '));

        $this->assertFalse(BotMessages::matches($key, BotMessages::default('menu_chat_button', 'ru')));
        $this->assertFalse(BotMessages::matches($key, ''));
        $this->assertFalse(BotMessages::matches($key, null));
    }

    public function test_an_unknown_message_key_is_a_developer_error(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        BotMessages::text('no_such_message', 'ru');
    }

    public function test_a_broken_settings_table_never_silences_the_bot(): void
    {
        \Illuminate\Support\Facades\Schema::drop('site_settings');
        BotMessages::flush();

        $this->assertSame(BotMessages::default('menu_chat_stub', 'ru'), BotMessages::text('menu_chat_stub', 'ru'));
    }

    // ---------------------------------------------------------------- проверка введённого текста

    public function test_valid_telegram_html_passes(): void
    {
        [$text, $errors] = BotMessages::check('search_intro', "🔍 <b>Поиск</b>\r\n\r\n<i>например</i> и <a href=\"https://t.me/x\">ссылка</a>  ");

        $this->assertSame([], $errors);
        $this->assertSame("🔍 <b>Поиск</b>\n\n<i>например</i> и <a href=\"https://t.me/x\">ссылка</a>", $text, 'CRLF → LF, края обрезаны');
    }

    #[DataProvider('brokenHtml')]
    public function test_broken_telegram_html_is_rejected_before_it_can_reach_telegram(string $html, string $expected): void
    {
        [, $errors] = TelegramHtml::check($html);

        $this->assertNotEmpty($errors, $html);
        $this->assertStringContainsString($expected, implode(' ', $errors), $html);
    }

    public static function brokenHtml(): array
    {
        return [
            'не закрыт' => ['<b>жирный', 'не закрыт'],
            'закрыт не там' => ['<b><i>текст</b></i>', 'закрыт не там'],
            'лишний закрывающий' => ['текст</b>', 'закрыт не там'],
            'неизвестный тег' => ['<script>alert(1)</script>', 'не поддерживается'],
            'div' => ['<div>текст</div>', 'не поддерживается'],
            'голый знак меньше' => ['цена < 5', 'Символ «<»'],
            'обрезанный тег' => ['<b>текст</b', 'Символ «<»'],
            'параметры у b' => ['<b class="x">текст</b>', 'не должно быть параметров'],
            'ссылка без адреса' => ['<a>текст</a>', 'У ссылки должен быть адрес'],
            'опасная схема' => ['<a href="javascript:alert(1)">текст</a>', 'У ссылки должен быть адрес'],
            'лишний параметр у ссылки' => ['<a href="https://x.md" onclick="y()">текст</a>', 'У ссылки должен быть адрес'],
            'неподдерживаемая сущность' => ['a&nbsp;b', 'не понимает'],
        ];
    }

    public function test_bare_ampersands_and_greater_than_signs_are_escaped_automatically(): void
    {
        [$text, $errors] = BotMessages::check('search_intro', 'Q&A -> ответ, &amp; уже &#8212; в порядке, &lt;');

        $this->assertSame([], $errors);
        $this->assertSame('Q&amp;A -&gt; ответ, &amp; уже &#8212; в порядке, &lt;', $text);
    }

    public function test_unknown_and_missing_variables_are_reported_in_plain_words(): void
    {
        [, $errors] = BotMessages::check('status_pending', 'Здравствуйте, {imya}!');
        $this->assertStringContainsString('Переменная {imya} здесь недоступна', implode(' ', $errors));
        $this->assertStringContainsString('{name}', implode(' ', $errors), 'подсказка, что можно использовать');

        [, $errors] = BotMessages::check('status_rejected', 'Доступ закрыт {name}');
        $this->assertStringContainsString('переменных нет', implode(' ', $errors));

        // Без ссылки письмо для входа бессмысленно.
        [, $errors] = BotMessages::check('login_link', 'Здравствуйте, {name}! Ссылка придёт позже.');
        $this->assertStringContainsString('В тексте должна быть переменная {url}', implode(' ', $errors));
    }

    public function test_length_limits_and_single_line_buttons(): void
    {
        [, $errors] = BotMessages::check('registration_welcome', str_repeat('а', BotMessages::MAX_TEXT + 1));
        $this->assertStringContainsString('слишком длинный', implode(' ', $errors));

        // Разметка не считается: тегами длину не обойти и не накрутить.
        [, $errors] = BotMessages::check('registration_welcome', '<b>'.str_repeat('а', BotMessages::MAX_TEXT).'</b>');
        $this->assertSame([], $errors);

        [$text, $errors] = BotMessages::check('registration_skip_button', "Пропустить\nсейчас");
        $this->assertSame('Пропустить сейчас', $text, 'подпись кнопки — одна строка');
        $this->assertSame([], $errors);

        [, $errors] = BotMessages::check('registration_skip_button', str_repeat('я', BotMessages::MAX_BUTTON + 1));
        $this->assertStringContainsString('Слишком длинно', implode(' ', $errors));
    }
}
