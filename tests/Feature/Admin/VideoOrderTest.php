<?php

namespace Tests\Feature\Admin;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ручной порядок видео: перетаскивание и стрелки в админке, тот же порядок на сайте. */
class VideoOrderTest extends TestCase
{
    use RefreshDatabase;

    private function video(string $id, int $position): Video
    {
        $video = Video::create(['youtube_id' => $id, 'youtube_url' => "https://www.youtube.com/watch?v={$id}", 'position' => $position]);
        $video->translations()->create(['locale' => 'ru', 'title' => "Ролик {$id}"]);

        return $video;
    }

    public function test_the_list_has_the_drag_handles_and_the_save_address(): void
    {
        $this->video('aaaaaaaaaaa', 1);
        $this->video('bbbbbbbbbbb', 2);

        $html = $this->actingAsAdmin()->get(route('admin.videos.index'))->assertOk()->getContent();

        $this->assertStringContainsString('x-data="rowReorder(', $html);
        $this->assertStringContainsString(route('admin.videos.reorder'), str_replace('\/', '/', $html));
        $this->assertSame(2, substr_count($html, 'data-move="up"'));
        $this->assertSame(2, substr_count($html, 'data-id='));
    }

    public function test_the_new_order_is_saved_and_the_rest_follow(): void
    {
        $a = $this->video('aaaaaaaaaaa', 1);
        $b = $this->video('bbbbbbbbbbb', 2);
        $c = $this->video('ccccccccccc', 3);

        $this->actingAsAdmin()->postJson(route('admin.videos.reorder'), ['ids' => [$c->id, $a->id]])->assertOk();

        $this->assertSame([$c->id, $a->id, $b->id], Video::orderBy('position')->pluck('id')->all());
        $this->assertSame([1, 2, 3], Video::orderBy('position')->pluck('position')->all());
    }

    public function test_the_site_shows_the_videos_in_the_saved_order(): void
    {
        $a = $this->video('aaaaaaaaaaa', 1);
        $b = $this->video('bbbbbbbbbbb', 2);

        $this->actingAsAdmin()->postJson(route('admin.videos.reorder'), ['ids' => [$b->id, $a->id]])->assertOk();

        $html = $this->get('/media/videos')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'aaaaaaaaaaa'), strpos($html, 'bbbbbbbbbbb'));
    }

    public function test_the_request_is_validated_and_closed_to_guests(): void
    {
        $this->actingAsAdmin()->postJson(route('admin.videos.reorder'), ['ids' => [999]])->assertStatus(422);
        $this->actingAsAdmin()->postJson(route('admin.videos.reorder'), [])->assertStatus(422);

        auth()->logout();
        $this->postJson(route('admin.videos.reorder'), ['ids' => [1]])->assertStatus(401);
    }
}
