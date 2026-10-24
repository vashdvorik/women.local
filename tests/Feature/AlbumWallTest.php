<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\ImageSource;
use App\Support\AlbumWall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Фотоальбомы полотном: раскладка рядов по ориентации снимков (AlbumWall), страница «Фото» (альбомы один за другим,
 * по четыре, «Смотреть ещё»), страница альбома во всю ширину и просмотр фото в полном размере.
 */
class AlbumWallTest extends TestCase
{
    use RefreshDatabase;

    /** Снимки: «h» вертикальный (2:3), «w» горизонтальный (3:2), «s» квадрат. @return list<array{w: int, h: int}> */
    private function shots(string $pattern): array
    {
        return array_map(fn (string $c) => match ($c) {
            'h' => ['w' => 1000, 'h' => 1500],
            's' => ['w' => 1000, 'h' => 1000],
            default => ['w' => 1500, 'h' => 1000],
        }, str_split($pattern));
    }

    /** Ряды одной строкой: «В4» — вертикальные по четыре, «Г3»/«Г2» — горизонтальные, «1» — один на всю ширину. */
    private function layout(string $pattern): string
    {
        return implode(' ', array_map(fn (array $row) => match ($row['type']) {
            'gallery_4' => 'В4', 'gallery_3' => 'Г3', 'gallery_2' => 'Г2', default => '1',
        }, AlbumWall::rows($this->shots($pattern))));
    }

    // ---------------------------------------------------------------- раскладка рядов

    public function test_tall_photos_go_by_four_and_wide_ones_by_three_or_two(): void
    {
        $this->assertSame('В4', $this->layout('hhhh'));
        $this->assertSame('Г3', $this->layout('www'));
        $this->assertSame('Г2', $this->layout('ww'));
        $this->assertSame('1', $this->layout('w'));

        // Ряды вертикальных и горизонтальных чередуются, начиная с группы, чей снимок в папке раньше.
        $this->assertSame('Г3 В4 Г3 В4', $this->layout('wwwhhhhwwwhhhh'));
        $this->assertSame('В4 Г3 В4 Г3', $this->layout('hhhhwwwhhhhwww'));
        $this->assertSame('В4 Г3 Г3 Г3', $this->layout('hhhhwwwwwwwww'));
    }

    public function test_wide_leftovers_never_leave_a_lone_photo_in_the_last_row(): void
    {
        $this->assertSame('Г2 Г2', $this->layout('wwww'));
        $this->assertSame('Г3 Г2', $this->layout('wwwww'));
        $this->assertSame('Г3 Г3', $this->layout('wwwwww'));
        $this->assertSame('Г3 Г2 Г2', $this->layout('wwwwwww'));
        $this->assertSame('Г3 Г3 Г2', $this->layout('wwwwwwww'));
        $this->assertSame('Г3 Г3 Г2 Г2', $this->layout('wwwwwwwwww'));
    }

    public function test_extra_tall_photos_that_do_not_fill_a_row_join_the_wide_ones(): void
    {
        // Пять вертикальных: четыре рядом, пятый (один) уходит к горизонтальным.
        $this->assertSame('Г3 В4 Г2', $this->layout('hhhhhwwww'), 'ушёл первый из равных по форме: ряд горизонтальных начинается с него');
        // Три вертикальных не набирают ряд вовсе.
        $this->assertSame('Г3 Г3', $this->layout('hhhwww'));
        // Один вертикальный и четыре горизонтальных: пять в ряд по тройке и двойке.
        $this->assertSame('Г3 Г2', $this->layout('wwwwh'));
    }

    public function test_the_most_square_tall_photos_are_the_ones_moved_to_the_wide_rows(): void
    {
        $photos = [
            ['w' => 1000, 'h' => 2000], ['w' => 1000, 'h' => 1300], ['w' => 1000, 'h' => 2000],
            ['w' => 1000, 'h' => 2000], ['w' => 1000, 'h' => 2000], ['w' => 1500, 'h' => 1000],
            ['w' => 1500, 'h' => 1000], ['w' => 1500, 'h' => 1000],
        ];

        $rows = AlbumWall::rows($photos);
        $tallRow = collect($rows)->firstWhere('type', 'gallery_4');

        $this->assertSame([0, 2, 3, 4], $tallRow['photos'], 'самый «квадратный» вертикальный (№1) ушёл к горизонтальным');
        $this->assertContains(1, collect($rows)->where('type', 'gallery_2')->flatMap(fn ($r) => $r['photos'])->all(), 'вместе с тремя горизонтальными получилось четыре: два ряда по два');
    }

