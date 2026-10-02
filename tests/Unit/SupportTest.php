<?php

namespace Tests\Unit;

use App\Support\AspectRatio;
use App\Support\Blocks;
use App\Support\Contrast;
use App\Support\FileBlock;
use App\Support\TranslationStatus;
use App\Support\YouTube;
use PHPUnit\Framework\TestCase;

class SupportTest extends TestCase
{
    public function test_contrast_picks_readable_text_colour(): void
    {
        $this->assertSame('#ffffff', Contrast::textOn('#0066cc'));
        $this->assertSame('#1d1d1f', Contrast::textOn('#ffee00'));
    }

    public function test_youtube_id_extraction(): void
    {
        $this->assertSame('dQw4w9WgXcQ', YouTube::id('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', YouTube::id('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', YouTube::id('https://youtube.com/shorts/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', YouTube::id('https://www.youtube.com/embed/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', YouTube::id('https://www.youtube.com/live/dQw4w9WgXcQ'));
        $this->assertNull(YouTube::id('https://example.com/video'));
    }

    public function test_normalize_path_keeps_any_safe_webp_under_uploads(): void
    {
        // Все источники картинок сразу, без привязки к «каким инструментом создан».
        foreach ([
            '2026/09/abcdef123456.webp',          // загрузчик панели
            'seed/opp-generation-lab.webp',        // основные сиды
            'seed/irina.webp',
            'dev/news-1.webp',                      // демоконтент
            'dev/photo-tall-4.webp',
        ] as $path) {
            $this->assertSame($path, Blocks::normalizePath($path));
        }

        // Домен, ведущий /uploads/ (в т.ч. задвоенный) — срезаются.
        $this->assertSame('2026/09/ab.webp', Blocks::normalizePath('https://site.md/uploads/2026/09/ab.webp'));
        $this->assertSame('2026/09/ab.webp', Blocks::normalizePath('/uploads/uploads/2026/09/ab.webp'));

        // Отбрасывается всё небезопасное и не-webp.
        foreach ([
            'seed/../evil.webp', 'seed/..webp', '../../etc/passwd',
            'a/b/c/d/e/f.webp',                     // слишком глубоко
            'seed/photo.png', '.hidden/x.webp', '-danger/x.webp', '',
        ] as $bad) {
            $this->assertNull(Blocks::normalizePath($bad), "должен отбрасываться: {$bad}");
        }
        $this->assertNull(Blocks::normalizePath(null));
    }

    public function test_aspect_ratio_registry_is_consistent(): void
    {
        $this->assertSame('1320 / 600', AspectRatio::css('cover'));
        $this->assertSame('gallery_4', AspectRatio::forGallery(4));
        $this->assertTrue(AspectRatio::exists('image'));
        $this->assertFalse(AspectRatio::exists('nonsense'));
    }

    public function test_blocks_canonical_drops_unknown_types_and_pads_galleries(): void
    {
        $canonical = Blocks::canonical([
            ['uid' => 'a', 'type' => 'text', 'data' => ['html' => '<p>x</p>']],
            ['uid' => 'b', 'type' => 'quote', 'data' => []],
            ['uid' => 'c', 'type' => 'gallery_3', 'data' => ['images' => ['2026/09/aaaaaaaaaaaa.webp']]],
        ], Blocks::ARTICLE_KINDS);

        $this->assertCount(2, $canonical);
        $this->assertSame('text', $canonical[0]['type']);
        $this->assertCount(3, $canonical[1]['data']['images']);
        $this->assertSame('2026/09/aaaaaaaaaaaa.webp', $canonical[1]['data']['images'][0]);
        $this->assertNull($canonical[1]['data']['images'][1]);
    }

    public function test_blocks_merge_translation_keeps_structure_and_images_from_primary(): void
    {
        $primary = Blocks::canonical([
            ['uid' => 'h', 'type' => 'heading', 'data' => ['text' => 'Русский', 'level' => 'h3']],
            ['uid' => 'i', 'type' => 'image', 'data' => ['path' => '2026/09/bbbbbbbbbbbb.webp']],
        ], Blocks::ARTICLE_KINDS);

        $merged = Blocks::mergeTranslation($primary, [
            ['uid' => 'h', 'type' => 'heading', 'data' => ['text' => 'Titlu', 'level' => 'h2']],
            ['uid' => 'i', 'type' => 'image', 'data' => ['path' => 'evil.webp']],
        ]);

        $this->assertSame('Titlu', $merged[0]['data']['text']);
        $this->assertSame('h3', $merged[0]['data']['level']); // уровень из русской версии
        $this->assertSame('2026/09/bbbbbbbbbbbb.webp', $merged[1]['data']['path']); // картинка из русской версии
    }

    public function test_blocks_reject_full_urls_in_image_paths(): void
    {
        $canonical = Blocks::canonical([
            ['uid' => 'i', 'type' => 'image', 'data' => ['path' => 'https://evil.example/2026/09/cccccccccccc.webp']],
        ], Blocks::ARTICLE_KINDS);

        $this->assertSame('2026/09/cccccccccccc.webp', $canonical[0]['data']['path']);
    }

    public function test_file_path_accepts_only_a_safe_pdf_under_uploads(): void
    {
        $this->assertSame('files/2026/09/abcdefgh12345678.pdf', Blocks::normalizeFilePath('files/2026/09/abcdefgh12345678.pdf'));
        $this->assertSame('files/2026/09/abcdefgh12345678.pdf', Blocks::normalizeFilePath('/uploads/files/2026/09/abcdefgh12345678.pdf'));
        $this->assertSame('files/2026/09/abcdefgh12345678.pdf', Blocks::normalizeFilePath('https://evil.example/uploads/files/2026/09/abcdefgh12345678.pdf'));

        $this->assertNull(Blocks::normalizeFilePath('../secret.pdf'));
        $this->assertNull(Blocks::normalizeFilePath('files/../../secret.pdf'));
        $this->assertNull(Blocks::normalizeFilePath('files/2026/09/run.php'));
        $this->assertNull(Blocks::normalizeFilePath('files/2026/09/photo.webp'));
        $this->assertNull(Blocks::normalizeFilePath('a/b/c/d/e.pdf')); // слишком глубоко
        $this->assertNull(Blocks::normalizeFilePath(''));
        $this->assertNull(Blocks::normalizeFilePath(null));

        // Виды не подменяют друг друга: PDF — не картинка, картинка — не файл.
        $this->assertNull(Blocks::normalizePath('files/2026/09/abcdefgh12345678.pdf'));
    }

    public function test_file_block_canonical_form(): void
    {
        $canonical = Blocks::canonical([
            ['uid' => 'f', 'type' => 'file', 'data' => [
                'path' => 'files/2026/09/abcdefgh12345678.pdf',
                'title' => "  Каталог \n  2026  ",
                'name' => "../..\\каталог\x07.pdf",
                'size' => '2500000',
            ]],
            ['uid' => 'g', 'type' => 'file', 'data' => ['path' => 'nope.txt', 'title' => 'Без файла', 'name' => 'x.pdf', 'size' => 5]],
            ['uid' => 'h', 'type' => 'file', 'data' => ['path' => 'files/2026/09/abcdefgh12345678.pdf', 'size' => -1]],
        ], Blocks::ARTICLE_KINDS);

        $this->assertSame([
            'path' => 'files/2026/09/abcdefgh12345678.pdf',
            'title' => 'Каталог 2026',
            'name' => 'каталог.pdf',
            'size' => 2500000,
        ], $canonical[0]['data']);

        // Без корректного файла имя и размер тоже не сохраняются; название остаётся.
        $this->assertSame(['path' => null, 'title' => 'Без файла', 'name' => null, 'size' => null], $canonical[1]['data']);

        // Неизвестный размер — null, а не «0» или отрицательное число.
        $this->assertNull($canonical[2]['data']['size']);
        $this->assertSame('', $canonical[2]['data']['title']);

        // Альбому файлы не нужны: там только картинки.
        $this->assertSame([], Blocks::canonical([['uid' => 'f', 'type' => 'file', 'data' => []]], Blocks::ALBUM_KINDS));
    }

    public function test_file_block_merge_takes_the_title_from_the_translation_and_the_file_from_primary(): void
    {
        $primary = Blocks::canonical([
            ['uid' => 'f', 'type' => 'file', 'data' => ['path' => 'files/2026/09/abcdefgh12345678.pdf', 'title' => 'Каталог', 'name' => 'k.pdf', 'size' => 10]],
        ], Blocks::ARTICLE_KINDS);

        $merged = Blocks::mergeTranslation($primary, [
            ['uid' => 'f', 'type' => 'file', 'data' => ['path' => 'files/evil.pdf', 'title' => ' Catalog ', 'name' => 'evil.pdf', 'size' => 999]],
        ]);

        $this->assertSame([
            'path' => 'files/2026/09/abcdefgh12345678.pdf',
            'title' => 'Catalog',
            'name' => 'k.pdf',
            'size' => 10,
        ], $merged[0]['data']);

        // Нет присланного перевода — название пустое (на сайте подставится русское).
        $this->assertSame('', Blocks::mergeTranslation($primary, [])[0]['data']['title']);
    }

    public function test_translation_status_counts_the_file_title_as_text_and_the_file_as_content(): void
    {
        $file = fn (?string $path, string $title) => ['uid' => 'f', 'type' => 'file', 'data' => ['path' => $path, 'title' => $title]];

        // Русская вкладка: файл — содержимое, а название без файла — нет.
        $withFile = ['title' => 'Т', 'excerpt' => 'О', 'content' => [$file('files/2026/09/a.pdf', '')]];
        $titleOnly = ['title' => 'Т', 'excerpt' => 'О', 'content' => [$file(null, 'Каталог')]];

        $this->assertSame(TranslationStatus::DONE, TranslationStatus::primary($withFile));
        $this->assertSame(TranslationStatus::EMPTY, TranslationStatus::primary($titleOnly));

        // Перевод: название файла — переводимый текст, его нехватка даёт «частично».
        $ru = ['title' => 'Т', 'excerpt' => 'О', 'content' => [$file('files/2026/09/a.pdf', 'Каталог')]];

        $this->assertSame(TranslationStatus::PARTIAL, TranslationStatus::secondary($ru, [
            'title' => 'T', 'excerpt' => 'O', 'content' => [$file('files/2026/09/a.pdf', '')],
        ]));
        $this->assertSame(TranslationStatus::DONE, TranslationStatus::secondary($ru, [
            'title' => 'T', 'excerpt' => 'O', 'content' => [$file('files/2026/09/a.pdf', 'Catalog')],
        ]));
    }

    public function test_pdf_size_label_is_short_and_follows_the_language(): void
    {
        $this->assertNull(FileBlock::sizeLabel(0));
        $this->assertSame('1 КБ', FileBlock::sizeLabel(10));
        $this->assertSame('340 КБ', FileBlock::sizeLabel(340 * 1024));
        $this->assertSame('2,4 МБ', FileBlock::sizeLabel(2_500_000));
        $this->assertSame('2.4 MB', FileBlock::sizeLabel(2_500_000, 'en'));
        $this->assertSame('2,4 MB', FileBlock::sizeLabel(2_500_000, 'ro'));
        $this->assertSame('340 KB', FileBlock::sizeLabel(340 * 1024, 'en'));
    }

    public function test_translation_status_distinguishes_partial_from_empty(): void
    {
        $ru = [
            'title' => 'Заголовок',
            'excerpt' => 'Описание',
            'content' => [['uid' => 'b1', 'type' => 'text', 'data' => ['html' => '<p>текст</p>']]],
        ];

        $this->assertSame(TranslationStatus::DONE, TranslationStatus::primary($ru));

        $this->assertSame(TranslationStatus::EMPTY, TranslationStatus::secondary($ru, [
            'title' => null, 'excerpt' => null, 'content' => [],
        ]));

        $this->assertSame(TranslationStatus::PARTIAL, TranslationStatus::secondary($ru, [
            'title' => 'Titlu', 'excerpt' => null, 'content' => [],
        ]));

        $this->assertSame(TranslationStatus::DONE, TranslationStatus::secondary($ru, [
            'title' => 'Titlu',
            'excerpt' => 'Descriere',
            'content' => [['uid' => 'b1', 'type' => 'text', 'data' => ['html' => '<p>text</p>']]],
        ]));
    }
}
