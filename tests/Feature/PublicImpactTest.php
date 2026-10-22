<?php

namespace Tests\Feature;

use App\Support\ImpactStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Блок «Наш вклад в цифрах» на главной: оргсхема «центр → три ветви → показатели» после «Послания директора». */
class PublicImpactTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_infographic_sits_between_the_director_note_and_the_benefits(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="impact"'));
        $note = strpos($html, 'class="miro-director-note"');
        $impact = strpos($html, 'id="impact"');
        $benefits = strpos($html, 'id="benefits"');

        $this->assertNotFalse($note);
        $this->assertTrue($note < $impact && $impact < $benefits, 'порядок: послание директора → цифры → преимущества');
    }

    public function test_the_visual_order_puts_it_right_after_the_director_note(): void
    {
        // Порядок блоков главной задаёт CSS (flex + order), а не порядок в разметке: без своего order блок встал бы первым.
        $css = (string) file_get_contents(public_path('themes/public/miro/css/landing.css'));
        preg_match_all('/main > (?:\.|#)([a-z-]+) \{ order: (\d+); \}/', $css, $m);
        $order = array_combine($m[1], array_map('intval', $m[2]));

        $this->assertArrayHasKey('impact', $order);
        $this->assertSame($order['miro-director-note'] + 1, $order['impact']);
        $this->assertSame($order['impact'] + 1, $order['members']);
        $this->assertSame(count($order), count(array_unique($order)), 'у каждого блока свой номер порядка');
    }

    public function test_it_shows_three_branches_nine_cards_and_ten_figures_with_final_values_in_the_markup(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, 'class="miro-impact__branch"'));
        $this->assertSame(9, substr_count($html, 'class="miro-impact__node"'), 'девять карточек: конференции и форумы в одной');
        $this->assertSame(10, substr_count($html, 'data-count-to='), 'десять цифр');
        $this->assertSame(1, substr_count($html, 'class="miro-impact__pair"'));

        // Без скрипта (или с «уменьшить анимацию») цифры показаны сразу готовыми: в разметке стоит итоговое значение.
        foreach ([[1000, '1'."\u{00A0}".'000'], [5000, '5'."\u{00A0}".'000'], [1500, '1'."\u{00A0}".'500'], [800, '800'], [900, '900'], [45, '45'], [25, '25'], [15, '15']] as [$value, $text]) {
            $this->assertMatchesRegularExpression('#data-count-to="'.$value.'"[^>]*>'.preg_quote($text, '#').'</span>#u', $html, (string) $value);
        }
        $this->assertSame(7, substr_count($html, 'class="miro-impact__plus"'), 'плюс у семи показателей: 1000+, 5000+, 800+, 1500+, 900+, 30+, 30+');
    }

    public function test_every_label_is_written_in_all_three_languages(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['Наш вклад в цифрах', 'Our impact in numbers', 'Impactul nostru în cifre', 'мероприятий', 'events', 'evenimente', 'женщин-беженок', 'refugee women', 'femei refugiate', 'бизнес-школ', 'business schools', 'școli de afaceri'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }

        foreach (ImpactStats::groups() as $group) {
            foreach ([$group['title'], ...array_column($group['nodes'], 'label'), ...array_column($group['nodes'], 'note')] as $text) {
                $this->assertSame(['ru', 'en', 'ro'], array_keys($text));
                foreach ($text as $value) {
                    $this->assertNotSame('', trim($value));
                }
            }
        }
    }

    public function test_the_script_is_versioned_and_the_page_keeps_working_without_it(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<script defer src="[^"]*/js/impact\.js\?v=\d+"#', $html);
        $this->assertFileExists(public_path('themes/public/miro/js/impact.js'));
        // Скрытие появляется только после того, как скрипт «взвёл» блок (is-armed): в самой разметке его нет.
        $this->assertStringNotContainsString('is-armed', $html);
    }
}
