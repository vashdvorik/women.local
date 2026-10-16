<?php

namespace Tests\Feature;

use App\Http\Middleware\FlushPublicCacheAfterAdminChange;
use App\Models\Album;
use App\Models\Event;
use App\Models\Post;
use App\Models\Tag;
use App\Support\PublicCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Кеш публичных страниц (App\Support\PublicCache): новости, фотоальбомы и другие разделы читаются из кеша, а не из базы,
 * и сбрасываются сами, когда что-то поменяли. Плюс кеш фото в браузере (public/.htaccess).
 */
class PublicCacheTest extends TestCase
{
    use RefreshDatabase;

    private function news(string $slug = 'vstrecha', string $title = 'Встреча', array $attributes = []): Event
    {
        $event = Event::create(array_merge([
            'slug' => $slug, 'tone' => 'rose', 'position' => 1, 'starts_at' => '2026-05-20',
            'image_path' => '2026/09/cover.webp', 'is_published' => true,
        ], $attributes));

        $event->translations()->create([
            'locale' => 'ru', 'title' => $title, 'type' => 'Конференция', 'description' => 'Коротко.',
            'content' => [['uid' => 't1', 'type' => 'text', 'data' => ['html' => '<p>Мы встретились.</p>']]],
        ]);

        return $event;
    }

    private function album(string $slug, string $title, array $attributes = []): Album
    {
        $album = Album::create(array_merge([
            'slug' => $slug, 'status' => 'published', 'published_at' => now()->subDay(),
            'cover_path' => '2026/09/cover.webp',
            'blocks' => [['uid' => 'g', 'type' => 'gallery_2', 'data' => ['images' => ['2026/09/p1.webp', '2026/09/p2.webp']]]],
        ], $attributes));
        $album->translations()->create(['locale' => 'ru', 'title' => $title, 'excerpt' => 'Как это было']);

        return $album;
    }

    /** Запросы к базе за время $action, затрагивающие перечисленные таблицы. @return list<string> */
    private function queriesTo(array $tables, callable $action): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $action();
        $log = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        $pattern = '/\b(?:from|into|update|join)\s+["`]?(?:'.implode('|', $tables).')\b/i';

