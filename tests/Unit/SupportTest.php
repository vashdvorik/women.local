<?php

namespace Tests\Unit;

use App\Support\AspectRatio;
use App\Support\Blocks;
use App\Support\Contrast;
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
