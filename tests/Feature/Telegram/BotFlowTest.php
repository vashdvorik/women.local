<?php

namespace Tests\Feature\Telegram;

use App\Jobs\ComputeUserEmbedding;
use App\Jobs\NotifyOpportunity;
use App\Models\BotUser;
use App\Models\Opportunity;
use App\Services\ProfileModeration;
use App\Support\BotMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ChatType;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\User\User;
use Tests\TestCase;

/**
 * Бот и сайт отправляют участницам только тексты из resources/data/bot_messages.php, на языке её Telegram,
 * с учётом правок администратора. Здесь это проверяется на настоящих обработчиках: Nutgram в тестах —
 * подменённый бот, который записывает, что бы он отправил. Разные сценарии одного теста ведут разные
 * «участницы» (свой Telegram-id): диалог одной не мешает другой.
 */
class BotFlowTest extends TestCase
{
    use RefreshDatabase;

    private const ID = 5001;

    protected function setUp(): void
    {
        parent::setUp();

        // Ни аватар, ни расчёт эмбеддинга, ни рассылка не должны выходить в сеть.
        Http::fake();
        Queue::fake();
        config(['nutgram.token' => 'TESTTOKEN']);
    }

    /** Бот, который «слышит» участницу с заданным Telegram-id и языком Telegram. */
    private function bot(?string $lang = 'ru', int $id = self::ID): Nutgram
    {
        $bot = app(Nutgram::class);

        return $bot->setCommonUser(User::make(id: $id, is_bot: false, first_name: 'Ann', language_code: $lang))
            ->setCommonChat(Chat::make(id: $id, type: ChatType::PRIVATE));
    }

    /**
     * Тела сообщений, отправленных этой участнице (без id — всем), по порядку. Подменённый бот очищает журнал
     * при каждом новом «услышанном» обновлении, поэтому читать его нужно после каждого шага диалога.
     *
     * @return list<array<string, mixed>>
     */
    private function sent(Nutgram $bot, ?int $id = self::ID): array
    {
        $bodies = [];

        foreach ($bot->getRequestHistory() as $item) {
            $request = $item['request'] ?? array_values($item)[0];

            if ($request->getUri()->getPath() !== 'sendMessage') {
                continue;
            }

            $body = json_decode((string) $request->getBody(), true);

            if ($id === null || (int) ($body['chat_id'] ?? 0) === $id) {
                $bodies[] = $body;
            }
        }

        return $bodies;
    }

    private function participant(string $status = 'approved', ?string $locale = null, array $attributes = [], int $id = self::ID): BotUser
    {
        // «approved» в этих тестах — участница с Community: на Open часть функций бота закрыта (см. BotPlanTest).
        $state = $status === 'approved' ? 'community' : $status;

        return BotUser::factory()->{$state}()->create([
            'telegram_id' => $id,
            'full_name' => 'Анна Иванова',
            'locale' => $locale,
            ...$attributes,
        ]);
    }

    // ---------------------------------------------------------------- язык по Telegram

    public function test_a_new_participant_gets_the_welcome_in_her_telegram_language(): void
    {
        $i = 0;

        foreach (['ru' => 'ru', 'en' => 'en', 'ro' => 'ro', 'en-GB' => 'en', 'uk' => 'ru', 'de' => 'ru'] as $code => $expected) {
            $id = self::ID + ++$i;
            $bot = $this->bot($code, $id);
            $bot->hearText('/start')->reply();

            [$message] = $this->sent($bot, $id);

            $this->assertSame(BotMessages::default('registration_welcome', $expected), $message['text'], $code);
            $this->assertSame('HTML', $message['parse_mode'], 'тексты идут в HTML: разметку из админки Telegram разберёт');
            $this->assertSame(
                BotMessages::default('registration_start_button', $expected),
                $message['reply_markup']['inline_keyboard'][0][0]['text'],
                "{$code}: подпись кнопки на том же языке",
            );
        }
    }

