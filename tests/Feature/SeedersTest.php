<?php

namespace Tests\Feature;

use App\Models\BotUser;
use App\Models\Event;
use App\Models\Expert;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AiSettingSeeder;
use Database\Seeders\BotUserSeeder;
use Database\Seeders\CommunitySeeder;
use Database\Seeders\ContentSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoNewsSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * Всё, что сайт показывает из БД, должно воспроизводиться сидерами: с пустой базы полный запуск
 * даёт то же содержимое, а повторный запуск ничего не дублирует и не затирает. На боевом сервере
 * полный запуск не должен ни падать, ни добавлять демо-данные и демо-доступы.
 */
class SeedersTest extends TestCase
{
    use RefreshDatabase;

    /** Случайный пароль без шаблонных слов: такой боевой сервер принимает. */
    private const STRONG_PASSWORD = 'Xk9-vQ2m-Lp7w-Rt4z-Hn8c';

    protected function setUp(): void
    {
        parent::setUp();

        // Подменный диск: сидеры импортируют фото, а тест не должен писать в реальный public/uploads.
        Storage::fake('uploads');
    }

    private function seedDatabase(): void
    {
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();
    }

    private function seedClass(string $class): void
    {
        $this->artisan('db:seed', ['--class' => $class, '--force' => true])->assertSuccessful();
    }

    /** Сколько новостей имеют текст страницы. */
    private function newsWithBody(): int
    {
        return Event::with('translations')->get()->filter(fn (Event $event) => $event->hasBody())->count();
    }

    // ------------------------------------------------------------------ тема сайта и кабинета

    public function test_site_setting_seeder_restores_the_themes_the_site_runs_on(): void
    {
        $this->seed(SiteSettingSeeder::class);

        $this->assertSame(2, SiteSetting::count());
        $this->assertSame('miro', SiteSetting::landingTheme());
        $this->assertSame('fortuntwo', SiteSetting::accountTheme());
    }

    public function test_site_setting_seeder_keeps_a_theme_the_admin_already_chose(): void
    {
        SiteSetting::setLandingTheme('fortun');

        $this->seed(SiteSettingSeeder::class);
        $this->seed(SiteSettingSeeder::class);

        $this->assertSame(2, SiteSetting::count(), 'повторный запуск ничего не дублирует');
        $this->assertSame('fortun', SiteSetting::landingTheme());
        $this->assertSame('fortuntwo', SiteSetting::accountTheme(), 'недостающее добавлено');
    }

    // ------------------------------------------------------------------ параметры ИИ

    public function test_ai_setting_seeder_restores_the_ai_configuration(): void
    {
        $this->seed(AiSettingSeeder::class);

        $this->assertSame(4, SiteSetting::count());
        $this->assertSame(0.45, SiteSetting::searchMinScore());

        $agent = SiteSetting::agentFeatureConfig();
        $this->assertSame('openrouter', $agent['provider']);
        $this->assertSame('deepseek/deepseek-chat-v3.1', $agent['model']);

        $embedding = SiteSetting::embeddingFeatureConfig();
        $this->assertSame('openrouter', $embedding['provider']);
        $this->assertSame('baai/bge-m3', $embedding['model']);
        $this->assertSame(15, $embedding['timeout']);
    }

    public function test_ai_setting_seeder_never_stores_an_api_key(): void
    {
        $this->seed(AiSettingSeeder::class);

        foreach (SiteSetting::all() as $setting) {
            $this->assertStringNotContainsString('api_key', json_encode($setting->value), "в настройке {$setting->key} не должно быть ключа API");
        }

        $this->assertFalse(SiteSetting::openRouterProviderConfig()['api_key_configured']);
        $this->assertFalse(SiteSetting::deepSeekProviderConfig()['api_key_configured']);
        $this->assertFalse(SiteSetting::geminiEmbeddingConfig()['api_key_configured']);
    }