    public function test_squares_count_as_wide_and_every_photo_is_placed_exactly_once(): void
    {
        $this->assertSame('Г3', $this->layout('wsw'));

        mt_srand(7);

        foreach (range(1, 120) as $round) {
            $count = mt_rand(1, 40);
            $pattern = implode('', array_map(fn () => ['h', 'w', 'w', 's'][mt_rand(0, 3)], range(1, $count)));
            $rows = AlbumWall::rows($this->shots($pattern));

            $placed = collect($rows)->flatMap(fn (array $row) => $row['photos'])->sort()->values()->all();
            $this->assertSame(range(0, $count - 1), $placed, "каждое фото ровно один раз: {$pattern}");

            foreach ($rows as $row) {
                $this->assertSame(
                    ['gallery_4' => 4, 'gallery_3' => 3, 'gallery_2' => 2, 'image' => 1][$row['type']],
                    count($row['photos']),
                    "полный ряд: {$pattern}",
                );
            }

            // Одиночный снимок на всю ширину бывает не чаще одного раза и только вместо всей группы горизонтальных.
            $this->assertLessThanOrEqual(1, collect($rows)->where('type', 'image')->count(), $pattern);
            if (collect($rows)->contains('type', 'image')) {
                $this->assertCount(0, collect($rows)->whereIn('type', ['gallery_3', 'gallery_2']), "одиночный снимок не соседствует с другими горизонтальными: {$pattern}");
            }
        }
    }

    public function test_tall_rows_hold_only_tall_photos(): void
    {
        mt_srand(11);

        foreach (range(1, 60) as $round) {
            $pattern = implode('', array_map(fn () => ['h', 'w', 's'][mt_rand(0, 2)], range(1, mt_rand(4, 30))));
            $photos = $this->shots($pattern);

            foreach (AlbumWall::rows($photos) as $row) {
                if ($row['type'] === 'gallery_4') {
                    foreach ($row['photos'] as $index) {
                        $this->assertGreaterThanOrEqual(1.1, $photos[$index]['h'] / $photos[$index]['w'], "в ряду вертикальных только вертикальные: {$pattern}");
                    }
                }
            }
        }
    }

    // ---------------------------------------------------------------- страницы

    private function album(string $slug, string $title, int $photos = 3, array $attributes = []): Album
    {
        $images = array_map(fn (int $i) => "2026/10/{$slug}{$i}.webp", range(1, $photos));
        $album = Album::create(array_merge([
            'slug' => $slug, 'status' => 'published', 'published_at' => now()->subMinutes(count(Album::all())),
            'cover_path' => $images[0],
            'blocks' => [['uid' => "u-{$slug}", 'type' => $photos === 3 ? 'gallery_3' : 'gallery_2', 'data' => ['images' => array_slice($images, 0, $photos === 3 ? 3 : 2)]]],
        ], $attributes));
        $album->translations()->create(['locale' => 'ru', 'title' => $title]);
        $album->translations()->create(['locale' => 'en', 'title' => $title.' EN']);

        return $album;
    }

    public function test_the_list_shows_only_two_rows_of_photos_and_a_button_to_the_whole_album(): void
    {
        // Три ряда по три фото (девять снимков): в списке видны два ряда, остальное на странице альбома
        $images = array_map(fn (int $i) => "2026/10/big{$i}.webp", range(1, 9));
        $big = $this->album('big', 'Большой', 3, ['blocks' => [['uid' => 'g', 'type' => 'gallery_3', 'data' => ['images' => $images]]]]);
        $small = $this->album('small', 'Маленький', 3);

        $html = $this->get(route('media.photos'))->assertOk()->getContent();
        $bigHtml = substr($html, strpos($html, 'id="album-big"'), strpos($html, 'id="album-small"') - strpos($html, 'id="album-big"'));

        $this->assertSame(6, substr_count($bigHtml, '<img src="/uploads/2026/10/big'), 'два ряда по три фото');
        $this->assertStringContainsString('Смотреть альбом', $bigHtml);
        $this->assertStringContainsString('href="'.url('media/photos/big').'"', $bigHtml);
        // Число фото в подписи остаётся полным
        $this->assertStringContainsString('9 фото', $bigHtml);

        // В маленьком альбоме всего один ряд: кнопка не нужна
        $smallHtml = substr($html, strpos($html, 'id="album-small"'));
        $this->assertStringNotContainsString('Смотреть альбом', $smallHtml);

        // Страница альбома показывает все девять
        $page = $this->get(route('media.photos.show', 'big'))->assertOk()->getContent();
        $this->assertSame(9, substr_count($page, '<img src="/uploads/2026/10/big'));
        $this->assertStringNotContainsString('Смотреть альбом', $page);
    }

