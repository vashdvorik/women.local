<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Support\Blocks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbedBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_embed_block_is_stored_verbatim(): void
    {
        $raw = '<script src="https://widget.example/e.js"></script><div class="w" data-x="1">hi</div>';

        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => ['ru' => [
                'title' => 'С виджетом',
                'content' => json_encode([
                    ['uid' => 'e1', 'type' => 'embed', 'data' => ['html' => $raw]],
                ]),
            ]],
        ]);

        $block = Post::with('translations')->first()->translations->firstWhere('locale', 'ru')->content[0];

        $this->assertSame('embed', $block['type']);
        $this->assertSame($raw, $block['data']['html']); // ничего не вырезано
    }

    public function test_embed_is_structural_and_shared_across_languages(): void
    {
        $raw = '<iframe src="https://maps.example"></iframe>';

        $this->actingAsAdmin()->post(route('admin.posts.store'), [
            'translations' => [
                'ru' => ['title' => 'RU', 'content' => json_encode([
                    ['uid' => 'e1', 'type' => 'embed', 'data' => ['html' => $raw]],
                ])],
                'ro' => ['title' => 'RO', 'content' => json_encode([
                    ['uid' => 'e1', 'type' => 'embed', 'data' => ['html' => '<iframe src="https://evil.example"></iframe>']],
                ])],
            ],
        ]);

        $post = Post::with('translations')->first();
        // румынская вкладка не может подменить embed — он берётся из русской версии
        $rendered = $post->renderBlocks('ro');
        $this->assertSame($raw, $rendered[0]['data']['html']);
    }

    public function test_text_block_still_strips_dangerous_html(): void
    {
        $rendered = clean(
            '<p>Норм</p><script>alert(1)</script><h2>Заголовок</h2><u>подчёркнуто</u><table><tr><td>x</td></tr></table>',
            'content_block'
        );

        $this->assertStringNotContainsString('<script', $rendered);
        $this->assertStringNotContainsString('<table', $rendered);
        $this->assertStringContainsString('<h2>Заголовок</h2>', $rendered);
        $this->assertStringContainsString('<u>подчёркнуто</u>', $rendered);
    }

    public function test_canonical_keeps_embed_html_untouched(): void
    {
        $raw = "  <div onclick=\"x()\">\n  сохранить как есть  </div>  ";

        $canonical = Blocks::canonical([
            ['uid' => 'e1', 'type' => 'embed', 'data' => ['html' => $raw]],
        ], Blocks::ARTICLE_KINDS);

        $this->assertSame($raw, $canonical[0]['data']['html']); // без trim, без очистки
    }

    public function test_link_without_explicit_target_stays_in_the_same_window(): void
    {
        // Раньше HTML.TargetBlank принудительно открывал ЛЮБУЮ ссылку в новой
        // вкладке — редактор не мог выбрать «в этом же окне» (жалоба пользователя).
        $rendered = clean('<p><a href="https://example.com">ссылка</a></p>', 'content_block');

        $this->assertStringNotContainsString('target=', $rendered);
    }

    public function test_link_target_blank_chosen_in_editor_is_preserved(): void
    {
        $rendered = clean(
            '<p><a href="https://example.com" target="_blank" rel="noopener noreferrer">ссылка</a></p>',
            'content_block'
        );

        $this->assertStringContainsString('target="_blank"', $rendered);
        $this->assertStringContainsString('noopener', $rendered);
        $this->assertStringContainsString('noreferrer', $rendered);
    }

    public function test_external_link_still_gets_nofollow(): void
    {
        $rendered = clean('<p><a href="https://example.com">ссылка</a></p>', 'content_block');

        $this->assertStringContainsString('nofollow', $rendered);
    }
}