    public function test_ai_setting_seeder_keeps_what_the_admin_already_saved(): void
    {
        // Админ внёс ключ и свой адрес OpenRouter: сидер этого не трогает.
        SiteSetting::setOpenRouterProviderConfig(['base_url' => 'https://proxy.example/v1', 'timeout' => 45, 'api_key' => 'sk-test-key']);

        $this->seed(AiSettingSeeder::class);
        $this->seed(AiSettingSeeder::class);

        $this->assertSame(4, SiteSetting::count());
        $this->assertSame('https://proxy.example/v1', SiteSetting::openRouterProviderConfig()['base_url']);
        $this->assertSame('sk-test-key', SiteSetting::openRouterProviderApiKey());
    }

    // ------------------------------------------------------------------ полный запуск

    public function test_database_seeder_loads_everything_the_site_shows_and_can_be_rerun(): void
    {
        config(['admin.email' => 'admin@example.test', 'admin.password' => self::STRONG_PASSWORD]);

        $this->seedDatabase();
        $this->seedDatabase(); // повторный запуск не падает и не дублирует

        $this->assertSame(11, Expert::count());
        $this->assertSame(9, Event::count());
        $this->assertSame(9, Event::whereNotNull('slug')->distinct()->count('slug'), 'у каждой новости свой адрес страницы');
        $this->assertSame(9, $this->newsWithBody(), 'вне боевого сервера у новостей есть демо-текст');
        $this->assertSame(28, BotUser::count());
        $this->assertSame(0, BotUser::where('status', '!=', BotUser::STATUS_APPROVED)->count());
        $this->assertSame(6, SiteSetting::count()); // две темы и четыре настройки ИИ

        $this->assertSame(['admin@example.test'], User::pluck('email')->all(), 'создаётся только администратор');

        // Картинки экспертов и новостей на диске: без них на сайте были бы «битые» картинки.
        foreach (Expert::pluck('photo_path') as $path) {
            Storage::disk('uploads')->assertExists($path);
        }
        foreach (Event::pluck('image_path') as $path) {
            Storage::disk('uploads')->assertExists($path);
        }
    }

    public function test_database_seeder_on_production_adds_only_the_real_content(): void
    {
        config(['admin.email' => 'admin@example.test', 'admin.password' => self::STRONG_PASSWORD]);
        $this->app['env'] = 'production';

        $this->seedDatabase();
        $this->seedDatabase(); // и повторный запуск на проде безопасен

        // Нужное боевому сайту: администратор, эксперты, новости, темы оформления.
        $this->assertSame(['admin@example.test'], User::pluck('email')->all());
        $this->assertSame(11, Expert::count());
        $this->assertSame(9, Event::count());
        $this->assertSame(9, Event::whereNotNull('slug')->count(), 'адреса страниц новостей нужны и боевому сайту');
        $this->assertSame(2, SiteSetting::count());
        $this->assertSame('fortuntwo', SiteSetting::accountTheme());

        // Никаких демо-доступов и демо-текстов: ни тестовых участниц, ни пользователей с известным паролем,
        // ни выдуманного текста на страницах настоящих новостей.
        $this->assertSame(0, BotUser::count());
        $this->assertSame(0, $this->newsWithBody(), 'демо-текст новостей на боевом сервере не создаётся');
        $this->assertFalse(User::all()->contains(fn (User $u) => password_verify('password', $u->password)));

        // Выбор провайдера ИИ остаётся за админом.
        $this->assertNull(SiteSetting::query()->where('key', SiteSetting::AI_FEATURES_KEY)->first());
        $this->assertSame('gemini', SiteSetting::embeddingFeatureConfig()['provider']);
    }

    public function test_database_seeder_without_admin_credentials_still_loads_the_content(): void
    {
        config(['admin.email' => null, 'admin.password' => null]);

        $this->seedDatabase();

        $this->assertSame(11, Expert::count());
        $this->assertSame(0, User::count(), 'без данных администратора пользователь не создаётся');
    }

