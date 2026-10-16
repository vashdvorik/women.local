<?php

namespace Tests\Feature;

use App\Actions\StoreUploadedImage;
use App\Models\Album;
use App\Models\ImageSource;
use App\Models\SiteSetting;
use App\Support\AspectRatio;
use Database\Seeders\AlbumArchiveSeeder;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\FakesImageProcessing;
use Tests\TestCase;

/**
 * Фотоальбомы организации (database/seeders/data/albums_archive.php, фото в images/albums): девять альбомов с названиями
 * на трёх языках и фото рядами по ориентации. Повторный запуск ничего не дублирует, удалённое не возвращает.
 */
class AlbumArchiveSeederTest extends TestCase
{
    use FakesImageProcessing;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Подменный диск и заглушка конвейера: настоящая обработка девяноста фото заняла бы больше минуты.
        Storage::fake('uploads');
        $this->fakeImageProcessing();
    }

    /** @return list<array{slug: string, folder: string, title: array<string, string>}> */
    private function data(): array
    {
        return require database_path('seeders/data/albums_archive.php');
    }

    private function files(string $slug): array
    {
        return glob(public_path("themes/public/miro/images/albums/{$slug}/*.webp"));
    }

    // ---------------------------------------------------------------- данные

    public function test_the_data_has_nine_albums_with_photos_and_titles_in_three_languages(): void
    {
        $items = $this->data();

        $this->assertSame(
            ['nrc', 'sdc', 'un-women', 'gala-premiya-2025', 'gala-premiya-biznes-forum', 'belyy-shum', 'atik', 'helvetas', 'gala-premiya-2024'],
            array_column($items, 'slug'),
            'порядок как на образце, затем альбомы, которых на образце нет',
        );

        $expectedPhotos = ['nrc' => 12, 'sdc' => 10, 'un-women' => 9, 'gala-premiya-2025' => 17, 'gala-premiya-biznes-forum' => 10, 'belyy-shum' => 11, 'atik' => 5, 'helvetas' => 9, 'gala-premiya-2024' => 9];

        foreach ($items as $item) {
            $this->assertCount($expectedPhotos[$item['slug']], $this->files($item['slug']), "{$item['slug']}: сжатые фото (php artisan albums:compress-archive)");

            foreach (['ru', 'en', 'ro'] as $locale) {
                $this->assertNotSame('', trim($item['title'][$locale] ?? ''), "{$item['slug']}: название {$locale}");
            }
        }

        $this->assertSame(92, array_sum($expectedPhotos));
    }

    public function test_the_photo_compression_keeps_the_resolution_and_the_quality(): void
    {
        $options = Artisan::all()['albums:compress-archive']->getDefinition();
        $this->assertGreaterThanOrEqual(2048, (int) $options->getOption('max-side')->getDefault());
        $this->assertGreaterThanOrEqual(85, (int) $options->getOption('quality')->getDefault());
    }

    // ---------------------------------------------------------------- сидер

    public function test_every_album_is_created_with_titles_in_three_languages_in_the_order_of_the_list(): void
    {
        $this->seed(AlbumArchiveSeeder::class);

        $this->assertSame(9, Album::count());
        $this->assertSame(
            array_column($this->data(), 'slug'),
            Album::published()->orderByDesc('published_at')->pluck('slug')->all(),
            'порядок на странице — по дате публикации, как в списке',
        );

        $album = Album::with('translations')->where('slug', 'belyy-shum')->first();
        $this->assertSame('Белый шум', $album->field('title', 'ru'));
        $this->assertSame('White Noise', $album->field('title', 'en'));
        $this->assertSame('Zgomot alb', $album->field('title', 'ro'));
        $this->assertSame('published', $album->status->value);
        $this->assertFalse($album->published_at->isFuture(), 'альбом виден при любом запуске');
    }

    public function test_every_photo_is_in_exactly_one_row_with_the_row_kinds_of_the_wall(): void
    {
        $this->seed(AlbumArchiveSeeder::class);

        foreach (Album::all() as $album) {
            $this->assertSame(count($this->files($album->slug)), $album->photoCount(), "{$album->slug}: все фото на полотне");
            $this->assertCount($album->photoCount(), array_unique($album->photoPaths()), 'и каждое один раз');

            $uids = [];
            foreach ($album->blocks as $block) {
                $uids[] = $block['uid'];
                $count = $block['type'] === 'image' ? 1 : count($block['data']['images']);
                $this->assertSame(['gallery_4' => 4, 'gallery_3' => 3, 'gallery_2' => 2, 'image' => 1][$block['type']], $count, "{$album->slug}: полный ряд");
            }

            $this->assertCount(count($uids), array_unique($uids));
        }

        // У альбома ATIK пять фото: три и два (вертикальный снимок один, он не набирает ряд).
        $this->assertSame(['gallery_3', 'gallery_2'], array_column(Album::where('slug', 'atik')->first()->blocks, 'type'));
        // У «Гала Премия 2025» вертикальные идут рядами по четыре.
        $this->assertSame(['gallery_3', 'gallery_4', 'gallery_3', 'gallery_4', 'gallery_3'], array_column(Album::where('slug', 'gala-premiya-2025')->first()->blocks, 'type'));
    }

    public function test_crops_fit_the_row_slots_and_the_vertical_ones_keep_the_top_of_the_frame(): void
    {
        $this->seed(AlbumArchiveSeeder::class);

        $this->assertCount(92, $this->imageCalls);
        $tall = 0;

        foreach ($this->imageCalls as $call) {
            $this->assertContains($call['slot'], ['gallery_4', 'gallery_3', 'gallery_2', 'image']);
            [$w, $h] = getimagesize($call['source']);
            [$slotWidth, $slotHeight] = AspectRatio::dimensions($call['slot']);
            $crop = $call['crop'];

            $this->assertNotNull($crop);
            $this->assertLessThanOrEqual($w, $crop['x'] + $crop['width']);
            $this->assertLessThanOrEqual($h, $crop['y'] + $crop['height']);
            $this->assertEqualsWithDelta($slotWidth / $slotHeight, $crop['width'] / $crop['height'], 0.01);

            if ($call['slot'] === 'gallery_4') {
                $tall++;
                $this->assertGreaterThanOrEqual(1.1, $h / $w, 'в ряду вертикальных только вертикальные снимки');
            }

            if ($crop['height'] < $h) {
                $this->assertEqualsWithDelta(0.25 * ($h - $crop['height']), $crop['y'], 0.51, 'окно кадра к верху, чтобы не срезать лица');
            }
        }

        $this->assertGreaterThan(0, $tall);
        $this->assertSame(0, $tall % 4, 'вертикальные идут только полными рядами по четыре');
    }

    public function test_the_cover_is_the_first_horizontal_tile(): void
    {
        $this->seed(AlbumArchiveSeeder::class);

        $album = Album::where('slug', 'helvetas')->first();
        // Альбом открывается рядом вертикальных, но обложка (миниатюра в админке) берётся из горизонтального ряда.
        $this->assertSame('gallery_4', $album->blocks[0]['type']);
        $this->assertSame($album->blocks[1]['data']['images'][0], $album->cover_path);
        $this->assertNotNull(Album::where('slug', 'nrc')->value('cover_path'));
    }

    public function test_a_second_run_changes_nothing_and_a_deleted_album_is_not_brought_back(): void
    {
        $this->seed(AlbumArchiveSeeder::class);
        $this->seed(AlbumArchiveSeeder::class);

        $this->assertSame(9, Album::count());
        $this->assertSame(27, \App\Models\AlbumTranslation::count());

        $before = Album::where('slug', 'nrc')->first()->blocks;
        Album::where('slug', 'sdc')->first()->delete();
        Album::where('slug', 'atik')->first()->translations()->where('locale', 'ru')->update(['title' => 'Название поправили в админке']);

        $this->seed(AlbumArchiveSeeder::class);

        $this->assertSame(8, Album::count(), 'удалённый не вернулся');
        $this->assertSame($before, Album::where('slug', 'nrc')->first()->blocks, 'готовое не перезаписано');
        $this->assertSame('Название поправили в админке', Album::where('slug', 'atik')->first()->field('title', 'ru'));
        $this->assertContains('sdc', SiteSetting::read(AlbumArchiveSeeder::MARKER_KEY)['keys']);
    }

    public function test_an_album_with_the_same_address_is_not_duplicated(): void
    {
        Album::create(['slug' => 'nrc', 'status' => 'published', 'published_at' => now()->subDay(), 'blocks' => []]);

        $this->seed(AlbumArchiveSeeder::class);

        $this->assertSame(9, Album::count());
        $this->assertSame([], Album::where('slug', 'nrc')->first()->blocks, 'чужой альбом не меняется');
    }

    public function test_pictures_that_vanished_from_the_disk_are_restored_and_the_rest_is_untouched(): void
    {
        $this->seed(AlbumArchiveSeeder::class);

        $album = Album::where('slug', 'nrc')->first();
        $paths = $album->photoPaths();
        $lostCover = $album->cover_path;
        $lost = $paths[4];
        Storage::disk('uploads')->delete([$lost, $lostCover]);

        $this->seed(AlbumArchiveSeeder::class);

        $album = $album->fresh();
        $this->assertNotContains($lost, $album->photoPaths());
        $this->assertCount(12, $album->photoPaths());

        foreach ($album->photoPaths() as $path) {
            Storage::disk('uploads')->assertExists($path);
        }
        Storage::disk('uploads')->assertExists($album->cover_path);
        $this->assertContains($album->cover_path, $album->photoPaths(), 'обложка — одно из фото альбома');

        $kept = array_values(array_diff($paths, [$lost, $lostCover]));
        foreach ($kept as $path) {
            $this->assertContains($path, $album->photoPaths(), 'уцелевшие фото остались прежними');
        }
    }

    public function test_the_content_seeder_includes_the_albums_and_the_seeder_runs_on_production(): void
    {
        $this->seed(ContentSeeder::class);
        $this->assertSame(9, Album::count());

        $this->app['env'] = 'production';
        $this->artisan('db:seed', ['--class' => AlbumArchiveSeeder::class, '--force' => true])->assertSuccessful();
        $this->assertSame(9, Album::count());
    }

    // ---------------------------------------------------------------- настоящий конвейер

    public function test_the_real_pipeline_makes_slot_sized_tiles_and_keeps_the_whole_photo_for_the_full_view(): void
    {
        // Настоящий конвейер вместо заглушки, но на одном альбоме из пяти фото.
        $this->app->forgetInstance(StoreUploadedImage::class);

        (new class extends AlbumArchiveSeeder
        {
            protected function items(): array
            {
                return array_values(array_filter(parent::items(), fn (array $item) => $item['slug'] === 'atik'));
            }
        })->run();

        $album = Album::where('slug', 'atik')->first();
        $this->assertSame(5, $album->photoCount());

        foreach ($album->blocks as $block) {
            foreach ($block['data']['images'] as $path) {
                [$width, $height, $type] = array_slice(getimagesizefromstring(Storage::disk('uploads')->get($path)), 0, 3);
                $this->assertSame([1200, 900, IMAGETYPE_WEBP], [$width, $height, $type], 'плитка 4:3, WebP');

                // У каждой плитки есть сжатое фото целиком: по нему просмотр открывает снимок без обрезки.
                $source = ImageSource::where('path', $path)->firstOrFail();
                Storage::disk('originals')->assertExists($source->source_path);
                $this->assertGreaterThan(1200, max($source->source_width, $source->source_height));
            }
        }

        $this->assertSame(5, ImageSource::count());
    }
}
