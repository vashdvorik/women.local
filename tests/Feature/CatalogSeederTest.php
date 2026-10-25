<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\SiteSetting;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Журналы как каталоги с перелистыванием (database/seeders/data/catalogs.php и папки со страницами). */
class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');

        // Маленькая своя папка вместо настоящих журналов (десятки мегабайт): три страницы и PDF
        $this->dir = sys_get_temp_dir().'/catalog-seeder-'.uniqid();
        File::ensureDirectoryExists($this->dir.'/demo');
        foreach (['001', '002', '003'] as $name) {
            File::put($this->dir."/demo/{$name}.webp", 'x'.$name);
        }
        File::put($this->dir.'/demo/catalog.pdf', '%PDF-1.4');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    private function seeder(): CatalogSeeder
    {
        $dir = $this->dir;

        return new class($dir) extends CatalogSeeder
        {
            public function __construct(private readonly string $base)
            {
            }

            protected function items(): array
            {
                return [[
                    'slug' => 'demo-magazine',
                    'folder' => 'demo',
                    'ratio' => 0.75,
                    'title' => ['ru' => 'Демо-журнал', 'en' => 'Demo magazine', 'ro' => 'Revistă demo'],
                    'description' => ['ru' => 'Описание', 'en' => 'Description', 'ro' => 'Descriere'],
                ]];
            }

            protected function folder(string $name): string
            {
                return $this->base.'/'.$name;
            }
        };
    }

    public function test_it_creates_a_published_catalogue_with_pages_pdf_and_three_languages(): void
    {
        $this->seeder()->run();

        $catalog = Catalog::with('translations')->where('slug', 'demo-magazine')->firstOrFail();

        $this->assertTrue($catalog->is_published);
        $this->assertCount(3, $catalog->pagePaths());
        $this->assertSame(0.75, $catalog->page_ratio);
        $this->assertTrue($catalog->allow_download);
        Storage::disk('uploads')->assertExists($catalog->pdf_path);
        foreach ($catalog->pagePaths() as $path) {
            Storage::disk('uploads')->assertExists($path);
        }
        foreach (['ru', 'en', 'ro'] as $locale) {
            $this->assertNotSame('', trim((string) $catalog->rawTranslation($locale)?->title), $locale);
        }

        $page = $this->get('/media/publications/catalog/demo-magazine')->assertOk()->getContent();
        $this->assertStringContainsString('data-flip', $page);
        $this->assertStringContainsString($catalog->pdf_path, $page);
        $this->assertStringContainsString('/media/publications/catalog/demo-magazine', $this->get('/media/publications')->getContent());
    }

    public function test_a_second_run_does_not_duplicate_and_a_deleted_catalogue_stays_deleted(): void
    {
        $this->seeder()->run();
        $this->seeder()->run();

        $this->assertSame(1, Catalog::count());
        $this->assertSame(['demo-magazine'], SiteSetting::read(CatalogSeeder::MARKER_KEY)['keys']);

        Catalog::first()->delete();
        $this->seeder()->run();

        $this->assertSame(0, Catalog::count());
    }

    public function test_the_real_data_points_to_existing_pages_and_pdf_for_every_magazine(): void
    {
        $items = require database_path('seeders/data/catalogs.php');

        $this->assertCount(2, $items);

        foreach ($items as $item) {
            $folder = database_path('seeders/data/catalogs/'.$item['folder']);

            $this->assertFileExists($folder.'/catalog.pdf', $item['slug']);
            $this->assertGreaterThan(40, count(glob($folder.'/*.webp')), $item['slug']);
            $this->assertGreaterThan(0.5, $item['ratio']);

            foreach (['ru', 'en', 'ro'] as $locale) {
                $this->assertNotSame('', trim($item['title'][$locale]));
                $this->assertNotSame('', trim($item['description'][$locale]));
            }
        }
    }
}
