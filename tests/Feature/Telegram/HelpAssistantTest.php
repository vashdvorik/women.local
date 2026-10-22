<?php

namespace Tests\Feature\Telegram;

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Models\SiteSetting;
use App\Services\AiAssistantService;
use App\Services\BotHelpAssistant;
use App\Support\BotHelp;
use App\Support\BotMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ChatType;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\User\User;
use Tests\TestCase;

/**
 * Главное меню бота («Кабинет» и «AI помощник») и разговор с помощником, который объясняет, как пользоваться ботом и
 * кабинетом. Провайдер ИИ подменён: настоящих запросов тесты не делают.
 */
class HelpAssistantTest extends TestCase
{
    use RefreshDatabase;

    private const ID = 7001;

    /** @var list<array{system: string, message: string, history: list<array{role: string, content: string}>}> */
    private array $aiCalls = [];

    /** Что ответит «модель»; исключение вместо текста — сбой провайдера. */
    private string|RuntimeException $aiReply = 'Нажмите кнопку «Кабинет» внизу экрана.';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Queue::fake();
        config(['nutgram.token' => 'TESTTOKEN', 'site.contacts.telegram' => '@team_test']);

        $this->aiCalls = [];
        $calls = &$this->aiCalls;
        $reply = &$this->aiReply;

