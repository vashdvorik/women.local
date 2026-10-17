<?php

namespace Tests\Feature;

use App\Support\GalaRegulations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Положение о Гала-премии «Женщина года 2026» (/gala/regulations): текст документа docs/pologenie на трёх языках
 * (App\Support\GalaRegulations), пункт «Положение о премии» в меню GALA после ссылок на сайты по годам.
 */
class PublicGalaRegulationsTest extends TestCase
{
    use RefreshDatabase;

    private const SECTION_IDS = ['general', 'purpose', 'terms', 'procedure', 'committee', 'criteria', 'laureates', 'organizer', 'final'];

    /** Номера пунктов положения в порядке документа. */
    private const CLAUSES = [
        '1.1.', '1.2.', '1.3.', '1.4.', '1.5.',
        '2.1.', '2.2.',
        '3.1.', '3.2.', '3.3.', '3.4.', '3.5.', '3.6.', '3.7.', '3.8.',
        '4.1.', '4.2.',
        '5.1.', '5.2.', '5.3.', '5.4.', '5.5.', '5.6.', '5.7.',
        '6.1.', '6.2.', '6.3.',
        '7.1.', '7.2.', '7.3.', '7.4.',
        '8.1.', '8.2.', '8.3.', '8.4.',
        '9.1.', '9.2.', '9.3.',
    ];

    // ---------------------------------------------------------------- страница

    public function test_the_page_opens_with_the_title_and_the_ceremony_date(): void
    {
        $html = $this->get(route('gala.regulations'))->assertOk()->getContent();

        $this->assertSame('/gala/regulations', parse_url(route('gala.regulations'), PHP_URL_PATH));
        $this->assertStringContainsString('<title>Положение о Гала-премии «Женщина года 2026» — Women', $html);
        $this->assertStringContainsString('<meta name="description" content="Положение о Гала-премии «Женщина года 2026»: условия участия', $html);
        $this->assertSame(1, substr_count($html, '<h1'));

        foreach (['Положение о Гала-премии «Женщина года 2026»', 'Regulations of the Gala Award “Woman of the Year 2026”', 'Regulamentul Gala-premiului „Femeia Anului 2026”'] as $title) {
            $this->assertStringContainsString($title, $html, $title);
        }
        foreach (['Дата проведения церемонии награждения: 4 декабря 2026 года', 'Award ceremony date: 4 December 2026', 'Data ceremoniei de decernare: 4 decembrie 2026'] as $date) {
            $this->assertStringContainsString($date, $html, $date);
        }

        // Примечания у этого документа нет, цен тоже.
        $this->assertStringNotContainsString('miro-doc__note', $html);
        $this->assertStringNotContainsString('miro-doc__package', $html);
        $this->assertStringNotContainsString('Раздел готовится', $html);
    }

    public function test_every_section_clause_and_list_of_the_document_is_on_the_page(): void
    {
        $html = $this->get(route('gala.regulations'))->assertOk()->getContent();

        $this->assertSame(9, substr_count($html, 'class="miro-doc__section"'));

        $last = 0;
        foreach (self::SECTION_IDS as $id) {
            $position = strpos($html, 'id="'.$id.'"');
            $this->assertNotFalse($position, $id);
            $this->assertGreaterThan($last, $position, "раздел {$id} по порядку");
            $last = $position;
        }

        preg_match_all('#<span class="miro-doc__num">([\d.]+)</span>#', $html, $m);
        $this->assertSame(self::CLAUSES, $m[1], 'все 38 пунктов, номера как в документе');

        // Четыре маркированных списка (задачи, этапы, состав Комиссии, критерии) на 20 пунктов.
        preg_match_all('#<ul class="miro-doc__list">(.*?)</ul>#s', $html, $lists);
        $this->assertCount(4, $lists[1]);
        $this->assertSame([5, 6, 3, 6], array_map(fn (string $list) => substr_count($list, '<li>'), $lists[1]), 'задачи премии, этапы, состав Комиссии, критерии');

        // Ключевые фразы русского текста дословно.
        foreach ([
            'Организатором Гала-премии «Женщина года 2026» является Некоммерческое партнерство «Агентство инноваций и развития».',
            'Для рассмотрения заявок и определения лауреатов Гала-премии формируется Комиссия в составе 15 человек.',
            '5 амбассадоров премии, представляющих участниц и лауреатов Гала-премии прошлых лет;',
            '5 международных экспертов.',
            'Лауреатами Гала-премии «Женщина года 2026» могут стать исключительно участницы Женской платформы.',
            'торжественная церемония награждения 4 декабря 2026 года.',
            'Вопросы, не урегулированные настоящим Положением, решаются организатором',
        ] as $phrase) {
            $this->assertStringContainsString($phrase, $html, $phrase);
        }

        // И переводы тех же мест.
        foreach (['a Committee of 15 people is formed', 'se formează o Comisie în componență de 15 persoane', '5 international experts.', '5 experți internaționali.'] as $phrase) {
            $this->assertStringContainsString($phrase, $html, $phrase);
        }
    }

