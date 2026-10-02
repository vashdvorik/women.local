<?php

namespace Tests\Feature\Admin;

use App\Actions\StoreUploadedFile;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Блок «Файл (PDF)» в редакторе публикаций: загрузка PDF, сохранение блока и то, как он делится
 * между языками. Файл общий для всех языков (как картинка), переводится только название.
 */
class FileBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Подменный диск: тест не должен писать в реальный public/uploads.
        Storage::fake('uploads');
    }

    private function pdf(string $name = 'Каталог 2026.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n"
        );
    }

    private function fileBlock(array $data, string $uid = 'f1'): string
    {
        return json_encode([['uid' => $uid, 'type' => 'file', 'data' => $data]]);
    }

    // ------------------------------------------------------------------ загрузка

    public function test_pdf_is_stored_under_a_random_name_and_the_original_name_is_reported(): void
    {
        $response = $this->actingAsAdmin()->postJson(route('admin.uploads.file'), [
            'file' => $this->pdf('Каталог 2026.pdf'),
        ]);

        $response->assertOk();
        $path = $response->json('path');

        $this->assertMatchesRegularExpression('#^files/\d{4}/\d{2}/[a-z0-9]{16}\.pdf$#', $path);
        Storage::disk('uploads')->assertExists($path);
        $this->assertSame('/uploads/'.$path, $response->json('url'));
        $this->assertSame('Каталог 2026.pdf', $response->json('name'));
        $this->assertGreaterThan(0, $response->json('size'));
    }

    public function test_a_path_or_extension_in_the_original_name_cannot_reach_the_disk(): void
    {
        $response = $this->actingAsAdmin()->postJson(route('admin.uploads.file'), [
            'file' => $this->pdf('../../evil.php.pdf'),
        ]);

        $response->assertOk();
        $this->assertStringStartsWith('files/', $response->json('path'));
        $this->assertStringNotContainsString('evil', $response->json('path'));
        $this->assertSame('evil.php.pdf', $response->json('name')); // в редакторе — только последний сегмент
    }

    public function test_an_image_is_not_accepted_as_a_pdf(): void
    {
        $this->actingAsAdmin()->postJson(route('admin.uploads.file'), [
            'file' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertStatus(422)->assertJsonValidationErrors('file');
    }

    public function test_html_renamed_to_pdf_is_rejected_by_its_content(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($path, '<!doctype html><html><body><script>alert(1)</script></body></html>');
        $disguised = new UploadedFile($path, 'catalog.pdf', 'application/pdf', null, true);

        $this->actingAsAdmin()->postJson(route('admin.uploads.file'), ['file' => $disguised])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');

        $this->assertSame([], Storage::disk('uploads')->allFiles());

        @unlink($path);
    }

    public function test_oversized_pdf_is_rejected_with_a_readable_message(): void
    {
        $response = $this->actingAsAdmin()->postJson(route('admin.uploads.file'), [
            'file' => UploadedFile::fake()->create('big.pdf', StoreUploadedFile::MAX_KB + 1, 'application/pdf'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('file');
        $this->assertStringContainsString('25 МБ', $response->json('errors.file.0'));
        $this->assertSame([], Storage::disk('uploads')->allFiles());
    }

    public function test_a_missing_file_is_rejected(): void
    {
        $this->actingAsAdmin()->postJson(route('admin.uploads.file'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_guests_cannot_upload_files(): void
    {
        $this->post(route('admin.uploads.file'), [])->assertRedirect(route('login'));
    }

    // ------------------------------------------------------------------ сохранение

    public function test_file_block_is_saved_with_the_path_title_and_file_details(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => ['ru' => [
                'title' => 'Каталог',
                'content' => $this->fileBlock([
                    'path' => 'files/2026/09/abcdefgh12345678.pdf',
                    'title' => '  Каталог   участниц  ',
                    'name' => 'catalog.pdf',
                    'size' => 2500000,
                ]),
            ]],
        ])->assertRedirect();

        $block = Post::with('translations')->first()->translations->firstWhere('locale', 'ru')->content[0];

        $this->assertSame('file', $block['type']);
        $this->assertSame('files/2026/09/abcdefgh12345678.pdf', $block['data']['path']);
        $this->assertSame('Каталог участниц', $block['data']['title']); // пробелы схлопнуты
        $this->assertSame('catalog.pdf', $block['data']['name']);
        $this->assertSame(2500000, $block['data']['size']);
    }

    public function test_the_title_is_translated_but_the_file_comes_from_the_russian_version(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => [
                'ru' => ['title' => 'Каталог', 'content' => $this->fileBlock([
                    'path' => 'files/2026/09/abcdefgh12345678.pdf', 'title' => 'Каталог 2026',
                ])],
                'ro' => ['title' => 'Catalog', 'content' => $this->fileBlock([
                    // Румынская вкладка не может подменить файл — он берётся из русской версии.
                    'path' => 'files/2026/09/evil0000evil0000.pdf', 'title' => 'Catalog 2026',
                ])],
            ],
        ]);

        $post = Post::with('translations')->first();

        $ro = $post->translations->firstWhere('locale', 'ro')->content[0]['data'];
        $this->assertSame('files/2026/09/abcdefgh12345678.pdf', $ro['path']);
        $this->assertSame('Catalog 2026', $ro['title']);

        // Английского перевода нет вовсе — на странице показывается русское название.
        $this->assertSame('Catalog 2026', $post->renderBlocks('ro')[0]['data']['title']);
        $this->assertSame('Каталог 2026', $post->renderBlocks('en')[0]['data']['title']);
    }

    public function test_an_untranslated_title_falls_back_to_the_russian_one(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => [
                'ru' => ['title' => 'Каталог', 'content' => $this->fileBlock([
                    'path' => 'files/2026/09/abcdefgh12345678.pdf', 'title' => 'Каталог 2026',
                ])],
                'ro' => ['title' => 'Catalog', 'content' => $this->fileBlock(['title' => ''])],
            ],
        ]);

        $post = Post::with('translations')->first();

        $this->assertSame('Каталог 2026', $post->renderBlocks('ro')[0]['data']['title']);
    }

    public function test_a_translation_that_only_has_a_file_title_is_kept(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => [
                'ru' => ['title' => 'Каталог', 'content' => $this->fileBlock([
                    'path' => 'files/2026/09/abcdefgh12345678.pdf', 'title' => 'Каталог 2026',
                ])],
                'en' => ['title' => '', 'content' => $this->fileBlock(['title' => 'Catalogue 2026'])],
            ],
        ]);

        $en = Post::with('translations')->first()->translations->firstWhere('locale', 'en');

        $this->assertNotNull($en, 'перевод только с названием файла не должен пропадать');
        $this->assertSame('Catalogue 2026', $en->content[0]['data']['title']);
    }

    public function test_an_unsafe_or_foreign_file_path_is_dropped(): void
    {
        foreach (['../secret.pdf', 'files/../../secret.pdf', 'files/2026/09/photo.webp', 'files/2026/09/run.php', '/etc/passwd'] as $bad) {
            $this->actingAsAdmin()->post(route('admin.posts.store'), [
                'translations' => ['ru' => ['title' => 'Т '.$bad, 'content' => $this->fileBlock(['path' => $bad, 'name' => 'x.pdf', 'size' => 10])]],
            ]);

            $data = Post::with('translations')->latest('id')->first()
                ->translations->firstWhere('locale', 'ru')->content[0]['data'];

            $this->assertNull($data['path'], "путь {$bad} должен отбрасываться");
            $this->assertNull($data['name'], 'имя и размер без файла не сохраняются');
            $this->assertNull($data['size']);
        }
    }

    // ------------------------------------------------------------------ редактор

    public function test_editor_offers_the_file_block_and_knows_where_to_upload_it(): void
    {
        $this->actingAsAdmin()->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee("addBlock('file')", false)
            ->assertSee('Файл (PDF)')
            ->assertSee('uploadFileUrl', false)
            ->assertSee('fileMaxMb', false);
    }

    public function test_editor_loads_a_saved_file_block_with_its_details(): void
    {
        $post = Post::create(['slug' => 'katalog', 'status' => 'draft']);
        $post->translations()->create(['locale' => 'ru', 'title' => 'Каталог', 'content' => [
            ['uid' => 'f1', 'type' => 'file', 'data' => [
                'path' => 'files/2026/09/abcdefgh12345678.pdf', 'title' => 'Каталог 2026', 'name' => 'catalog.pdf', 'size' => 1234,
            ]],
        ]]);

        $this->actingAsAdmin()->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertSee('abcdefgh12345678.pdf', false)
            ->assertSee('catalog.pdf', false);
    }
}
