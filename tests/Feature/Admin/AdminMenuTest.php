<?php

namespace Tests\Feature\Admin;

use App\Models\BotUser;
use App\Models\Opportunity;
use App\Support\AdminSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Меню админки одно и сгруппировано по задачам администратора: Участницы, Пакеты и оплата, Контент, Общение, Настройки.
 * Разделения на «Внешний сайт» и «Кабинеты участниц» больше нет; над заголовком страницы стоит название её группы.
 */
class AdminMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Эти проверки смотрят на тексты админки, а она по умолчанию русская (язык по cookie админки — AdminLanguageTest).
        app()->setLocale('ru');
    }

    /** Группа меню → её пункты. */
    private const MENU = [
        'Участницы' => ['Профили участниц', 'Инициативы участниц', 'Вопросы AI-менеджеру', 'Статистика'],
        'Пакеты и оплата' => ['Пакеты и цены', 'Заявки на пакет', 'Платежи'],
        'Контент' => ['Новости', 'Возможности/обучение', 'Обучающие видео', 'Проекты', 'Эксперты', 'Медиатека', 'Фотоальбомы', 'Видео', 'Каталоги'],
        'Общение' => ['Рассылка в Telegram', 'Обращения', 'Подписчики', 'Сообщения бота'],
        'Настройки' => ['Настройки сайта', 'Настройки кабинетов', 'Теги'],
    ];

    /** Группы бокового меню, которые страница раскрывает принудительно (список из x-data="sidebarNav(...)"). */
    private function openGroups(string $route): array
    {
        $html = $this->actingAsAdmin()->get(route($route))->assertOk()->getContent();

        preg_match('/x-data="sidebarNav\((.*?)\)"/', $html, $m);
        $json = html_entity_decode($m[1] ?? '[]', ENT_QUOTES);
        $json = preg_replace("/^JSON\.parse\('(.*)'\)$/", '$1', $json);

        return json_decode(str_replace('\\u0022', '"', $json), true) ?? [];
    }

    public static function pages(): array
    {
        return [
            ['admin.dashboard'],
            ['admin.profiles.index'],
            ['admin.member-posts.index'],
            ['admin.statistics.index'],
            ['admin.subscriptions.index'],
            ['admin.events.index'],
            ['admin.opportunities.index'],
            ['admin.learning-videos.index'],
            ['admin.albums.index'],
            ['admin.tags.index'],
            ['admin.broadcasts.index'],
            ['admin.settings.edit'],
            ['admin.cabinets.settings'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function test_every_page_shows_the_same_full_menu_without_the_camp_switch(string $route): void
    {
        $content = $this->actingAsAdmin()->get(route($route))->assertOk()->getContent();

        foreach (self::MENU as $group => $items) {
            $this->assertStringContainsString('<button type="button" class="nav-group"', $content);
            $this->assertStringContainsString('<span>'.$group.'</span>', $content, $group);

            foreach ($items as $item) {
                $this->assertStringContainsString('<span>'.$item.'</span>', $content, "{$route}: {$item}");
            }
        }

        $this->assertStringNotContainsString('camp-switch', $content);
        $this->assertStringNotContainsString('Внешний сайт', $content);
        $this->assertStringNotContainsString('Кабинеты участниц', $content);
    }

    public function test_the_groups_go_in_order_of_the_administrators_tasks(): void
    {
        $content = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();

        $positions = array_map(fn (string $group) => strpos($content, '<span>'.$group.'</span>'), array_keys(self::MENU));
        $sorted = $positions;
        sort($sorted);

        $this->assertNotContains(false, $positions);
        $this->assertSame($sorted, $positions);
        $this->assertLessThan($positions[0], strpos($content, '<span>Инфопанель</span>'), 'инфопанель выше всех групп');
    }

    public function test_the_menu_has_one_item_per_section_without_add_rows(): void
    {
        $content = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();

        // «Добавить …» и «Все …» остались только кнопками внутри самих списков, а не пунктами меню
        foreach (['Добавить новость', 'Добавить проект', 'Добавить возможность', 'Добавить эксперта', 'Добавить фотоальбом', 'Добавить видео', 'Добавить каталог', 'Все новости', 'Все проекты', 'Все возможности', 'Все эксперты'] as $row) {
            $this->assertStringNotContainsString('<span>'.$row.'</span>', $content, $row);
        }
    }

    public function test_catalogues_live_inside_the_media_library_group_and_publications_are_hidden_for_now(): void
    {
        $content = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();

        $media = strpos($content, '<span>Медиатека</span>');
        $albums = strpos($content, '<span>Фотоальбомы</span>');
        $videos = strpos($content, '<span>Видео</span>');
        $catalogs = strpos($content, '<span>Каталоги</span>');
        $communication = strpos($content, '<span>Общение</span>');

        $this->assertNotFalse($media);
        $this->assertTrue($media < $albums && $albums < $videos && $videos < $catalogs && $catalogs < $communication,
            'Порядок: Медиатека → фотоальбомы → видео → каталоги → группа «Общение»');

        // Публикации пока не используются на сайте: пункта меню нет, а страницы открываются по прямой ссылке
        $this->assertStringNotContainsString('<span>Публикации</span>', $content);
    }

    public function test_publications_pages_open_the_media_library_group_and_use_publication_wording(): void
    {
        $this->actingAsAdmin()->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee('Публикаций пока нет.')
            ->assertSee('Добавить публикацию')
            ->assertDontSee('Новостей пока нет');

        // Открытая страница публикаций раскрывает «Контент» и вложенную группу «Медиатека».
        $this->assertSame(['content', 'media'], $this->openGroups('admin.posts.index'));

        $this->actingAsAdmin()->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee('Новая публикация')
            ->assertDontSee('Новая новость');
    }

    public function test_the_page_header_names_the_menu_group(): void
    {
        $this->actingAsAdmin()->get(route('admin.events.index'))->assertSee('text-micro uppercase text-ink-muted truncate">Контент<', false);
        $this->actingAsAdmin()->get(route('admin.statistics.index'))->assertSee('text-micro uppercase text-ink-muted truncate">Участницы<', false);
        $this->actingAsAdmin()->get(route('admin.payments.index'))->assertSee('text-micro uppercase text-ink-muted truncate">Пакеты и оплата<', false);
        $this->actingAsAdmin()->get(route('admin.broadcasts.index'))->assertSee('text-micro uppercase text-ink-muted truncate">Общение<', false);
        $this->actingAsAdmin()->get(route('admin.settings.edit'))->assertSee('text-micro uppercase text-ink-muted truncate">Настройки<', false);

        // У инфопанели подписи группы нет
        $this->actingAsAdmin()->get(route('admin.dashboard'))->assertDontSee('text-micro uppercase text-ink-muted truncate"', false);
    }

    public function test_the_section_is_detected_by_route_name(): void
    {
        $label = function (string $routeName): ?string {
            $request = \Illuminate\Http\Request::create('/');
            $request->setRouteResolver(fn () => (new \Illuminate\Routing\Route('GET', '/', []))->name($routeName));

            return AdminSection::label($request);
        };

        $this->assertSame('Участницы', $label('admin.profiles.show'));
        $this->assertSame('Участницы', $label('admin.statistics.pdf'));
        $this->assertSame('Пакеты и оплата', $label('admin.plan-requests.index'));
        $this->assertSame('Контент', $label('admin.learning-videos.edit'));
        $this->assertSame('Контент', $label('admin.catalogs.create'));
        $this->assertSame('Общение', $label('admin.cabinets.bot-messages'));
        $this->assertSame('Настройки', $label('admin.cabinets.settings'));
        $this->assertNull($label('admin.dashboard'));
    }

    public function test_the_attention_counters_stand_next_to_their_items_and_sum_up_on_the_dashboard(): void
    {
        BotUser::factory()->pending()->count(2)->create();
        $author = BotUser::factory()->approved()->create();
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'event', 'title' => 'Пост', 'body' => 'Текст', 'status' => 'pending']);

        $content = $this->actingAsAdmin()->get(route('admin.events.index'))->assertOk()->getContent();

        $this->assertStringContainsString('<span class="nav-counter" title="Ждут решения">2</span>', $content, 'профили');
        $this->assertStringContainsString('<span class="nav-counter" title="Ждут решения">1</span>', $content, 'инициативы');
        $this->assertStringContainsString('<span class="nav-counter" title="Требует внимания">3</span>', $content, 'сумма на инфопанели');
    }

    public function test_no_counter_when_nothing_waits_for_a_decision(): void
    {
        $this->actingAsAdmin()->get(route('admin.events.index'))->assertDontSee('nav-counter', false);
    }

    public function test_the_dashboard_gathers_all_queues_in_one_place(): void
    {
        BotUser::factory()->pending()->count(2)->create();
        BotUser::factory()->approved()->count(3)->create();

        $this->actingAsAdmin()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Требует внимания')
            ->assertSee('Профили ждут решения')
            ->assertSee('Инициативы участниц ждут решения')
            ->assertSee('Новые заявки на пакет')
            ->assertSee('Платежи на разбор')
            ->assertSee('Вопросы AI-менеджеру без ответа')
            ->assertSee('Непрочитанные обращения')
            ->assertSee('Профили участниц · 3 одобрено')
            ->assertSee(route('admin.profiles.index', ['status' => 'pending']), false)
            ->assertSee(route('admin.member-posts.index', ['status' => 'pending']), false)
            ->assertSee(route('admin.plan-requests.index'), false)
            ->assertSee(route('admin.learning-videos.create'), false);
    }

    public function test_an_empty_dashboard_says_everything_is_done(): void
    {
        $this->actingAsAdmin()->get(route('admin.dashboard'))->assertSee('Всё разобрано')->assertDontSee('Требует внимания</h2>', false);
    }

    public function test_the_old_cabinets_dashboard_address_leads_to_the_common_dashboard(): void
    {
        $this->actingAsAdmin()->get(route('admin.cabinets.dashboard'))->assertRedirect('/admin')->assertStatus(301);
    }

    public function test_cabinet_pages_are_closed_to_non_admins(): void
    {
        config(['admin.email' => 'someone-else@example.test']);
        $intruder = \App\Models\User::factory()->create(['email' => 'intruder@example.test']);

        foreach (['admin.profiles.index', 'admin.cabinets.settings'] as $route) {
            $this->actingAs($intruder)->get(route($route))->assertForbidden();
        }
    }

    public function test_the_groups_collapse_and_the_current_page_group_is_open(): void
    {
        // Каждая группа — кнопка сворачивания; «Медиатека» вложена в «Контент»
        $content = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();

        foreach (['people', 'money', 'content', 'media', 'comm', 'settings'] as $group) {
            $this->assertStringContainsString("toggle('{$group}')", $content, $group);
            $this->assertStringContainsString("open.{$group} ? '' : 'nav-collapsible--closed'", $content, $group);
        }

        // Страница фотоальбомов раскрывает «Контент» и вложенную «Медиатеку»; инфопанель — ничего
        $this->assertSame(['content', 'media'], $this->openGroups('admin.albums.index'));
        $this->assertSame(['money'], $this->openGroups('admin.payments.index'));
        $this->assertSame([], $this->openGroups('admin.dashboard'));
    }

    public function test_a_collapsed_group_still_shows_how_many_items_wait_inside(): void
    {
        BotUser::factory()->pending()->count(2)->create();

        $content = $this->actingAsAdmin()->get(route('admin.events.index'))->assertOk()->getContent();

        // Число на заголовке группы «Участницы» видно, пока она свёрнута
        $this->assertMatchesRegularExpression('#<span>Участницы</span>\s*<span class="nav-counter mr-1" data-group-counter="people" x-show="!open\.people"[^>]*>2</span>#', $content);
        // У группы без очередей числа нет
        $this->assertDoesNotMatchRegularExpression('#<span>Контент</span>\s*<span class="nav-counter#', $content);
    }

    public function test_the_early_menu_script_only_sets_temporary_styles_and_never_the_classes_alpine_manages(): void
    {
        $content = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();

        // До запуска Alpine меню показывается временными inline-стилями, а x-init снимает их после запуска
        $this->assertSame(6, substr_count($content, "x-init=\"\$nextTick(() => \$el.style.maxHeight = '')\""), 'по одному на группу');
        $this->assertStringContainsString("box.style.maxHeight = '0px'", $content);

        // Классы «свёрнуто» и поворот стрелки ставит только Alpine: класс, уже стоявший до него, он по клику не снимет,
        // и группа не раскрылась бы (ошибка, которая уже была)
        $script = substr($content, strpos($content, '(function () {'), 1800);
        $this->assertStringNotContainsString('classList', $script);
    }
}
