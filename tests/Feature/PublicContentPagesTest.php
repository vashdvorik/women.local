<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Event;
use App\Models\Expert;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteOpportunity;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Публичные страницы, читающие материалы из админки: публикации, возможности,
 * фотоальбомы, видео, проекты, а также превью на главной и подписка в подвале.
 */
class PublicContentPagesTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $attributes = [], array $translations = []): Post
    {
        $post = Post::create(array_merge([
            'slug' => 'novost-'.uniqid(),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $attributes));

        foreach ($translations ?: ['ru' => ['title' => 'Заголовок публикации']] as $locale => $fields) {
            $post->translations()->create(['locale' => $locale, ...$fields]);
        }

        return $post;
    }

    // ------------------------------------------------------------------ публикации

    public function test_publications_list_shows_only_published_posts_newest_first(): void
    {
        $this->makePost(['published_at' => now()->subDays(3)], ['ru' => ['title' => 'Старая публикация', 'excerpt' => 'Кратко']]);
        $this->makePost(['published_at' => now()->subDay()], ['ru' => ['title' => 'Свежая публикация']]);
        $this->makePost(['status' => 'draft', 'published_at' => null], ['ru' => ['title' => 'Черновая публикация']]);
        $this->makePost(['published_at' => now()->addDay()], ['ru' => ['title' => 'Будущая публикация']]);

        $response = $this->get(route('media.publications'));
        $content = $response->getContent();

        $response->assertOk()
            ->assertSee('Старая публикация')
            ->assertSee('Свежая публикация')
            ->assertDontSee('Черновая публикация')
            ->assertDontSee('Будущая публикация');

        $this->assertLessThan(strpos($content, 'Старая публикация'), strpos($content, 'Свежая публикация'));
        $this->assertSame(1, substr_count($content, 'id="miro-nav"'));
    }

    public function test_publications_list_shows_the_empty_state_when_nothing_is_published(): void
    {
        $this->get(route('media.publications'))
            ->assertOk()
            ->assertSee('Раздел готовится')
            ->assertDontSee('miro-event-card"', false);
    }

    public function test_publication_page_renders_blocks_in_every_language_and_sanitises_text(): void
    {
        $tag = Tag::create(['color' => '#1d8a3f']);
        $tag->translations()->create(['locale' => 'ru', 'name' => 'Событие']);

        $post = $this->makePost(['slug' => 'itogi', 'tag_id' => $tag->id], [
            'ru' => ['title' => 'Итоги встречи', 'excerpt' => 'Коротко о встрече', 'content' => [
                ['uid' => 'a', 'type' => 'heading', 'data' => ['text' => 'Главное', 'level' => 'h2']],
                ['uid' => 'b', 'type' => 'text', 'data' => ['html' => '<p>Мы встретились.</p><script>alert(1)</script>']],
                ['uid' => 'c', 'type' => 'gallery_2', 'data' => ['images' => ['2026/09/a.webp', '2026/09/b.webp']]],
            ]],
            'en' => ['title' => 'Meeting recap', 'content' => [
                ['uid' => 'b', 'type' => 'text', 'data' => ['html' => '<p>We met.</p>']],
            ]],
        ]);

        $response = $this->get(route('media.publications.show', $post));

        $response->assertOk()
            ->assertSee('Итоги встречи')
            ->assertSee('Meeting recap')
            ->assertSee('Событие')
            ->assertSee('Мы встретились.')
            ->assertSee('We met.')
            ->assertSee('/uploads/2026/09/a.webp', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        // Английская версия: переведённый текст, а заголовок блока берётся из русской.
        $this->assertStringContainsString('data-lang="en"', $response->getContent());
    }

    public function test_draft_and_future_publications_are_hidden_from_guests_but_previewable_by_the_admin(): void
    {
        $draft = $this->makePost(['status' => 'draft', 'published_at' => null, 'slug' => 'chernovik'], ['ru' => ['title' => 'Черновик']]);
        $future = $this->makePost(['published_at' => now()->addWeek(), 'slug' => 'budushchee'], ['ru' => ['title' => 'Будущая']]);

        $this->get(route('media.publications.show', $draft))->assertNotFound();
        $this->get(route('media.publications.show', $future))->assertNotFound();

        $this->actingAsAdmin()->get(route('media.publications.show', $draft))
            ->assertOk()
            ->assertSee('Черновик: эту страницу видит только администратор');
    }

    // ------------------------------------------------------------------ возможности

    public function test_opportunities_list_and_page_show_the_deadline(): void
    {
        $opportunity = SiteOpportunity::create([
            'slug' => 'grant', 'status' => 'published', 'published_at' => now()->subDay(), 'deadline_at' => '2026-10-15',
        ]);
        $opportunity->translations()->create(['locale' => 'ru', 'title' => 'Грант на развитие', 'excerpt' => 'Приём заявок']);
        SiteOpportunity::create(['slug' => 'skrytaya', 'status' => 'draft'])
            ->translations()->create(['locale' => 'ru', 'title' => 'Скрытая возможность']);

        $this->get(route('opportunities'))
            ->assertOk()
            ->assertSee('Грант на развитие')
            ->assertSee('Подать заявку до 15 октября 2026')
            ->assertSee('Apply by October 15, 2026')
            ->assertDontSee('Скрытая возможность');

        $this->get(route('opportunities.show', $opportunity))->assertOk()->assertSee('Грант на развитие');
        $this->get('/opportunities/skrytaya')->assertNotFound();
    }

    // ------------------------------------------------------------------ фото и видео

    public function test_albums_list_and_page_show_photos(): void
    {
        $album = Album::create([
            'slug' => 'forum', 'status' => 'published', 'published_at' => now()->subDay(),
            'cover_path' => '2026/09/cover.webp',
            'blocks' => [['uid' => 'g', 'type' => 'gallery_2', 'data' => ['images' => ['2026/09/p1.webp', '2026/09/p2.webp']]]],
        ]);
        $album->translations()->create(['locale' => 'ru', 'title' => 'Форум 2026', 'excerpt' => 'Как это было']);

        $this->get(route('media.photos'))
            ->assertOk()
            ->assertSee('Форум 2026')
            ->assertSee('2 фото')
            ->assertSee('2 photos')
            ->assertSee('/uploads/2026/09/cover.webp', false);

        $this->get(route('media.photos.show', $album))
            ->assertOk()
            ->assertSee('/uploads/2026/09/p1.webp', false)
            ->assertSee('/uploads/2026/09/p2.webp', false);
    }

    public function test_videos_page_lists_youtube_videos_in_the_admin_order_with_thumbnails(): void
    {
        $second = Video::create(['youtube_id' => 'BBBBBBBBBBB', 'youtube_url' => 'https://youtu.be/BBBBBBBBBBB', 'position' => 2]);
        $second->translations()->create(['locale' => 'ru', 'title' => 'Второе видео']);
        $first = Video::create(['youtube_id' => 'AAAAAAAAAAA', 'youtube_url' => 'https://youtu.be/AAAAAAAAAAA', 'position' => 1]);
        $first->translations()->create(['locale' => 'ru', 'title' => 'Первое видео']);

        $response = $this->get(route('media.videos'));
        $content = $response->getContent();

        $response->assertOk()
            ->assertSee('https://i.ytimg.com/vi/AAAAAAAAAAA/hqdefault.jpg', false)
            ->assertSee('https://youtu.be/AAAAAAAAAAA', false);

        $this->assertLessThan(strpos($content, 'Второе видео'), strpos($content, 'Первое видео'));
    }

    // ------------------------------------------------------------------ проекты

    public function test_projects_page_lists_only_published_projects_with_external_links(): void
    {
        $shown = Project::create(['is_published' => true, 'position' => 1, 'url' => 'https://project.example.test']);
        $shown->translations()->create(['locale' => 'ru', 'title' => 'Клуб предпринимательниц', 'category' => 'Сообщество', 'text' => 'Описание проекта']);
        Project::create(['is_published' => false, 'position' => 2])
            ->translations()->create(['locale' => 'ru', 'title' => 'Скрытый проект']);

        $this->get(route('projects'))
            ->assertOk()
            ->assertSee('Клуб предпринимательниц')
            ->assertSee('Сообщество')
            ->assertSee('https://project.example.test', false)
            ->assertDontSee('Скрытый проект');
    }

    // ------------------------------------------------------------------ темы

    public function test_pages_connected_to_the_admin_fall_back_to_miro_in_other_themes(): void
    {
        SiteSetting::setLandingTheme('fortun');

        // У прочих тем этих страниц нет: они выводятся целиком в оформлении miro, вместе
        // со стилями и картинками, а не «наполовину» из чужой темы.
        foreach (['/experts', '/projects', '/opportunities', '/media/publications', '/media/photos', '/media/videos'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('/themes/public/miro/css/navigation.css', false)
                ->assertDontSee('/themes/public/fortun/', false);
        }

        // Страница событий у темы fortun своя (старая вёрстка), она остаётся как есть.
        $this->get('/events')->assertOk()->assertSee('/themes/public/fortun/', false);
    }

    // ------------------------------------------------------------------ главная и эксперты

    public function test_landing_previews_come_from_the_database(): void
    {
        foreach (range(1, 8) as $i) {
            Expert::create(['position' => $i, 'is_published' => $i !== 2])
                ->translations()->create(['locale' => 'ru', 'name' => "Эксперт номер {$i}"]);
        }
        foreach (range(1, 5) as $i) {
            Event::create(['position' => $i])->translations()->create(['locale' => 'ru', 'title' => "Событие номер {$i}"]);
        }

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Эксперт номер 1')
            ->assertDontSee('Эксперт номер 2')  // не опубликован
            ->assertSee('Эксперт номер 7')
            ->assertDontSee('Эксперт номер 8')  // только шесть первых
            ->assertSee('Событие номер 3')
            ->assertDontSee('Событие номер 4'); // только три первых

        $this->assertSame(6, substr_count($response->getContent(), 'class="miro-member-card"'));
    }

    public function test_experts_page_lists_published_experts_with_photo_and_languages(): void
    {
        $expert = Expert::create(['position' => 1, 'tone' => 'teal', 'photo_path' => '2026/09/portrait.webp']);
        $expert->translations()->create(['locale' => 'ru', 'name' => 'Каролина Бугаян', 'role' => 'Президент AFAM', 'description' => 'Развивает бизнес.']);
        $expert->translations()->create(['locale' => 'en', 'name' => 'Carolina Bugaiyan']);
        Expert::create(['position' => 2, 'is_published' => false])->translations()->create(['locale' => 'ru', 'name' => 'Скрытый эксперт']);

        $response = $this->get(route('experts'));

        $response->assertOk()
            ->assertSee('Каролина Бугаян')
            ->assertSee('Carolina Bugaiyan')
            ->assertSee('Президент AFAM')
            ->assertSee('/uploads/2026/09/portrait.webp', false)
            ->assertSee('var(--miro-teal)', false)
            ->assertDontSee('Скрытый эксперт');

        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-public-member"'));
    }

    // ------------------------------------------------------------------ подписка

    public function test_subscription_stores_the_email_and_confirms_in_the_footer(): void
    {
        $this->from('/events')
            ->post(route('subscribe'), ['email' => 'Reader@Example.Test', 'name' => 'Мария', 'consent' => '1'])
            ->assertRedirect(url('/events').'#subscribe')
            ->assertSessionHas('subscribed');

        $this->assertDatabaseHas('subscribers', ['email' => 'reader@example.test', 'name' => 'Мария']);

        $this->withSession(['subscribed' => true])->get(route('events'))
            ->assertSee('Thank you! You are subscribed.');
    }

    public function test_repeat_subscription_does_not_duplicate_and_keeps_the_name_when_none_is_sent(): void
    {
        $this->post(route('subscribe'), ['email' => 'reader@example.test', 'name' => 'Мария', 'consent' => '1']);
        $this->post(route('subscribe'), ['email' => 'reader@example.test', 'consent' => '1']);

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertSame('Мария', Subscriber::first()->name);
    }

    public function test_subscription_requires_a_valid_email_and_consent(): void
    {
        $this->post(route('subscribe'), ['email' => 'not-an-email', 'consent' => '1'])
            ->assertSessionHas('subscribe_error');
        $this->post(route('subscribe'), ['email' => 'reader@example.test'])
            ->assertSessionHas('subscribe_error');

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post(route('subscribe'), ['email' => 'bot@example.test', 'consent' => '1', 'website' => 'http://spam.test'])
            ->assertSessionHas('subscribe_error');

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_subscription_is_rate_limited(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post(route('subscribe'), ['email' => "reader{$i}@example.test", 'consent' => '1'])->assertRedirect();
        }

        $this->post(route('subscribe'), ['email' => 'reader6@example.test', 'consent' => '1'])->assertStatus(429);
    }
}
