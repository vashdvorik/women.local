<?php

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Models\Opportunity;
use App\Services\AiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Главная кабинета (account.index) — навигационная: список «С чего начать» с галочками, плитки всех разделов с
 * пояснением, «Что нового», подсказка «как тут всё устроено»; на телефоне — нижняя панель. Чат с ИИ-помощником живёт в
 * своём разделе (account.assistant).
 */
class CabinetHomeTest extends TestCase
{
    use RefreshDatabase;

    private function as(BotUser $user): static
    {
        return $this->withSession($this->participantSession($user));
    }

    private function community(array $attributes = []): BotUser
    {
        return BotUser::factory()->community()->create(['full_name' => 'Анна Иванова', ...$attributes]);
    }

    // ---------------------------------------------------------------- главная не чат

    public function test_the_home_is_a_navigation_page_not_the_chat(): void
    {
        $html = $this->as($this->community())->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Здравствуйте, Анна!', $html);
        $this->assertStringNotContainsString('fortun-ai-assistant', $html, 'чата на главной нет');
        $this->assertStringContainsString(__('account.home.steps_title'), $html);
        $this->assertStringContainsString(__('account.home.tiles_title'), $html);
        $this->assertStringContainsString(__('account.home.hint_title'), $html);
        $this->assertStringContainsString(__('account.home.hint_mobile'), $html, 'подсказка про нижнюю панель для телефона');
        $this->assertStringContainsString(__('account.home.hint_desktop'), $html, 'и про меню слева для компьютера');
        $this->assertStringContainsString(__('account.home.bot_help'), $html);
    }

    public function test_every_section_of_the_menu_has_a_tile_that_explains_it(): void
    {
        $html = $this->as($this->community())->get(route('account.index'))->assertOk()->getContent();

        foreach (['assistant', 'matches', 'search', 'people', 'opportunities', 'knowledge', 'profile', 'subscription'] as $key) {
            $this->assertStringContainsString(__("account.home.tiles.{$key}.what"), $html, "{$key}: что это");
            $this->assertStringContainsString(__("account.home.tiles.{$key}.how"), $html, "{$key}: как пользоваться");
        }
        foreach (['account.assistant', 'account.matches', 'account.search', 'account.people', 'account.opportunities.index', 'account.knowledge', 'account.profile', 'account.subscription'] as $route) {
            $this->assertStringContainsString('href="'.route($route).'"', $html, $route);
        }
        $this->assertStringNotContainsString(__('account.home.tile_locked'), $html, 'у платных замков нет');
    }