    public function test_the_thank_you_message_says_what_happens_next_and_links_the_site_not_the_telegram_community(): void
    {
        // Раньше адрес сайта брался из TELEGRAM_COMMUNITY_URL, и участницам уходил чужой адрес по умолчанию.
        config(['app.url' => 'https://womenhub.example', 'nutgram.community_url' => 'https://t.me/some_community']);

        $bot = $this->bot('ru');
        $done = null;

        foreach ([
            fn (Nutgram $b) => $b->hearText('/start'),
            fn (Nutgram $b) => $b->hearCallbackQueryData('reg:yes'),
            fn (Nutgram $b) => $b->hearText('Ольга Иванова'),
            fn (Nutgram $b) => $b->hearText('Кондитерская'),
            fn (Nutgram $b) => $b->hearCallbackQueryData('reg:skip'),
        ] as $step) {
            $step($bot)->reply();
            $done = $this->sent($bot, null);
        }

        $message = end($done);

        // Что дальше: проверка, письмо от бота, меню; контакт команды вместо чужих адресов.
        $this->assertStringContainsString('Команда проекта проверит профиль вручную', $message['text']);
        $this->assertStringContainsString('«Кабинет» и «AI помощник»', $message['text']);
        $this->assertStringContainsString(config('site.contacts.telegram'), $message['text']);
        $this->assertStringNotContainsString('some_community', json_encode($message));
        // Под сообщением кнопка «Сайт платформы» на сайт, а не в чужое сообщество.
        $button = $message['reply_markup']['inline_keyboard'][0][0];
        $this->assertSame(BotMessages::default('registration_done_site_button', 'ru'), $button['text']);
        $this->assertSame('https://womenhub.example', $button['url']);
    }

    public function test_the_thank_you_message_has_no_site_button_in_a_local_http_setup(): void
    {
        config(['app.url' => 'http://localhost']);

        $bot = $this->bot('ru');
        foreach ([
            fn (Nutgram $b) => $b->hearText('/start'),
            fn (Nutgram $b) => $b->hearCallbackQueryData('reg:yes'),
            fn (Nutgram $b) => $b->hearText('Ольга Иванова'),
            fn (Nutgram $b) => $b->hearText('Кондитерская'),
            fn (Nutgram $b) => $b->hearCallbackQueryData('reg:skip'),
        ] as $step) {
            $step($bot)->reply();
        }

        $bodies = $this->sent($bot, null);
        $message = end($bodies);

        $this->assertStringContainsString('Заявка отправлена', $message['text']);
        $this->assertArrayNotHasKey('reply_markup', $message, 'Telegram отклонил бы адрес без https, а сообщение должно дойти');
    }

    public function test_the_registration_dialog_runs_in_her_language_and_remembers_it(): void
    {
        $bot = $this->bot('ro');
        $texts = [];

        // После каждого шага читаем, что бот ответил: журнал подменённого бота очищается на следующем шаге.
        foreach ([
            fn (Nutgram $b) => $b->hearText('/start'),
            fn (Nutgram $b) => $b->hearCallbackQueryData('reg:yes'),
            fn (Nutgram $b) => $b->hearText('Maria Popescu'),
            fn (Nutgram $b) => $b->hearText('Fabrică de dulciuri'),
            fn (Nutgram $b) => $b->hearCallbackQueryData('reg:skip'),
        ] as $step) {
            $step($bot)->reply();
            array_push($texts, ...array_column($this->sent($bot, null), 'text'));
        }

        $this->assertSame([
            BotMessages::default('registration_welcome', 'ro'),
            BotMessages::default('registration_ask_name', 'ro'),
            BotMessages::default('registration_ask_description', 'ro'),
            BotMessages::default('registration_ask_expectation', 'ro'),
            BotMessages::text('registration_done', 'ro', ['name' => 'Maria', 'contact' => config('site.contacts.telegram')]),
        ], $texts);

        // Язык запомнен в профиле: по нему потом идут решение модератора и рассылки.
        $profile = BotUser::where('telegram_id', self::ID)->firstOrFail();
        $this->assertSame('ro', $profile->locale);
        $this->assertTrue($profile->isPending());
        Queue::assertPushed(ComputeUserEmbedding::class);
    }

