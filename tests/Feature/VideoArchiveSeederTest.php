<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\Video;
use App\Support\YouTube;
use Database\Seeders\ContentSeeder;
use Database\Seeders\VideoArchiveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\FakesImageProcessing;
use Tests\TestCase;

/**
 * Видео организации (database/seeders/data/videos_archive.php): сидер, страница «Видео» и проигрыватель поверх
 * страницы (js/video-player.js).
 */
class VideoArchiveSeederTest extends TestCase
{
    use FakesImageProcessing;
    use RefreshDatabase;

    /** Ролики, которые должны быть на сайте (из ссылок в задаче), в порядке списка. */
    private const IDS = [
        '5GXgqcI4mjA', 'BI5HayZ9EQM', 'umt-JeAVjkM', 'rrco24tGM34', '5OF7906EtZs',
        'Uq4YKeR5_-o', '5B9TPEPOCEU', 'AcesUVGyAkU', '1t02PdPSAXg',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        $this->fakeImageProcessing();
    }

    /** @return list<array{youtube_id: string, title: array<string, string>}> */
    private function data(): array
    {
        return require database_path('seeders/data/videos_archive.php');
    }

    // ---------------------------------------------------------------- данные

    public function test_the_data_has_every_requested_video_with_titles_in_three_languages(): void
    {
        $items = $this->data();

        $this->assertSame(self::IDS, array_column($items, 'youtube_id'));
        $this->assertCount(count(self::IDS), array_unique(array_column($items, 'youtube_id')));

        foreach ($items as $item) {
            $this->assertSame($item['youtube_id'], YouTube::id('https://www.youtube.com/watch?v='.$item['youtube_id']), 'настоящий идентификатор YouTube');

            foreach (['ru', 'en', 'ro'] as $locale) {
                $title = $item['title'][$locale] ?? '';
                $this->assertNotSame('', trim($title), "{$item['youtube_id']}: название {$locale}");
                $this->assertLessThanOrEqual(191, mb_strlen($title), "название влезает в поле: {$item['youtube_id']} {$locale}");
                $this->assertDoesNotMatchRegularExpression('/\x{0306}/u', $title, 'без лишних комбинируемых знаков');
            }
        }
    }

    // ---------------------------------------------------------------- сидер

    public function test_the_seeder_adds_every_video_in_the_order_of_the_list(): void
    {
        $this->seed(VideoArchiveSeeder::class);

        $videos = Video::with('translations')->orderBy('position')->get();

        $this->assertSame(self::IDS, $videos->pluck('youtube_id')->all());
        $this->assertSame(range(1, 9), $videos->pluck('position')->all());

        foreach ($videos as $video) {
            $this->assertSame($video->youtube_id, YouTube::id($video->youtube_url), 'ссылка и идентификатор совпадают');
            $this->assertNull($video->cover_path, 'обложка — миниатюра YouTube');
            $this->assertNull($video->event_date);
            $this->assertCount(3, $video->translations);
        }

        $first = $videos->first();
        $this->assertStringStartsWith('Рады объявить о начале работы SMM школы', $first->field('title', 'ru'));
        $this->assertStringStartsWith('We are glad to announce', $first->field('title', 'en'));
        $this->assertStringStartsWith('Ne bucurăm să anunțăm', $first->field('title', 'ro'));
        $this->assertSame('Женский форум 2022', $videos[2]->field('title', 'ru'));
        $this->assertSame('Forumul Femeilor 2022', $videos[2]->field('title', 'ro'));
    }

