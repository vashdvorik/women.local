<?php

namespace Tests\Feature\Admin;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_video_is_created_from_a_youtube_link(): void
    {
        $this->actingAsAdmin()->post(route('admin.videos.store'), [
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'translations' => ['ru' => ['title' => 'Открытие']],
        ])->assertRedirect(route('admin.videos.index'));

        $this->assertSame('dQw4w9WgXcQ', Video::first()->youtube_id);
    }

    public function test_bad_link_is_rejected(): void
    {
        $this->actingAsAdmin()->post(route('admin.videos.store'), [
            'youtube_url' => 'https://vimeo.com/12345',
        ])->assertSessionHasErrors('youtube_url');
    }

    public function test_duplicate_identifier_is_rejected(): void
    {
        Video::create(['youtube_id' => 'dQw4w9WgXcQ', 'youtube_url' => 'x', 'position' => 1]);

        $this->actingAsAdmin()->post(route('admin.videos.store'), [
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertSessionHasErrors('youtube_url');
    }

    public function test_new_video_goes_to_the_end_and_can_move_up(): void
    {
        $first = Video::create(['youtube_id' => 'aaaaaaaaaaa', 'youtube_url' => 'x', 'position' => 1]);

        $this->actingAsAdmin()->post(route('admin.videos.store'), [
            'youtube_url' => 'https://youtu.be/bbbbbbbbbbb',
        ]);
        $second = Video::where('youtube_id', 'bbbbbbbbbbb')->first();
        $this->assertGreaterThan($first->position, $second->position);

        $this->actingAsAdmin()->post(route('admin.videos.move', $second), ['direction' => 'up']);

        $this->assertLessThan($first->fresh()->position, $second->fresh()->position);
    }
}