    public function test_a_pending_participant_is_told_the_application_is_under_review(): void
    {
        $this->participant('pending');

        $bot = $this->bot('en');
        $bot->hearText('hello?')->reply();

        $this->assertSame(BotMessages::text('status_pending', 'en', ['name' => 'Анна']), $this->sent($bot)[0]['text']);
        $this->assertSame('en', BotUser::where('telegram_id', self::ID)->first()->locale, 'язык запоминается при каждом обращении');
    }

    public function test_a_rejected_participant_is_told_access_is_closed(): void
    {
        $this->participant('pending', attributes: ['status' => BotUser::STATUS_REJECTED]);

        $bot = $this->bot('ro');
        $bot->hearText('/start')->reply();

        $this->assertSame(BotMessages::default('status_rejected', 'ro'), $this->sent($bot)[0]['text']);
    }

    public function test_login_gives_an_approved_participant_the_link_and_the_menu_in_her_language(): void
    {
        $this->participant('approved');

        $bot = $this->bot('ro');
        $bot->hearText('/login')->reply();

        [$message] = $this->sent($bot);

        $this->assertMatchesRegularExpression('#^Bună ziua, Анна! Accesați linkul de mai jos .*🔐 http\S+/go/[0-9a-f]{8}\n\n⏱ Linkul este valabil 24 de ore\.$#su', $message['text']);
        $this->assertSame(
            [[
                ['text' => BotMessages::default('menu_cabinet_button', 'ro')],
                ['text' => BotMessages::default('menu_help_button', 'ro')],
            ]],
            $message['reply_markup']['keyboard'],
        );
        $this->assertSame('ro', BotUser::where('telegram_id', self::ID)->first()->locale);
    }

    public function test_login_is_refused_to_everyone_else(): void
    {
        $bot = $this->bot('en');
        $bot->hearText('/login')->reply();

        $this->assertSame(BotMessages::default('login_not_approved', 'en'), $this->sent($bot)[0]['text']);
    }

    public function test_the_stored_language_is_used_when_telegram_does_not_send_one(): void
    {
        // У Telegram язык необязателен: он может отсутствовать или прийти пустым. Тогда берётся запомненный в профиле.
        foreach ([null, ''] as $n => $lang) {
            $id = self::ID + $n;
            $this->participant('approved', 'ro', id: $id);

            $bot = $this->bot($lang, $id);
            $bot->hearText('/login')->reply();

            $this->assertStringStartsWith('Bună ziua', $this->sent($bot, $id)[0]['text'], var_export($lang, true));
            $this->assertSame('ro', BotUser::where('telegram_id', $id)->first()->locale, 'пустой язык не затирает запомненный');
        }
    }

    // ---------------------------------------------------------------- кнопки меню

    public function test_menu_buttons_work_in_every_language_and_after_the_administrator_renames_them(): void
    {
        $captions = [];

        foreach (BotMessages::LOCALES as $locale) {
            $captions[] = BotMessages::default('menu_matches_button', $locale);
        }

        // Администратор переименовал кнопку: работают и новая подпись, и прежние (у части телефонов клавиатура старая).
        BotMessages::save('ru', ['menu_matches_button' => '🧭 Подобрать людей']);
        $captions[] = '🧭 Подобрать людей';

        foreach ($captions as $n => $caption) {
            $id = self::ID + 10 + $n;
            $this->participant('approved', 'ru', id: $id);

            $bot = $this->bot('ru', $id);
            $bot->hearText($caption)->reply();

            $this->assertSame(BotMessages::default('search_intro', 'ru'), $this->sent($bot, $id)[0]['text'], $caption);
        }
    }

