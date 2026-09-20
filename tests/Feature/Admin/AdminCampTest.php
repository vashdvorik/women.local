<?php

namespace Tests\Feature\Admin;

use App\Models\BotUser;
use App\Models\Opportunity;
use App\Support\AdminCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Админка разделена на два лагеря: «Внешний сайт» и «Кабинеты участниц». На каждой странице
 * виден переключатель и меню только её лагеря, а в шапке — подпись, где находится администратор.
 */
class AdminCampTest extends TestCase
{
    use RefreshDatabase;

    /** Пункты меню, которые есть только у лагеря «Внешний сайт». */
    private const SITE_ONLY = ['Все публикации', 'Все новости', 'Все эксперты', 'Все проекты', 'Все фотоальбомы', 'Подписчики', 'Теги', 'Настройки сайта'];

    /** Пункты меню, которые есть только у лагеря «Кабинеты участниц». */
    private const CABINET_ONLY = ['Профили участниц', 'Посты участниц', 'Статистика', 'Настройки кабинетов'];

    public static function siteRoutes(): array
    {
        return [
            ['admin.dashboard'],
            ['admin.posts.index'],
            ['admin.posts.create'],
            ['admin.opportunities.index'],
            ['admin.events.index'],
            ['admin.experts.index'],
            ['admin.projects.index'],
            ['admin.albums.index'],
            ['admin.videos.index'],
            ['admin.tags.index'],
            ['admin.subscribers.index'],
            ['admin.settings.edit'],
        ];
    }