    // ------------------------------------------------------------------ демо-доступы

    public function test_demo_participants_are_never_created_on_production_even_by_a_direct_run(): void
    {
        $this->app['env'] = 'production';

        $this->seedClass(BotUserSeeder::class);
        $this->seedClass(CommunitySeeder::class);

        $this->assertSame(0, BotUser::count());
    }

    public function test_demo_participants_are_created_outside_production(): void
    {
        // Контрольный случай к предыдущему: вне боевого сервера сидеры работают, они не «сломаны».
        $this->app['env'] = 'local';

        $this->seedClass(BotUserSeeder::class);
        $this->seedClass(CommunitySeeder::class);

        $this->assertSame(28, BotUser::count());
    }

    // ------------------------------------------------------------------ демо-текст новостей

    public function test_demo_news_seeder_fills_every_news_page_in_three_languages_with_photos_of_other_news(): void
    {
        $this->app['env'] = 'local';
        $this->seed(ContentSeeder::class);
        $this->seedClass(DemoNewsSeeder::class);

        foreach (Event::with('translations')->get() as $event) {
            $this->assertTrue($event->hasBody(), $event->slug);

            foreach (['ru', 'ro', 'en'] as $locale) {
                $content = $event->rawTranslation($locale)->content;
                $this->assertCount(10, $content, "{$event->slug}: блоки на языке {$locale}");
            }

            // Структура общая: одни и те же блоки и картинки на всех языках, различается только текст.
            $ru = $event->rawTranslation('ru')->content;
            $en = $event->rawTranslation('en')->content;
            $this->assertSame(array_column($ru, 'uid'), array_column($en, 'uid'));
            $this->assertNotSame($ru[0]['data']['text'], $en[0]['data']['text']);

            // Фото — из уже загруженных обложек других новостей, а не своя обложка.
            $photos = array_merge([$ru[3]['data']['path']], $ru[7]['data']['images']);
            $this->assertCount(4, array_filter($photos));
            $this->assertNotContains($event->image_path, $photos, 'на странице не повторяется её же обложка');
            foreach ($photos as $path) {
                Storage::disk('uploads')->assertExists($path);
            }
        }
    }

    public function test_demo_news_seeder_never_overwrites_a_real_text_and_can_be_rerun(): void
    {
        $this->app['env'] = 'local';
        $this->seed(ContentSeeder::class);

        $real = [['uid' => 'r1', 'type' => 'text', 'data' => ['html' => '<p>Настоящий текст.</p>']]];
        $first = Event::orderBy('id')->first();
        $first->rawTranslation('ru')->update(['content' => $real]);

        $this->seedClass(DemoNewsSeeder::class);
        $before = Event::with('translations')->orderBy('id')->get()->map(fn ($e) => $e->rawTranslation('ru')->content)->all();
        $this->seedClass(DemoNewsSeeder::class);
        $after = Event::with('translations')->orderBy('id')->get()->map(fn ($e) => $e->rawTranslation('ru')->content)->all();

        $this->assertSame($real, Event::orderBy('id')->first()->rawTranslation('ru')->content, 'текст, внесённый редактором, не тронут');
        $this->assertSame($before, $after, 'повторный запуск ничего не меняет');
    }