    public function test_the_old_community_chat_button_gets_the_new_menu_in_her_language(): void
    {
        $this->participant('approved', 'en');

        $bot = $this->bot('en');
        $bot->hearText(BotMessages::default('menu_chat_button', 'en'))->reply();

        $message = $this->sent($bot)[0];

        $this->assertSame(BotMessages::text('menu_updated', 'en', ['name' => 'Анна']), $message['text']);
        $this->assertSame(BotMessages::default('menu_cabinet_button', 'en'), $message['reply_markup']['keyboard'][0][0]['text']);
        $this->assertSame(BotMessages::default('menu_help_button', 'en'), $message['reply_markup']['keyboard'][0][1]['text']);
    }

    public function test_an_unrelated_message_from_an_approved_participant_gets_no_answer(): void
    {
        $this->participant('approved');

        $bot = $this->bot('ru');
        $bot->hearText('просто текст')->reply();

        $this->assertSame([], $this->sent($bot));
    }

    public function test_the_start_guide_button_sends_the_guide_in_her_language(): void
    {
        $bot = $this->bot('en');
        $bot->hearCallbackQueryData('start_guide')->reply();

        $this->assertSame(BotMessages::default('guide_start', 'en'), $this->sent($bot)[0]['text']);
    }

    // ---------------------------------------------------------------- правки администратора

    public function test_administrator_edits_reach_the_bot_at_once(): void
    {
        BotMessages::save('ru', ['registration_welcome' => "Добро пожаловать!\n\n<b>Готовы?</b>"]);

        $bot = $this->bot('ru');
        $bot->hearText('/start')->reply();

        $message = $this->sent($bot)[0];

        $this->assertSame("Добро пожаловать!\n\n<b>Готовы?</b>", $message['text']);
        $this->assertSame('HTML', $message['parse_mode']);
    }

    // ---------------------------------------------------------------- решение по заявке (из админки)

    public function test_approval_reaches_the_participant_in_her_language_with_her_menu(): void
    {
        $profile = $this->participant('pending', 'ro');

        app(ProfileModeration::class)->approve($profile);

        [$approved, $prompt] = $this->sent(app(Nutgram::class));

        $this->assertSame(BotMessages::text('moderation_approved', 'ro', ['name' => 'Анна']), $approved['text']);
        $this->assertSame('HTML', $approved['parse_mode']);
        $this->assertSame(BotMessages::default('menu_cabinet_button', 'ro'), $approved['reply_markup']['keyboard'][0][0]['text']);
        $this->assertSame(BotMessages::default('menu_help_button', 'ro'), $approved['reply_markup']['keyboard'][0][1]['text']);

        $this->assertSame(BotMessages::default('moderation_approved_guide_prompt', 'ro'), $prompt['text']);
        $this->assertSame(
            ['text' => BotMessages::default('moderation_approved_guide_button', 'ro'), 'callback_data' => 'start_guide'],
            $prompt['reply_markup']['inline_keyboard'][0][0],
        );
    }

    public function test_approval_offers_a_one_tap_open_account_button_above_the_guide_button(): void
    {
        \Illuminate\Support\Facades\URL::forceRootUrl('https://womenhub.example');
        \Illuminate\Support\Facades\URL::forceScheme('https');

        app(ProfileModeration::class)->approve($this->participant('pending', 'ru'));

        [, $prompt] = $this->sent(app(Nutgram::class));
        $rows = $prompt['reply_markup']['inline_keyboard'];

        $this->assertSame(BotMessages::default('cabinet_open_button', 'ru'), $rows[0][0]['text']);
        $this->assertSame('https://womenhub.example/app/account/login', $rows[0][0]['web_app']['url'], 'мини-приложение: вход по подписи Telegram, без ссылки с токеном');
        $this->assertSame('start_guide', $rows[1][0]['callback_data']);
        $this->assertSame(['text' => BotMessages::default('help_ask_button', 'ru'), 'callback_data' => 'help_start'], $rows[2][0], 'и «Спросить помощника»');
        $this->assertCount(3, $rows);
        $this->assertStringContainsString('«Открыть кабинет»', $prompt['text'], 'в тексте названа та же кнопка');
    }

