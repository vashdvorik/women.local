<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\Video;
use Database\Seeders\VideoArchiveSeeder;
use Database\Seeders\VideoCreativeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Девять видео Ассоциации креативных индустрий (database/seeders/data/videos_creative.php), в том числе shorts. */
class VideoCreativeSeederTest extends TestCase
{
    use RefreshDatabase;

    private const IDS = ['VpLLRnugaPI', 'fBoH52oNg0A', 'Ik0qJl5GjlI', '_2yvW-VMDRo', 'M7CTULIjq60', '3sg5ZMWf-Yc', 'hbnQqzyeYnU', '_Gqmv_qurNg', '64qrDTsm32w'];

    public function test_it_adds_nine_videos_with_titles_in_three_languages_after_the_existing_ones(): void
    {
        $this->seed(VideoArchiveSeeder::class);
        $before = Video::count();

        $this->seed(VideoCreativeSeeder::class);

        $this->assertSame($before + 9, Video::count());

        foreach (self::IDS as $i => $id) {
            $video = Video::with('translations')->where('youtube_id', $id)->firstOrFail();
            $this->assertSame($before + $i + 1, $video->position, $id);
            $this->assertSame("https://www.youtube.com/watch?v={$id}", $video->youtube_url);

            foreach (['ru', 'en', 'ro'] as $locale) {
                $this->assertNotSame('', trim((string) $video->translations->firstWhere('locale', $locale)?->title), "{$id} {$locale}");
            }
        }

        $this->assertSame(self::IDS, SiteSetting::read(VideoCreativeSeeder::MARKER_KEY)['keys']);
        $this->assertCount(9, SiteSetting::read(VideoArchiveSeeder::MARKER_KEY)['keys'], 'список прежнего сидера не затронут');
    }

    public function test_a_second_run_does_not_duplicate_and_a_deleted_video_stays_deleted(): void
    {
        $this->seed(VideoCreativeSeeder::class);
        Video::where('youtube_id', '64qrDTsm32w')->first()->delete();

        $this->seed(VideoCreativeSeeder::class);

        $this->assertSame(8, Video::count());
        $this->assertNull(Video::where('youtube_id', '64qrDTsm32w')->first());
    }

    public function test_the_videos_show_on_the_public_page(): void
    {
        $this->seed(VideoCreativeSeeder::class);

        $html = $this->get('/media/videos')->assertOk()->getContent();

        foreach (['VpLLRnugaPI', '64qrDTsm32w'] as $id) {
            $this->assertStringContainsString($id, $html);
        }
    }
}
