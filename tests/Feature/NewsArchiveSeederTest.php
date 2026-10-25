<?php

namespace Tests\Feature;

use App\Actions\StoreUploadedImage;
use App\Models\Event;
use App\Models\ImageSource;
use App\Models\SiteSetting;
use App\Support\AspectRatio;
use App\Support\ImageSettings;
use Database\Seeders\EventSeeder;
use Database\Seeders\NewsArchiveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\FakesImageProcessing;
use Tests\TestCase;

/**
 * Новости организации из архива постов: тексты на трёх языках (database/seeders/data/news_archive) и сжатые фото
 * (public/themes/public/miro/images/news/archive). Одна команда на сервере добавляет их, повторный запуск ничего
 * не дублирует и не возвращает удалённое, а потерянные с диска картинки возвращает.
 */
class NewsArchiveSeederTest extends TestCase
{
    use FakesImageProcessing;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Подменный диск: тест не должен писать в реальный public/uploads. Картинки обрабатывает заглушка:
        // настоящий конвейер на сотню фото занял бы полминуты в каждом тесте (его проверяет отдельный тест).
        Storage::fake('uploads');
        $this->fakeImageProcessing();
    }

    /** @return array<string, array<string, mixed>> данные новостей по номеру */
    private function data(): array
    {
        $items = [];

        foreach (glob(database_path('seeders/data/news_archive/*.php')) as $file) {
            $items[pathinfo($file, PATHINFO_FILENAME)] = require $file;
        }

        ksort($items);

        return $items;
    }

    /** Новость в базе по русскому названию. */
    private function eventFor(array $item): ?Event
    {
        return Event::query()->with('translations')
            ->whereHas('translations', fn ($q) => $q->where('locale', 'ru')->where('title', $item['merge'] ?? $item['title']['ru']))
            ->first();
    }

    /** Все картинки новости: обложка и пути из блоков страницы. @return list<string> */
    private function imagePaths(Event $event): array
    {
        $paths = array_filter([$event->image_path]);

        foreach ($event->translations as $translation) {
            foreach ($translation->content ?? [] as $block) {
                if ($block['type'] === 'image') {
                    $paths[] = $block['data']['path'];
                } elseif (str_starts_with($block['type'], 'gallery_')) {
                    array_push($paths, ...$block['data']['images']);
                }
            }
        }

        return array_values(array_unique(array_filter($paths)));
    }

    // ---------------------------------------------------------------- сами данные

    public function test_the_prepared_news_are_complete_on_every_language(): void
    {
        $items = $this->data();
        $slugs = [];
        $titles = [];

        $this->assertNotEmpty($items);

        foreach ($items as $key => $item) {
            $photos = glob(public_path("themes/public/miro/images/news/archive/{$key}/*.webp"));
            $this->assertNotEmpty($photos, "{$key}: нет сжатых фото (php artisan news:compress-archive)");
            $this->assertLessThanOrEqual(5, count($photos), "{$key}: фото не больше пяти");
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $item['date'], $key);
            $this->assertNotFalse(strtotime($item['date']), $key);
            $this->assertContains($item['type'], ['training', 'conference', 'forum', 'networking', 'award', 'project', 'event', 'announcement'], $key);

            $paragraphs = count($item['body']['ru']);
            $this->assertGreaterThan(0, $paragraphs, $key);

            foreach (['ru', 'ro', 'en'] as $locale) {
                $this->assertNotSame('', trim($item['title'][$locale]), "{$key}: заголовок {$locale}");
                $this->assertNotSame('', trim($item['description'][$locale]), "{$key}: описание {$locale}");
                $this->assertLessThanOrEqual(191, mb_strlen($item['title'][$locale]), "{$key}: заголовок {$locale} длиннее поля БД");
                $this->assertCount($paragraphs, $item['body'][$locale], "{$key}: число абзацев {$locale} не совпало с русским (структура страницы общая)");

                foreach ($item['body'][$locale] as $i => $text) {
                    $this->assertSame(trim($text), $text, "{$key}: {$locale}[{$i}] с пробелами по краям");
                    $this->assertNotSame('', $text, "{$key}: {$locale}[{$i}] пустой абзац");
                    $this->assertStringNotContainsString('Показать меньше', $text, "{$key}: хвост Facebook в тексте");
                }
            }

            // Пункты списка (абзацы с дефисом или точкой) стоят на тех же местах во всех языках.
            $isItem = fn (array $paragraphs): array => array_map(fn (string $text) => (bool) preg_match('/^[-–•]/u', $text), $paragraphs);

            foreach (['ro', 'en'] as $locale) {
                $this->assertSame($isItem($item['body']['ru']), $isItem($item['body'][$locale]), "{$key}: пункты списка {$locale} не там, где в русском");
            }

            // Перевод не должен остаться русским: кириллицы в английском и румынском тексте нет.
            foreach (['ro', 'en'] as $locale) {
                $this->assertDoesNotMatchRegularExpression('/[А-Яа-яЁё]/u', implode(' ', [$item['title'][$locale], $item['description'][$locale], ...$item['body'][$locale]]), "{$key}: в тексте {$locale} осталась кириллица");
            }

            $slug = \Illuminate\Support\Str::slug($item['title']['ru'], '-', 'ru');
            $this->assertNotContains($slug, $slugs, "{$key}: заголовок повторяет другую новость (одинаковый адрес)");
            $slugs[] = $slug;
            $titles[] = $item['title']['ru'];
        }

        $this->assertSame(count($titles), count(array_unique($titles)));
    }

    // ---------------------------------------------------------------- загрузка

    public function test_every_news_is_created_with_a_full_page_on_three_languages_and_photos_on_disk(): void
    {
        $items = $this->data();

        $this->seed(NewsArchiveSeeder::class);

        $this->assertSame(count($items), Event::count());

        foreach ($items as $key => $item) {
            $event = $this->eventFor($item);
            $this->assertNotNull($event, "{$key}: новости нет в базе");
            $this->assertTrue($event->is_published, $key);
            $this->assertSame($item['date'], $event->starts_at->toDateString(), $key);
            $this->assertNotNull($event->image_path, "{$key}: нет обложки");
            $this->assertTrue($event->hasBody(), "{$key}: нет текста страницы");
            $this->assertNull($event->url, $key);
            $this->assertCount(3, $event->translations, $key);

            foreach ($this->imagePaths($event) as $path) {
                Storage::disk('uploads')->assertExists($path);
            }

            // Структура общая для языков: те же блоки, в том же порядке, с теми же картинками.
            $structure = fn (string $locale) => array_map(
                fn (array $b) => [$b['uid'], $b['type'], $b['type'] === 'text' || $b['type'] === 'heading' ? null : $b['data']],
                $event->translations->firstWhere('locale', $locale)->content,
            );
            $this->assertSame($structure('ru'), $structure('en'), "{$key}: структура en");
            $this->assertSame($structure('ru'), $structure('ro'), "{$key}: структура ro");

            foreach (['ru', 'ro', 'en'] as $locale) {
                $translation = $event->translations->firstWhere('locale', $locale);
                $this->assertSame($item['title'][$locale], $translation->title, "{$key} {$locale}");
                $this->assertNull($translation->type, "{$key} {$locale}: плашку сидер не ставит");
                $this->assertStringContainsString((string) $event->starts_at->year, (string) $translation->date_label, "{$key}: в подписи даты должен быть год");
            }
        }
    }

    public function test_the_cards_already_on_the_site_get_text_photos_and_the_real_date_and_keep_everything_else(): void
    {
        $this->seed(EventSeeder::class);

        $merge = array_filter($this->data(), fn (array $item) => ($item['merge'] ?? null) !== null);
        $this->assertCount(3, $merge, 'три новости из папки уже есть на сайте карточками');

        $before = Event::query()->with('translations')->get()->mapWithKeys(fn (Event $e) => [$e->id => [
            'slug' => $e->slug, 'cover' => $e->image_path, 'url' => $e->url, 'tone' => $e->tone,
            'texts' => $e->translations->sortBy('locale')->map(fn ($t) => [$t->type, $t->title, $t->description])->values()->all(),
        ]])->all();

        $this->seed(NewsArchiveSeeder::class);

        foreach ($merge as $key => $item) {
            $matching = Event::query()->whereHas('translations', fn ($q) => $q->where('locale', 'ru')->where('title', $item['merge']))->get();
            $this->assertCount(1, $matching, "{$key}: вторая запись про то же событие не создаётся");

            $event = $matching->first()->load('translations');
            $old = $before[$event->id];

            // Прежнее остаётся: адрес страницы, обложка, ссылка, цвет, заголовок и описание на всех языках.
            $this->assertSame($old['slug'], $event->slug, $key);
            $this->assertSame($old['cover'], $event->image_path, "{$key}: обложка прежняя");
            $this->assertSame($old['url'], $event->url, $key);
            $this->assertSame($old['tone'], $event->tone, $key);
            $this->assertSame($old['texts'], $event->translations->sortBy('locale')->map(fn ($t) => [$t->type, $t->title, $t->description])->values()->all(), "{$key}: заголовки не тронуты");

            // Добавилось: текст, фото и настоящая дата вместо условной 20.05.2026.
            $this->assertTrue($event->hasBody(), $key);
            $this->assertSame($item['date'], $event->starts_at->toDateString(), $key);
            // На странице все фото поста, кроме первого: это тот же снимок, что уже стоит старой обложкой карточки.
            $photos = count(glob(public_path("themes/public/miro/images/news/archive/{$key}/*.webp")));
            $this->assertCount($photos - 1, array_diff($this->imagePaths($event), [$event->image_path]), "{$key}: фото на странице без дубля обложки");

            foreach ($event->translations as $translation) {
                $this->assertStringNotContainsString('20.05.2026', (string) $translation->date_label, "{$key} {$translation->locale}");
                $this->assertStringContainsString((string) $event->starts_at->year, (string) $translation->date_label, "{$key} {$translation->locale}");
            }
        }

        $this->assertSame(9 + count($this->data()) - count($merge), Event::count());
    }

    public function test_lists_in_the_text_become_real_bulleted_lists_and_site_addresses_become_links(): void
    {
        $this->seed(NewsArchiveSeeder::class);

        $html = fn (string $key, string $locale): string => collect(
            $this->eventFor($this->data()[$key])->translations->firstWhere('locale', $locale)->content
        )->where('type', 'text')->pluck('data.html')->implode('');

        foreach (['ru', 'ro', 'en'] as $locale) {
            $training = $html('058', $locale);
            $this->assertSame(3, substr_count($training, '<li>'), "058 {$locale}: три пункта списка");
            $this->assertSame(1, substr_count($training, '<ul>'), "058 {$locale}: один список");
            $this->assertStringNotContainsString('<p>-', $training);
            $this->assertStringNotContainsString('<li>-', $training);

            $initiative = $html('102', $locale);
            $this->assertSame(5, substr_count($initiative, '<li>'), "102 {$locale}: пять пунктов");
            $this->assertStringNotContainsString('•', $initiative);
            $this->assertStringContainsString('<a href="https://innovation.md/"', $initiative, "102 {$locale}: адрес сайта стал ссылкой");
        }
    }

    public function test_new_cards_are_put_on_top_of_the_list_by_date_and_old_ones_move_down(): void
    {
        $this->seed(EventSeeder::class);
        $old = Event::query()->ordered()->pluck('id')->all();
        $this->assertCount(9, $old);

        $this->seed(NewsArchiveSeeder::class);

        $ordered = Event::query()->ordered()->with('translations')->get();
        $mergeCount = count(array_filter($this->data(), fn (array $item) => ($item['merge'] ?? null) !== null));
        $new = count($this->data()) - $mergeCount;

        // Новые идут первыми и строго от свежей даты к давней.
        $first = $ordered->take($new);
        $this->assertEquals($first->sortByDesc(fn (Event $e) => $e->starts_at->timestamp)->pluck('id')->values(), $first->pluck('id')->values());
        $this->assertSame(range(1, $new), $first->pluck('position')->all());

        // За ними прежние девять в прежнем порядке.
        $this->assertSame($old, $ordered->skip($new)->pluck('id')->all());
    }

    public function test_a_second_run_changes_nothing(): void
    {
        $this->seed(NewsArchiveSeeder::class);

        $snapshot = Event::query()->ordered()->with('translations')->get()->map(fn (Event $e) => [
            $e->id, $e->slug, $e->image_path, $e->position, $e->translations->sortBy('locale')->pluck('content')->all(),
        ])->all();
        $files = Storage::disk('uploads')->allFiles();

        $this->seed(NewsArchiveSeeder::class);

        $after = Event::query()->ordered()->with('translations')->get()->map(fn (Event $e) => [
            $e->id, $e->slug, $e->image_path, $e->position, $e->translations->sortBy('locale')->pluck('content')->all(),
        ])->all();

        $this->assertSame($snapshot, $after);
        $this->assertEqualsCanonicalizing($files, Storage::disk('uploads')->allFiles(), 'повторный запуск не загружает фото заново');
        $this->assertSame(count($this->data()), Event::count());
    }

    public function test_a_news_deleted_in_the_admin_is_not_brought_back(): void
    {
        $this->seed(NewsArchiveSeeder::class);
        $total = Event::count();

        $victim = Event::query()->ordered()->first();
        $victim->delete();

        $this->seed(NewsArchiveSeeder::class);

        $this->assertSame($total - 1, Event::count());
        $this->assertNull(Event::find($victim->id));
        $this->assertNotEmpty(SiteSetting::read(NewsArchiveSeeder::MARKER_KEY)['keys']);
    }

    public function test_edited_text_is_never_overwritten(): void
    {
        $this->seed(NewsArchiveSeeder::class);

        $event = Event::query()->ordered()->with('translations')->first();
        $ru = $event->translations->firstWhere('locale', 'ru');
        $ru->update(['title' => 'Заголовок, поправленный редактором']);

        $this->seed(NewsArchiveSeeder::class);

        $this->assertSame('Заголовок, поправленный редактором', $ru->fresh()->title);
    }

    // ---------------------------------------------------------------- пропавшие картинки

    public function test_pictures_that_vanished_from_the_disk_are_restored_and_nothing_else_is_touched(): void
    {
        $this->seed(NewsArchiveSeeder::class);

        $event = Event::query()->ordered()->with('translations')->first();
        $paths = $this->imagePaths($event);
        $this->assertGreaterThan(1, count($paths));

        // Пропали обложка и одно фото страницы.
        $lostCover = $event->image_path;
        $lostPhoto = collect($paths)->first(fn (string $p) => $p !== $lostCover);
        Storage::disk('uploads')->delete([$lostCover, $lostPhoto]);

        $keptOthers = collect($paths)->reject(fn (string $p) => in_array($p, [$lostCover, $lostPhoto], true))->all();

        $this->seed(NewsArchiveSeeder::class);

        $event = $event->fresh()->load('translations');
        $this->assertNotSame($lostCover, $event->image_path);

        foreach ($this->imagePaths($event) as $path) {
            Storage::disk('uploads')->assertExists($path);
        }

        foreach ($keptOthers as $path) {
            $this->assertContains($path, $this->imagePaths($event), 'уцелевшие картинки остались прежними');
        }

        // Путь заменён во всех языках одинаково.
        $contents = $event->translations->map(fn ($t) => collect($t->content)->where('type', '!=', 'text')->pluck('data')->all())->unique()->count();
        $this->assertSame(1, $contents);
    }

    // ---------------------------------------------------------------- страница на сайте

    public function test_the_news_pages_open_and_show_every_language_with_photos(): void
    {
        $this->seed(NewsArchiveSeeder::class);

        foreach ($this->data() as $key => $item) {
            $event = $this->eventFor($item);
            $html = str_replace(chr(194).chr(160), ' ', $this->get(route('events.show', ['event' => $event->slug]))->assertOk()->getContent());

            $this->assertStringContainsString(e($item['title']['ru']), $html, "{$key}: ru");
            $this->assertStringContainsString(e($item['title']['en']), $html, "{$key}: en");
            $this->assertStringContainsString(e($item['title']['ro']), $html, "{$key}: ro");
            $this->assertStringContainsString('/uploads/', $html, "{$key}: фото");
            $this->assertStringContainsString(e(mb_substr($item['body']['en'][0], 0, 40)), $html, "{$key}: английский текст на странице");
        }

        // Карточки на странице «Новости» ведут на собственные страницы, а не на внешние ссылки.
        $list = $this->get(route('events'))->assertOk()->getContent();
        $this->assertStringContainsString('/events/', $list);
    }

    public function test_the_seeder_runs_on_the_production_server_too(): void
    {
        // В отличие от демо-текстов, это настоящие новости организации: на боевом сервере они нужны.
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => NewsArchiveSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertSame(count($this->data()), Event::count());
    }

    // ---------------------------------------------------------------- кадрирование и настоящий конвейер

    public function test_every_crop_has_the_slot_ratio_stays_inside_the_photo_and_keeps_the_top_of_portraits(): void
    {
        $this->seed(NewsArchiveSeeder::class);

        $this->assertNotEmpty($this->imageCalls);
        $portraitCrops = 0;

        foreach ($this->imageCalls as $call) {
            $this->assertContains($call['slot'], ['event', 'image', 'gallery_2', 'gallery_3'], 'слот из закреплённых пропорций');
            $this->assertTrue(AspectRatio::exists($call['slot']));

            [$w, $h] = getimagesize($call['source']);
            [$targetWidth, $targetHeight] = AspectRatio::dimensions($call['slot']);
            $crop = $call['crop'];

            $this->assertNotNull($crop, basename($call['source']));
            $this->assertGreaterThanOrEqual(0, $crop['x']);
            $this->assertGreaterThanOrEqual(0, $crop['y']);
            $this->assertLessThanOrEqual($w, $crop['x'] + $crop['width'], 'кадр не выходит за фото по ширине');
            $this->assertLessThanOrEqual($h, $crop['y'] + $crop['height'], 'кадр не выходит за фото по высоте');
            $this->assertEqualsWithDelta($targetWidth / $targetHeight, $crop['width'] / $crop['height'], 0.01, 'пропорции кадра совпадают со слотом');

            if ($crop['height'] < $h) {
                // Фото выше нужного кадра (портрет): окно сдвинуто к верху, а не взято по центру.
                $portraitCrops++;
                $this->assertEqualsWithDelta(0.25 * ($h - $crop['height']), $crop['y'], 0.51, 'окно кадра на четверть свободной высоты от верха, с точностью до пикселя');
            }
        }

        $this->assertGreaterThan(0, $portraitCrops, 'среди фото есть портреты: окно кадра к верху проверено');
    }

    public function test_the_real_image_pipeline_makes_slot_sized_webp_files_from_the_compressed_sources(): void
    {
        // Настоящий конвейер вместо заглушки, но на одной новости: это 4 фото, а не вся сотня.
        $this->app->forgetInstance(StoreUploadedImage::class);

        (new class extends NewsArchiveSeeder
        {
            protected function items(): array
            {
                return array_slice(parent::items(), 0, 1);
            }
        })->run();

        $event = Event::query()->with('translations')->firstOrFail();
        $size = fn (string $path): array => array_slice(getimagesizefromstring(Storage::disk('uploads')->get($path)), 0, 3);

        [$width, $height, $type] = $size($event->image_path);
        $this->assertSame([1280, 720, IMAGETYPE_WEBP], [$width, $height, $type], 'обложка: слот event, WebP');

        $slots = ['image' => [1280, 720], 'gallery_2' => [1200, 900], 'gallery_3' => [1200, 900]];
        $checked = 0;

        foreach ($event->translations->firstWhere('locale', 'ru')->content as $block) {
            $paths = match (true) {
                $block['type'] === 'image' => [$block['data']['path']],
                str_starts_with($block['type'], 'gallery_') => $block['data']['images'],
                default => [],
            };

            foreach ($paths as $path) {
                [$width, $height, $type] = $size($path);
                $this->assertSame([...$slots[$block['type']], IMAGETYPE_WEBP], [$width, $height, $type], $block['type']);
                $checked++;
            }
        }

        $this->assertGreaterThan(0, $checked);

        // Каждая картинка новости получила сжатое фото целиком и рамку: кадр можно менять в админке, не загружая фото заново.
        $paths = array_values(array_unique(array_filter(array_merge(
            [$event->image_path],
            collect($event->translations->firstWhere('locale', 'ru')->content)->flatMap(fn (array $b) => match (true) {
                $b['type'] === 'image' => [$b['data']['path']],
                str_starts_with($b['type'], 'gallery_') => $b['data']['images'],
                default => [],
            })->all(),
        ))));

        $this->assertSame(count($paths), ImageSource::query()->whereIn('path', $paths)->count(), 'у каждой картинки новости есть запись о фото целиком');

        foreach (ImageSource::query()->whereIn('path', $paths)->get() as $source) {
            Storage::disk('originals')->assertExists($source->source_path);
            Storage::disk('uploads')->assertMissing($source->source_path);
            $this->assertLessThanOrEqual($source->source_width, $source->crop_x + $source->crop_width, 'рамка внутри фото по ширине');
            $this->assertLessThanOrEqual($source->source_height, $source->crop_y + $source->crop_height, 'рамка внутри фото по высоте');
            [$width, $height] = array_slice(getimagesizefromstring(Storage::disk('originals')->get($source->source_path)), 0, 2);
            $this->assertSame([$width, $height], [$source->source_width, $source->source_height], 'размер фото в записи совпадает с файлом');
        }
    }

    // ---------------------------------------------------------------- переделка фото и качество

    /** Сидер на одной новости (4 фото) с настоящим конвейером: вся сотня заняла бы полминуты. */
    private function oneNews(): NewsArchiveSeeder
    {
        $this->app->forgetInstance(StoreUploadedImage::class);

        return new class extends NewsArchiveSeeder
        {
            protected function items(): array
            {
                return array_slice(parent::items(), 0, 1);
            }
        };
    }

    /** Заменяет пути картинок в блоках страницы на всех языках. @param  array<string, string>  $map */
    private function swapPaths(Event $event, array $map): void
    {
        foreach ($event->translations as $translation) {
            $content = $translation->content;
            array_walk_recursive($content, function (&$value) use ($map) {
                if (is_string($value) && isset($map[$value])) {
                    $value = $map[$value];
                }
            });
            $translation->forceFill(['content' => $content])->save();
        }
    }

    public function test_rebake_makes_untouched_frames_again_and_removes_the_old_files(): void
    {
        $this->oneNews()->run();

        $event = Event::query()->with('translations')->firstOrFail();
        $before = $this->imagePaths($event);
        $this->assertGreaterThan(1, count($before));
        $oldSources = ImageSource::query()->whereIn('path', $before)->pluck('source_path')->all();

        $count = $this->oneNews()->rebakePhotos();

        $event = $event->fresh()->load('translations');
        $after = $this->imagePaths($event);

        $this->assertSame(count($before), $count, 'переделана каждая картинка новости');
        $this->assertCount(count($before), $after);
        $this->assertSame([], array_intersect($before, $after), 'все кадры новые');

        foreach ($after as $path) {
            Storage::disk('uploads')->assertExists($path);
            $this->assertSame(IMAGETYPE_WEBP, getimagesizefromstring(Storage::disk('uploads')->get($path))[2]);
        }

        // Прежние кадры, записи о них и ставшие ненужными фото целиком убраны: ничего не копится.
        foreach ($before as $path) {
            Storage::disk('uploads')->assertMissing($path);
        }
        foreach ($oldSources as $source) {
            Storage::disk('originals')->assertMissing($source);
        }
        $this->assertSame(0, ImageSource::query()->whereIn('path', $before)->count());
        $this->assertSame(count($after), ImageSource::query()->count(), 'у каждого нового кадра есть запись о фото целиком');

        // Путь заменён во всех языках одинаково, а текст не тронут.
        $contents = $event->translations->map(fn ($t) => collect($t->content)->where('type', '!=', 'text')->pluck('data')->all())->unique()->count();
        $this->assertSame(1, $contents);
        $this->assertNotEmpty($event->translations->firstWhere('locale', 'ru')->content);

        // Повторная переделка так же безопасна.
        $this->assertSame(count($after), $this->oneNews()->rebakePhotos());
    }

    public function test_rebake_leaves_a_recropped_frame_and_a_replaced_photo_alone(): void
    {
        $this->oneNews()->run();

        $event = Event::query()->with('translations')->firstOrFail();
        $paths = $this->imagePaths($event);
        $cover = $event->image_path;
        $others = array_values(array_diff($paths, [$cover]));
        $this->assertGreaterThan(1, count($others));

        // Редактор перекадрировал одну картинку кнопкой «Изменить кадр» и заменил другую своей фотографией.
        $row = ImageSource::query()->where('path', $others[0])->firstOrFail();
        [$slotWidth, $slotHeight] = AspectRatio::dimensions($row->slot);
        $cropWidth = intdiv($row->source_width, 2);
        $recropped = app(StoreUploadedImage::class)->recrop($row, [
            'x' => 0, 'y' => 0, 'width' => $cropWidth, 'height' => (int) round($cropWidth * $slotHeight / $slotWidth),
        ]);

        $slot = (string) ImageSource::query()->where('path', $others[1])->value('slot');
        $mine = app(StoreUploadedImage::class)->handle(UploadedFile::fake()->image('mine.jpg', 900, 700), $slot);

        $this->swapPaths($event, [$others[0] => $recropped, $others[1] => $mine]);

        $count = $this->oneNews()->rebakePhotos();

        $event = $event->fresh()->load('translations');
        $after = $this->imagePaths($event);

        $this->assertContains($recropped, $after, 'перекадрированный кадр остался');
        $this->assertContains($mine, $after, 'фото, заменённое редактором, осталось');
        Storage::disk('uploads')->assertExists($recropped);
        Storage::disk('uploads')->assertExists($mine);

        // Остальные картинки переделаны: обложка и всё, что редактор не трогал.
        $this->assertSame(count($paths) - 2, $count);
        $this->assertNotSame($cover, $event->image_path, 'обложка новая');
        $this->assertCount(count($paths), $after);
    }

    public function test_rebake_command_runs_and_reports_how_many_photos_it_made_again(): void
    {
        // С заглушкой конвейера у картинок нет записей о фото целиком, то есть сидер их не делал: команда ничего не меняет.
        $this->seed(NewsArchiveSeeder::class);
        $paths = Event::query()->ordered()->with('translations')->get()->flatMap(fn (Event $e) => $this->imagePaths($e))->all();

        $this->artisan('news:rebake-archive-photos')
            ->expectsOutputToContain('Фото новостей переделано: 0')
            ->assertSuccessful();

        foreach ($paths as $path) {
            Storage::disk('uploads')->assertExists($path);
        }
    }

    public function test_photos_are_compressed_gently_so_that_they_stay_sharp(): void
    {
        // Качество кадра по умолчанию: 60 давало «кубики» на гладких местах.
        $this->assertGreaterThanOrEqual(80, ImageSettings::QUALITY_DEFAULT);
        $this->assertSame(ImageSettings::QUALITY_DEFAULT, ImageSettings::quality());
        $this->assertGreaterThan(ImageSettings::QUALITY_DEFAULT, StoreUploadedImage::SOURCE_QUALITY, 'целое фото сжато слабее кадра: потери не копятся');

        // Промежуточные копии архива: разрешение исходников (до 2048 px) и высокое качество. Из них режется кадр,
        // поэтому сузить их до 1400 px и сжать до 76 значило испортить фото ещё до кадрирования.
        $options = Artisan::all()['news:compress-archive']->getDefinition();
        $this->assertGreaterThanOrEqual(2048, (int) $options->getOption('max-side')->getDefault());
        $this->assertGreaterThanOrEqual(85, (int) $options->getOption('quality')->getDefault());

        $sides = array_map(fn (string $f) => max(array_slice(getimagesize($f), 0, 2)), glob(public_path('themes/public/miro/images/news/archive/*/*.webp')));
        $this->assertGreaterThan(1800, max($sides), 'крупные фото архива не уменьшены до 1400 px');
    }
}
