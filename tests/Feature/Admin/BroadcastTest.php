<?php

namespace Tests\Feature\Admin;

use App\Jobs\SendBroadcast;
use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Models\BotUser;
use App\Models\SiteSetting;
use App\Services\Broadcasts\BroadcastLauncher;
use App\Support\BroadcastText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * «Отправить всем сообщение»: рассылка участницам в Telegram из админки (текст на трёх языках, кнопки-ссылки, фото,
 * выбор получателей, проба, расписание, отчёт). Telegram везде подменён: настоящих сообщений тесты не шлют.
 */
class BroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('originals');
        config(['nutgram.token' => 'TEST-TOKEN']);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'action' => 'draft',
            'audience' => 'approved',
            'texts' => ['ru' => 'Приходите на встречу в пятницу!', 'en' => 'Join us on Friday!', 'ro' => ''],
            'buttons' => [['url' => 'https://example.test/register', 'label' => ['ru' => 'Зарегистрироваться', 'en' => 'Register', 'ro' => '']]],
        ], $overrides);
    }

    /** Telegram отвечает «дошло» всем; возвращает, что именно ушло. */
    private function fakeTelegram(array $responses = []): void
    {
        Http::fake($responses + ['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);
    }

    /** @return list<Request> запросы к Telegram по порядку */
    private function sent(): array
    {
        return Http::recorded()->map(fn (array $pair) => $pair[0])->values()->all();
    }

    private function body(Request $request): array
    {
        return $request->isMultipart() ? collect($request->data())->mapWithKeys(fn ($part) => [$part['name'] => $part['contents']])->all() : $request->data();
    }

    private function submit(array $payload, ?Broadcast $broadcast = null)
    {
        $admin = $this->actingAsAdmin();

        return $broadcast
            ? $admin->put(route('admin.broadcasts.update', $broadcast), $payload)
            : $admin->post(route('admin.broadcasts.store'), $payload);
    }

    // ---------------------------------------------------------------- страницы

    public function test_pages_render_and_the_menu_item_lives_in_the_cabinets_camp(): void
    {
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'draft']);

        $this->actingAsAdmin()->get(route('admin.broadcasts.index'))->assertOk()->assertSee('Новая рассылка')->assertSee('Рассылка');
        $this->actingAsAdmin()->get(route('admin.broadcasts.create'))->assertOk()->assertSee('Кому отправить')->assertSee('Кнопка 1')->assertSee('Отправить мне для проверки')->assertSee('Зарегистрироваться');
        $this->actingAsAdmin()->get(route('admin.broadcasts.edit', $broadcast))->assertOk();
        $this->actingAsAdmin()->get(route('admin.broadcasts.show', $broadcast))->assertOk();

        $this->actingAsAdmin()->get(route('admin.profiles.index'))->assertSee(route('admin.broadcasts.index'), false);
        // Раздел относится к лагерю «Кабинеты участниц»: пункт меню виден на его страницах, а не на страницах сайта.
        $this->actingAsAdmin()->get(route('admin.broadcasts.index'))->assertSee(route('admin.statistics.index'), false);
        $this->actingAsAdmin()->get(route('admin.events.index'))->assertDontSee(route('admin.broadcasts.index'), false);
    }

    public function test_guests_cannot_open_the_pages(): void
    {
        $this->get(route('admin.broadcasts.index'))->assertRedirect();
        $this->post(route('admin.broadcasts.store'), $this->payload())->assertRedirect();
    }

    public function test_the_form_shows_how_many_will_receive_the_message(): void
    {
        BotUser::factory()->active()->count(2)->create();
        BotUser::factory()->approved()->create();
        BotUser::factory()->pending()->create();

        $html = $this->actingAsAdmin()->get(route('admin.broadcasts.create'))->assertOk()->getContent();

        $this->assertStringContainsString('получат: 3', $html, 'все одобренные');
        $this->assertStringContainsString('получат: 2', $html, 'платные');
        $this->assertStringContainsString('получат: 1', $html, 'бесплатные');
    }

    // ---------------------------------------------------------------- проверки формы

    public function test_the_russian_text_is_required(): void
    {
        $this->submit($this->payload(['texts' => ['ru' => '']]))->assertSessionHasErrors('texts.ru');
        $this->assertSame(0, Broadcast::count());
    }

    public function test_button_addresses_must_be_https_and_need_a_russian_label(): void
    {
        $this->submit($this->payload(['buttons' => [['url' => 'http://example.test/x']]]))->assertSessionHasErrors('buttons.0.url');
        $this->submit($this->payload(['buttons' => [['url' => 'javascript:alert(1)']]]))->assertSessionHasErrors('buttons.0.url');
        $this->submit($this->payload(['buttons' => [['url' => 'https://example.test/x', 'label' => ['ru' => '']]]]))->assertSessionHasErrors('buttons.0.label.ru');
        $this->assertSame(0, Broadcast::count());
    }

    public function test_no_more_than_three_buttons(): void
    {
        $four = array_fill(0, 4, ['url' => 'https://example.test/x', 'label' => ['ru' => 'Кнопка']]);

        $this->submit($this->payload(['buttons' => $four]))->assertSessionHasErrors('buttons');
    }

    public function test_empty_button_rows_are_ignored_and_the_broadcast_saves_as_a_draft(): void
    {
        $this->submit($this->payload(['buttons' => [
            ['url' => 'https://example.test/register', 'label' => ['ru' => 'Зарегистрироваться', 'en' => 'Register']],
            ['url' => '', 'label' => ['ru' => '']],
        ]]))->assertRedirect();

        $broadcast = Broadcast::firstOrFail();
        $this->assertSame('draft', $broadcast->status);
        $this->assertSame('Приходите на встречу в пятницу!', $broadcast->texts['ru']);
        $this->assertCount(1, $broadcast->buttons);
        $this->assertSame('Register', $broadcast->buttons[0]['label']['en']);
        $this->assertSame([], $this->sent(), 'черновик ничего не отправляет');
    }

    public function test_with_a_photo_the_text_must_fit_a_telegram_caption(): void
    {
        $this->submit($this->payload([
            'texts' => ['ru' => str_repeat('а', BroadcastText::MAX_CAPTION + 1)],
            'photo' => UploadedFile::fake()->image('afisha.jpg'),
        ]))->assertSessionHasErrors('texts.ru');

        // Без фото такой же текст допустим.
        $this->submit($this->payload(['texts' => ['ru' => str_repeat('а', BroadcastText::MAX_CAPTION + 1)]]))->assertSessionDoesntHaveErrors();
    }

    public function test_the_schedule_must_be_in_the_future(): void
    {
        $past = now(Broadcast::TIMEZONE)->subHour()->format('Y-m-d\TH:i');

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'later', 'scheduled_at' => $past]))->assertSessionHasErrors('scheduled_at');
        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'later']))->assertSessionHasErrors('scheduled_at');
        $this->assertSame(0, Broadcast::count());
    }

    // ---------------------------------------------------------------- проба

    public function test_the_test_message_goes_to_the_given_chat_once_per_filled_language_with_its_own_button_label(): void
    {
        $this->fakeTelegram();

        $this->submit($this->payload(['action' => 'test', 'test_chat' => '123456789', 'buttons' => [['url' => 'https://example.test/register', 'label' => ['ru' => 'Зарегистрироваться', 'en' => 'Register']]]]))
            ->assertRedirect()->assertSessionHas('success');

        $requests = $this->sent();
        $this->assertCount(2, $requests, 'русский и английский заполнены, румынский нет');

        $ru = $this->body($requests[0]);
        $this->assertStringEndsWith('/botTEST-TOKEN/sendMessage', $requests[0]->url());
        $this->assertSame('123456789', (string) $ru['chat_id']);
        $this->assertSame('Приходите на встречу в пятницу!', $ru['text']);
        $this->assertSame('HTML', $ru['parse_mode']);
        $this->assertSame(
            ['inline_keyboard' => [[['text' => 'Зарегистрироваться', 'url' => 'https://example.test/register']]]],
            json_decode($ru['reply_markup'], true),
        );
        $en = $this->body($requests[1]);
        $this->assertSame('Join us on Friday!', $en['text']);
        $this->assertSame('Register', json_decode($en['reply_markup'], true)['inline_keyboard'][0][0]['text']);

        $this->assertSame('123456789', SiteSetting::read('broadcast_test_chat'), 'ID запоминается');
        $this->assertSame('draft', Broadcast::firstOrFail()->status);
    }

    public function test_the_test_needs_a_telegram_id_and_reports_a_telegram_error(): void
    {
        $this->submit($this->payload(['action' => 'test']))->assertSessionHasErrors('test_chat');
        $this->submit($this->payload(['action' => 'test', 'test_chat' => 'abc']))->assertSessionHasErrors('test_chat');

        $this->fakeTelegram(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 403, 'description' => 'Forbidden: bot was blocked by the user'], 403)]);

        $response = $this->submit($this->payload(['action' => 'test', 'test_chat' => '111111']))->assertRedirect()->assertSessionHas('error');
        $this->assertStringContainsString('blocked', session('error'));
    }

    // ---------------------------------------------------------------- отправка

    public function test_send_now_delivers_to_the_chosen_audience_in_each_members_language(): void
    {
        $this->fakeTelegram();
        $ru = BotUser::factory()->active()->create(['locale' => 'ru']);
        $en = BotUser::factory()->private()->create(['locale' => 'en']);
        $ro = BotUser::factory()->active()->create(['locale' => 'ro']);   // перевода на румынский нет: придёт русский
        $open = BotUser::factory()->approved()->create(['locale' => 'ru']);
        BotUser::factory()->pending()->create();                              // не одобрена: не получит

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'now', 'audience' => 'members']))->assertRedirect();

        $broadcast = Broadcast::firstOrFail();
        $this->assertSame('sent', $broadcast->status);
        $this->assertSame([3, 3, 0, 0], [$broadcast->total, $broadcast->sent, $broadcast->failed, $broadcast->blocked]);
        $this->assertNotNull($broadcast->finished_at);

        $byChat = collect($this->sent())->mapWithKeys(fn (Request $r) => [(string) $this->body($r)['chat_id'] => $this->body($r)]);
        $this->assertEqualsCanonicalizing([(string) $ru->telegram_id, (string) $en->telegram_id, (string) $ro->telegram_id], $byChat->keys()->all());
        $this->assertArrayNotHasKey((string) $open->telegram_id, $byChat->all(), 'бесплатный тариф не в выбранной группе');
        $this->assertSame('Приходите на встречу в пятницу!', $byChat[(string) $ru->telegram_id]['text']);
        $this->assertSame('Join us on Friday!', $byChat[(string) $en->telegram_id]['text']);
        $this->assertSame('Приходите на встречу в пятницу!', $byChat[(string) $ro->telegram_id]['text']);
        $this->assertSame('Register', json_decode($byChat[(string) $en->telegram_id]['reply_markup'], true)['inline_keyboard'][0][0]['text']);
        $this->assertSame('Зарегистрироваться', json_decode($byChat[(string) $ro->telegram_id]['reply_markup'], true)['inline_keyboard'][0][0]['text']);
    }

    public function test_the_three_audiences_pick_the_right_people(): void
    {
        $paid = BotUser::factory()->active()->create();
        $private = BotUser::factory()->private()->create();
        $open = BotUser::factory()->approved()->create();
        $pending = BotUser::factory()->pending()->create();

        $ids = fn (string $audience) => Broadcast::audienceQuery($audience)->pluck('bot_users.id')->all();

        $this->assertEqualsCanonicalizing([$paid->id, $private->id, $open->id], $ids('approved'));
        $this->assertEqualsCanonicalizing([$paid->id, $private->id], $ids('members'));
        $this->assertSame([$open->id], $ids('open'));
        $this->assertNotContains($pending->id, $ids('approved'));
    }

    public function test_a_member_who_blocked_the_bot_is_marked_and_the_rest_still_get_the_message(): void
    {
        $blocked = BotUser::factory()->active()->create();
        $fine = BotUser::factory()->active()->create();
        Http::fake([
            'api.telegram.org/*' => fn (Request $r) => (string) $this->body($r)['chat_id'] === (string) $blocked->telegram_id
                ? Http::response(['ok' => false, 'error_code' => 403, 'description' => 'Forbidden: bot was blocked by the user'], 403)
                : Http::response(['ok' => true, 'result' => []]),
        ]);

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'now']))->assertRedirect();

        $broadcast = Broadcast::firstOrFail();
        $this->assertSame([2, 1, 0, 1], [$broadcast->total, $broadcast->sent, $broadcast->failed, $broadcast->blocked]);
        $this->assertSame('blocked', BroadcastDelivery::where('bot_user_id', $blocked->id)->firstOrFail()->status);
        $this->assertStringContainsString('blocked', BroadcastDelivery::where('bot_user_id', $blocked->id)->firstOrFail()->error);
        $this->assertSame('sent', BroadcastDelivery::where('bot_user_id', $fine->id)->firstOrFail()->status);

        $this->actingAsAdmin()->get(route('admin.broadcasts.show', $broadcast))->assertOk()->assertSee('Заблокировала бота');
    }

    public function test_another_telegram_error_is_a_failure_not_a_block(): void
    {
        BotUser::factory()->active()->create();
        $this->fakeTelegram(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 400, 'description' => 'Bad Request: something odd'], 400)]);

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'now']))->assertRedirect();

        $broadcast = Broadcast::firstOrFail();
        $this->assertSame([1, 0, 1, 0], [$broadcast->total, $broadcast->sent, $broadcast->failed, $broadcast->blocked]);
    }

    public function test_telegram_asking_to_wait_is_retried_and_the_message_is_not_lost(): void
    {
        $user = BotUser::factory()->active()->create();
        Http::fake(['api.telegram.org/*' => Http::sequence()
            ->push(['ok' => false, 'error_code' => 429, 'description' => 'Too Many Requests', 'parameters' => ['retry_after' => 1]], 429)
            ->push(['ok' => true, 'result' => []])]);

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'now']))->assertRedirect();

        $this->assertSame('sent', BroadcastDelivery::where('bot_user_id', $user->id)->firstOrFail()->status);
        $this->assertCount(2, $this->sent());
    }

    public function test_if_telegram_rejects_the_markup_the_message_goes_out_as_plain_text(): void
    {
        BotUser::factory()->active()->create();
        Http::fake(['api.telegram.org/*' => Http::sequence()
            ->push(['ok' => false, 'error_code' => 400, 'description' => "Bad Request: can't parse entities: Unsupported start tag"], 400)
            ->push(['ok' => true, 'result' => []])]);

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'now', 'texts' => ['ru' => 'Привет <b>всем']]))->assertRedirect();

        $requests = $this->sent();
        $this->assertCount(2, $requests);
        $this->assertArrayNotHasKey('parse_mode', $this->body($requests[1]));
        $this->assertSame('Привет всем', $this->body($requests[1])['text']);
        $this->assertSame(1, Broadcast::firstOrFail()->sent);
    }

    public function test_sending_to_an_empty_group_is_refused(): void
    {
        $this->fakeTelegram();

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'now', 'audience' => 'members']))->assertRedirect()->assertSessionHas('error');

        $this->assertNotSame('sent', Broadcast::firstOrFail()->status);
        $this->assertSame([], $this->sent());
    }

    // ---------------------------------------------------------------- фото

    public function test_the_photo_is_uploaded_once_then_sent_by_file_id(): void
    {
        BotUser::factory()->active()->count(3)->create();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['photo' => [
            ['file_id' => 'SMALL', 'width' => 90], ['file_id' => 'BIG-FILE-ID', 'width' => 1280],
        ]]])]);

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'now', 'photo' => UploadedFile::fake()->image('afisha.jpg', 2400, 1600)]))->assertRedirect();

        $broadcast = Broadcast::firstOrFail();
        $this->assertTrue($broadcast->hasPhoto());
        Storage::disk('originals')->assertExists($broadcast->photo_path);
        [$width] = getimagesizefromstring(Storage::disk('originals')->get($broadcast->photo_path));
        $this->assertLessThanOrEqual(1600, $width, 'фото уменьшено, но не обрезано');
        $this->assertSame('BIG-FILE-ID', $broadcast->photo_file_id);

        $requests = $this->sent();
        $this->assertCount(3, $requests);
        $this->assertStringEndsWith('/sendPhoto', $requests[0]->url());
        $this->assertTrue($requests[0]->isMultipart(), 'первая отправка загружает файл');
        $this->assertSame('Приходите на встречу в пятницу!', $this->body($requests[0])['caption']);
        foreach ([1, 2] as $i) {
            $this->assertFalse($requests[$i]->isMultipart(), 'остальным — по file_id, без повторной загрузки');
            $this->assertSame('BIG-FILE-ID', $this->body($requests[$i])['photo']);
        }
    }

    public function test_the_photo_can_be_removed_and_is_deleted_with_the_broadcast(): void
    {
        $this->submit($this->payload(['photo' => UploadedFile::fake()->image('a.jpg')]))->assertRedirect();
        $broadcast = Broadcast::firstOrFail();
        $path = $broadcast->photo_path;
        Storage::disk('originals')->assertExists($path);

        $this->submit($this->payload(['remove_photo' => '1']), $broadcast)->assertRedirect();
        $this->assertFalse($broadcast->fresh()->hasPhoto());
        Storage::disk('originals')->assertMissing($path);

        $this->submit($this->payload(['photo' => UploadedFile::fake()->image('b.jpg')]), $broadcast->fresh());
        $path = $broadcast->fresh()->photo_path;
        $this->actingAsAdmin()->delete(route('admin.broadcasts.destroy', $broadcast))->assertRedirect(route('admin.broadcasts.index'));
        $this->assertSame(0, Broadcast::count());
        Storage::disk('originals')->assertMissing($path);
    }

    // ---------------------------------------------------------------- расписание, повторы, отмена

    public function test_a_scheduled_broadcast_goes_out_when_its_time_comes(): void
    {
        $this->fakeTelegram();
        BotUser::factory()->active()->count(2)->create();
        $at = now(Broadcast::TIMEZONE)->addHours(2)->startOfMinute();

        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'later', 'scheduled_at' => $at->format('Y-m-d\TH:i')]))
            ->assertRedirect()->assertSessionHas('success');

        $broadcast = Broadcast::firstOrFail();
        $this->assertSame('scheduled', $broadcast->status);
        $this->assertTrue($broadcast->scheduled_at->equalTo($at->copy()->utc()), 'время по Кишинёву хранится в UTC');
        $this->assertSame([], $this->sent());

        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();
        $this->assertSame('scheduled', $broadcast->fresh()->status, 'раньше времени не уходит');
        $this->assertSame([], $this->sent());

        $this->travelTo($at->copy()->addMinute());
        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();

        $this->assertSame('sent', $broadcast->fresh()->status);
        $this->assertCount(2, $this->sent());

        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();
        $this->assertCount(2, $this->sent(), 'второй раз не уходит');
    }

    public function test_a_cancelled_scheduled_broadcast_never_goes_out(): void
    {
        $this->fakeTelegram();
        BotUser::factory()->active()->create();
        $at = now(Broadcast::TIMEZONE)->addHour();
        $this->submit($this->payload(['action' => 'send', 'send_mode' => 'later', 'scheduled_at' => $at->format('Y-m-d\TH:i')]));
        $broadcast = Broadcast::firstOrFail();

        $this->actingAsAdmin()->post(route('admin.broadcasts.cancel', $broadcast))->assertRedirect()->assertSessionHas('success');
        $this->travelTo($at->copy()->addHour());
        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();

        $this->assertSame('cancelled', $broadcast->fresh()->status);
        $this->assertSame([], $this->sent());
    }

    public function test_starting_twice_sends_only_once(): void
    {
        $this->fakeTelegram();
        BotUser::factory()->active()->create();
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'draft']);

        $this->assertTrue(app(BroadcastLauncher::class)->start($broadcast));
        $this->assertFalse(app(BroadcastLauncher::class)->start($broadcast), 'повторный запуск отклонён');
        SendBroadcast::dispatchSync($broadcast->id);                // задание, запущенное ещё раз, у законченной рассылки ничего не делает

        $this->assertCount(1, $this->sent());
    }

    public function test_a_restarted_job_does_not_write_again_to_those_who_already_got_the_message(): void
    {
        $this->fakeTelegram();
        $done = BotUser::factory()->active()->create();
        $left = BotUser::factory()->active()->create();
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'sending']);
        foreach ([[$done, 'sent'], [$left, 'pending']] as [$user, $status]) {
            BroadcastDelivery::create(['broadcast_id' => $broadcast->id, 'bot_user_id' => $user->id, 'telegram_id' => $user->telegram_id, 'locale' => 'ru', 'status' => $status]);
        }

        SendBroadcast::dispatchSync($broadcast->id);

        $requests = $this->sent();
        $this->assertCount(1, $requests);
        $this->assertSame((string) $left->telegram_id, (string) $this->body($requests[0])['chat_id']);
        $this->assertSame([2, 2], [$broadcast->fresh()->total, $broadcast->fresh()->sent]);
    }

    public function test_a_stalled_broadcast_is_resumed_by_the_scheduler(): void
    {
        $this->fakeTelegram();
        $user = BotUser::factory()->active()->create();
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'sending', 'started_at' => now()->subHour()]);
        BroadcastDelivery::create(['broadcast_id' => $broadcast->id, 'bot_user_id' => $user->id, 'telegram_id' => $user->telegram_id, 'locale' => 'ru', 'status' => 'pending']);
        Broadcast::whereKey($broadcast->id)->update(['updated_at' => now()->subMinutes(30)]);

        $this->artisan('broadcasts:dispatch-due')->assertSuccessful();

        $this->assertSame('sent', $broadcast->fresh()->status);
        $this->assertCount(1, $this->sent());
    }

    public function test_cancelling_while_sending_stops_the_rest(): void
    {
        $this->fakeTelegram();
        $user = BotUser::factory()->active()->create();
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'sending']);
        BroadcastDelivery::create(['broadcast_id' => $broadcast->id, 'bot_user_id' => $user->id, 'telegram_id' => $user->telegram_id, 'locale' => 'ru', 'status' => 'pending']);

        $this->actingAsAdmin()->post(route('admin.broadcasts.cancel', $broadcast));
        SendBroadcast::dispatchSync($broadcast->id);

        $this->assertSame('cancelled', $broadcast->fresh()->status);
        $this->assertSame([], $this->sent());
    }

    public function test_only_drafts_and_scheduled_broadcasts_can_be_edited(): void
    {
        $sent = Broadcast::create(['texts' => ['ru' => 'Старое'], 'audience' => 'approved', 'status' => 'sent', 'total' => 1, 'sent' => 1]);

        $this->actingAsAdmin()->get(route('admin.broadcasts.edit', $sent))->assertRedirect(route('admin.broadcasts.show', $sent));
        $this->submit($this->payload(), $sent)->assertRedirect(route('admin.broadcasts.show', $sent))->assertSessionHas('error');
        $this->assertSame('Старое', $sent->fresh()->texts['ru']);
    }

    public function test_a_running_broadcast_cannot_be_deleted(): void
    {
        $running = Broadcast::create(['texts' => ['ru' => 'Идёт'], 'audience' => 'approved', 'status' => 'sending']);

        $this->actingAsAdmin()->delete(route('admin.broadcasts.destroy', $running))->assertRedirect()->assertSessionHas('error');

        $this->assertSame(1, Broadcast::count());
    }

    public function test_the_report_lists_failures_first_and_filters_by_result(): void
    {
        $broadcast = Broadcast::create(['texts' => ['ru' => 'Текст'], 'audience' => 'approved', 'status' => 'sent', 'total' => 3, 'sent' => 1, 'failed' => 1, 'blocked' => 1]);
        foreach ([['sent', null, 'Анна Ровная'], ['blocked', 'Forbidden: bot was blocked by the user', 'Борис Блок'], ['failed', 'Bad Request: oops', 'Вера Ошибка']] as [$status, $error, $name]) {
            $user = BotUser::factory()->active()->create(['full_name' => $name]);
            BroadcastDelivery::create(['broadcast_id' => $broadcast->id, 'bot_user_id' => $user->id, 'telegram_id' => $user->telegram_id, 'locale' => 'ru', 'status' => $status, 'error' => $error]);
        }

        $html = $this->actingAsAdmin()->get(route('admin.broadcasts.show', $broadcast))->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'Борис Блок'), strpos($html, 'Вера Ошибка'), 'ошибки выше всего');
        $this->assertLessThan(strpos($html, 'Анна Ровная'), strpos($html, 'Борис Блок'), 'затем заблокировавшие бота');
        $this->assertStringContainsString('Forbidden: bot was blocked by the user', $html);

        $this->actingAsAdmin()->get(route('admin.broadcasts.show', ['broadcast' => $broadcast, 'filter' => 'blocked']))
            ->assertSee('Борис Блок')->assertDontSee('Анна Ровная')->assertDontSee('Вера Ошибка');
    }

    // ---------------------------------------------------------------- текст

    public function test_the_text_keeps_the_allowed_tags_and_escapes_everything_else(): void
    {
        $this->assertSame('<b>Важно</b> &amp; <i>срочно</i>', BroadcastText::html('<b>Важно</b> & <i>срочно</i>'));
        $this->assertSame('<a href="https://example.test/x?a=1&amp;b=2">ссылка</a>', BroadcastText::html('<a href="https://example.test/x?a=1&b=2">ссылка</a>'));
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', BroadcastText::html('<script>alert(1)</script>'));
        $this->assertSame('&lt;a href="javascript:alert(1)"&gt;x&lt;/a&gt;', BroadcastText::html('<a href="javascript:alert(1)">x</a>'), 'опасные адреса не становятся ссылками');
        $this->assertSame('5 &lt; 7', BroadcastText::html('5 < 7'));
        // Тег не закрыт: разметка не применяется совсем.
        $this->assertSame('Привет &lt;b&gt;всем', BroadcastText::html('Привет <b>всем'));
        $this->assertSame('Привет всем', BroadcastText::plain('Привет <b>всем</b>'));
    }
}