        return array_values(array_filter($log, fn (string $sql) => preg_match($pattern, $sql) === 1));
    }

    // ---------------------------------------------------------------- новости

    public function test_the_news_list_is_read_from_the_database_once_and_then_from_the_cache(): void
    {
        $this->news();

        $first = $this->queriesTo(['events', 'event_translations'], fn () => $this->get(route('events'))->assertOk()->assertSee('Встреча'));
        $second = $this->queriesTo(['events', 'event_translations'], fn () => $this->get(route('events'))->assertOk()->assertSee('Встреча'));

        $this->assertNotEmpty($first, 'первый заход читает новости из базы');
        $this->assertSame([], $second, 'второй заход обходится без базы: '.implode(' | ', $second));
    }

    public function test_a_published_news_page_is_built_once_and_served_from_the_cache(): void
    {
        $this->news();

        $first = $this->queriesTo(['events', 'event_translations'], fn () => $html = $this->get(route('events.show', ['event' => 'vstrecha']))->assertOk()->getContent());
        $second = $this->queriesTo(['events', 'event_translations'], fn () => $again = $this->get(route('events.show', ['event' => 'vstrecha']))->assertOk()->getContent());

        $this->assertNotEmpty($first);
        $this->assertSame([], $second, 'страница новости из кеша: '.implode(' | ', $second));
        $this->assertStringContainsString('Мы встретились.', $this->get(route('events.show', ['event' => 'vstrecha']))->getContent());
    }

    public function test_saving_a_news_item_is_visible_on_the_site_at_once(): void
    {
        $event = $this->news();

        $this->get(route('events'))->assertSee('Встреча');
        $this->get(route('events.show', ['event' => 'vstrecha']))->assertSee('Встреча');

        $event->translations()->first()->update(['title' => 'Новая встреча']);

        $this->get(route('events'))->assertSee('Новая встреча')->assertDontSee('>Встреча<', false);
        $this->get(route('events.show', ['event' => 'vstrecha']))->assertSee('Новая встреча');

        // Снятие с публикации и удаление тоже сразу видны.
        $event->update(['is_published' => false]);
        $this->get(route('events.show', ['event' => 'vstrecha']))->assertNotFound();
        $this->get(route('events'))->assertDontSee('Новая встреча');
    }

    public function test_a_new_news_item_appears_in_the_cached_list(): void
    {
        $this->news();
        $this->get(route('events'))->assertOk();

        $this->news('vtoraya', 'Вторая новость', ['position' => 2]);

        $this->get(route('events'))->assertSee('Вторая новость');
    }

    public function test_a_draft_is_never_cached_and_never_shown_to_guests(): void
    {
        $this->news('chernovik', 'Черновик новости', ['is_published' => false]);

        $this->get(route('events.show', ['event' => 'chernovik']))->assertNotFound();

        // Администратор видит черновик (предпросмотр), но в кеш он не попадает.
        $this->actingAsAdmin()->get(route('events.show', ['event' => 'chernovik']))->assertOk()->assertSee('Черновик новости');

        auth()->logout();
        $this->flushSession();
        $this->get(route('events.show', ['event' => 'chernovik']))->assertNotFound();
    }

    public function test_an_unknown_address_is_not_found_and_leaves_nothing_in_the_cache(): void
    {
        $this->get(route('events.show', ['event' => 'net-takoy']))->assertNotFound();

        $this->assertNull(Cache::store(PublicCache::STORE)->get(md5(url('/')).'.events.article.net-takoy'));

        // Появилась в базе: страница открывается, а не берётся «ненайденной» из кеша.
        $this->news('net-takoy', 'Теперь есть');
        $this->get(route('events.show', ['event' => 'net-takoy']))->assertOk()->assertSee('Теперь есть');
    }

    public function test_links_use_the_address_the_site_was_opened_on(): void
    {
        $this->news();
        $this->get(route('events'))->assertOk();

        // Кеш одного адреса не достаётся другому: в карточках абсолютные ссылки.
        $original = url('/');

        $this->assertSame('first', PublicCache::remember('probe', fn () => 'first'));
        URL::forceRootUrl('http://other.example');
        $this->assertSame('second', PublicCache::remember('probe', fn () => 'second'));
        URL::forceRootUrl($original);
        $this->assertSame('first', PublicCache::remember('probe', fn () => 'never'));
    }

    // ---------------------------------------------------------------- фотоальбомы и остальные разделы

    public function test_photo_albums_and_their_pages_come_from_the_cache(): void
    {
        $this->album('forum', 'Форум 2026');

        $tables = ['albums', 'album_translations'];
        $first = $this->queriesTo($tables, fn () => $this->get(route('media.photos'))->assertOk()->assertSee('Форум 2026')->assertSee('2 фото'));
        $second = $this->queriesTo($tables, fn () => $this->get(route('media.photos'))->assertOk()->assertSee('Форум 2026')->assertSee('2 фото'));

        $this->assertNotEmpty($first);
        $this->assertSame([], $second, 'список альбомов из кеша: '.implode(' | ', $second));

        $first = $this->queriesTo($tables, fn () => $this->get(route('media.photos.show', 'forum'))->assertOk()->assertSee('/uploads/2026/09/p2.webp', false));
        $second = $this->queriesTo($tables, fn () => $this->get(route('media.photos.show', 'forum'))->assertOk()->assertSee('/uploads/2026/09/p2.webp', false));

        $this->assertNotEmpty($first);
        $this->assertSame([], $second, 'страница альбома из кеша: '.implode(' | ', $second));
    }

    public function test_a_new_album_and_a_deleted_one_show_up_at_once(): void
    {
        $album = $this->album('forum', 'Форум 2026');
        $this->get(route('media.photos'))->assertSee('Форум 2026');

        $this->album('vstrecha', 'Встреча участниц');
        $this->get(route('media.photos'))->assertSee('Форум 2026')->assertSee('Встреча участниц');

        $album->delete();
        $this->get(route('media.photos'))->assertDontSee('Форум 2026')->assertSee('Встреча участниц');
        $this->get(route('media.photos.show', 'forum'))->assertNotFound();
    }

    public function test_list_pages_are_cached_each_and_keep_their_pager(): void
    {
        foreach (range(1, 13) as $i) {
            $this->album("album-{$i}", "Альбом {$i}", ['published_at' => now()->subMinutes($i)]);
        }

        $tables = ['albums', 'album_translations'];
        $this->get(route('media.photos'))->assertOk()->assertSee('Альбом 1<', false)->assertSee('1 / 2')->assertSee('page=2', false);
        $this->get(route('media.photos', ['page' => 2]))->assertOk()->assertSee('Альбом 13<', false)->assertSee('2 / 2');

        $this->assertSame([], $this->queriesTo($tables, fn () => $this->get(route('media.photos'))->assertOk()->assertSee('1 / 2')));
        $this->assertSame([], $this->queriesTo($tables, fn () => $this->get(route('media.photos', ['page' => 2]))->assertOk()->assertSee('Альбом 13<', false)->assertSee('2 / 2')));
    }

    public function test_far_away_pages_do_not_fill_the_cache(): void
    {
        $this->album('forum', 'Форум 2026');

        $page = PublicCache::MAX_CACHED_PAGE + 5;
        $this->get(route('media.photos', ['page' => $page]))->assertOk();

        $this->assertNull(Cache::store(PublicCache::STORE)->get(md5(url('/')).".photos.list.page.{$page}"));

        // А обычные страницы списка кешируются.
        $this->get(route('media.photos'))->assertOk();
        $this->assertNotNull(Cache::store(PublicCache::STORE)->get(md5(url('/')).'.photos.list.page.1'));
    }

    public function test_a_tag_rename_reaches_the_cached_publications(): void
    {
        $tag = Tag::create(['color' => '#FFE4E1']);
        $tag->translations()->create(['locale' => 'ru', 'name' => 'Каталог']);

        $post = Post::create(['slug' => 'katalog', 'status' => 'published', 'published_at' => now()->subDay(), 'tag_id' => $tag->id]);
        $post->translations()->create(['locale' => 'ru', 'title' => 'Каталог 2026', 'excerpt' => 'Все участницы']);

        $this->get(route('media.publications'))->assertSee('Каталог 2026')->assertSee('Каталог<', false);

        $tag->translations()->first()->update(['name' => 'Брошюра']);

        $this->get(route('media.publications'))->assertSee('Брошюра<', false);
    }

    public function test_the_landing_previews_follow_the_news_and_come_from_the_cache(): void
    {
        $this->news();
        $this->get('/')->assertOk();

        $tables = ['events', 'event_translations', 'experts', 'expert_translations'];
        $this->assertSame([], $this->queriesTo($tables, fn () => $this->get('/')->assertOk()));

        $this->news('vtoraya', 'Вторая новость', ['position' => 2]);
        $this->get('/')->assertSee('Вторая новость');
    }

    // ---------------------------------------------------------------- когда кеш сбрасывается

    public function test_any_change_in_the_admin_flushes_the_cache_even_without_a_model(): void
    {
        PublicCache::remember('probe', fn () => 'old');

        $middleware = new FlushPublicCacheAfterAdminChange();
        $next = fn () => response('ok');

        // Просмотр страницы кеш не трогает.
        $middleware->handle(Request::create('/admin/events', 'GET'), $next);
        $this->assertSame('old', PublicCache::remember('probe', fn () => 'new'));

        // Сдвиг порядка, удаление, сохранение формы: сброс.
        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            PublicCache::remember('probe', fn () => 'old');
            $middleware->handle(Request::create('/admin/events/1', $method), $next);
            $this->assertSame('new', PublicCache::remember('probe', fn () => 'new'), $method);
            PublicCache::flush();
        }

        // Сбой сервера (500) ничего не меняет: сбрасывать нечего.
        PublicCache::remember('probe', fn () => 'old');
        $middleware->handle(Request::create('/admin/events/1', 'POST'), fn () => response('boom', 500));
        $this->assertSame('old', PublicCache::remember('probe', fn () => 'new'));
    }

    public function test_the_admin_routes_carry_the_flush_middleware(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            if (str_starts_with($route->getName() ?? '', 'admin.') && ! in_array('GET', $route->methods(), true)) {
                $this->assertContains('admin.flush-public-cache', $route->gatherMiddleware(), $route->getName());
            }
        }
    }

    public function test_the_cache_can_be_cleared_by_a_command_and_with_optimize_clear(): void
    {
        PublicCache::remember('probe', fn () => 'old');

        $this->artisan('public-cache:clear')->expectsOutputToContain('Кеш публичных страниц очищен')->assertSuccessful();
        $this->assertSame('new', PublicCache::remember('probe', fn () => 'new'));

        // Обычная выкладка (`optimize:clear`) сбрасывает его заодно.
        $registered = (new ReflectionProperty(ServiceProvider::class, 'optimizeClearCommands'))->getValue();
        $this->assertContains('public-cache:clear', $registered);
        $this->assertArrayHasKey('public-cache:clear', Artisan::all());
    }

    // ---------------------------------------------------------------- где лежит кеш и фото в браузере

    public function test_the_public_cache_has_its_own_file_store_without_the_database(): void
    {
        // В тестах он в памяти (phpunit.xml), на сервере это файлы: свой каталог, не база и не общий кеш.
        $store = config('cache.stores.public');
        $this->assertSame('array', $store['driver']);
        $this->assertStringEndsWith('framework/cache/public', str_replace('\\', '/', $store['path']));

        $config = file_get_contents(config_path('cache.php'));
        $this->assertStringContainsString("env('PUBLIC_CACHE_DRIVER', 'file')", $config);

        // На новой установке (.env.example) кеш и сессии в файлах: публичные страницы не ходят за ними в базу.
        $env = file_get_contents(base_path('.env.example'));
        $this->assertMatchesRegularExpression('/^CACHE_STORE=file$/m', $env);
        $this->assertMatchesRegularExpression('/^SESSION_DRIVER=file$/m', $env);
        $this->assertStringContainsString("env('CACHE_STORE', 'file')", $config);
        $this->assertStringContainsString("env('SESSION_DRIVER', 'file')", file_get_contents(config_path('session.php')));
    }

    public function test_photos_get_long_browser_cache_headers(): void
    {
        $htaccess = file_get_contents(public_path('.htaccess'));

        // Загрузки и собранные скрипты: имя уникально, значит год и без проверки.
        $this->assertStringContainsString('SetEnvIf Request_URI "/(uploads|build)/" LONG_LIVED_FILE', $htaccess);
        $this->assertStringContainsString('Header set Cache-Control "public, max-age=31536000, immutable" env=LONG_LIVED_FILE', $htaccess);
        // Остальные картинки и шрифты (под постоянными именами): неделя.
        $this->assertStringContainsString('<FilesMatch "\.(avif|gif|ico|jpe?g|png|svg|webp|woff2?)$">', $htaccess);
        $this->assertStringContainsString('Header setifempty Cache-Control "public, max-age=604800"', $htaccess);

        // Без нужных модулей Apache блоки молча пропускаются, сайт не падает.
        $this->assertStringContainsString('<IfModule mod_headers.c>', $htaccess);
        $this->assertStringContainsString('<IfModule mod_setenvif.c>', $htaccess);
        // Переписывание адресов и защита страниц остались на месте.
        $this->assertStringContainsString('RewriteRule ^ index.php [L]', $htaccess);
    }
}
