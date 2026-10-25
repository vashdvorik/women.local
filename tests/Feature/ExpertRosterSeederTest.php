<?php

namespace Tests\Feature;

use App\Models\Expert;
use App\Models\SiteSetting;
use App\Support\CardTone;
use Database\Seeders\ExpertRosterSeeder;
use Database\Seeders\ExpertSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\FakesImageProcessing;
use Tests\Concerns\NewsArchiveCounts;
use Tests\TestCase;

/**
 * Дополнение состава экспертов (docs/эксперты фото): пять новых экспертов и новое фото Аурелии Саликов. Заводится
 * сидером, который работает и на базе, где эксперты уже есть (на сервере), и на пустой.
 */
class ExpertRosterSeederTest extends TestCase
{
    use FakesImageProcessing;
    use NewsArchiveCounts;
    use RefreshDatabase;

    /** Новые эксперты в порядке файла данных (имена по-русски, как в инструкции заказчика). */
    private const NEW = ['Оксана Паладичук', 'Виорика Чербушка', 'Лариса Чепой', 'Кристина Цуркан', 'Инна Барышникова'];

    private const AURELIA = 'Аурелия Саликов';

    /** Стоит на последнем месте списка (ей поручено 'move_to_end'); до дополнения была шестой. */
    private const OLGA = 'Ольга Левицкая';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        $this->fakeImageProcessing();
    }

    /** @return list<array<string, mixed>> */
    private function profiles(): array
    {
        return require database_path('seeders/data/experts.php');
    }

    /** @return array<string, mixed> */
    private function profile(string $ruName): array
    {
        foreach ($this->profiles() as $profile) {
            if ($profile['name']['ru'] === $ruName) {
                return $profile;
            }
        }

        $this->fail("В данных нет эксперта «{$ruName}»");
    }

    private function expert(string $ruName): ?Expert
    {
        return Expert::query()->whereHas('translations', fn ($q) => $q->where('locale', 'ru')->where('name', $ruName))->first();
    }

    /** @return list<string> одиннадцать прежних экспертов в порядке файла данных (новые пятеро отсеяны). */
    private function originalNames(): array
    {
        return array_values(array_filter(
            array_map(fn (array $p) => $p['name']['ru'], $this->profiles()),
            fn (string $name) => ! in_array($name, self::NEW, true),
        ));
    }

    /** @return list<string> имена экспертов на сайте в порядке показа */
    private function orderedNames(): array
    {
        return Expert::query()->ordered()->get()->map(fn (Expert $e) => $e->field('name', 'ru'))->values()->all();
    }

    /**
     * База как на сервере до этого дополнения: одиннадцать прежних экспертов со старыми фото в прежнем порядке (Ольга
     * Левицкая шестая), списка обработанных нет.
     */
    private function serverBeforeTheRoster(): void
    {
        $this->seed(ExpertSeeder::class);

        foreach (self::NEW as $name) {
            $this->expert($name)->delete();
        }
        SiteSetting::query()->where('key', ExpertRosterSeeder::MARKER_KEY)->delete();
        Expert::query()->each(fn (Expert $expert) => $expert->update(['photo_path' => '2026/09/old-'.$expert->id.'.webp']));

        $oldOrder = array_values(array_diff($this->originalNames(), [self::OLGA]));
        array_splice($oldOrder, 5, 0, [self::OLGA]);
        foreach ($oldOrder as $index => $name) {
            $this->expert($name)->update(['position' => $index + 1]);
        }

        $this->imageCalls = [];
    }

    /** Записи данных с ключом 'photo_rev': фото этих экспертов сидер заменяет на сервере. */
    private function withPhotoRevision(): int
    {
        return count(array_filter($this->profiles(), fn (array $p) => array_key_exists('photo_rev', $p)));
    }

    // ---------------------------------------------------------------- данные

    public function test_the_five_new_experts_follow_the_original_ones_and_olga_levitskaya_is_last(): void
    {
        $names = array_map(fn (array $p) => $p['name']['ru'], $this->profiles());

        $this->assertCount(16, $names);
        $this->assertSame(self::NEW, array_slice($names, -6, 5), 'новые эксперты после прежних, в порядке инструкции');
        $this->assertSame(self::OLGA, $names[15], 'Ольга Левицкая — на последнем месте');
        $this->assertArrayHasKey('move_to_end', $this->profile(self::OLGA));

        foreach (self::NEW as $name) {
            $profile = $this->profile($name);

            foreach (['name', 'role', 'specialization', 'description'] as $field) {
                foreach (['ru', 'en', 'ro'] as $locale) {
                    $this->assertNotEmpty($profile[$field][$locale] ?? null, "{$name}: {$field} ({$locale})");
                }
            }
            $this->assertCount(3, $profile['tags'], $name);
            $this->assertContains($profile['tone'], CardTone::keys(), $name);
            $this->assertFileExists(public_path('themes/public/miro/images/'.$profile['photo']), $name);
        }
    }

    public function test_the_texts_match_the_customer_instructions(): void
    {
        $this->assertSame('Руководитель FinTech HUB', $this->profile('Оксана Паладичук')['role']['ru']);
        $this->assertSame('Специалист в области финтех-экосистем, развития стартапов и инноваций.', $this->profile('Оксана Паладичук')['description']['ru']);
        $this->assertSame('Генеральный директор Artcor', $this->profile('Виорика Чербушка')['role']['ru']);
        $this->assertSame('Амбассадор международной школы коучинга Noble Manhattan Coaching', $this->profile('Лариса Чепой')['role']['ru']);
        $this->assertSame('Основательница Woman Up и управляющая бизнес-сообществом Tractor', $this->profile('Кристина Цуркан')['role']['ru']);
        $this->assertSame('Помогает развивать малый бизнес и поддерживает социальные проекты.', $this->profile('Инна Барышникова')['description']['ru']);
    }

    public function test_the_crops_are_squares_inside_the_photos(): void
    {
        foreach (self::NEW as $name) {
            $profile = $this->profile($name);
            [$width, $height] = getimagesize(public_path('themes/public/miro/images/'.$profile['photo']));

            $crop = $profile['crop'] ?? ['x' => 0, 'y' => 0, 'width' => min($width, $height), 'height' => min($width, $height)];
            $this->assertSame($crop['width'], $crop['height'], "{$name}: кадр 1:1 под слот «expert»");
            $this->assertGreaterThanOrEqual(0, $crop['x']);
            $this->assertGreaterThanOrEqual(0, $crop['y']);
            $this->assertLessThanOrEqual($width, $crop['x'] + $crop['width'], "{$name}: кадр внутри фото по ширине");
            $this->assertLessThanOrEqual($height, $crop['y'] + $crop['height'], "{$name}: кадр внутри фото по высоте");
        }
    }

    public function test_aurelia_gets_the_new_photo_and_the_old_file_is_no_longer_referenced(): void
    {
        $profile = $this->profile(self::AURELIA);

        $this->assertSame('experts/expert-aurelia-portrait.webp', $profile['photo']);
        $this->assertArrayHasKey('photo_rev', $profile);
        $this->assertFileExists(public_path('themes/public/miro/images/'.$profile['photo']));
        $this->assertSame(
            [],
            array_filter($this->profiles(), fn (array $p) => ($p['photo'] ?? null) === 'experts/expert-aurelia.png'),
            'прежний портрет Аурелии из данных убран',
        );
    }

    // ---------------------------------------------------------------- база, где эксперты уже есть

    public function test_every_portrait_is_marked_for_replacement(): void
    {
        $marked = array_map(fn (array $p) => $p['name']['ru'], array_filter($this->profiles(), fn (array $p) => array_key_exists('photo_rev', $p)));

        $this->assertCount(16, $marked, 'все портреты приведены к одному размеру головы: у каждого своя версия фото');
        $this->assertContains('Каролина Бугаян', $marked);
        $this->assertContains(self::AURELIA, $marked);
    }

    public function test_on_a_database_with_experts_it_adds_the_five_at_the_end_and_replaces_the_marked_photos(): void
    {
        $this->serverBeforeTheRoster();
        $this->assertSame(11, Expert::count());

        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame(16, Expert::count());
        $this->assertSame(
            [...array_values(array_diff($this->originalNames(), [self::OLGA])), ...self::NEW, self::OLGA],
            $this->orderedNames(),
            'прежние по порядку, новые в конец, а Ольга Левицкая — последняя',
        );
        $this->assertSame(range(1, 16), Expert::query()->ordered()->pluck('position')->all(), 'номера подряд, без дыры на месте Ольги');

        foreach (self::NEW as $name) {
            $expert = $this->expert($name);
            $this->assertTrue($expert->is_published, $name);
            $this->assertNotNull($expert->photo_path, $name);
            Storage::disk('uploads')->assertExists($expert->photo_path);
            $this->assertSame($this->profile($name)['role']['en'], $expert->field('role', 'en'), $name);
            $this->assertSame($this->profile($name)['role']['ro'], $expert->field('role', 'ro'), $name);
            $this->assertSame(['Эксперт'], array_slice($expert->rawTranslation('ru')->tags, 0, 1), $name);
        }

        // У всех прежних экспертов фото заменено (и новое лежит на диске), а тексты не тронуты.
        foreach ($this->originalNames() as $name) {
            $expert = $this->expert($name);

            $this->assertStringStartsNotWith('2026/09/old-', $expert->photo_path, $name);
            Storage::disk('uploads')->assertExists($expert->photo_path);
            $this->assertSame($this->profile($name)['role']['ru'], $expert->field('role', 'ru'), $name);
        }
    }

    public function test_olga_levitskaya_moves_from_sixth_to_last_once_and_a_later_reorder_in_the_admin_survives(): void
    {
        $this->serverBeforeTheRoster();
        $this->assertSame(6, $this->expert(self::OLGA)->position, 'на сервере до дополнения она шестая');

        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame(self::OLGA, $this->orderedNames()[15]);
        $this->assertSame(16, $this->expert(self::OLGA)->position);

        // Редактор поставил её выше стрелками; повторный запуск порядок не возвращает.
        Expert::query()->where('position', '>=', 3)->where('id', '!=', $this->expert(self::OLGA)->id)->increment('position');
        $this->expert(self::OLGA)->update(['position' => 3]);
        $order = $this->orderedNames();

        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame($order, $this->orderedNames());
        $this->assertSame(3, $this->expert(self::OLGA)->position);
    }

    public function test_on_an_empty_database_olga_levitskaya_is_last_too(): void
    {
        $this->seed(ExpertSeeder::class);

        $this->assertSame(self::OLGA, $this->orderedNames()[15]);
        $this->assertSame(array_slice($this->orderedNames(), 10, 5), self::NEW);
        $this->assertSame(range(1, 16), Expert::query()->ordered()->pluck('position')->all());
    }

    public function test_raising_the_photo_revision_replaces_that_photo_once_more_and_leaves_the_others(): void
    {
        $this->serverBeforeTheRoster();
        $this->seed(ExpertRosterSeeder::class);

        $vlada = $this->expert('Влада Лысенко');
        $before = $vlada->photo_path;
        $this->expert('Мариана Руфа')->update(['photo_path' => '2026/10/edited.webp']);
        $this->imageCalls = [];

        (new class extends ExpertRosterSeeder
        {
            protected function profiles(): array
            {
                return array_map(function (array $profile) {
                    if ($profile['name']['ru'] === 'Влада Лысенко') {
                        $profile['photo_rev']++;
                    }

                    return $profile;
                }, parent::profiles());
            }
        })->run();

        $this->assertNotSame($before, $vlada->fresh()->photo_path, 'новая версия фото — замена ещё раз');
        $this->assertSame(['expert-vlada-wide.webp'], array_map(fn (array $call) => basename($call['source']), $this->imageCalls));
        $this->assertSame('2026/10/edited.webp', $this->expert('Мариана Руфа')->photo_path, 'правка в админке у других не затронута');
    }

    public function test_the_photos_go_through_the_expert_slot_with_the_crop_from_the_data(): void
    {
        $this->serverBeforeTheRoster();

        $this->seed(ExpertRosterSeeder::class);

        $this->assertCount($this->withPhotoRevision(), $this->imageCalls, 'пять новых фото и замена помеченных (у всех шестнадцати есть версия фото)');
        $this->assertSame(['expert'], array_values(array_unique(array_column($this->imageCalls, 'slot'))));

        // Кадр в конвейер уходит ровно тот, что задан в данных (нет кадра — null: центр квадратного фото).
        $expected = [];
        foreach ($this->profiles() as $profile) {
            $expected[basename($profile['photo'])] = $profile['crop'] ?? null;
        }
        $bySource = [];
        foreach ($this->imageCalls as $call) {
            $bySource[basename($call['source'])] = $call['crop'];
        }
        foreach ($bySource as $file => $crop) {
            $this->assertSame($expected[$file], $crop, $file);
        }
        $this->assertArrayHasKey('expert-aurelia-portrait.webp', $bySource);
        $this->assertArrayHasKey('expert-vlada-wide.webp', $bySource, 'перерисованный портрет уходит в тот же конвейер');
        $this->assertArrayHasKey('expert-oxana-wide.webp', $bySource);
    }

    public function test_a_crop_set_in_the_data_is_passed_to_the_pipeline(): void
    {
        $seeder = new class
        {
            use \Database\Seeders\Concerns\CreatesExperts;

            public $command = null;

            public function make(array $profile): Expert
            {
                return $this->createExpert($profile, 1);
            }
        };
        $profile = $this->profile('Оксана Паладичук');
        $profile['crop'] = ['x' => 10, 'y' => 20, 'width' => 300, 'height' => 300];

        $expert = $seeder->make($profile);

        $this->assertSame($profile['crop'], $this->imageCalls[0]['crop']);
        $this->assertSame('expert', $this->imageCalls[0]['slot']);
        Storage::disk('uploads')->assertExists($expert->photo_path);
    }

    public function test_every_photo_in_the_data_is_square_and_big_enough_so_the_slot_loses_nothing(): void
    {
        foreach ($this->profiles() as $profile) {
            [$width, $height] = getimagesize(public_path('themes/public/miro/images/'.$profile['photo']));

            $this->assertSame($width, $height, $profile['name']['ru'].': фото без кадра в данных квадратное');
            $this->assertGreaterThanOrEqual(800, $width, $profile['name']['ru'].': не меньше слота 800×800');
        }
    }

    public function test_a_rerun_changes_nothing_and_an_admin_edit_of_the_photo_survives_it(): void
    {
        $this->serverBeforeTheRoster();
        $this->seed(ExpertRosterSeeder::class);
        $this->imageCalls = [];

        $this->expert(self::AURELIA)->update(['photo_path' => '2026/10/edited-in-admin.webp']);
        $this->expert('Лариса Чепой')->translations()->where('locale', 'ru')->update(['role' => 'Правка из админки']);

        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame(16, Expert::count(), 'никого не дублирует');
        $this->assertSame([], $this->imageCalls, 'новых картинок не загружает');
        $this->assertSame('2026/10/edited-in-admin.webp', $this->expert(self::AURELIA)->photo_path);
        $this->assertSame('Правка из админки', $this->expert('Лариса Чепой')->field('role', 'ru'), 'текст эксперта не перезаписывается');
    }

    public function test_an_expert_deleted_in_the_admin_is_not_brought_back(): void
    {
        $this->serverBeforeTheRoster();
        $this->seed(ExpertRosterSeeder::class);

        $this->expert('Кристина Цуркан')->delete();
        $this->seed(ExpertRosterSeeder::class);

        $this->assertNull($this->expert('Кристина Цуркан'));
        $this->assertSame(15, Expert::count());
    }

    public function test_an_expert_who_is_already_there_is_not_duplicated(): void
    {
        $this->serverBeforeTheRoster();
        $existing = Expert::create(['position' => 12, 'tone' => 'teal', 'photo_path' => '2026/10/own.webp']);
        $existing->translations()->create(['locale' => 'ru', 'name' => 'Инна Барышникова', 'role' => 'Своя подпись']);

        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame(1, Expert::query()->whereHas('translations', fn ($q) => $q->where('name', 'Инна Барышникова'))->count());
        $this->assertSame('Своя подпись', $this->expert('Инна Барышникова')->field('role', 'ru'));
        $this->assertNotSame('2026/10/own.webp', $this->expert('Инна Барышникова')->photo_path, 'у портрета новая версия: фото заменяется один раз');
        $this->assertSame(16, Expert::count());
    }

    // ---------------------------------------------------------------- пустая база

    public function test_on_an_empty_database_the_expert_seeder_creates_all_sixteen_and_the_roster_seeder_adds_nothing(): void
    {
        $this->seed(ExpertSeeder::class);

        $this->assertSame($this->expertsTotal(), Expert::count());
        $this->assertSame(16, Expert::count());
        $callsAfterImport = count($this->imageCalls);
        $aureliaPhoto = $this->expert(self::AURELIA)->photo_path;

        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame(16, Expert::count());
        $this->assertCount($callsAfterImport, $this->imageCalls, 'только что заведённые фото второй раз не грузятся');
        $this->assertSame($aureliaPhoto, $this->expert(self::AURELIA)->photo_path);
        $this->assertContains(
            'expert-aurelia-portrait.webp',
            array_map(fn (array $call) => basename($call['source']), $this->imageCalls),
            'Аурелию сразу завели с новым фото',
        );
        $this->assertNotContains('expert-aurelia.png', array_map(fn (array $call) => basename($call['source']), $this->imageCalls));
    }

    public function test_after_deleting_every_expert_in_the_admin_the_expert_seeder_brings_all_sixteen_back(): void
    {
        $this->seed(ExpertSeeder::class);
        $this->seed(ExpertRosterSeeder::class); // как на сервере: список обработанных уже есть
        Expert::query()->each(fn (Expert $expert) => $expert->delete());
        $this->assertSame(0, Expert::count());
        $this->imageCalls = [];

        $this->seed(ExpertSeeder::class);

        $this->assertSame(16, Expert::count());
        $this->assertSame(range(1, 16), Expert::query()->ordered()->pluck('position')->all());
        $this->assertSame(
            array_map(fn (array $p) => $p['name']['ru'], $this->profiles()),
            Expert::query()->ordered()->get()->map(fn (Expert $e) => $e->field('name', 'ru'))->all(),
            'порядок как в файле данных, новые в конце',
        );
        foreach (Expert::pluck('photo_path') as $path) {
            Storage::disk('uploads')->assertExists($path);
        }
        $this->assertCount(16, $this->imageCalls, 'каждый эксперт со своим фото');

        // И дополняющий сидер после этого ничего не меняет.
        $this->imageCalls = [];
        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame(16, Expert::count());
        $this->assertSame([], $this->imageCalls);
    }

    public function test_the_roster_seeder_alone_respects_the_deletion_until_its_list_is_cleared(): void
    {
        $this->seed(ExpertSeeder::class);
        $this->seed(ExpertRosterSeeder::class);
        Expert::query()->each(fn (Expert $expert) => $expert->delete());

        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame(0, Expert::count(), 'удалённых в админке дополняющий сидер не возвращает');

        SiteSetting::query()->where('key', ExpertRosterSeeder::MARKER_KEY)->delete();
        $this->seed(ExpertRosterSeeder::class);

        $this->assertSame(16, Expert::count(), 'после очистки списка заводит всех');
        $this->assertSame(range(1, 16), Expert::query()->ordered()->pluck('position')->all());
    }

    public function test_a_missing_photo_of_a_new_expert_is_restored_from_the_theme_source(): void
    {
        $this->seed(ExpertSeeder::class);
        $viorica = $this->expert('Виорика Чербушка');
        $viorica->update(['photo_path' => '2026/09/lost.webp']);
        $this->imageCalls = [];

        $this->seed(ExpertSeeder::class);

        $this->assertNotSame('2026/09/lost.webp', $viorica->fresh()->photo_path);
        $this->assertSame($this->profile('Виорика Чербушка')['crop'] ?? null, $this->imageCalls[0]['crop']);
        $this->assertSame('expert-viorica.webp', basename($this->imageCalls[0]['source']));
    }

    public function test_content_seeder_runs_the_roster_seeder_and_the_public_page_lists_the_new_experts(): void
    {
        $this->seedContent();

        $html = $this->get(route('experts'))->assertOk()->getContent();

        foreach (self::NEW as $name) {
            $this->assertStringContainsString($name, $html);
        }
        $this->assertStringContainsString('Oxana Paladiciuc', $html);
        $this->assertStringContainsString('Cristina Țurcan', $html);
        $this->assertStringContainsString('Руководитель FinTech HUB', $html);
        $this->assertStringContainsString('Experții noștri', $html);
        $this->assertSame(16, substr_count($html, 'class="miro-public-member"'));
    }

    private function seedContent(): void
    {
        $this->seed(\Database\Seeders\ContentSeeder::class);
    }
}