    public function test_without_https_the_open_account_button_is_left_out_but_the_approval_still_goes_out(): void
    {
        // Telegram принимает мини-приложение только по https; на локальной машине (http) иначе отклонил бы всё сообщение.
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost:8000');

        app(ProfileModeration::class)->approve($this->participant('pending', 'ru'));

        [$approved, $prompt] = $this->sent(app(Nutgram::class));

        $this->assertNotEmpty($approved['text']);
        $this->assertSame(['text' => BotMessages::default('moderation_approved_guide_button', 'ru'), 'callback_data' => 'start_guide'], $prompt['reply_markup']['inline_keyboard'][0][0]);
        $this->assertSame('help_start', $prompt['reply_markup']['inline_keyboard'][1][0]['callback_data']);
        $this->assertCount(2, $prompt['reply_markup']['inline_keyboard'], 'без кабинета: памятка и помощник');
    }

    public function test_the_start_guide_ends_with_the_open_account_button(): void
    {
        \Illuminate\Support\Facades\URL::forceRootUrl('https://womenhub.example');
        \Illuminate\Support\Facades\URL::forceScheme('https');

        $bot = $this->bot('en');
        $bot->hearCallbackQueryData('start_guide')->reply();

        $message = $this->sent($bot)[0];

        $this->assertSame(BotMessages::default('guide_start', 'en'), $message['text']);
        $this->assertSame('Open my account', $message['reply_markup']['inline_keyboard'][0][0]['text']);
        $this->assertSame('https://womenhub.example/app/account/login', $message['reply_markup']['inline_keyboard'][0][0]['web_app']['url']);
        $this->assertSame('help_start', $message['reply_markup']['inline_keyboard'][1][0]['callback_data'], 'ниже — «Спросить помощника»');
        $this->assertStringContainsString('Tap “Open my account” under this message', $message['text'], 'шаг 1 памятки называет эту кнопку');
    }

    public function test_the_guide_has_no_button_to_press_in_a_local_http_setup(): void
    {
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost:8000');

        $bot = $this->bot('en');
        $bot->hearCallbackQueryData('start_guide')->reply();

        // Мини-приложения без https нет, но «Спросить помощника» есть: callback-кнопке адрес не нужен.
        $rows = $this->sent($bot)[0]['reply_markup']['inline_keyboard'];
        $this->assertCount(1, $rows);
        $this->assertSame('help_start', $rows[0][0]['callback_data']);
    }

    public function test_rejection_and_revoking_access_use_their_own_texts(): void
    {
        $bot = app(Nutgram::class);

        app(ProfileModeration::class)->reject($this->participant('pending', 'en'));
        $this->assertSame(BotMessages::text('moderation_rejected', 'en', ['name' => 'Анна']), $this->sent($bot)[0]['text']);

        app(ProfileModeration::class)->reject($this->participant('approved', id: self::ID + 1));
        $revoked = $this->sent($bot, self::ID + 1)[0];

        $this->assertSame(BotMessages::default('moderation_access_revoked', 'ru'), $revoked['text']);
        $this->assertTrue($revoked['reply_markup']['remove_keyboard'], 'главное меню убирается');
    }

    public function test_the_participants_own_words_cannot_break_the_notification(): void
    {
        $profile = $this->participant('pending', attributes: ['full_name' => 'Анна<script> & Ко']);

        app(ProfileModeration::class)->approve($profile);

        $this->assertStringStartsWith('🎉 Анна&lt;script&gt;, ваша заявка', $this->sent(app(Nutgram::class))[0]['text']);
    }

