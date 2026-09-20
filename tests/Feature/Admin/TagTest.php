<?php

namespace Tests\Feature\Admin;

use App\Models\SiteOpportunity;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_is_created_with_three_names_and_colour(): void
    {
        $this->actingAsAdmin()->post(route('admin.tags.store'), [
            'color' => '#12AB34',
            'names' => ['ru' => 'Грант', 'ro' => 'Grant', 'en' => 'Grant'],
        ])->assertRedirect(route('admin.tags.index'));

        $tag = Tag::with('translations')->first();
        $this->assertSame('#12ab34', $tag->color);
        $this->assertCount(3, $tag->translations);
    }

    public function test_invalid_hex_is_rejected(): void
    {
        $this->actingAsAdmin()->post(route('admin.tags.store'), [
            'color' => 'not-a-colour',
            'names' => ['ru' => 'Г', 'ro' => 'G', 'en' => 'G'],
        ])->assertSessionHasErrors('color');

        $this->assertDatabaseCount('tags', 0);
    }

    public function test_deleting_a_tag_keeps_material_and_only_clears_the_link(): void
    {
        $tag = Tag::create(['color' => '#000000']);
        $opportunity = SiteOpportunity::create(['slug' => 'o1', 'tag_id' => $tag->id]);
        $post = \App\Models\Post::create(['slug' => 'p1', 'tag_id' => $tag->id]);

        $this->actingAsAdmin()->delete(route('admin.tags.destroy', $tag))->assertRedirect();

        $this->assertModelExists($opportunity->fresh());
        $this->assertModelExists($post->fresh());
        $this->assertNull($opportunity->fresh()->tag_id);
        $this->assertNull($post->fresh()->tag_id);
    }

    public function test_news_can_be_tagged(): void
    {
        $tag = Tag::create(['color' => '#0066cc']);
        $tag->translations()->create(['locale' => 'ru', 'name' => 'Грант']);

        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'tag_id' => $tag->id,
            'translations' => ['ru' => ['title' => 'С тегом', 'content' => '[]']],
        ]);

        $this->assertSame($tag->id, \App\Models\Post::first()->tag_id);
    }

    public function test_unknown_tag_id_on_news_is_rejected(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'tag_id' => 999,
            'translations' => ['ru' => ['title' => 'Т', 'content' => '[]']],
        ])->assertSessionHasErrors('tag_id');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_tag_shows_on_public_news_page(): void
    {
        $tag = Tag::create(['color' => '#1d8a3f']);
        $tag->translations()->create(['locale' => 'ru', 'name' => 'Событие']);

        $post = \App\Models\Post::create([
            'slug' => 'novost', 'status' => 'published', 'published_at' => now()->subDay(), 'tag_id' => $tag->id,
        ]);
        $post->translations()->create(['locale' => 'ru', 'title' => 'Заголовок']);

        $this->get('/media/publications/novost')->assertOk()->assertSee('Событие');
    }
}
