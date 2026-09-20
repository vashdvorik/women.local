<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_saves_with_any_fill_level(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.posts.store'), [
                'translations' => ['ru' => ['title' => '', 'content' => '[]']],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('posts', 1);
        $this->assertSame('draft', Post::first()->status->value);
    }

    public function test_slug_is_built_from_russian_title(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => ['ru' => ['title' => 'Щёлкающий ёж', 'content' => '[]']],
        ]);

        $this->assertSame('schyolkayuschiy-yozh', Post::first()->slug);
    }

    public function test_temporary_slug_is_replaced_once_title_appears(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => ['ru' => ['title' => '', 'content' => '[]']],
        ]);
        $post = Post::first();
        $this->assertStringStartsWith('draft-', $post->slug);

        $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'save',
            'translations' => ['ru' => ['title' => 'Настоящий заголовок', 'content' => '[]']],
        ]);

        $this->assertSame('nastoyaschiy-zagolovok', $post->fresh()->slug);
    }

    public function test_meaningful_slug_is_not_overwritten_on_retitle(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => ['ru' => ['title' => 'Первый', 'content' => '[]']],
        ]);
        $post = Post::first();

        $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'save',
            'translations' => ['ru' => ['title' => 'Другой заголовок', 'content' => '[]']],
        ]);

        $this->assertSame('pervyy', $post->fresh()->slug);
    }

    public function test_can_publish_straight_from_the_create_screen(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'intent' => 'publish',
            'translations' => ['ru' => [
                'title' => 'Сразу в публикацию',
                'excerpt' => 'Краткое описание',
                'content' => '[]',
            ]],
        ])->assertSessionHasNoErrors();

        $post = Post::first();
        $this->assertSame('published', $post->status->value);
        $this->assertNotNull($post->published_at);
    }

    public function test_failed_publish_from_create_still_saves_the_draft(): void
    {
        $response = $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'intent' => 'publish',
            'translations' => ['ru' => ['title' => 'Только заголовок', 'content' => '[]']],
        ]);

        // материал создан как черновик, но не опубликован
        $post = Post::first();
        $this->assertNotNull($post);
        $this->assertSame('draft', $post->status->value);

        $response->assertRedirect(route('admin.posts.edit', $post));
        $response->assertSessionHasErrors('translations.ru.excerpt');
        $response->assertSessionHas('publish_failed', true);
    }

    public function test_publish_is_blocked_without_russian_title_and_excerpt(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => ['ru' => ['title' => '', 'content' => '[]']],
        ]);
        $post = Post::first();

        $response = $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'publish',
            'translations' => ['ru' => ['title' => '', 'excerpt' => '', 'content' => '[]']],
        ]);

        $response->assertSessionHasErrors(['translations.ru.title', 'translations.ru.excerpt']);
        $this->assertSame('draft', $post->fresh()->status->value);
    }

    public function test_publish_succeeds_with_required_russian_fields(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => ['ru' => ['title' => 'Заголовок', 'content' => '[]']],
        ]);
        $post = Post::first();

        $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'publish',
            'translations' => ['ru' => ['title' => 'Заголовок', 'excerpt' => 'Описание', 'content' => '[]']],
        ])->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertSame('published', $post->status->value);
        $this->assertNotNull($post->published_at);

        $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'unpublish',
            'translations' => ['ru' => ['title' => 'Заголовок', 'excerpt' => 'Описание', 'content' => '[]']],
        ]);

        $post->refresh();
        $this->assertSame('draft', $post->status->value);
        $this->assertNull($post->published_at);
    }

    public function test_cover_is_saved_and_survives_a_later_save(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'cover_path' => '2026/09/abcdef123456.webp',
            'translations' => ['ru' => ['title' => 'С обложкой', 'content' => '[]']],
        ]);

        $post = Post::first();
        $this->assertSame('2026/09/abcdef123456.webp', $post->cover_path);

        // повторное сохранение без изменения обложки её не теряет
        $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'save',
            'cover_path' => '2026/09/abcdef123456.webp',
            'translations' => ['ru' => ['title' => 'С обложкой', 'content' => '[]']],
        ]);

        $this->assertSame('2026/09/abcdef123456.webp', $post->fresh()->cover_path);
    }

    public function test_seeded_cover_survives_a_later_save(): void
    {
        // Запись, залитая сидом: обложка лежит в public/uploads/seed/, не в ГГГГ/ММ/.
        $post = Post::create(['slug' => 'p-seed', 'cover_path' => 'seed/opp-generation-lab.webp']);
        $post->translations()->create(['locale' => 'ru', 'title' => 'Из сида']);

        // Редактор поправил только текст, обложку не трогал — форма отдаёт её как есть.
        $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'save',
            'cover_path' => 'seed/opp-generation-lab.webp',
            'translations' => ['ru' => ['title' => 'Из сида, правка', 'content' => '[]']],
        ]);

        $this->assertSame('seed/opp-generation-lab.webp', $post->fresh()->cover_path);
    }

    public function test_seeded_content_block_images_survive_a_save(): void
    {
        // Картинка в блоке из демоконтента (путь dev/…) тоже не должна теряться.
        $blocks = json_encode([
            ['uid' => 'b1', 'type' => 'image', 'data' => ['path' => 'dev/photo-1.webp']],
        ]);

        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => ['ru' => ['title' => 'С блоком-картинкой', 'content' => $blocks]],
        ]);

        $post = Post::with('translations')->first();
        $this->assertSame('dev/photo-1.webp', $post->rawTranslation('ru')->content[0]['data']['path']);
    }

    public function test_clearing_the_cover_removes_it(): void
    {
        $post = Post::create(['slug' => 'p1', 'cover_path' => '2026/09/abcdef123456.webp']);
        $post->translations()->create(['locale' => 'ru', 'title' => 'Т']);

        $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'save',
            'cover_path' => '',
            'translations' => ['ru' => ['title' => 'Т', 'content' => '[]']],
        ]);

        $this->assertNull($post->fresh()->cover_path);
    }

    public function test_block_structure_is_shared_across_languages(): void
    {
        $blocks = json_encode([
            ['uid' => 'b1', 'type' => 'text', 'data' => ['html' => '<p>русский</p>']],
        ]);

        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => [
                'ru' => ['title' => 'Т', 'content' => $blocks],
                'ro' => ['title' => 'T', 'content' => json_encode([
                    ['uid' => 'b1', 'type' => 'text', 'data' => ['html' => '<p>română</p>']],
                ])],
            ],
        ]);

        $post = Post::with('translations')->first();
        $ro = $post->translations->firstWhere('locale', 'ro');

        $this->assertCount(1, $ro->content);
        $this->assertSame('b1', $ro->content[0]['uid']);
        $this->assertStringContainsString('română', $ro->content[0]['data']['html']);
    }

    public function test_clearing_a_translation_removes_the_row(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => [
                'ru' => ['title' => 'Т', 'content' => '[]'],
                'ro' => ['title' => 'Titlu', 'content' => '[]'],
            ],
        ]);
        $post = Post::with('translations')->first();
        $this->assertNotNull($post->translations->firstWhere('locale', 'ro'));

        $this->actingAsAdmin()->put(route('admin.posts.update', $post), [
            'intent' => 'save',
            'translations' => [
                'ru' => ['title' => 'Т', 'content' => '[]'],
                'ro' => ['title' => '', 'excerpt' => '', 'content' => '[]'],
            ],
        ]);

        $this->assertNull($post->fresh()->load('translations')->translations->firstWhere('locale', 'ro'));
    }
}