    public function test_the_trimming_counts_rows_by_the_columns_of_each_gallery(): void
    {
        $img = fn (int $n) => array_map(fn (int $i) => "2026/10/p{$i}.webp", range(1, $n));

        [$out, $more] = \App\Support\PublicCards::firstRows([
            ['type' => 'text', 'data' => ['html' => '<p>Вступление</p>']],
            ['type' => 'gallery_3', 'data' => ['images' => $img(7)]],
            ['type' => 'gallery_4', 'data' => ['images' => $img(8)]],
        ], 2);
        $this->assertTrue($more);
        $this->assertCount(2, $out, 'текст остаётся, вторая галерея отброшена');
        $this->assertCount(6, $out[1]['data']['images']);

        [$out, $more] = \App\Support\PublicCards::firstRows([['type' => 'gallery_4', 'data' => ['images' => $img(8)]]], 2);
        $this->assertFalse($more, 'ровно два ряда: кнопка не нужна');
        $this->assertCount(8, $out[0]['data']['images']);

        [, $more] = \App\Support\PublicCards::firstRows([
            ['type' => 'image', 'data' => ['path' => 'a']], ['type' => 'image', 'data' => ['path' => 'b']], ['type' => 'image', 'data' => ['path' => 'c']],
        ], 2);
        $this->assertTrue($more);
    }
    public function test_the_photo_page_shows_albums_as_walls_not_as_cover_cards(): void
    {
        $this->album('nrc', 'NRC');
        $this->album('sdc', 'SDC');

        $html = $this->get(route('media.photos'))->assertOk()->getContent();

        // Название со ссылкой на альбом, число фото и сами фото. Карточек с обложкой нет.
        $this->assertStringContainsString('id="album-nrc"', $html);
        $this->assertStringContainsString('id="album-sdc"', $html);
        $this->assertMatchesRegularExpression('#<h2 class="miro-wall__title"><a href="[^"]*/media/photos/nrc">#', $html);
        $this->assertStringContainsString('3 фото', $html);
        $this->assertStringContainsString('3 photos', $html);
        $this->assertStringContainsString('/uploads/2026/10/nrc1.webp', $html);
        $this->assertStringContainsString('/uploads/2026/10/nrc3.webp', $html);
        $this->assertStringNotContainsString('miro-events-grid', $html);
        $this->assertStringNotContainsString('miro-card', $html);

        // Порядок: сначала тот, что опубликован позже.
        $this->assertLessThan(strpos($html, 'id="album-sdc"'), strpos($html, 'id="album-nrc"'));
        // Просмотр листает фото одного альбома, а не всей страницы.
        $this->assertSame(2, substr_count($html, 'data-lightbox-group'));
    }

