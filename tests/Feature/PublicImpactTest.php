<?php

namespace Tests\Feature;

use App\Support\ImpactStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Блок «Наш вклад в цифрах» на главной: светлая компактная сетка из десяти цифр, внизу страницы: после «Как это работает», перед призывом «Присоединяйтесь к платформе» и «Партнёрами». */
class PublicImpactTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_infographic_is_on_the_page_once(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="impact"'));
    }

    public function test_the_visual_order_puts_it_before_the_join_call_and_the_partners(): void
    {
        // Порядок блоков главной задаёт CSS (flex + order), а не порядок в разметке: без своего order блок встал бы первым.
        // Светлый компактный блок стоит внизу: после «Как это работает», затем призыв «Присоединяйтесь к платформе» и партнёры
        // (по просьбе заказчика блок убрали с верха страницы, а позже поставили перед призывом).
        $css = (string) file_get_contents(public_path('themes/public/miro/css/landing.css'));
        preg_match_all('/main > (?:\.|#)([a-z-]+) \{ order: (\d+); \}/', $css, $m);
        $order = array_combine($m[1], array_map('intval', $m[2]));

        $this->assertArrayHasKey('impact', $order);
        $this->assertSame($order['how-it-works'] + 1, $order['impact'], 'после «Как это работает»');
        $this->assertSame($order['impact'] + 1, $order['register-cta'], 'прямо перед призывом «Присоединяйтесь к платформе»');
        $this->assertSame($order['register-cta'] + 1, $order['partners-strip'], 'а за призывом идут «Партнёры»');
        $this->assertSame(count($order), count(array_unique($order)), 'у каждого блока свой номер порядка');
    }

    public function test_it_shows_ten_figures_in_one_grid_with_final_values_in_the_markup(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'class="miro-impact__grid"'));
        $this->assertSame(10, substr_count($html, 'class="miro-impact__item"'), 'десять цифр, конференции и форумы — отдельными');
        $this->assertSame(10, substr_count($html, 'data-count-to='));
        // Заголовок и подзаголовок — общие, как у соседних секций
        $this->assertMatchesRegularExpression('#<div class="miro-section__head miro-section__head--center">\s*<h2 id="impact-title">#', $html);

        // Без скрипта (или с «уменьшить анимацию») цифры показаны сразу готовыми: в разметке стоит итоговое значение.
        foreach ([[1000, '1'."\u{00A0}".'000'], [5000, '5'."\u{00A0}".'000'], [1500, '1'."\u{00A0}".'500'], [800, '800'], [900, '900'], [45, '45'], [25, '25'], [15, '15']] as [$value, $text]) {
            $this->assertMatchesRegularExpression('#data-count-to="'.$value.'"[^>]*>'.preg_quote($text, '#').'</span>#u', $html, (string) $value);
        }
        $this->assertSame(7, substr_count($html, 'class="miro-impact__plus"'), 'плюс у семи показателей: 1000+, 5000+, 800+, 1500+, 900+, 30+, 30+');
        $this->assertSame(10, substr_count($html, 'class="miro-impact__label"'));
    }

    public function test_every_label_is_written_in_all_three_languages(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['Наш вклад в цифрах', 'Our impact in numbers', 'Impactul nostru în cifre', 'мероприятий', 'events', 'evenimente', 'женщин-беженок', 'refugee women', 'femei refugiate', 'бизнес-школ', 'business schools', 'școli de afaceri'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }

        foreach ([ImpactStats::head()['title'], ImpactStats::head()['intro'], ...array_column(ImpactStats::items(), 'label')] as $text) {
            $this->assertSame(['ru', 'en', 'ro'], array_keys($text));
            foreach ($text as $value) {
                $this->assertNotSame('', trim($value));
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
