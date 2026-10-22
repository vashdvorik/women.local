<?php

namespace Tests\Feature\Telegram;

use App\Models\BotUser;
use App\Support\BotHelp;
use App\Support\BotMessages;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use SergiX44\Nutgram\Nutgram;
use Tests\TestCase;

/**
 * Чтобы человек не застревал после регистрации: напоминание о кабинете тем, кто так и не зашёл, обновление меню у всех
 * одобренных и правка инструкции помощника в админке. Telegram подменён: бот записывает, что бы он отправил.
 */
class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Queue::fake();
        config(['nutgram.token' => 'TESTTOKEN']);
        URL::forceRootUrl('https://womenhub.example');
        URL::forceScheme('https');
    }

    /** @return list<array<string, mixed>> все сообщения, которые ушли через бота, по порядку */
    private function sent(): array
    {
        $bodies = [];

        foreach (app(Nutgram::class)->getRequestHistory() as $item) {
            $request = $item['request'] ?? array_values($item)[0];

            if ($request->getUri()->getPath() === 'sendMessage') {
                $bodies[] = json_decode((string) $request->getBody(), true);
            }
        }

        return $bodies;
    }

    private function approvedDaysAgo(int $days, array $attributes = []): BotUser
    {
        return BotUser::factory()->approved()->create([
            'full_name' => 'Анна Иванова',
            'approved_at' => now()->subDays($days)->subHour(),
            ...$attributes,
        ]);
    }

    // ---------------------------------------------------------------- напоминание о кабинете

    public function test_the_reminder_goes_once_to_those_approved_a_while_ago_who_never_opened_the_cabinet(): void
    {
        $due = $this->approvedDaysAgo(3, ['telegram_id' => 8101, 'locale' => 'en']);
        $this->approvedDaysAgo(1, ['telegram_id' => 8102]);                                              // слишком рано
        $this->approvedDaysAgo(30, ['telegram_id' => 8103]);                                             // слишком давно
        $this->approvedDaysAgo(3, ['telegram_id' => 8104, 'cabinet_opened_at' => now()->subDay()]);      // уже заходила
        $this->approvedDaysAgo(3, ['telegram_id' => 8105, 'cabinet_reminded_at' => now()->subDay()]);    // уже напоминали
        BotUser::factory()->pending()->create(['telegram_id' => 8106, 'approved_at' => now()->subDays(3)]); // не одобрена

        $this->artisan('bot:remind-cabinet')->assertSuccessful();

        $messages = $this->sent();
        $this->assertCount(1, $messages);
        $this->assertSame(8101, (int) $messages[0]['chat_id']);
        $this->assertSame(BotMessages::text('cabinet_reminder', 'en', ['name' => 'Анна']), $messages[0]['text'], 'на её языке');

        $rows = $messages[0]['reply_markup']['inline_keyboard'];
        $this->assertSame('https://womenhub.example/app/account/login', $rows[0][0]['web_app']['url'], 'кабинет одним касанием');
        $this->assertSame(['text' => BotMessages::default('help_ask_button', 'en'), 'callback_data' => 'help_start'], $rows[1][0], 'и помощник');
        $this->assertNotNull($due->fresh()->cabinet_reminded_at);

        // Повторный запуск (на следующие сутки) тем же людям ничего не шлёт.
        $this->artisan('bot:remind-cabinet')->assertSuccessful();
        $this->assertCount(1, $this->sent());
    }

    public function test_the_dry_run_sends_nothing_and_does_not_mark_anyone(): void
    {
        $user = $this->approvedDaysAgo(3, ['telegram_id' => 8201]);

        $this->artisan('bot:remind-cabinet', ['--dry-run' => true])->expectsOutputToContain('Напомнили бы: 1')->assertSuccessful();

        $this->assertSame([], $this->sent());
        $this->assertNull($user->fresh()->cabinet_reminded_at);
    }

    public function test_the_reminder_is_scheduled_daily(): void
    {
        $events = collect(app(Schedule::class)->events())->filter(fn ($event) => str_contains((string) $event->command, 'bot:remind-cabinet'));

        $this->assertCount(1, $events);
        $this->assertSame('0 7 * * *', $events->first()->expression);
    }

    // ---------------------------------------------------------------- обновление меню

    public function test_refresh_menu_sends_the_new_keyboard_to_approved_members_only(): void
    {
        BotUser::factory()->community()->create(['telegram_id' => 8301, 'full_name' => 'Анна Иванова', 'locale' => 'ro']);
        BotUser::factory()->approved()->create(['telegram_id' => 8302, 'full_name' => 'Мария Петрова']);
        BotUser::factory()->pending()->create(['telegram_id' => 8303]);

        $this->artisan('bot:refresh-menu')->assertSuccessful();

        $messages = $this->sent();
        $this->assertEqualsCanonicalizing([8301, 8302], array_map(fn ($m) => (int) $m['chat_id'], $messages));

        $ro = collect($messages)->firstWhere('chat_id', 8301);
        $this->assertSame(BotMessages::text('menu_updated', 'ro', ['name' => 'Анна']), $ro['text']);
        $this->assertSame(BotMessages::default('menu_cabinet_button', 'ro'), $ro['reply_markup']['keyboard'][0][0]['text']);
        $this->assertSame(BotMessages::default('menu_help_button', 'ro'), $ro['reply_markup']['keyboard'][0][1]['text']);
    }

    public function test_refresh_menu_dry_run_only_counts(): void
    {
        BotUser::factory()->approved()->count(2)->create();

        $this->artisan('bot:refresh-menu', ['--dry-run' => true])->expectsOutputToContain('Получат сообщение: 2')->assertSuccessful();

        $this->assertSame([], $this->sent());
    }

    // ---------------------------------------------------------------- инструкция помощника в админке

    public function test_the_bot_assistant_tab_shows_the_instruction_and_saves_an_edit(): void
    {
        $this->actingAsAdmin()->get(route('admin.cabinets.settings', ['tab' => 'bot-help']))
            ->assertOk()->assertSee('Помощник бота')->assertSee('КАК ВОЙТИ В КАБИНЕТ')->assertSee('{community_price}');

        $this->actingAsAdmin()->put(route('admin.cabinets.settings.bot-help'), ['texts' => ['ru' => 'Новая инструкция. Контакт: {contact}', 'en' => '', 'ro' => '']])
            ->assertRedirect(route('admin.cabinets.settings', ['tab' => 'bot-help']))->assertSessionHas('success');

        $this->assertSame('Новая инструкция. Контакт: {contact}', BotHelp::template('ru'));
        $this->assertTrue(BotHelp::isCustomized('ru'));
        $this->assertFalse(BotHelp::isCustomized('en'));
        $this->actingAsAdmin()->get(route('admin.cabinets.settings', ['tab' => 'bot-help']))->assertSee('Вернуть исходный: Русский')->assertSee('Изменено');
    }

    public function test_the_original_instruction_can_be_restored_for_one_language(): void
    {
        BotHelp::save(['ru' => 'Правка ru', 'en' => 'Edit en']);

        $this->actingAsAdmin()->post(route('admin.cabinets.settings.bot-help.reset'), ['locale' => 'ru'])->assertRedirect()->assertSessionHas('success');

        $this->assertFalse(BotHelp::isCustomized('ru'));
        $this->assertSame(BotHelp::default('ru'), BotHelp::template('ru'));
        $this->assertSame('Edit en', BotHelp::template('en'), 'другой язык не тронут');
    }

    public function test_a_too_long_instruction_is_refused(): void
    {
        $this->actingAsAdmin()->put(route('admin.cabinets.settings.bot-help'), ['texts' => ['ru' => str_repeat('а', BotHelp::MAX_LENGTH + 1)]])
            ->assertSessionHasErrors('texts.ru');

        $this->assertFalse(BotHelp::isCustomized('ru'));
    }

    public function test_only_the_admin_can_change_the_instruction(): void
    {
        $this->put(route('admin.cabinets.settings.bot-help'), ['texts' => ['ru' => 'x']])->assertRedirect();

        $this->assertFalse(BotHelp::isCustomized('ru'));
    }
}