    public static function cabinetRoutes(): array
    {
        return [
            ['admin.cabinets.dashboard'],
            ['admin.profiles.index'],
            ['admin.member-posts.index'],
            ['admin.statistics.index'],
            ['admin.cabinets.settings'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('siteRoutes')]
    public function test_site_pages_show_only_the_site_menu(string $route): void
    {
        $response = $this->actingAsAdmin()->get(route($route))->assertOk();

        $response->assertSee('Внешний сайт')
            ->assertSee('Кабинеты участниц');

        foreach (self::CABINET_ONLY as $item) {
            $response->assertDontSee('>'.$item.'<', false);
        }

        $this->assertStringContainsString('camp-switch__tab--active', $response->getContent());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cabinetRoutes')]
    public function test_cabinet_pages_show_only_the_cabinet_menu(string $route): void
    {
        $response = $this->actingAsAdmin()->get(route($route))->assertOk();

        foreach (self::SITE_ONLY as $item) {
            $response->assertDontSee('<span>'.$item.'</span>', false);
        }

        foreach (self::CABINET_ONLY as $item) {
            $response->assertSee('<span>'.$item.'</span>', false);
        }
    }

    public function test_switcher_marks_the_current_camp_as_active(): void
    {
        $site = $this->actingAsAdmin()->get(route('admin.posts.index'))->getContent();
        $cabinets = $this->actingAsAdmin()->get(route('admin.profiles.index'))->getContent();

        $this->assertMatchesRegularExpression('/camp-switch__tab--active[^>]*aria-current="page"[^>]*>\s*<span>Внешний сайт<\/span>/', $site);
        $this->assertDoesNotMatchRegularExpression('/camp-switch__tab--active[^>]*>\s*<span>Кабинеты участниц<\/span>/', $site);

        $this->assertMatchesRegularExpression('/camp-switch__tab--active[^>]*aria-current="page"[^>]*>\s*<span>Кабинеты участниц<\/span>/', $cabinets);
        $this->assertDoesNotMatchRegularExpression('/camp-switch__tab--active[^>]*>\s*<span>Внешний сайт<\/span>/', $cabinets);
    }

    public function test_page_header_names_the_camp(): void
    {
        $this->actingAsAdmin()->get(route('admin.events.index'))
            ->assertSee('text-micro uppercase text-ink-muted truncate">Внешний сайт<', false);

        $this->actingAsAdmin()->get(route('admin.statistics.index'))
            ->assertSee('text-micro uppercase text-ink-muted truncate">Кабинеты участниц<', false);
    }

    public function test_moderation_queue_counter_is_visible_from_every_site_page(): void
    {
        BotUser::factory()->pending()->count(2)->create();
        $author = BotUser::factory()->approved()->create();
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'event', 'title' => 'Пост', 'body' => 'Текст', 'status' => 'pending']);

        // Счётчик на вкладке «Кабинеты участниц»: два профиля и один пост.
        $this->actingAsAdmin()->get(route('admin.events.index'))
            ->assertSee('<span class="nav-counter" title="Ждут решения">3</span>', false);

        // Внутри лагеря счётчики стоят у своих пунктов.
        $this->actingAsAdmin()->get(route('admin.cabinets.dashboard'))
            ->assertSee('<span class="nav-counter">2</span>', false)
            ->assertSee('<span class="nav-counter">1</span>', false);
    }

    public function test_no_counter_when_nothing_waits_for_a_decision(): void
    {
        $this->actingAsAdmin()->get(route('admin.events.index'))->assertDontSee('nav-counter', false);
    }

    public function test_site_dashboard_reminds_about_the_moderation_queue_only_when_it_is_not_empty(): void
    {
        $this->actingAsAdmin()->get(route('admin.dashboard'))
            ->assertDontSee('В кабинетах участниц есть что решить');

        BotUser::factory()->pending()->create();

        $this->actingAsAdmin()->get(route('admin.dashboard'))
            ->assertSee('В кабинетах участниц есть что решить')
            ->assertSee('Профили: 1.')
            ->assertSee(route('admin.cabinets.dashboard'), false);
    }

    public function test_cabinet_dashboard_shows_the_queues_and_links_to_them(): void
    {
        BotUser::factory()->pending()->count(2)->create();
        BotUser::factory()->approved()->count(3)->create();

        $this->actingAsAdmin()->get(route('admin.cabinets.dashboard'))
            ->assertOk()
            ->assertSee('Профили ждут решения')
            ->assertSee('Посты участниц ждут решения')
            ->assertSee('Профили участниц · 3 одобрено')
            ->assertSee(route('admin.profiles.index', ['status' => 'pending']), false)
            ->assertSee(route('admin.member-posts.index', ['status' => 'pending']), false)
            ->assertSee(route('admin.statistics.index'), false)
            ->assertSee(route('admin.cabinets.settings'), false);
    }

    public function test_site_dashboard_no_longer_links_to_cabinet_sections_inside_the_site_menu(): void
    {
        $content = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();

        // Статистика — раздел кабинетов: на инфопанели сайта нет кнопки к ней.
        $this->assertStringNotContainsString('class="btn-secondary">Статистика<', $content);
    }

    public function test_cabinet_pages_are_closed_to_non_admins(): void
    {
        config(['admin.email' => 'someone-else@example.test']);
        $intruder = \App\Models\User::factory()->create(['email' => 'intruder@example.test']);

        foreach (['admin.cabinets.dashboard', 'admin.cabinets.settings'] as $route) {
            $this->actingAs($intruder)->get(route($route))->assertForbidden();
        }
    }

    public function test_camp_detection_by_route_name(): void
    {
        $detect = function (string $routeName) {
            $request = \Illuminate\Http\Request::create('/');
            $request->setRouteResolver(fn () => (new \Illuminate\Routing\Route('GET', '/', []))->name($routeName));

            return AdminCamp::current($request);
        };

        foreach (['admin.dashboard', 'admin.posts.index', 'admin.settings.edit', 'admin.subscribers.index'] as $name) {
            $this->assertSame(AdminCamp::SITE, $detect($name), $name);
        }
        foreach (['admin.cabinets.dashboard', 'admin.cabinets.settings', 'admin.profiles.show', 'admin.member-posts.index', 'admin.statistics.pdf'] as $name) {
            $this->assertSame(AdminCamp::CABINETS, $detect($name), $name);
        }

        $this->assertSame('Внешний сайт', AdminCamp::label(AdminCamp::SITE));
        $this->assertSame('Кабинеты участниц', AdminCamp::label(AdminCamp::CABINETS));
    }
}