    public function test_new_videos_go_after_the_ones_already_on_the_site(): void
    {
        $existing = Video::create(['youtube_id' => 'dQw4w9WgXcQ', 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ', 'position' => 5]);
        $existing->translations()->create(['locale' => 'ru', 'title' => 'Прежнее видео']);

        $this->seed(VideoArchiveSeeder::class);

        $this->assertSame(10, Video::count());
        $this->assertSame(['dQw4w9WgXcQ', ...self::IDS], Video::orderBy('position')->pluck('youtube_id')->all());
        $this->assertSame(6, Video::where('youtube_id', self::IDS[0])->value('position'));
    }

    public function test_a_second_run_changes_nothing_and_a_video_removed_in_the_admin_is_not_brought_back(): void
    {
        $this->seed(VideoArchiveSeeder::class);
        $this->seed(VideoArchiveSeeder::class);

        $this->assertSame(9, Video::count());
        $this->assertSame(27, \App\Models\VideoTranslation::count());

        $video = Video::where('youtube_id', 'BI5HayZ9EQM')->first();
        $video->translations()->where('locale', 'ru')->update(['title' => 'Название поправили в админке']);
        Video::where('youtube_id', 'umt-JeAVjkM')->first()->delete();

        $this->seed(VideoArchiveSeeder::class);

        $this->assertSame(8, Video::count(), 'удалённое не вернулось');
        $this->assertSame('Название поправили в админке', Video::where('youtube_id', 'BI5HayZ9EQM')->first()->field('title', 'ru'), 'правка не затёрта');
        $this->assertSame(self::IDS, SiteSetting::read(VideoArchiveSeeder::MARKER_KEY)['keys']);
    }

    public function test_a_video_that_is_already_there_is_neither_duplicated_nor_changed(): void
    {
        $mine = Video::create(['youtube_id' => 'BI5HayZ9EQM', 'youtube_url' => 'https://youtu.be/BI5HayZ9EQM', 'position' => 1]);
        $mine->translations()->create(['locale' => 'ru', 'title' => 'Моё название']);

        $this->seed(VideoArchiveSeeder::class);

        $this->assertSame(9, Video::count());
        $this->assertSame('Моё название', $mine->fresh()->field('title', 'ru'));
        $this->assertSame(1, $mine->fresh()->translations()->count(), 'чужое видео сидер не дополняет');
    }

    public function test_the_content_seeder_includes_the_videos_and_the_seeder_runs_on_production(): void
    {
        $this->seed(ContentSeeder::class);
        $this->assertSame(9, Video::count());

        // Это настоящие ролики организации: на боевом сервере они нужны.
        $this->app['env'] = 'production';
        $this->artisan('db:seed', ['--class' => VideoArchiveSeeder::class, '--force' => true])->assertSuccessful();
        $this->assertSame(9, Video::count());
    }

    // ---------------------------------------------------------------- страница и проигрыватель

    public function test_the_videos_page_lists_all_nine_with_a_player_link_and_a_youtube_fallback(): void
    {
        $this->seed(VideoArchiveSeeder::class);

        $html = $this->get(route('media.videos'))->assertOk()->getContent();

        $this->assertSame(9, substr_count($html, '<article class="miro-event-card'));

        foreach (self::IDS as $id) {
            // Проигрыватель берёт встроенный адрес из data-video, без скриптов остаётся обычная ссылка на YouTube.
            $this->assertMatchesRegularExpression(
                '#href="https://www\.youtube\.com/watch\?v='.preg_quote($id, '#').'"\s+target="_blank"\s+rel="noopener noreferrer"\s+data-video="https://www\.youtube-nocookie\.com/embed/'.preg_quote($id, '#').'\?rel=0&amp;modestbranding=1&amp;playsinline=1"#',
                $html,
                $id,
            );
            $this->assertStringContainsString("https://i.ytimg.com/vi/{$id}/hqdefault.jpg", $html);
        }

        $this->assertSame(9, substr_count($html, 'miro-card-play'));
        $this->assertStringContainsString('Женский форум 2022', $html);
        $this->assertStringContainsString('Women’s Forum 2022', $html);
        $this->assertStringContainsString('/themes/public/miro/js/video-player.js', $html);

        // Порядок как в списке.
        $positions = array_map(fn (string $id) => strpos($html, 'watch?v='.$id), self::IDS);
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);
    }

    public function test_other_cards_have_no_player_link(): void
    {
        $this->seed(ContentSeeder::class);

        // Новости и альбомы открываются как обычно: проигрыватель только у карточек видео.
        $this->assertStringNotContainsString('data-video', $this->get(route('events'))->getContent());
        $this->assertStringNotContainsString('data-video', $this->get(route('media.photos'))->getContent());
    }

    public function test_the_embedded_address_does_not_set_youtube_cookies_and_hides_other_videos(): void
    {
        $video = new Video(['youtube_id' => 'AcesUVGyAkU']);

        $this->assertSame('https://www.youtube-nocookie.com/embed/AcesUVGyAkU?rel=0&modestbranding=1&playsinline=1', $video->embedUrl());
    }

    public function test_the_player_script_and_the_styles_are_in_place(): void
    {
        $script = file_get_contents(public_path('themes/public/miro/js/video-player.js'));

        foreach (['a[data-video]', 'aria-modal', 'Escape', "'&autoplay=1&hl='", 'iframe', "frame.innerHTML = ''", 'ctrlKey', 'preventDefault', 'Смотреть на YouTube', 'Watch on YouTube', 'Vezi pe YouTube'] as $needle) {
            $this->assertStringContainsString($needle, $script, $needle);
        }

        $css = file_get_contents(public_path('themes/public/miro/css/public-section.css'));

        foreach (['.miro-player {', '.miro-player__frame', 'aspect-ratio: 16 / 9', '.miro-player__close', 'body.miro-player-open', 'prefers-reduced-motion'] as $needle) {
            $this->assertStringContainsString($needle, $css, $needle);
        }
    }
}
