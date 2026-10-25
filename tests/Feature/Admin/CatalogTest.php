<?php

namespace Tests\Feature\Admin;

use App\Models\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Каталоги с перелистыванием: страницы-картинки приходят из браузера администратора, посетителю уходят только они. */
class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    /** @return list<string> пути страниц, уже лежащих на диске (как после загрузки из браузера) */
    private function pages(int $count = 3): array
    {
        $paths = [];

        for ($i = 1; $i <= $count; $i++) {
            $paths[] = "catalogs/2026/10/page{$i}.webp";
            Storage::disk('uploads')->put("catalogs/2026/10/page{$i}.webp", 'x');
        }

        return $paths;
    }

    private function payload(array $pages, array $overrides = []): array
    {
        return array_replace_recursive([
            'pages' => json_encode($pages),
            'page_ratio' => '0.7071',
            'is_published' => '1',
            'translations' => [
                'ru' => ['title' => 'Каталог услуг', 'description' => 'Все услуги в одном месте.'],
                'en' => ['title' => 'Service catalogue'],
            ],
        ], $overrides);
    }

    public function test_the_forms_render_with_the_pdf_converter_and_the_menu_has_the_section(): void
    {
        $create = $this->actingAsAdmin()->get(route('admin.catalogs.create'))->assertOk()->getContent();

        $this->assertStringContainsString('catalogPages(', $create);
        $this->assertStringContainsString('name="pdf"', $create);
        $this->assertStringContainsString(route('admin.catalogs.page'), str_replace('\/', '/', $create));
        $this->assertStringContainsString(route('admin.catalogs.index'), $create, 'пункт меню «Все каталоги»');

        $this->actingAsAdmin()->get(route('admin.catalogs.index'))->assertOk();
    }

    public function test_a_converted_page_is_stored_and_its_path_returned(): void
    {
        $file = UploadedFile::fake()->image('page-1.webp', 400, 560);

        $response = $this->actingAsAdmin()->postJson(route('admin.catalogs.page'), ['file' => $file])->assertOk();

        $path = $response->json('path');
        $this->assertStringStartsWith('catalogs/', $path);
        Storage::disk('uploads')->assertExists($path);
        $this->assertSame('/uploads/'.$path, $response->json('url'));

        $this->actingAsAdmin()->postJson(route('admin.catalogs.page'), ['file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertStatus(422);
    }

    public function test_a_catalogue_is_saved_with_pages_titles_and_an_address_and_shows_on_the_site(): void
    {
        $pages = $this->pages(4);

        $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload($pages))
            ->assertRedirect(route('admin.catalogs.index'));

        $catalog = Catalog::with('translations')->firstOrFail();

        $this->assertSame('katalog-uslug', $catalog->slug);
        $this->assertSame($pages, $catalog->pagePaths());
        $this->assertSame('Service catalogue', $catalog->rawTranslation('en')->title);
        $this->assertNull($catalog->rawTranslation('ro'), 'пустой румынский перевод не хранится');

        $list = $this->get('/media/publications')->assertOk()->getContent();
        $this->assertStringContainsString('/media/publications/catalog/katalog-uslug', $list);

        $page = $this->get('/media/publications/catalog/katalog-uslug')->assertOk()->getContent();
        $this->assertStringContainsString('data-flip', $page);
        $this->assertStringContainsString('page-flip.browser.js', $page);
        $this->assertStringContainsString('js/catalog.js', $page);
        // В разметке лежат только адреса страниц, картинки подгружает скрипт по мере листания
        $this->assertSame(4, substr_count(html_entity_decode($page), '/uploads/catalogs/2026/10/page'));
        $this->assertStringNotContainsString('<img src="/uploads/catalogs/2026/10/page2.webp"', $page);
    }

    public function test_the_download_button_needs_both_the_pdf_and_the_flag(): void
    {
        $pages = $this->pages(2);
        $pdf = UploadedFile::fake()->create('catalog.pdf', 100, 'application/pdf');

        $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload($pages, ['pdf' => $pdf, 'allow_download' => '1']))
            ->assertRedirect(route('admin.catalogs.index'));

        $catalog = Catalog::firstOrFail();
        Storage::disk('uploads')->assertExists($catalog->pdf_path);
        $this->assertStringContainsString('Скачать PDF', $this->get('/media/publications/catalog/'.$catalog->slug)->getContent());

        $catalog->update(['allow_download' => false]);
        \App\Support\PublicCache::flush();
        $this->assertStringNotContainsString('Скачать PDF', $this->get('/media/publications/catalog/'.$catalog->slug)->getContent());
    }

    public function test_page_paths_outside_the_catalogue_folder_or_missing_on_disk_are_rejected(): void
    {
        Storage::disk('uploads')->put('2026/10/secret.webp', 'x');

        $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload(['2026/10/secret.webp']))->assertSessionHasErrors('pages');
        $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload(['catalogs/../2026/10/secret.webp']))->assertSessionHasErrors('pages');
        $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload(['catalogs/2026/10/nothing.webp']))->assertSessionHasErrors('pages');
        $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload([]))->assertSessionHasErrors('pages');

        $this->assertSame(0, Catalog::count());
    }

    public function test_removed_pages_are_deleted_from_disk_and_the_address_stays(): void
    {
        $pages = $this->pages(3);
        $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload($pages));
        $catalog = Catalog::firstOrFail();

        $this->actingAsAdmin()->put(route('admin.catalogs.update', $catalog), $this->payload(
            [$pages[0], $pages[2]],
            ['translations' => ['ru' => ['title' => 'Каталог услуг 2']]],
        ))->assertRedirect(route('admin.catalogs.index'));

        $catalog->refresh();

        $this->assertSame('katalog-uslug', $catalog->slug);
        $this->assertSame([$pages[0], $pages[2]], $catalog->pagePaths());
        Storage::disk('uploads')->assertMissing($pages[1]);
        Storage::disk('uploads')->assertExists($pages[0]);
    }

    public function test_an_unpublished_catalogue_is_hidden_from_visitors_but_the_admin_can_preview_it(): void
    {
        $pages = $this->pages(2);
        $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload($pages, ['is_published' => '0']));
        $catalog = Catalog::firstOrFail();
        $this->assertFalse($catalog->is_published);

        auth()->logout();
        $this->get('/media/publications/catalog/'.$catalog->slug)->assertNotFound();
        $this->assertStringNotContainsString($catalog->slug, $this->get('/media/publications')->getContent());

        $this->actingAsAdmin()->get('/media/publications/catalog/'.$catalog->slug)->assertOk();
    }

    public function test_the_order_is_saved_and_deleting_removes_the_files(): void
    {
        foreach (['a', 'b'] as $name) {
            $this->actingAsAdmin()->post(route('admin.catalogs.store'), $this->payload($this->pages(1), ['translations' => ['ru' => ['title' => "Каталог {$name}"]]]));
        }

        [$first, $second] = Catalog::ordered()->get()->all();

        $this->actingAsAdmin()->postJson(route('admin.catalogs.reorder'), ['ids' => [$second->id, $first->id]])->assertOk();
        $this->assertSame([$second->id, $first->id], Catalog::ordered()->pluck('id')->all());

        $paths = $first->pagePaths();
        $this->actingAsAdmin()->delete(route('admin.catalogs.destroy', $first))->assertRedirect(route('admin.catalogs.index'));

        $this->assertNull(Catalog::find($first->id));
        Storage::disk('uploads')->assertMissing($paths[0]);
    }
}
