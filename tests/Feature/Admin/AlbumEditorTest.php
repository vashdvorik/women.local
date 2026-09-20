<?php

namespace Tests\Feature\Admin;

use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlbumEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_album_is_published_by_default(): void
    {
        $this->actingAsAdmin()->post(route('admin.albums.store'), [
            'intent' => 'publish',
            'translations' => ['ru' => ['title' => 'Открытие']],
            'blocks' => '[]',
        ])->assertRedirect();

        $this->assertSame('published', Album::first()->status->value);
    }

    public function test_save_as_draft_from_create_keeps_album_hidden(): void
    {
        $this->actingAsAdmin()->post(route('admin.albums.store'), [
            'intent' => 'unpublish',
            'translations' => ['ru' => ['title' => 'Черновик альбома']],
            'blocks' => '[]',
        ])->assertRedirect();

        $album = Album::first();
        $this->assertSame('draft', $album->status->value);
        $this->assertNull($album->published_at);
    }

    public function test_cover_and_backdated_date_are_saved(): void
    {
        $this->actingAsAdmin()->post(route('admin.albums.store'), [
            'intent' => 'publish',
            'cover_path' => '2026/09/coverabcdef12.webp',
            'published_at' => now()->subMonth()->format('Y-m-d\TH:i'),
            'translations' => ['ru' => ['title' => 'С обложкой']],
            'blocks' => '[]',
        ])->assertRedirect();

        $album = Album::first();
        $this->assertSame('2026/09/coverabcdef12.webp', $album->cover_path);
        $this->assertTrue($album->published_at->isBefore(now()->subWeek()));
    }

    public function test_future_publish_date_is_rejected(): void
    {
        $this->actingAsAdmin()->post(route('admin.albums.store'), [
            'intent' => 'publish',
            'published_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'translations' => ['ru' => ['title' => 'Т']],
            'blocks' => '[]',
        ]);

        $this->assertFalse(Album::first()->published_at->isFuture());
    }
}