    public function test_the_table_of_contents_links_to_every_section(): void
    {
        $html = $this->get(route('gala.regulations'))->assertOk()->getContent();

        preg_match_all('#<li><a href="\#([a-z]+)">#', $html, $m);

        $this->assertSame(self::SECTION_IDS, $m[1]);
        $this->assertStringContainsString('<span class="miro-doc__hnum">5.</span> Комиссия по выбору лауреатов', $html);
        $this->assertStringContainsString('<span class="miro-doc__hnum">9.</span> Dispoziții finale', $html);
    }

    // ---------------------------------------------------------------- меню

    public function test_the_gala_menu_ends_with_the_regulations_after_all_year_links(): void
    {
        foreach (['/', '/events', route('about.regulations'), route('gala.regulations')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $menu = substr($html, (int) strpos($html, 'id="miro-gala-menu"'), 1800);

            $positions = [];
            foreach (array_keys(config('site.gala')) as $year) {
                $positions[] = strpos($menu, "Gala {$year}");
            }
            $positions[] = strpos($menu, 'miro-nav__dropdown-sep');
            $positions[] = strpos($menu, route('gala.regulations'));

            $this->assertNotContains(false, $positions, $url);
            $sorted = $positions;
            sort($sorted);
            $this->assertSame($sorted, $positions, "{$url}: сначала годы, потом разделитель, потом положение");

            foreach (['Положение о премии', 'Award regulations', 'Regulamentul premiului'] as $label) {
                $this->assertStringContainsString($label, $menu, $url);
            }
        }
    }

    public function test_the_regulations_item_is_an_ordinary_link_inside_the_site(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        preg_match('#<a href="'.preg_quote(route('gala.regulations'), '#').'"([^>]*)>(.*?)</a>#s', $html, $m);

        $this->assertNotEmpty($m, 'ссылка в меню');
        $this->assertStringNotContainsString('target=', $m[1], 'открывается в этой же вкладке');
        $this->assertStringContainsString('role="menuitem"', $m[1]);
        $this->assertStringNotContainsString('miro-nav__external', $m[2], 'без стрелки внешней ссылки');

        // Ссылки на сайты по годам по-прежнему внешние и открываются в новой вкладке.
        foreach (config('site.gala') as $url) {
            $this->assertMatchesRegularExpression('#<a href="'.preg_quote($url, '#').'" target="_blank" rel="noopener" role="menuitem">#', $html);
        }
    }

    // ---------------------------------------------------------------- данные

    public function test_every_text_is_translated_into_all_languages(): void
    {
        $content = GalaRegulations::content();
        $walk = function (array $node, string $path) use (&$walk): void {
            if (isset($node['ru'], $node['en'], $node['ro']) && is_string($node['ru'])) {
                foreach (['ru', 'en', 'ro'] as $locale) {
                    $this->assertNotSame('', trim($node[$locale]), "{$path}: {$locale}");
                }
                if (preg_match('/[А-Яа-яЁё]/u', $node['ru'])) {
                    $this->assertNotSame($node['ru'], $node['en'], "{$path}: английский не повторяет русский");
                    $this->assertNotSame($node['ru'], $node['ro'], "{$path}: румынский не повторяет русский");
                    $this->assertDoesNotMatchRegularExpression('/[А-Яа-яЁё]/u', $node['en'].$node['ro'], "{$path}: в переводе нет русских букв");
                }

                return;
            }

            foreach ($node as $key => $value) {
                if (is_array($value)) {
                    $walk($value, $path.'/'.$key);
                }
            }
        };

        $walk($content, 'положение о премии');
        $this->assertSame(self::SECTION_IDS, array_column($content['sections'], 'id'));
    }

    public function test_the_platform_regulations_page_is_unaffected(): void
    {
        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        $this->assertStringContainsString('WOMAN’S HUB COMMUNITY', $html);
        $this->assertStringContainsString('ПЛАТФОРМА ДЛЯ ЖЕНЩИН-ПРЕДПРИНИМАТЕЛЕЙ WOMAN’S HUB', $html);
        $this->assertStringNotContainsString('Рабочая редакция', $html);
        $this->assertStringNotContainsString('Гала-премии «Женщина года 2026»</span><span', $html);
        $this->assertSame(10, substr_count($html, 'class="miro-doc__section"'));
    }
}