    public function test_albums_come_four_at_a_time_with_a_load_more_button(): void
    {
        foreach (range(1, 9) as $i) {
            $this->album("album-{$i}", "Альбом {$i}");
        }

        $first = $this->get(route('media.photos'))->assertOk()->getContent();
        $this->assertSame(4, substr_count($first, 'class="miro-wall"'));
        $this->assertStringContainsString('Альбом 1<', $first);
        $this->assertStringContainsString('Альбом 4<', $first);
        $this->assertStringNotContainsString('Альбом 5<', $first);
        $this->assertMatchesRegularExpression('#<div class="miro-events-more" data-load-more data-target="\[data-wall-list\]">\s*<a href="[^"]*[?&]page=2" rel="next"#', $first);
        foreach (['Смотреть ещё', 'Load more', 'Vezi mai multe'] as $label) {
            $this->assertStringContainsString($label, $first);
        }
        $this->assertStringContainsString('/themes/public/miro/js/load-more.js', $first);

        // Страница без скриптов: следующая порция по ссылке; на последней кнопки нет.
        $third = $this->get(route('media.photos', ['page' => 3]))->assertOk()->getContent();
        $this->assertSame(1, substr_count($third, 'class="miro-wall"'));
        $this->assertStringContainsString('Альбом 9<', $third);
        $this->assertStringNotContainsString('data-load-more', $third);
        $this->get(route('media.photos', ['page' => 4]))->assertRedirect(route('media.photos'));
    }

    public function test_the_script_gets_the_next_albums_as_json(): void
    {
        foreach (range(1, 9) as $i) {
            $this->album("album-{$i}", "Альбом {$i}");
        }

        $data = $this->getJson(route('media.photos', ['page' => 2, 'partial' => 1]))->assertOk()->json();

        $this->assertSame(4, substr_count($data['html'], 'class="miro-wall"'));
        $this->assertStringContainsString('Альбом 5<', $data['html']);
        $this->assertStringContainsString('Альбом 8<', $data['html']);
        $this->assertStringNotContainsString('<html', $data['html']);
        $this->assertStringEndsWith('/media/photos?page=3', $data['next']);

        $last = $this->getJson(route('media.photos', ['page' => 3, 'partial' => 1]))->assertOk()->json();
        $this->assertSame(1, substr_count($last['html'], 'class="miro-wall"'));
        $this->assertNull($last['next']);
    }

    public function test_an_empty_section_shows_the_placeholder(): void
    {
        $this->get(route('media.photos'))->assertOk()->assertSee('Раздел готовится');
    }

    public function test_the_album_page_is_the_wall_across_the_whole_width_without_a_side_column(): void
    {
        $this->album('nrc', 'NRC');

        $html = $this->get(route('media.photos.show', 'nrc'))->assertOk()->getContent();

        $this->assertStringContainsString('miro-article miro-article--wide', $html);
        $this->assertStringNotContainsString('miro-article__aside', $html);
        $this->assertStringNotContainsString('miro-article__layout', $html);
        // Название, число фото, полотно, ссылка на все альбомы. Дат у альбома нет.
        $this->assertMatchesRegularExpression('#miro-article__title.*miro-article__count.*miro-prose.*miro-article__back#s', $html);
        $this->assertStringContainsString('3 фото', $html);
        $this->assertStringContainsString(route('media.photos'), $html);
        $this->assertStringNotContainsString('miro-article__date', $html);
        // Фото одно на все языки: полотно в разметке один раз, а не по разу на язык.
        $this->assertSame(1, substr_count($html, 'class="miro-prose"'));
        $this->assertSame(3, substr_count($html, '<img src="/uploads/2026/10/nrc'));
    }