        $this->app->instance(AiAssistantService::class, new class($calls, $reply) extends AiAssistantService
        {
            public function __construct(private array &$calls, private string|RuntimeException &$reply) {}

            public function chat(string $system, string $message, array $history): string
            {
                $this->calls[] = ['system' => $system, 'message' => $message, 'history' => $history];

                if ($this->reply instanceof RuntimeException) {
                    throw $this->reply;
                }

                return $this->reply;
            }
        });
    }

    private function bot(string $lang = 'ru', int $id = self::ID): Nutgram
    {
        return app(Nutgram::class)
            ->setCommonUser(User::make(id: $id, is_bot: false, first_name: 'Ann', language_code: $lang))
            ->setCommonChat(Chat::make(id: $id, type: ChatType::PRIVATE));
    }

    /** @return list<array<string, mixed>> */
    private function sent(Nutgram $bot, int $id = self::ID): array
    {
        $bodies = [];

        foreach ($bot->getRequestHistory() as $item) {
            $request = $item['request'] ?? array_values($item)[0];

            if ($request->getUri()->getPath() !== 'sendMessage') {
                continue;
            }

            $body = json_decode((string) $request->getBody(), true);

            if ((int) ($body['chat_id'] ?? 0) === $id) {
                $bodies[] = $body;
            }
        }

        return $bodies;
    }

    private function member(array $attributes = [], string $state = 'community'): BotUser
    {
        return BotUser::factory()->{$state}()->create(['telegram_id' => self::ID, 'full_name' => 'Анна Иванова', ...$attributes]);
    }

    private function openHelp(Nutgram $bot, string $caption = null): void
    {
        $bot->hearText($caption ?? BotMessages::default('menu_help_button', 'ru'))->reply();
    }

    // ---------------------------------------------------------------- меню

    public function test_the_main_menu_has_the_cabinet_that_opens_in_telegram_and_the_assistant(): void
    {
        URL::forceRootUrl('https://womenhub.example');
        URL::forceScheme('https');
        $this->member();

        $bot = $this->bot();
        $bot->hearText('/login')->reply();

        $keyboard = $this->sent($bot)[0]['reply_markup']['keyboard'];

        $this->assertCount(1, $keyboard, 'одна строка: две кнопки');
        $this->assertSame(BotMessages::default('menu_cabinet_button', 'ru'), $keyboard[0][0]['text']);
        $this->assertSame('https://womenhub.example/app/account/login', $keyboard[0][0]['web_app']['url'], 'кабинет открывается прямо в Telegram');
        $this->assertSame(['text' => BotMessages::default('menu_help_button', 'ru')], $keyboard[0][1]);
        $this->assertStringNotContainsString('Найти контакты', json_encode($keyboard, JSON_UNESCAPED_UNICODE));
        $this->assertStringNotContainsString('Чат сообщества', json_encode($keyboard, JSON_UNESCAPED_UNICODE));
    }

    public function test_without_https_the_cabinet_button_is_a_plain_button_that_sends_the_login_link(): void
    {
        URL::forceRootUrl('http://localhost:8000');
        $this->member();

        $bot = $this->bot();
        $bot->hearText('/login')->reply();
        $this->assertArrayNotHasKey('web_app', $this->sent($bot)[0]['reply_markup']['keyboard'][0][0]);

        // Нажатие обычной кнопки «Кабинет» — ссылка для входа.
        $bot->hearText(BotMessages::default('menu_cabinet_button', 'ru'))->reply();
        $this->assertMatchesRegularExpression('#/go/[0-9a-f]{8}#', $this->sent($bot)[0]['text']);
    }

    // ---------------------------------------------------------------- разговор с помощником

    public function test_the_assistant_button_starts_a_conversation_with_a_greeting_and_a_closing_button(): void
    {
        $this->member();

        $bot = $this->bot();
        $this->openHelp($bot);

        $message = $this->sent($bot)[0];

        $this->assertSame(BotMessages::text('help_intro', 'ru', ['name' => 'Анна', 'contact' => '@team_test']), $message['text']);
        $this->assertStringContainsString('@team_test', $message['text']);
        $keyboard = $message['reply_markup']['keyboard'][0];
        $this->assertSame(BotMessages::default('menu_cabinet_button', 'ru'), $keyboard[0]['text'], 'кабинет остаётся под рукой');
        $this->assertSame(BotMessages::default('help_exit_button', 'ru'), $keyboard[1]['text']);
        $this->assertSame([], $this->aiCalls, 'приветствие ничего не спрашивает у ИИ');
    }

    public function test_a_question_is_answered_by_the_assistant_with_the_instruction_and_member_facts(): void
    {
        $this->member(['cabinet_opened_at' => null, 'description' => 'Кондитерская']);

        $bot = $this->bot();
        $this->openHelp($bot);
        $bot->hearText('Как войти в кабинет?')->reply();

        $reply = $this->sent($bot)[0];
        $this->assertSame('Нажмите кнопку «Кабинет» внизу экрана.', $reply['text']);
        $this->assertArrayNotHasKey('parse_mode', $reply, 'ответ модели уходит обычным текстом');

        $this->assertCount(1, $this->aiCalls);
        $call = $this->aiCalls[0];
        $this->assertSame('Как войти в кабинет?', $call['message']);
        $this->assertStringContainsString('Reply only in ru', $call['system']);
        $this->assertStringContainsString('КАК ВОЙТИ В КАБИНЕТ', $call['system'], 'знания берутся из инструкции');
        $this->assertStringContainsString('@team_test', $call['system'], 'контакт команды подставлен');
        $this->assertStringNotContainsString('{contact}', $call['system']);
        $this->assertStringNotContainsString('{community_price}', $call['system']);
        $this->assertStringContainsString('"plan":"Community"', $call['system']);
        $this->assertStringContainsString('"has_opened_the_cabinet":false', $call['system']);
    }

    public function test_the_conversation_keeps_a_short_history_and_markdown_is_stripped(): void
    {
        $this->member();
        $bot = $this->bot();
        $this->openHelp($bot);

        $this->aiReply = "**Шаг 1.** Нажмите `Кабинет`.\n\n* Это просто\n* Это быстро";
        $bot->hearText('Первый вопрос')->reply();
        $this->assertSame("Шаг 1. Нажмите Кабинет.\n\n— Это просто\n— Это быстро", $this->sent($bot)[0]['text']);

        $this->aiReply = 'Второй ответ';
        $bot->hearText('Второй вопрос')->reply();

        $this->assertSame(
            [
                ['role' => 'user', 'content' => 'Первый вопрос'],
                ['role' => 'assistant', 'content' => "Шаг 1. Нажмите Кабинет.\n\n— Это просто\n— Это быстро"],
            ],
            $this->aiCalls[1]['history'],
            'второй вопрос уходит модели вместе с первым обменом',
        );
    }

    public function test_the_system_prompt_speaks_the_members_language_and_uses_that_languages_instruction(): void
    {
        $this->member(['locale' => 'en']);
        $bot = $this->bot('en');
        $this->openHelp($bot, BotMessages::default('menu_help_button', 'en'));
        $this->assertSame(BotMessages::text('help_intro', 'en', ['name' => 'Анна', 'contact' => '@team_test']), $this->sent($bot)[0]['text']);

        $bot->hearText('How do I sign in?')->reply();

        $this->assertStringContainsString('Reply only in en', $this->aiCalls[0]['system']);
        $this->assertStringContainsString('HOW TO ENTER THE ACCOUNT', $this->aiCalls[0]['system']);
    }

    public function test_the_close_button_ends_the_conversation_and_brings_back_the_main_menu(): void
    {
        $this->member();
        $bot = $this->bot();
        $this->openHelp($bot);

        $bot->hearText(BotMessages::default('help_exit_button', 'ru'))->reply();

        $message = $this->sent($bot)[0];
        $this->assertSame(BotMessages::default('help_exit', 'ru'), $message['text']);
        $this->assertSame(BotMessages::default('menu_help_button', 'ru'), $message['reply_markup']['keyboard'][0][1]['text']);

        // Разговор закончился: обычный текст больше не уходит в ИИ, а получает подсказку с кнопкой «Спросить помощника».
        $bot->hearText('Вопрос после закрытия')->reply();
        $after = $this->sent($bot);
        $this->assertCount(1, $after);
        $this->assertStringContainsString('Этот бот не ведёт переписку', $after[0]['text']);
        $this->assertContains('help_start', array_column(array_merge(...$after[0]['reply_markup']['inline_keyboard']), 'callback_data'));
        $this->assertSame([], $this->aiCalls);
    }

    public function test_a_command_in_the_middle_of_the_conversation_leaves_it_and_works_as_usual(): void
    {
        $this->member();
        $bot = $this->bot();
        $this->openHelp($bot);

        $bot->hearText('/login')->reply();

        $this->assertMatchesRegularExpression('#/go/[0-9a-f]{8}#', $this->sent($bot)[0]['text'], 'ссылка для входа, а не вопрос помощнику');
        $this->assertSame([], $this->aiCalls);

        $bot->hearText('Ещё вопрос')->reply();
        $this->assertSame([], $this->aiCalls, 'после команды разговор закончен');
    }

    public function test_the_plain_cabinet_button_in_the_middle_of_the_conversation_sends_the_login_link(): void
    {
        URL::forceRootUrl('http://localhost:8000');
        $this->member();
        $bot = $this->bot();
        $this->openHelp($bot);

        $bot->hearText(BotMessages::default('menu_cabinet_button', 'ru'))->reply();

        $this->assertMatchesRegularExpression('#/go/[0-9a-f]{8}#', $this->sent($bot)[0]['text']);
        $this->assertSame([], $this->aiCalls);
    }

    public function test_the_ask_the_assistant_button_under_bot_messages_starts_the_conversation(): void
    {
        $this->member();
        $bot = $this->bot();

        $bot->hearCallbackQueryData('help_start')->reply();

        $this->assertSame(BotMessages::text('help_intro', 'ru', ['name' => 'Анна', 'contact' => '@team_test']), $this->sent($bot)[0]['text']);
    }

    public function test_someone_whose_application_is_not_approved_does_not_get_the_assistant(): void
    {
        $this->member(state: 'pending');
        $bot = $this->bot();

        $bot->hearCallbackQueryData('help_start')->reply();

        $this->assertSame(BotMessages::text('status_pending', 'ru', ['name' => 'Анна']), $this->sent($bot)[0]['text']);
        $bot->hearText('Привет, помогите')->reply();
        $this->assertSame([], $this->aiCalls, 'ИИ не тратится на тех, кто ещё не одобрен');
    }

    public function test_the_open_plan_member_gets_the_assistant_too(): void
    {
        $this->member(state: 'approved');
        $bot = $this->bot();
        $this->openHelp($bot);

        $bot->hearText('Что даёт Community?')->reply();

        $this->assertCount(1, $this->aiCalls);
        $this->assertStringContainsString('"plan":"Open"', $this->aiCalls[0]['system']);
    }

    // ---------------------------------------------------------------- сбои и лимит

    public function test_when_the_provider_fails_she_is_told_so_and_the_failed_question_is_not_counted(): void
    {
        $user = $this->member();
        $bot = $this->bot();
        $this->openHelp($bot);

        $this->aiReply = new RuntimeException('provider down');
        $bot->hearText('Как войти?')->reply();

        $this->assertSame(BotMessages::text('help_unavailable', 'ru', ['contact' => '@team_test']), $this->sent($bot)[0]['text']);
        $this->assertSame(BotHelpAssistant::DAILY_LIMIT, app(BotHelpAssistant::class)->remaining($user), 'сбой лимит не тратит');

        // Разговор не оборвался: когда провайдер вернулся, следующий вопрос получает ответ.
        $this->aiReply = 'Готово';
        $bot->hearText('Ещё раз')->reply();
        $this->assertSame('Готово', $this->sent($bot)[0]['text']);
        $this->assertSame(BotHelpAssistant::DAILY_LIMIT - 1, app(BotHelpAssistant::class)->remaining($user));
    }

    public function test_the_daily_limit_stops_the_questions_politely(): void
    {
        $user = $this->member();
        $bot = $this->bot();
        $this->openHelp($bot);
        Cache::put('bot-help:'.$user->id.':'.now()->format('Y-m-d'), BotHelpAssistant::DAILY_LIMIT, now()->endOfDay());

        $bot->hearText('Ещё один вопрос')->reply();

        $this->assertSame(
            BotMessages::text('help_limit', 'ru', ['limit' => BotHelpAssistant::DAILY_LIMIT, 'contact' => '@team_test']),
            $this->sent($bot)[0]['text'],
        );
        $this->assertSame([], $this->aiCalls);
    }

    // ---------------------------------------------------------------- инструкция из админки

    public function test_the_instruction_defaults_to_the_reference_and_the_admin_edit_overrides_it_per_language(): void
    {
        $this->assertSame(BotHelp::default('ru'), BotHelp::template('ru'));
        $this->assertFalse(BotHelp::isCustomized('ru'));

        BotHelp::save(['ru' => 'Свой текст. Контакт: {contact}', 'en' => '', 'ro' => '']);

        $this->assertSame('Свой текст. Контакт: {contact}', BotHelp::template('ru'));
        $this->assertSame('Свой текст. Контакт: @team_test', BotHelp::render('ru'));
        $this->assertSame(BotHelp::default('en'), BotHelp::template('en'), 'другие языки остаются эталонными');

        // Совпавший с эталоном текст — это возврат к эталону, ничего не хранится.
        BotHelp::save(['ru' => BotHelp::default('ru')]);
        $this->assertFalse(BotHelp::isCustomized('ru'));
    }

    public function test_the_assistant_reads_the_admins_instruction_and_fresh_prices(): void
    {
        SiteSetting::write('subscription_prices', ['community' => 777, 'private' => 1999]);
        BotHelp::save(['ru' => 'Community стоит {community_price}, Private — {private_price}.']);
        $this->member();
        $bot = $this->bot();
        $this->openHelp($bot);

        $bot->hearText('Сколько стоит?')->reply();

        $system = $this->aiCalls[0]['system'];
        $this->assertStringContainsString('Community стоит 777', $system);
        $this->assertStringContainsString(\App\Enums\Plan::Private->priceLabel('ru'), $system);
        $this->assertStringNotContainsString('КАК ВОЙТИ В КАБИНЕТ', $system, 'эталон заменён правкой');
    }

    public function test_the_reference_instruction_is_complete_in_every_language_and_has_no_unknown_variables(): void
    {
        foreach (BotMessages::LOCALES as $locale) {
            $text = BotHelp::default($locale);

            $this->assertGreaterThan(1500, mb_strlen($text), $locale);
            $this->assertLessThanOrEqual(BotHelp::MAX_LENGTH, mb_strlen($text), $locale);
            preg_match_all('/\{([a-z_]+)\}/', $text, $matches);
            $this->assertEmpty(array_diff($matches[1], ['site_url', 'community_price', 'private_price', 'contact']), $locale.': только известные переменные');
            $this->assertStringContainsString('/login', $text, $locale);
            $this->assertStringContainsString('{contact}', $text, $locale);
            $this->assertStringNotContainsString('Найти контакты', $text, $locale.': кнопки, которой уже нет, в инструкции нет');
        }
    }

    // ---------------------------------------------------------------- вход в кабинет запоминается

    public function test_the_first_cabinet_sign_in_is_remembered_by_both_ways_in(): void
    {
        // Вход по ссылке из бота.
        $byLink = BotUser::factory()->community()->create(['telegram_id' => 7101]);
        $token = LoginToken::generateFor(7101);
        $this->get(route('account.auth', ['token' => $token->token]))->assertRedirect(route('account.index'));
        $this->assertNotNull($byLink->fresh()->cabinet_opened_at);

        // Вход мини-приложением: подпись Telegram по настоящему алгоритму.
        $inApp = BotUser::factory()->community()->create(['telegram_id' => 7102]);
        $params = ['auth_date' => (string) time(), 'user' => json_encode(['id' => 7102])];
        ksort($params);
        $check = implode("\n", array_map(fn ($k, $v) => "{$k}={$v}", array_keys($params), array_values($params)));
        $hash = hash_hmac('sha256', $check, hash_hmac('sha256', 'TESTTOKEN', 'WebAppData', true));

        $this->postJson(route('account.tma-auth'), ['init_data' => http_build_query($params + ['hash' => $hash])])->assertOk()->assertJson(['ok' => true]);
        $this->assertNotNull($inApp->fresh()->cabinet_opened_at);

        // Второй вход время первого не затирает.
        $first = $inApp->fresh()->cabinet_opened_at;
        $this->travel(2)->days();
        $inApp->fresh()->markCabinetOpened();
        $this->assertTrue($first->equalTo($inApp->fresh()->cabinet_opened_at));
    }
}