    // ---------------------------------------------------------------- рассылка о публикации

    public function test_the_publication_broadcast_goes_out_in_each_recipients_language(): void
    {
        $author = BotUser::factory()->community()->create(['locale' => 'ru']);
        $ru = BotUser::factory()->community()->create(['locale' => 'ru']);
        $en = BotUser::factory()->community()->create(['locale' => 'en']);
        $unknown = BotUser::factory()->community()->create(['locale' => null]);
        BotUser::factory()->pending()->create(['locale' => 'en']);

        $post = Opportunity::create([
            'bot_user_id' => $author->id,
            'type' => 'project',
            'title' => 'Ищу <партнёров> & Ко',
            'body' => 'Нужны партнёры.',
            'event_date' => '2027-03-05',
            'location' => 'Кишинёв "центр"',
            'status' => Opportunity::STATUS_APPROVED,
        ]);

        (new NotifyOpportunity($post))->handle();

        $requests = collect(Http::recorded())->map(fn (array $pair) => $pair[0])
            ->filter(fn ($request) => str_ends_with($request->url(), '/botTESTTOKEN/sendMessage'))
            ->keyBy(fn ($request) => $request['chat_id']);

        // Получили трое: автор и ожидающая заявку — нет.
        $this->assertEqualsCanonicalizing([$ru->telegram_id, $en->telegram_id, $unknown->telegram_id], $requests->keys()->all());

        $russian = $requests[$ru->telegram_id];
        $english = $requests[$en->telegram_id];

        $this->assertSame('HTML', $russian['parse_mode']);
        $this->assertStringContainsString("🔔 <b>Новая публикация на платформе</b>\n\n💼 <b>Запрос:</b> Ищу &lt;партнёров&gt; &amp; Ко", $russian['text']);
        $this->assertStringContainsString("Нужны партнёры.\n\n📅 05.03.2027\n📍 Кишинёв &quot;центр&quot;\n\n👤 Опубликовала: ".BotMessages::escape($author->full_name), $russian['text']);
        $this->assertSame($russian['text'], $requests[$unknown->telegram_id]['text'], 'язык не известен — русский');

        $this->assertStringContainsString("🔔 <b>New post on the platform</b>\n\n💼 <b>Request:</b> Ищу &lt;партнёров&gt; &amp; Ко", $english['text']);
        $this->assertStringContainsString('👤 Published by:', $english['text']);

        $this->assertSame('Посмотреть в кабинете', json_decode($russian['reply_markup'], true)['inline_keyboard'][0][0]['text']);
        $this->assertSame('View in your account', json_decode($english['reply_markup'], true)['inline_keyboard'][0][0]['text']);
    }

    // ---------------------------------------------------------------- сообщение сайта в бот

    public function test_deleting_the_profile_on_the_site_tells_the_participant_in_her_language(): void
    {
        $user = $this->participant('approved', 'en');

        $this->withSession([
            'account_telegram_id' => $user->telegram_id,
            '_account_expires' => now()->addDays(7)->timestamp,
        ])->delete(route('account.profile.delete'))->assertRedirect();

        $sent = collect(Http::recorded())->map(fn (array $pair) => $pair[0])
            ->first(fn ($request) => str_ends_with($request->url(), '/sendMessage'));

        $this->assertNotNull($sent, 'сообщение ушло в Telegram');
        $this->assertSame(BotMessages::default('site_profile_deleted', 'en'), $sent['text']);
        $this->assertSame('HTML', $sent['parse_mode']);

        $markup = json_decode($sent['reply_markup'], true);
        $this->assertSame(BotMessages::default('site_profile_deleted_button', 'en'), $markup['inline_keyboard'][0][0]['text']);
        $this->assertSame('restart', $markup['inline_keyboard'][0][0]['callback_data']);
    }
}