    public function test_the_menu_has_home_and_the_assistant_as_separate_items(): void
    {
        $html = $this->as($this->community())->get(route('account.profile'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~href="'.preg_quote(route('account.index'), '~').'"[^>]*>(?:(?!</a>).)*Главная~su', $html);
        $this->assertMatchesRegularExpression('~href="'.preg_quote(route('account.assistant'), '~').'"[^>]*>(?:(?!</a>).)*ИИ-помощник~su', $html);
    }

    public function test_the_chat_lives_on_its_own_page_for_paid_members_and_is_explained_for_open(): void
    {
        $this->as($this->community())->get(route('account.assistant'))->assertOk()->assertSee('fortun-ai-assistant', false);

        $open = BotUser::factory()->approved()->create();
        $this->as($open)->get(route('account.assistant'))->assertOk()->assertDontSee('fortun-ai-assistant', false)->assertSee('Купить подписку');
    }

    // ---------------------------------------------------------------- нижняя панель на телефоне

    public function test_phones_get_a_bottom_bar_with_the_main_sections_and_a_more_button(): void
    {
        $html = $this->as($this->community())->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringContainsString('account-bottom-nav', $html);
        preg_match('~<nav class="account-bottom-nav.*?</nav>~s', $html, $bar);
        $this->assertNotEmpty($bar, 'нижняя панель есть');

        foreach (['account.index', 'account.search', 'account.opportunities.index', 'account.profile'] as $route) {
            $this->assertStringContainsString('href="'.route($route).'"', $bar[0], $route);
        }
        $this->assertStringContainsString('aria-current="page"', $bar[0], 'текущий раздел подсвечен');
        $this->assertStringContainsString('sidebarOpen = true', $bar[0], '«Ещё» открывает полное меню');
        $this->assertStringContainsString(__('account.nav.more'), $bar[0]);
        $this->assertStringContainsString('lg:hidden', $bar[0], 'на компьютере панели нет: там меню слева');
    }

    public function test_the_bottom_bar_is_on_every_cabinet_page(): void
    {
        $user = $this->community();

        foreach (['account.profile', 'account.matches', 'account.opportunities.index', 'account.subscription'] as $route) {
            $this->as($user)->get(route($route))->assertOk()->assertSee('account-bottom-nav', false);
        }
    }

    // ---------------------------------------------------------------- С чего начать

    public function test_the_steps_tick_by_themselves_as_the_member_uses_the_cabinet(): void
    {
        $user = $this->community(['description' => null, 'expectation' => null]);

        $html = $this->as($user)->get(route('account.index'))->assertOk()->getContent();
        $this->assertStringContainsString(__('account.home.steps_progress', ['done' => 0, 'total' => 5]), $html);

        // Профиль заполнен: шаг отмечен сам.
        $user->update(['description' => 'Кондитерская', 'expectation' => 'Партнёры']);
        $this->as($user)->get(route('account.index'))->assertSee(__('account.home.steps_progress', ['done' => 1, 'total' => 5]));

        // Открыла разделы: матчи, поиск, возможности.
        $this->app->instance(\App\Services\MatchingService::class, new class extends \App\Services\MatchingService
        {
            public function __construct() {}

            public function topMatches(BotUser $user, ?int $n = null): \Illuminate\Support\Collection
            {
                return collect();
            }
        });
        $this->as($user)->get(route('account.matches'))->assertOk();
        $this->as($user)->get(route('account.opportunities.index'))->assertOk();

        $this->assertTrue($user->fresh()->hasSeen('matches'));
        $this->assertTrue($user->fresh()->hasSeen('opportunities'));
        $this->assertFalse($user->fresh()->hasSeen('search'));
        $this->as($user)->get(route('account.index'))->assertSee(__('account.home.steps_progress', ['done' => 3, 'total' => 5]));
    }

    public function test_asking_the_cabinet_assistant_a_real_question_ticks_its_step(): void
    {
        $user = $this->community();
        $this->app->instance(AiAssistantService::class, new class extends AiAssistantService
        {
            public function respond(BotUser $user, string $message, array $history, string $locale): array
            {
                return ['reply' => 'Привет', 'recommendation' => null, 'profile_proposal' => null];
            }
        });

        $this->as($user)->postJson(route('account.assistant.message'), ['message' => 'Найдите эксперта по маркетингу'])->assertOk();

        $this->assertTrue($user->fresh()->hasSeen('assistant'));
    }

    public function test_when_every_step_is_done_the_list_gives_way_to_a_short_note(): void
    {
        $user = $this->community(['description' => 'Кондитерская', 'expectation' => 'Партнёры', 'cabinet_seen' => ['matches', 'search', 'opportunities', 'assistant']]);

        $html = $this->as($user)->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('account.home.steps_all_done'), $html);
        $this->assertStringNotContainsString('account-home-step ', $html);
    }

    public function test_marking_a_section_seen_is_idempotent(): void
    {
        $user = $this->community();

        $user->markSeen('search');
        $user->markSeen('search');
        $user->markSeen('matches');

        $this->assertSame(['search', 'matches'], $user->fresh()->cabinet_seen);
    }

    // ---------------------------------------------------------------- Что нового

    public function test_whats_new_shows_fresh_opportunities_and_new_paid_members_but_not_myself_or_open_members(): void
    {
        $me = $this->community();
        $newcomer = BotUser::factory()->community()->create(['full_name' => 'Вера Новенькая', 'description' => 'Дизайн интерьеров', 'approved_at' => now()]);
        $open = BotUser::factory()->approved()->create(['full_name' => 'Ольга Бесплатная', 'approved_at' => now()]);
        $author = BotUser::factory()->community()->create(['full_name' => 'Нина Автор']);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'project', 'title' => 'Грант для основательниц', 'body' => 'Условия', 'status' => 'approved']);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'project', 'title' => 'Чужой черновик', 'body' => 'x', 'status' => 'pending']);

        $html = $this->as($me)->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Грант для основательниц', $html);
        $this->assertStringContainsString('Нина Автор', $html);
        $this->assertStringNotContainsString('Чужой черновик', $html, 'неодобренные чужие посты не показываются');
        $this->assertStringContainsString('Вера Новенькая', $html);
        $this->assertStringContainsString('Дизайн интерьеров', $html);
        $this->assertStringContainsString(route('account.people.show', $newcomer->id), $html, 'карточка ведёт в профиль');
        $this->assertStringNotContainsString('Ольга Бесплатная', $html, 'участницы без платного тарифа в каталоге не видны');
        $this->assertSame(0, substr_count($html, 'Анна Иванова'), 'себя в «новых участницах» нет');
    }

    public function test_whats_new_has_friendly_empty_states(): void
    {
        $html = $this->as($this->community())->get(route('account.index'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, __('account.home.fresh_empty')));
    }

    // ---------------------------------------------------------------- Open

    public function test_open_sees_the_same_home_with_locked_sections_two_steps_and_no_members_block(): void
    {
        $open = BotUser::factory()->approved()->create(['full_name' => 'Мария Петрова', 'description' => null]);

        $html = $this->as($open)->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('account.home.intro_open'), $html);
        $this->assertStringContainsString(__('account.home.steps_progress', ['done' => 0, 'total' => 2]), $html);
        $this->assertStringContainsString(__('account.home.steps.subscription.title'), $html);

        // Закрытые разделы с замком (их пять: ИИ-помощник, рекомендации, поиск, каталог, возможности и «Обучение» — шесть).
        $this->assertSame(6, substr_count($html, __('account.home.tile_locked')));
        // Открытые разделы без замка, закрытые ведут туда же, где объясняют подписку.
        $this->assertStringContainsString('href="'.route('account.search').'"', $html);
        $this->assertStringContainsString(__('subscription.open_home.upgrade_cta'), $html);
        $this->assertStringNotContainsString(__('account.home.fresh_title'), $html);
    }

    public function test_the_subscription_step_is_done_once_she_is_a_paid_member(): void
    {
        // Платная видит пять шагов без «подписки»: она уже есть.
        $html = $this->as($this->community())->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString(__('account.home.steps.subscription.title'), $html);
    }

    // ---------------------------------------------------------------- три языка

    public function test_the_home_texts_exist_in_every_language_with_the_same_keys(): void
    {
        $flatten = function (array $array, string $prefix = '') use (&$flatten): array {
            $keys = [];
            foreach ($array as $key => $value) {
                is_array($value) ? array_push($keys, ...$flatten($value, $prefix.$key.'.')) : $keys[] = $prefix.$key;
            }

            return $keys;
        };

        $ru = $flatten(require lang_path('ru/account.php'));
        foreach (['en', 'ro'] as $locale) {
            $keys = $flatten(require lang_path("{$locale}/account.php"));
            $this->assertSame([], array_values(array_diff($ru, $keys)), "{$locale}: нет ключей");
            $this->assertSame([], array_values(array_diff($keys, $ru)), "{$locale}: лишние ключи");
        }

        foreach (['en', 'ro'] as $locale) {
            $texts = (require lang_path("{$locale}/account.php"))['home'];
            $this->assertNotSame((require lang_path('ru/account.php'))['home']['hint_mobile'], $texts['hint_mobile'], "{$locale}: подсказка переведена");
        }
    }

    public function test_the_home_renders_in_english_and_romanian(): void
    {
        $user = $this->community();

        foreach (['en' => 'Where to start', 'ro' => 'De unde să începeți'] as $locale => $heading) {
            $this->as($user)->withCookie('locale', $locale)->withSession(['locale' => $locale, 'app_locale' => $locale]);
            app()->setLocale($locale);

            $html = $this->as($user)->get(route('account.index'))->assertOk()->getContent();

            $this->assertStringContainsString(__('account.home.steps_title'), $html, $locale);
            $this->assertSame($heading, __('account.home.steps_title'), $locale);
        }
    }
}
