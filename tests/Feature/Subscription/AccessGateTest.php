<?php

namespace Tests\Feature\Subscription;

use App\Enums\PublishStatus;
use App\Models\BotUser;
use App\Models\Event;
use App\Models\Opportunity;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Services\Subscriptions\SubscriptionService;
use App\Enums\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Что видит и чего не видит участница на каждом тарифе. Open — только то, что есть на публичном сайте;
 * каталог участниц, подбор, поиск, ИИ-помощник и публикации возможностей — с Community и выше.
 */
class AccessGateTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        // Сохранение профиля ставит в очередь пересчёт эмбеддинга; в тестах наружу (в Gemini) ходить нельзя.
        Queue::fake();
        Http::fake();
    }

    /** Страницы и действия, закрытые для Open. */
    private function closedForOpen(BotUser $someoneElse): array
    {
        return [
            'matches' => ['GET', route('account.matches')],
            'people' => ['GET', route('account.people')],
            'person' => ['GET', route('account.people.show', $someoneElse)],
            'search' => ['GET', route('account.search')],
            'opportunities' => ['GET', route('account.opportunities.index')],
            'new opportunity' => ['GET', route('account.opportunities.create')],
        ];
    }

    public function test_open_participant_is_sent_to_the_subscription_page_from_every_closed_section(): void
    {
        $open = BotUser::factory()->approved()->create();
        $other = BotUser::factory()->community()->create();

        foreach ($this->closedForOpen($other) as $name => [$method, $url]) {
            $this->asParticipant($open)->call($method, $url)
                ->assertRedirect(route('account.subscription'))
                ->assertSessionHas('error');

            $this->assertTrue(true, $name);
        }

        // Действия (запись) закрыты так же.
        $this->asParticipant($open)->post(route('account.opportunities.store'), ['type' => 'project', 'title' => 'Т', 'body' => 'Б'])
            ->assertRedirect(route('account.subscription'));
        $this->assertSame(0, Opportunity::count());
    }

    public function test_the_ai_assistant_endpoints_answer_open_participants_with_403_json(): void
    {
        $open = BotUser::factory()->approved()->create();

        $this->asParticipant($open)->postJson(route('account.assistant.message'), ['message' => 'Привет'])
            ->assertForbidden()
            ->assertJson(['upgrade_url' => route('account.subscription')])
            ->assertJsonStructure(['message', 'upgrade_url']);

        $this->asParticipant($open)->postJson(route('account.assistant.profile-update'), ['description' => 'x'])
            ->assertForbidden();
    }

    public function test_open_participant_keeps_the_pages_that_are_not_part_of_the_community(): void
    {
        $open = BotUser::factory()->approved()->create();

        foreach (['account.index', 'account.profile', 'account.profile.edit', 'account.knowledge', 'account.subscription', 'account.private'] as $route) {
            $this->asParticipant($open)->get(route($route))->assertOk();
        }
    }

    public function test_community_and_private_participants_open_everything(): void
    {
        $other = BotUser::factory()->community()->create();

        foreach ([BotUser::factory()->community()->create(), BotUser::factory()->private()->create()] as $user) {
            foreach ($this->closedForOpen($other) as $name => [$method, $url]) {
                $this->asParticipant($user)->call($method, $url)->assertOk();
                $this->assertTrue(true, $name);
            }
        }
    }

    public function test_access_closes_the_moment_the_subscription_ends(): void
    {
        $user = BotUser::factory()->community()->create();
        $this->asParticipant($user)->get(route('account.people'))->assertOk();

        $this->travel(13)->months();

        $this->asParticipant($user)->get(route('account.people'))->assertRedirect(route('account.subscription'));
        $this->asParticipant($user)->get(route('account.index'))->assertOk()->assertSee(__('subscription.open_home.upgrade_cta'));
    }

    public function test_open_home_shows_only_published_public_site_material_and_an_invitation_to_subscribe(): void
    {
        $open = BotUser::factory()->approved()->create(['full_name' => 'Мария Петрова']);

        $event = Event::create(['slug' => 'public-news', 'is_published' => true, 'sort_order' => 1, 'type' => 'news']);
        $event->translations()->create(['locale' => 'ru', 'title' => 'Открытая новость', 'description' => 'Описание новости']);
        $draft = Event::create(['slug' => 'draft-news', 'is_published' => false, 'sort_order' => 2, 'type' => 'news']);
        $draft->translations()->create(['locale' => 'ru', 'title' => 'Черновик новости', 'description' => 'не публиковать']);

        $post = Post::create(['slug' => 'public-post', 'status' => PublishStatus::Published, 'published_at' => now()->subDay()]);
        $post->translations()->create(['locale' => 'ru', 'title' => 'Каталог для всех', 'excerpt' => 'Скачайте каталог']);
        $hidden = Post::create(['slug' => 'later-post', 'status' => PublishStatus::Published, 'published_at' => now()->addWeek()]);
        $hidden->translations()->create(['locale' => 'ru', 'title' => 'Публикация из будущего', 'excerpt' => 'ещё рано']);

        $html = $this->asParticipant($open)->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Мария', $html);
        $this->assertStringContainsString('Открытая новость', $html);
        $this->assertStringContainsString('Каталог для всех', $html);
        $this->assertStringNotContainsString('Черновик новости', $html);
        $this->assertStringNotContainsString('Публикация из будущего', $html);
        // Приглашение подписаться; ни каталога участниц, ни ИИ-помощника.
        $this->assertStringContainsString(__('subscription.open_home.upgrade_cta'), $html);
        $this->assertStringContainsString(route('account.subscription'), $html);
        $this->assertStringNotContainsString('account/assistant', $html, 'ИИ-помощника на главной Open нет');
    }

    public function test_community_home_is_the_full_cabinet_not_the_open_feed(): void
    {
        $user = BotUser::factory()->community()->create();

        $this->asParticipant($user)->get(route('account.index'))
            ->assertOk()
            ->assertDontSee(__('subscription.open_home.upgrade_cta'));
    }

    // ---------------------------------------------------------------- кто виден в каталоге

    public function test_the_directory_lists_only_participants_with_a_live_paid_plan(): void
    {
        $viewer = BotUser::factory()->community()->create();
        $community = BotUser::factory()->community()->create(['full_name' => 'Видимая Платная']);
        $private = BotUser::factory()->private()->create(['full_name' => 'Видимая Приватная']);
        $open = BotUser::factory()->approved()->create(['full_name' => 'Скрытая Открытая']);
        $pending = BotUser::factory()->pending()->create(['full_name' => 'Скрытая Новая']);
        $expired = BotUser::factory()->community()->create(['full_name' => 'Скрытая Просроченная']);
        app(SubscriptionService::class)->revoke($expired);

        $html = $this->asParticipant($viewer)->get(route('account.people'))->assertOk()->getContent();

        $this->assertStringContainsString('Видимая Платная', $html);
        $this->assertStringContainsString('Видимая Приватная', $html);
        foreach (['Скрытая Открытая', 'Скрытая Новая', 'Скрытая Просроченная'] as $name) {
            $this->assertStringNotContainsString($name, $html, $name);
        }

        // Карточку скрытой участницы нельзя открыть и по прямой ссылке.
        $this->asParticipant($viewer)->get(route('account.people.show', $open))->assertNotFound();
        $this->asParticipant($viewer)->get(route('account.people.show', $pending))->assertNotFound();
        $this->asParticipant($viewer)->get(route('account.people.show', $expired))->assertNotFound();
        $this->asParticipant($viewer)->get(route('account.people.show', $private))->assertOk();
        $this->asParticipant($viewer)->get(route('account.people.show', $community))->assertOk();
    }

    public function test_matching_and_ai_search_never_offer_open_participants(): void
    {
        $vector = array_fill(0, 8, 0.5);
        $me = BotUser::factory()->community()->create(['embedding' => $vector]);
        $paid = BotUser::factory()->community()->create(['embedding' => $vector, 'full_name' => 'Платная Совпавшая']);
        $open = BotUser::factory()->approved()->create(['embedding' => $vector, 'full_name' => 'Открытая Совпавшая']);

        $matcher = app(\App\Services\MatchingService::class);

        $byProfile = $matcher->compute($me, 10)->pluck('user.id')->all();
        $byQuery = $matcher->searchByQuery($vector, $me)->pluck('user.id')->all();

        foreach ([$byProfile, $byQuery] as $ids) {
            $this->assertContains($paid->id, $ids);
            $this->assertNotContains($open->id, $ids);
        }
    }

    // ---------------------------------------------------------------- меню

    public function test_the_menu_has_the_subscription_item_for_everyone_and_private_only_for_paid_members(): void
    {
        foreach (['classic', 'fortun', 'fortuntwo', 'miro'] as $theme) {
            SiteSetting::setAccountTheme($theme);

            $open = $this->asParticipant(BotUser::factory()->approved()->create())->get(route('account.subscription'))->assertOk()->getContent();
            $this->assertStringContainsString('href="'.route('account.subscription').'"', $open, "{$theme}: пункт «Подписка»");
            $this->assertStringNotContainsString('href="'.route('account.private').'"', $open, "{$theme}: Private открытым не показывается");
            $this->assertStringNotContainsString('ИИ-помощник</', $open, "{$theme}: у Open нет пункта «ИИ-помощник»");
            $this->assertMatchesRegularExpression('~href="'.preg_quote(route('account.index'), '~').'"[^>]*>(?:(?!</a>).)*Главная~su', $open, "{$theme}: у Open первый пункт — «Главная»");

            foreach ([BotUser::factory()->community()->create(), BotUser::factory()->private()->create()] as $paid) {
                $html = $this->asParticipant($paid)->get(route('account.subscription'))->assertOk()->getContent();
                $this->assertStringContainsString('href="'.route('account.private').'"', $html, "{$theme}: вкладка Private");
                $this->assertStringContainsString('ИИ-помощник', $html, "{$theme}: у платных остаётся «ИИ-помощник»");
            }
        }
    }

    public function test_the_plans_page_renders_in_all_themes_and_languages_with_the_customers_descriptions(): void
    {
        $user = BotUser::factory()->community()->create();

        foreach (['classic', 'fortun', 'fortuntwo', 'miro'] as $theme) {
            SiteSetting::setAccountTheme($theme);

            $html = $this->asParticipant($user)->get(route('account.subscription'))->assertOk()->getContent();

            foreach (Plan::cases() as $plan) {
                $this->assertStringContainsString($plan->title('ru'), $html, "{$theme}: {$plan->value}");
            }
            $this->assertStringContainsString('участие в Gala «Woman of the Year»;', $html, $theme);
            $this->assertStringContainsString('персональный куратор в течение года;', $html, $theme);
            $this->assertStringContainsString('информация о грантах, конкурсах и проектах;', $html, $theme);
            $this->assertStringContainsString('600', $html);
            $this->assertStringContainsString('20', $html);
        }
    }

    public function test_the_plans_page_marks_the_current_plan_and_offers_the_right_buttons(): void
    {
        $community = BotUser::factory()->community()->create();
        $html = $this->asParticipant($community)->get(route('account.subscription'))->getContent();

        // Community: «Продлить»; Private: «Перейти»; Open: просто «входит в ваш тариф».
        $this->assertStringContainsString('Продлить за', $html);
        $this->assertStringContainsString('Перейти на Private', $html);
        $this->assertStringContainsString('до '.$community->fresh()->plan_ends_at->format('d.m.Y'), $html);

        $private = BotUser::factory()->private()->create();
        $htmlPrivate = $this->asParticipant($private)->get(route('account.subscription'))->getContent();
        $this->assertStringNotContainsString('Перейти на', $htmlPrivate, 'выше Private ничего нет');
        $this->assertStringNotContainsString('name="plan" value="community"', $htmlPrivate, 'на Private покупать Community незачем');

        $open = $this->asParticipant(BotUser::factory()->approved()->create())->get(route('account.subscription'))->getContent();
        $this->assertStringContainsString('Оформить за', $open);
        $this->assertStringContainsString('name="plan" value="community"', $open);
        $this->assertStringContainsString('name="plan" value="private"', $open);
    }

    public function test_the_private_tab_sells_private_to_community_and_shows_status_to_private(): void
    {
        $community = BotUser::factory()->community()->create();
        $this->asParticipant($community)->get(route('account.private'))
            ->assertOk()
            ->assertSee('WOMEN’S HUB PRIVATE')
            ->assertSee('name="plan" value="private"', false)
            ->assertSee('персональный куратор в течение года;');

        $private = BotUser::factory()->private()->create();
        $this->asParticipant($private)->get(route('account.private'))
            ->assertOk()
            ->assertSee('Ваше членство Private действует до '.$private->fresh()->plan_ends_at->format('d.m.Y'));
    }

    public function test_profile_forms_cannot_be_used_to_give_yourself_a_plan(): void
    {
        $open = BotUser::factory()->approved()->create();

        $this->asParticipant($open)->post(route('account.profile.update'), [
            'full_name' => 'Хитрая Участница',
            'plan' => 'private',
            'plan_ends_at' => now()->addYears(5)->toDateTimeString(),
            'status' => 'approved',
        ])->assertRedirect();

        $open->refresh();
        $this->assertSame('Хитрая Участница', $open->full_name);
        $this->assertSame(Plan::Open, $open->currentPlan());
        $this->assertSame('open', $open->plan);
    }

    public function test_guests_cannot_reach_the_subscription_pages(): void
    {
        foreach (['account.subscription', 'account.private', 'account.subscription.success', 'account.subscription.fail'] as $route) {
            $this->get(route($route))->assertRedirect(route('account.login'));
        }
    }
}
