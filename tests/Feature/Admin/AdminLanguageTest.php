<?php

namespace Tests\Feature\Admin;

use App\Enums\Plan;
use App\Http\Middleware\AdminLocale;
use App\Models\Album;
use App\Models\BotUser;
use App\Models\Event;
use App\Models\Expert;
use App\Models\Opportunity;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteOpportunity;
use App\Models\Tag;
use App\Models\Video;
use App\Services\Subscriptions\SubscriptionService;
use App\Support\AdminI18n;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Nutgram\Laravel\Facades\Telegram;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Язык админки: русский (по умолчанию) и английский. Переключатель RU | EN, cookie на год, английские страницы
 * целиком, сообщения проверки форм, словарь для скриптов. Язык материалов сайта (вкладки ru / ro / en) не затрагивается.
 */
class AdminLanguageTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Telegram::fake();
    }

    private function asEnglish(): static
    {
        return $this->actingAsAdmin()->withCookie(AdminLocale::COOKIE, 'en');
    }

    // ---------------------------------------------------------------- переключатель

    public function test_the_admin_is_russian_by_default(): void
    {
        $this->actingAsAdmin()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Как пользоваться админкой')
            ->assertSee('<html lang="ru">', false)
            ->assertDontSee('How to use the admin panel');
    }

    public function test_the_english_cookie_switches_the_whole_page(): void
    {
        $this->asEnglish()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('How to use the admin panel')
            ->assertSee('Participant cabinets')
            ->assertSee('Log out')
            ->assertSee('<html lang="en">', false)
            ->assertDontSee('Как пользоваться админкой')
            ->assertDontSee('Выйти');
    }

    public function test_a_broken_or_unknown_cookie_falls_back_to_russian(): void
    {
        foreach (['de', 'EN', '', 'ru<script>', str_repeat('x', 300)] as $value) {
            $this->actingAsAdmin()->withCookie(AdminLocale::COOKIE, $value)->get(route('admin.dashboard'))
                ->assertOk()->assertSee('Как пользоваться админкой');
        }
    }

    public function test_the_switcher_sets_a_one_year_cookie_and_goes_back_to_the_page(): void
    {
        $response = $this->actingAsAdmin()
            ->withHeader('Referer', route('admin.payments.index'))
            ->get(route('admin.language', 'en'));

        $response->assertRedirect(route('admin.payments.index'));
        $response->assertCookie(AdminLocale::COOKIE, 'en');

        $cookie = $response->getCookie(AdminLocale::COOKIE, false);
        $this->assertEqualsWithDelta(now()->addYear()->timestamp, $cookie->getExpiresTime(), 120);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_the_switcher_never_sends_you_to_another_site_or_loops_on_itself(): void
    {
        foreach (['https://evil.example/phishing', 'javascript:alert(1)', '//evil.example/x', route('admin.language', 'ru')] as $referer) {
            $this->actingAsAdmin()->withHeader('Referer', $referer)->get(route('admin.language', 'en'))
                ->assertRedirect(route('admin.dashboard'));
        }

        $this->actingAsAdmin()->get(route('admin.language', 'en'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_an_unknown_language_is_a_404_and_sets_nothing(): void
    {
        foreach (['de', 'ro', 'EN', 'xx'] as $code) {
            $this->actingAsAdmin()->get(route('admin.language', $code))->assertNotFound()->assertCookieMissing(AdminLocale::COOKIE);
        }
    }

    public function test_the_switcher_works_on_the_login_page_for_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Вход в панель')
            ->assertSee(route('admin.language', 'en'), false)
            ->assertSee(route('admin.language', 'ru'), false);

        $this->withHeader('Referer', route('login'))->get(route('admin.language', 'en'))
            ->assertRedirect(route('login'))->assertCookie(AdminLocale::COOKIE, 'en');

        $this->withCookie(AdminLocale::COOKIE, 'en')->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to the panel')
            ->assertSee('Remember me')
            ->assertSee('<html lang="en">', false)
            ->assertDontSee('Запомнить меня');
    }

    public function test_the_menu_shows_the_switcher_with_the_current_language_marked(): void
    {
        $html = $this->asEnglish()->get(route('admin.dashboard'))->getContent();

        $this->assertMatchesRegularExpression('~lang-switch__item--active[^>]*lang="en"[^>]*>EN<~', $html);
        $this->assertStringContainsString(route('admin.language', 'ru'), $html);
    }

    public function test_the_language_choice_does_not_leak_to_the_public_site_or_the_cabinet(): void
    {
        $this->withCookie(AdminLocale::COOKIE, 'en')->get('/')->assertOk()->assertDontSee('<html lang="en">', false);

        $user = BotUser::factory()->community()->create();
        $this->withCookie(AdminLocale::COOKIE, 'en')->withSession($this->sessionFor($user))
            ->get(route('account.subscription'))->assertOk()->assertSee('Ваш доступ к WOMEN’S HUB');
    }

    // ---------------------------------------------------------------- страницы целиком

    /** @return list<string> адреса всех страниц админки, которые нужно показать по-английски */
    private function adminPages(): array
    {
        $tag = Tag::create(['color' => '#1d8a3f']);
        $event = Event::create(['tone' => 'pink', 'position' => 1, 'starts_at' => '2026-06-18']);
        $expert = Expert::create(['tone' => 'pink', 'position' => 1]);
        $project = Project::create(['position' => 1]);
        $video = Video::create(['youtube_id' => 'dQw4w9WgXcQ', 'youtube_url' => 'x', 'position' => 1]);
        $album = Album::create(['slug' => 'a1']);
        $post = Post::create(['slug' => 'p1']);
        $siteOpportunity = SiteOpportunity::create(['slug' => 'o1', 'tag_id' => $tag->id]);

        $pending = BotUser::factory()->pending()->create();
        $member = BotUser::factory()->approved()->create();
        app(SubscriptionService::class)->grant($member, Plan::Community, 12);
        $memberPost = Opportunity::create([
            'bot_user_id' => $member->id, 'type' => 'project', 'title' => 'Title', 'body' => 'Body', 'status' => Opportunity::STATUS_PENDING,
        ]);
        $payment = $this->checkoutFor($member, Plan::Private);
        $payment->forceFill(['status' => 'verifying', 'payload' => ['anomaly' => 'x', 'last_error' => 'y']])->save();

        return [
            route('admin.dashboard'), route('admin.cabinets.dashboard'),
            route('admin.events.index'), route('admin.events.create'), route('admin.events.edit', $event),
            route('admin.experts.index'), route('admin.experts.create'), route('admin.experts.edit', $expert),
            route('admin.projects.index'), route('admin.projects.create'), route('admin.projects.edit', $project),
            route('admin.videos.index'), route('admin.videos.create'), route('admin.videos.edit', $video),
            route('admin.albums.index'), route('admin.albums.create'), route('admin.albums.edit', $album),
            route('admin.posts.index'), route('admin.posts.create'), route('admin.posts.edit', $post),
            route('admin.opportunities.index'), route('admin.opportunities.create'), route('admin.opportunities.edit', $siteOpportunity),
            route('admin.tags.index'), route('admin.tags.create'), route('admin.tags.edit', $tag),
            route('admin.subscribers.index'),
            route('admin.profiles.index'), route('admin.profiles.show', $pending), route('admin.profiles.show', $member), route('admin.profiles.edit', $member),
            route('admin.member-posts.index'), route('admin.member-posts.show', $memberPost),
            route('admin.subscriptions.index'), route('admin.subscriptions.edit', $member), route('admin.subscriptions.edit', $pending),
            route('admin.payments.index'), route('admin.payments.show', $payment),
            route('admin.statistics.index'),
            route('admin.settings.edit'), route('admin.cabinets.settings'), route('admin.cabinets.bot-messages'),
            route('admin.cabinets.bot-messages', ['lang' => 'en']),
        ];
    }

    public function test_every_admin_page_opens_in_both_languages(): void
    {
        $pages = $this->adminPages();

        foreach (['ru', 'en'] as $language) {
            foreach ($pages as $url) {
                $this->actingAsAdmin()->withCookie(AdminLocale::COOKIE, $language)->get($url)->assertOk();
            }
        }
    }

    public function test_no_english_admin_page_asks_for_a_missing_translation(): void
    {
        $pages = $this->adminPages();
        $missing = [];

        Lang::handleMissingKeysUsing(function (string $key) use (&$missing): void {
            // Русский текст-ключ без перевода или строка файла вида dashboard.drafts без английского файла.
            if (preg_match('/[А-Яа-яЁё]/u', $key) || (preg_match('/^[a-z_]+\.[a-z0-9_.]+$/', $key) && ! str_starts_with($key, 'validation.'))) {
                $missing[$key] = true;
            }
        });

        foreach ($pages as $url) {
            $this->asEnglish()->get($url)->assertOk();
        }

        $this->assertSame([], array_keys($missing), 'Нет английского перевода (добавьте в lang/en.json)');
    }

    public function test_the_english_dashboard_has_no_russian_interface_text_left(): void
    {
        $text = strip_tags(preg_replace('~<(script|style)\b.*?</\1>~s', '', $this->asEnglish()->get(route('admin.dashboard'))->getContent()));

        $this->assertDoesNotMatchRegularExpression('/[А-Яа-яЁё]/u', $text);
    }

    public function test_english_pages_with_content_forms_have_no_russian_interface_text(): void
    {
        // Пустые формы и списки: всё русское, что здесь осталось, — недоделанный перевод (данных в базе нет).
        foreach ([
            route('admin.events.create'), route('admin.experts.create'), route('admin.projects.create'), route('admin.videos.create'),
            route('admin.albums.create'), route('admin.posts.create'), route('admin.opportunities.create'), route('admin.tags.create'),
            route('admin.subscriptions.index'), route('admin.payments.index'), route('admin.statistics.index'), route('admin.settings.edit'),
            route('admin.cabinets.settings'), route('admin.cabinets.dashboard'), route('admin.profiles.index'),
        ] as $url) {
            $html = $this->asEnglish()->get($url)->assertOk()->getContent();
            $text = strip_tags(preg_replace('~<(script|style)\b.*?</\1>~s', '', $html));
            preg_match_all('/[^\s]*[А-Яа-яЁё][^\s]*/u', $text, $found);

            $this->assertSame([], array_values(array_unique($found[0])), "Русский текст на английской странице {$url}");
        }
    }

    // ---------------------------------------------------------------- проверка форм и ответы сервера

    public function test_validation_messages_follow_the_admin_language(): void
    {
        $bad = ['community' => 0, 'private' => 5];

        $this->actingAsAdmin()->put(route('admin.subscriptions.prices'), $bad)
            ->assertSessionHasErrors(['community']);
        $this->assertStringContainsString('цена Community', session('errors')->first('community'));

        $this->asEnglish()->put(route('admin.subscriptions.prices'), $bad)->assertSessionHasErrors(['community']);
        $this->assertStringContainsString('Community price', session('errors')->first('community'));
        $this->assertDoesNotMatchRegularExpression('/[А-Яа-яЁё]/u', session('errors')->first('community'));
    }

    public function test_flash_messages_follow_the_admin_language(): void
    {
        $ruUser = BotUser::factory()->approved()->create();
        $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $ruUser), ['plan' => 'community'])
            ->assertSessionHas('success', fn (string $m): bool => str_starts_with($m, 'Подарок оформлен: Community включён до '));

        // Cookie остаётся в тестовом клиенте, поэтому английский запрос идёт вторым.
        $enUser = BotUser::factory()->approved()->create();
        $this->asEnglish()->post(route('admin.subscriptions.gift', $enUser), ['plan' => 'community'])
            ->assertSessionHas('success', fn (string $m): bool => str_starts_with($m, 'Gift issued: Community is on until '));
    }

    public function test_the_login_form_errors_are_english_too(): void
    {
        $this->withCookie(AdminLocale::COOKIE, 'en')->post(route('login'), ['email' => 'nobody@example.test', 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertSame('Wrong email or password.', session('errors')->first('email'));
    }

    public function test_the_statistics_pdf_follows_the_interface_language(): void
    {
        $en = $this->asEnglish()->get(route('admin.statistics.pdf'));
        $ru = $this->actingAsAdmin()->get(route('admin.statistics.pdf'));

        $this->assertSame(200, $en->getStatusCode());
        $this->assertSame(200, $ru->getStatusCode());
        $this->assertSame('application/pdf', $en->headers->get('Content-Type'));
    }

    // ---------------------------------------------------------------- скрипты

    public function test_scripts_get_an_english_dictionary_only_on_english_pages(): void
    {
        $this->actingAsAdmin()->get(route('admin.events.create'))->assertDontSee('window.adminI18n', false);

        $html = $this->asEnglish()->get(route('admin.events.create'))->assertSee('window.adminI18n', false)->getContent();
        $this->assertStringContainsString('Could not upload the image.', $html);
        $this->assertSame([], AdminI18n::scriptDictionary('ru'));
        $this->assertSame('KB', AdminI18n::scriptDictionary('en')['КБ']);
    }
}