    public function test_demo_news_are_never_created_on_production_even_by_a_direct_run(): void
    {
        $this->seed(ContentSeeder::class);
        $this->app['env'] = 'production';

        $this->seedClass(DemoNewsSeeder::class);

        $this->assertSame(0, $this->newsWithBody());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function placeholderPasswords(): array
    {
        return [
            'из примера education3' => ['change_me_before_deploy_123'],
            'слово password' => ['Password-2026-strong'],
            'слово demo' => ['MyDemoSite-2026!'],
            'слово admin' => ['Admin-Strong-Pass-1'],
            'подряд цифры' => ['Zx-1234567890-Qw'],
        ];
    }

    #[DataProvider('placeholderPasswords')]
    public function test_production_refuses_a_placeholder_admin_password(string $password): void
    {
        $this->app['env'] = 'production';
        config(['admin.email' => 'admin@example.test', 'admin.password' => $password]);

        try {
            $this->app->make(AdminUserSeeder::class)->run();
            $this->fail('шаблонный пароль не должен приниматься на боевом сервере');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ADMIN_PASSWORD', $e->getMessage());
        }

        $this->assertSame(0, User::count(), 'администратор с таким паролем не создан');
    }

    public function test_a_rejected_admin_password_does_not_leave_the_site_empty(): void
    {
        // migrate:fresh --seed: таблицы уже стёрты. Если пароль администратора не подошёл, содержимое
        // сайта всё равно должно загрузиться, а установка — закончиться ошибкой, а не тихим успехом.
        $this->app['env'] = 'production';
        config(['admin.email' => 'admin@example.test', 'admin.password' => 'change_me_before_deploy_123']);

        try {
            $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->run();
            $this->fail('установка с шаблонным паролем должна завершиться ошибкой');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ADMIN_PASSWORD', $e->getMessage());
        }

        $this->assertSame(11, Expert::count());
        $this->assertSame(9, Event::count());
        $this->assertSame(2, SiteSetting::count());
        $this->assertSame(0, User::count(), 'администратор с шаблонным паролем не создан');
        $this->assertSame(0, BotUser::count());
    }

    public function test_production_accepts_a_strong_admin_password_and_development_stays_flexible(): void
    {
        config(['admin.email' => 'admin@example.test']);

        // Локальная разработка: шаблонный пароль допустим, чтобы не мешать работе.
        config(['admin.password' => 'change_me_before_deploy_123']);
        $this->app->make(AdminUserSeeder::class)->run();
        $this->assertSame(1, User::count());

        // Боевой сервер: случайный пароль принимается, и пароль администратора обновляется.
        $this->app['env'] = 'production';
        config(['admin.password' => self::STRONG_PASSWORD]);
        $this->app->make(AdminUserSeeder::class)->run();

        $this->assertSame(1, User::count());
        $this->assertTrue(password_verify(self::STRONG_PASSWORD, User::first()->password));
    }

    public function test_dev_only_routes_and_demo_templates_are_not_part_of_the_site(): void
    {
        // Вход в кабинет «одним кликом» существует только при APP_ENV=local.
        $this->assertFalse(Route::has('dev.account.login'));
        $this->assertFalse(Route::has('dev.account.login.submit'));
        $this->get('/dev/account-login')->assertNotFound();

        // Ни одного демо-маршрута. (Слово test не ищем: так называется кнопка проверки ключей ИИ в админке.)
        foreach (Route::getRoutes() as $route) {
            $this->assertDoesNotMatchRegularExpression(
                '#(^|/)(dev|demo|design-system|welcome|preview|debug|telescope|horizon|phpinfo)(/|$)#i',
                $route->uri(),
                'демо-маршрут /'.$route->uri()
            );
        }

        // Заготовки Laravel и стилевой каталог: на сайт они не подключены и в поставку не входят.
        $this->assertFileDoesNotExist(resource_path('views/welcome.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/design-system.blade.php'));
    }

    // ------------------------------------------------------------------ зависимости боевого сервера

    public function test_seeders_do_not_depend_on_dev_only_packages(): void
    {
        // Faker стоит в require-dev: на боевом сервере (composer install --no-dev) его нет.
        // Сидер с фабрикой или fake() падал там сразу: «Call to undefined function fake()».
        $files = array_merge(glob(database_path('seeders/*.php')), glob(database_path('seeders/Concerns/*.php')));
        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/::factory\(|\bfake\(|Faker\\\\/',
                file_get_contents($file),
                basename($file).' использует фабрики или Faker, которых нет на боевом сервере'
            );
        }
    }
}
