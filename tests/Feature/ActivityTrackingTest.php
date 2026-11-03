<?php

namespace Tests\Feature;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Broadcast;
use App\Models\BroadcastClick;
use App\Models\BroadcastDelivery;
use App\Models\Subscription;
use App\Services\Activity\CabinetActivity;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Учёт активности для «Статистики»: открытые разделы и действия в кабинете, «последний раз в кабинете», клики по кнопкам
 * рассылки через ссылку-переходник.
 */
class ActivityTrackingTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'ru');
    }

    private function member(Plan $plan = Plan::Active): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT);

        return $user->fresh();
    }

    private function hits(BotUser $user, string $key): int
    {
        return (int) DB::table('cabinet_activity')->where('bot_user_id', $user->id)->where('day', now()->toDateString())->where('key', $key)->value('hits');
    }

    // ------------------------------------------------------------------ кабинет

    public function test_each_opened_section_is_counted_per_day_and_repeats_add_up(): void
    {
        $user = $this->member();

        $this->asParticipant($user)->get(route('account.index'))->assertOk();
        $this->asParticipant($user)->get(route('account.index'))->assertOk();
        $this->asParticipant($user)->get(route('account.knowledge'))->assertOk();

        $this->assertSame(2, $this->hits($user, 'home'));
        $this->assertSame(1, $this->hits($user, 'knowledge'));
        $this->assertSame(0, $this->hits($user, 'matches'));
        $this->assertSame(2, DB::table('cabinet_activity')->where('bot_user_id', $user->id)->count(), 'по строке на день и раздел');
    }

    public function test_a_search_with_a_query_counts_as_an_action_too(): void
    {
        $user = $this->member();

        $this->asParticipant($user)->get(route('account.search'))->assertOk();
        $this->assertSame(1, $this->hits($user, 'search'));
        $this->assertSame(0, $this->hits($user, 'search_query'));

        $this->asParticipant($user)->get(route('account.search', ['q' => 'эксперт по маркетингу']));
        $this->assertSame(1, $this->hits($user, 'search_query'));
    }

    public function test_last_seen_is_set_and_not_rewritten_every_click(): void
    {
        $user = $this->member();
        $this->assertNull($user->last_seen_at);

        $this->asParticipant($user)->get(route('account.index'));
        $first = $user->fresh()->last_seen_at;
        $this->assertNotNull($first);

        $this->travel(2)->minutes();
        $this->asParticipant($user->fresh())->get(route('account.knowledge'));
        $this->assertEquals($first->timestamp, $user->fresh()->last_seen_at->timestamp, 'внутри 5 минут запись не обновляется');

        $this->travel(10)->minutes();
        $this->asParticipant($user->fresh())->get(route('account.knowledge'));
        $this->assertGreaterThan($first->timestamp, $user->fresh()->last_seen_at->timestamp);
    }

    public function test_a_redirect_without_a_plan_and_a_forbidden_page_are_not_counted(): void
    {
        $noPlan = BotUser::factory()->approved()->create();
        $this->asParticipant($noPlan)->get(route('account.index'))->assertRedirect();
        $this->assertSame(0, DB::table('cabinet_activity')->where('bot_user_id', $noPlan->id)->count());

        $this->assertSame(0, DB::table('cabinet_activity')->count(), 'гость ничего не записывает');
        $this->get(route('account.index'))->assertRedirect();
        $this->assertSame(0, DB::table('cabinet_activity')->count());
    }

    public function test_the_keys_of_pages_and_actions_come_from_the_route_name(): void
    {
        $keysFor = function (string $method, string $route, array $query = []): array {
            $request = Request::create('/', $method, $query);
            $request->setRouteResolver(fn () => (new \Illuminate\Routing\Route($method, '/', []))->name($route));

            return app(CabinetActivity::class)->keysFor($request);
        };

        $this->assertSame(['home'], $keysFor('GET', 'account.index'));
        $this->assertSame(['possibilities'], $keysFor('GET', 'account.possibilities.post'));
        $this->assertSame(['knowledge'], $keysFor('GET', 'account.knowledge.show'));
        $this->assertSame(['initiatives'], $keysFor('GET', 'account.opportunities.index'));
        $this->assertSame(['manager_question'], $keysFor('POST', 'account.manager.message'));
        $this->assertSame(['assistant_question'], $keysFor('POST', 'account.assistant.message'));
        $this->assertSame(['initiative_created'], $keysFor('POST', 'account.opportunities.store'));
        $this->assertSame([], $keysFor('GET', 'account.verification.notice'), 'служебные страницы не считаются');
        $this->assertSame([], $keysFor('POST', 'account.logout'));
    }

    // ------------------------------------------------------------------ рассылки

    private function delivery(?array $buttons = null, string $token = 'abcdefghij0123456789'): BroadcastDelivery
    {
        $user = BotUser::factory()->active()->create();
        $broadcast = Broadcast::create([
            'texts' => ['ru' => 'Текст'],
            'buttons' => $buttons ?? [
                ['url' => 'https://example.test/register', 'label' => ['ru' => 'Регистрация', 'en' => 'Register', 'ro' => '']],
                ['url' => 'https://example.test/program', 'label' => ['ru' => 'Программа', 'en' => 'Programme', 'ro' => '']],
            ],
            'audience' => 'approved',
            'status' => 'sent',
        ]);

        return BroadcastDelivery::create([
            'broadcast_id' => $broadcast->id, 'bot_user_id' => $user->id, 'telegram_id' => $user->telegram_id,
            'token' => $token, 'locale' => 'ru', 'status' => 'sent',
        ]);
    }

    public function test_buttons_get_personal_tracking_links_only_with_a_token(): void
    {
        $delivery = $this->delivery();
        $broadcast = $delivery->broadcast;

        $tracked = $broadcast->buttonsFor('ru', 'abcdefghij0123456789');
        $this->assertSame(route('broadcast.go', ['token' => 'abcdefghij0123456789', 'button' => 0]), $tracked[0]['url']);
        $this->assertSame(route('broadcast.go', ['token' => 'abcdefghij0123456789', 'button' => 1]), $tracked[1]['url']);
        $this->assertSame('Регистрация', $tracked[0]['text']);

        // Пробное сообщение себе токена не имеет: адреса прямые
        $this->assertSame('https://example.test/register', $broadcast->buttonsFor('ru')[0]['url']);
    }

    public function test_a_click_is_recorded_and_the_visitor_goes_to_the_button_address(): void
    {
        $delivery = $this->delivery();

        $this->get(route('broadcast.go', ['token' => $delivery->token, 'button' => 1]))->assertRedirect('https://example.test/program');
        $this->assertSame(1, BroadcastClick::where('broadcast_delivery_id', $delivery->id)->where('button', 1)->count());

        $first = $delivery->fresh()->first_clicked_at;
        $this->assertNotNull($first);
        $this->assertSame(1, $delivery->fresh()->clicks);

        $this->travel(1)->hour();
        $this->get(route('broadcast.go', ['token' => $delivery->token, 'button' => 0]))->assertRedirect('https://example.test/register');

        $fresh = $delivery->fresh();
        $this->assertSame(2, $fresh->clicks);
        $this->assertEquals($first->timestamp, $fresh->first_clicked_at->timestamp, 'первый клик не перезаписывается');
        $this->assertSame(2, BroadcastClick::where('broadcast_id', $delivery->broadcast_id)->count());
        $this->assertSame($delivery->bot_user_id, BroadcastClick::first()->bot_user_id);
    }

    public function test_the_destination_comes_from_the_broadcast_and_bad_requests_are_404(): void
    {
        $delivery = $this->delivery();

        $this->get(route('broadcast.go', ['token' => 'neizvestnyj12345678', 'button' => 0]))->assertNotFound();
        $this->get(route('broadcast.go', ['token' => $delivery->token, 'button' => 5]))->assertNotFound();
        $this->get('/r/'.$delivery->token.'/0?url=https://evil.test')->assertRedirect('https://example.test/register');
        $this->assertSame(1, BroadcastClick::count(), 'только настоящий клик');

        $this->delivery([['url' => 'javascript:alert(1)', 'label' => ['ru' => 'Плохая', 'en' => '', 'ro' => '']]], 'zzzzzzzzzz0123456789');
        $this->get(route('broadcast.go', ['token' => 'zzzzzzzzzz0123456789', 'button' => 0]))->assertNotFound();
    }

    public function test_a_link_preview_robot_does_not_count_as_a_click(): void
    {
        $delivery = $this->delivery();

        $this->call('HEAD', route('broadcast.go', ['token' => $delivery->token, 'button' => 0]))->assertRedirect();

        $this->assertSame(0, BroadcastClick::count());
        $this->assertSame(0, $delivery->fresh()->clicks);
    }
}