    public function test_the_wall_carries_no_dates(): void
    {
        $this->album('nrc', 'NRC', attributes: ['published_at' => '2026-03-04 10:00:00']);

        foreach ([route('media.photos'), route('media.photos.show', 'nrc')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('марта 2026', $html);
            $this->assertStringNotContainsString('March 4, 2026', $html);
        }
    }

    // ---------------------------------------------------------------- фото в полном размере

    /** Альбом, у фото которого есть сжатое фото целиком на закрытом диске (как после загрузки в админке или сидера). */
    private function albumWithSources(): Album
    {
        $album = $this->album('nrc', 'NRC');

        foreach ($album->photoPaths() as $i => $path) {
            Storage::disk('originals')->put("2026/10/source{$i}.webp", "FULL-SIZE-{$i}");
            ImageSource::create([
                'path' => $path, 'source_path' => "2026/10/source{$i}.webp", 'slot' => 'gallery_3',
                'source_width' => 2048, 'source_height' => 1365, 'crop_x' => 0, 'crop_y' => 0, 'crop_width' => 1820, 'crop_height' => 1365,
            ]);
        }

        return $album;
    }

    public function test_photos_open_in_full_size_without_cropping(): void
    {
        $this->albumWithSources();

        // Ссылки у фото ведут на полный размер, а в плитке показано то, что вырезано.
        $html = $this->get(route('media.photos.show', 'nrc'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<a class="miro-zoom" href="[^"]*/media/photos/full/2026/10/nrc1\.webp" data-lightbox><img src="/uploads/2026/10/nrc1\.webp"#', $html);

        $list = $this->get(route('media.photos'))->getContent();
        $this->assertStringContainsString('/media/photos/full/2026/10/nrc2.webp', $list);

        $response = $this->get(route('media.photos.full', ['path' => '2026/10/nrc2.webp']))->assertOk();
        $this->assertSame('FULL-SIZE-1', $response->streamedContent());
        $this->assertStringContainsString('immutable', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=31536000', (string) $response->headers->get('Cache-Control'));
    }

    public function test_only_photos_of_published_albums_are_given_out_in_full(): void
    {
        $this->albumWithSources();

        // Чужая картинка с сохранённым фото целиком (например, обложка новости): целиком не показывается.
        Storage::disk('originals')->put('2026/10/secret.webp', 'SECRET');
        ImageSource::create(['path' => '2026/10/news1.webp', 'source_path' => '2026/10/secret.webp', 'slot' => 'event', 'source_width' => 10, 'source_height' => 10, 'crop_x' => 0, 'crop_y' => 0, 'crop_width' => 10, 'crop_height' => 10]);
        $this->get(route('media.photos.full', ['path' => '2026/10/news1.webp']))->assertNotFound();

        // Фото несуществующее и адрес не по форме.
        $this->get(route('media.photos.full', ['path' => '2026/10/nrc9.webp']))->assertNotFound();
        $this->get('/media/photos/full/../../../.env')->assertNotFound();
        $this->get('/media/photos/full/2026/10/nrc1.php')->assertNotFound();

        // Альбом снят с публикации: полного размера тоже нет.
        Album::where('slug', 'nrc')->update(['status' => 'draft']);
        \App\Support\PublicCache::flush();
        $this->get(route('media.photos.full', ['path' => '2026/10/nrc1.webp']))->assertNotFound();
    }

    public function test_a_photo_without_a_saved_full_copy_opens_as_the_tile(): void
    {
        $this->album('nrc', 'NRC');

        $this->get(route('media.photos.full', ['path' => '2026/10/nrc1.webp']))->assertRedirect('/uploads/2026/10/nrc1.webp');
    }

    public function test_a_draft_album_preview_opens_the_tiles_not_the_full_size(): void
    {
        $this->album('nrc', 'NRC', attributes: ['status' => 'draft']);

        $this->get(route('media.photos.show', 'nrc'))->assertNotFound();

        $html = $this->actingAsAdmin()->get(route('media.photos.show', 'nrc'))->assertOk()->getContent();
        $this->assertStringContainsString('href="/uploads/2026/10/nrc1.webp"', $html);
        $this->assertStringNotContainsString('/media/photos/full/', $html);
    }

    public function test_news_and_publication_photos_keep_opening_their_own_file(): void
    {
        // Полный размер только у альбомов: в статьях фото открываются как раньше.
        $html = view('themes.public.miro.partials.blocks', [
            'blocks' => [['uid' => 'g', 'type' => 'gallery_2', 'data' => ['images' => ['2026/10/abc123def456.webp', '2026/10/abc123def457.webp']]]],
            'lang' => 'ru',
        ])->render();

        $this->assertStringContainsString('href="/uploads/2026/10/abc123def456.webp"', $html);
        $this->assertStringNotContainsString('/media/photos/full/', $html);
    }

    // ---------------------------------------------------------------- число фото и админка

    public function test_the_photo_count_includes_single_pictures_and_galleries(): void
    {
        $album = new Album(['blocks' => [
            ['uid' => 'a', 'type' => 'gallery_3', 'data' => ['images' => ['a.webp', 'b.webp', '']]],
            ['uid' => 'b', 'type' => 'image', 'data' => ['path' => 'c.webp']],
            ['uid' => 'c', 'type' => 'image', 'data' => ['path' => '']],
        ]]);

        $this->assertSame(3, $album->photoCount());
        $this->assertSame(['a.webp', 'b.webp', 'c.webp'], $album->photoPaths());
    }
}
