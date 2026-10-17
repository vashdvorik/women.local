<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\Regulations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Страница «Положение» (/about/regulations): текст положения о платформе Woman’s Hub на трёх языках
 * (App\Support\Regulations, русский текст из документа docs/pologenie), содержание со ссылками на разделы, цены пакетов
 * из настроек сайта.
 */
class PublicRegulationsTest extends TestCase
{
    use RefreshDatabase;

    private const SECTION_IDS = ['general', 'goals', 'partnership', 'membership', 'committee', 'packages', 'gala', 'rights', 'management', 'final'];

    /** Номера пунктов положения в порядке документа. */
    private const CLAUSES = [
        '1.1.', '1.2.', '1.3.', '1.4.',
        '3.1.', '3.2.', '3.3.', '3.4.',
        '4.1.', '4.2.', '4.3.', '4.4.', '4.5.',
        '5.1.', '5.2.', '5.3.', '5.4.', '5.5.',
        '7.1.', '7.2.', '7.3.',
        '8.1.', '8.2.',
        '9.1.', '9.2.',
        '10.1.', '10.2.', '10.3.',
    ];

    private function nbsp(int $price): string
    {
        return number_format($price, 0, ',', "\u{00A0}");
    }

    // ---------------------------------------------------------------- страница

    public function test_the_page_is_filled_with_the_regulations_and_is_no_longer_a_placeholder(): void
    {
        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Раздел готовится', $html);
        $this->assertStringNotContainsString('Здесь будет опубликовано', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertMatchesRegularExpression('#<h1 class="miro-article__title"><span data-lang="ru">Положение</span><span data-lang="en">Regulations</span><span data-lang="ro">Regulament</span></h1>#', $html);
        $this->assertStringContainsString('<title>Положение — Women', $html);
        $this->assertStringContainsString('<meta name="description" content="Положение о платформе Woman’s Hub', $html);

        // Подзаголовок из документа на трёх языках.
        foreach (['ПЛАТФОРМА ДЛЯ ЖЕНЩИН-ПРЕДПРИНИМАТЕЛЕЙ WOMAN’S HUB', 'PLATFORM FOR WOMEN ENTREPRENEURS WOMAN’S HUB', 'PLATFORMĂ PENTRU FEMEI ANTREPRENOARE WOMAN’S HUB'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
    }

    public function test_the_working_draft_note_is_not_shown(): void
    {
        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        // Фразу «Рабочая редакция. Конкретные финансовые, организационные и процедурные условия могут уточняться отдельными
        // регламентами» с страницы убрали: ни на одном языке, ни в разметке, ни в данных.
        foreach (['Рабочая редакция', 'Working draft', 'Ediție de lucru', 'могут уточняться отдельными регламентами', 'miro-doc__note'] as $text) {
            $this->assertStringNotContainsString($text, $html, $text);
        }

        $this->assertArrayNotHasKey('note', Regulations::content());
        $this->assertStringNotContainsString('Рабочая редакция', file_get_contents(public_path('themes/public/miro/css/public-section.css')));
    }

    public function test_every_section_and_clause_of_the_document_is_on_the_page_in_the_order_of_the_document(): void
    {
        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        $this->assertSame(10, substr_count($html, 'class="miro-doc__section"'));

        $last = 0;
        foreach (self::SECTION_IDS as $id) {
            $position = strpos($html, 'id="'.$id.'"');
            $this->assertNotFalse($position, $id);
            $this->assertGreaterThan($last, $position, "раздел {$id} по порядку");
            $last = $position;
        }

        preg_match_all('#<span class="miro-doc__num">([\d.]+)</span>#', $html, $m);
        $numbers = array_values(array_filter($m[1], fn (string $n) => $n !== '6.1.' && $n !== '6.2.'));
        $this->assertSame(self::CLAUSES, $numbers, 'все 28 пунктов, номера как в документе');

        // Ключевые фразы русского текста дословно.
        foreach ([
            'Комиссия состоит из шести человек: трёх представителей Агентства Инноваций и Развития и трёх представителей партнёрских организаций Woman’s Hub.',
            'Статус резидента действует в течение оплаченного периода членства.',
            'Партнёрами Woman’s Hub являются Ассоциация социальных предпринимателей и Ассоциация креативных индустрий.',
            'Изменения и дополнения в настоящее Положение утверждаются в установленном порядке.',
            'развитие женского предпринимательства и профессионального потенциала;',
        ] as $phrase) {
            $this->assertStringContainsString($phrase, $html, $phrase);
        }
    }

    public function test_the_two_packages_list_every_benefit(): void
    {
        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'class="miro-doc__package '));
        $this->assertStringContainsString('WOMAN’S HUB COMMUNITY', $html);
        $this->assertStringContainsString('WOMAN’S HUB PRIVATE', $html);

        // Community: 12 пунктов, Private: 20; каждый в трёх языках.
        $community = explode('miro-doc__package--private', $html)[0];
        $this->assertSame(12, substr_count(substr($community, (int) strpos($community, 'miro-doc__package--community')), '<li>'));
        $private = substr($html, (int) strpos($html, 'miro-doc__package--private'));
        $this->assertSame(20, substr_count(substr($private, 0, (int) strpos($private, '</ol>')), '<li>'));

        foreach (['Персональный куратор.', 'A personal curator.', 'Curator personal.', 'Закрытый Private Circle.', 'Юридические консультации.', 'Legal consultations.', 'Consultanță juridică.'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        foreach (['В пакет входит:', 'The package includes:', 'Pachetul include:'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function test_the_table_of_contents_links_to_every_section(): void
    {
        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        preg_match_all('#<li><a href="\#([a-z]+)">#', $html, $m);

        $this->assertSame(self::SECTION_IDS, $m[1], 'содержание ведёт на разделы по порядку');
        foreach (['Содержание', 'Contents', 'Cuprins'] as $title) {
            $this->assertStringContainsString($title, $html);
        }
        // Номер раздела вынесен в отдельный span: в шрифте заголовков единица похожа на римскую I.
        $this->assertStringContainsString('<span class="miro-doc__hnum">1.</span> Общие положения', $html);
        $this->assertStringContainsString('<span class="miro-doc__hnum">10.</span> Final provisions', $html);
    }

    // ---------------------------------------------------------------- цены

    public function test_package_prices_come_from_the_site_settings_not_from_the_text(): void
    {
        // Пока в админке цены не меняли, действуют значения из config/subscription.php.
        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        $this->assertStringContainsString('Стоимость: 600 рублей / год.', $html);
        $this->assertStringContainsString('Price: 600 rubles / year.', $html);
        $this->assertStringContainsString('Cost: 600 ruble / an.', $html);
        $this->assertStringContainsString('Стоимость: '.$this->nbsp(20000).' рублей / год.', $html);

        // Цену поменяли в админке («Подписки → Цена подписки»): страница показывает новую сразу, без правки текста.
        SiteSetting::setSubscriptionPrices(['community' => 700, 'private' => 19000]);

        $html = $this->get(route('about.regulations'))->assertOk()->getContent();

        $this->assertStringContainsString('Стоимость: 700 рублей / год.', $html);
        $this->assertStringContainsString('Стоимость: '.$this->nbsp(19000).' рублей / год.', $html);
        $this->assertStringContainsString('Price: '.$this->nbsp(19000).' rubles / year.', $html);
        $this->assertStringContainsString('Cost: '.$this->nbsp(19000).' ruble / an.', $html);
        $this->assertStringNotContainsString($this->nbsp(20000), $html);
    }

    public function test_no_price_is_written_into_the_text(): void
    {
        $json = json_encode(Regulations::content(), JSON_UNESCAPED_UNICODE);

        $this->assertStringContainsString(':price', $json);
        $this->assertStringNotContainsString('19 000', $json);
        $this->assertStringNotContainsString('20 000', $json);
        $this->assertStringNotContainsString('600 руб', $json);
    }

    // ---------------------------------------------------------------- данные

    public function test_every_text_is_translated_into_all_languages(): void
    {
        $content = Regulations::content();
        $walk = function (array $node, string $path) use (&$walk): void {
            if (isset($node['ru'], $node['en'], $node['ro']) && is_string($node['ru'])) {
                foreach (['ru', 'en', 'ro'] as $locale) {
                    $this->assertNotSame('', trim($node[$locale]), "{$path}: {$locale}");
                }
                // Перевод не копия русского (кроме названий, где русский и перевод совпадают по написанию).
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

        $walk($content, 'положение');
        $this->assertCount(10, $content['sections']);
        $this->assertSame(self::SECTION_IDS, array_column($content['sections'], 'id'));
    }

    public function test_the_neighbouring_about_pages_and_the_menu_are_unaffected(): void
    {
        $this->get(route('about.priorities'))->assertOk();
        $this->get('/about/leadership')->assertNotFound();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString(route('about.regulations'), $html);
    }

    public function test_the_styles_are_in_place(): void
    {
        $css = file_get_contents(public_path('themes/public/miro/css/public-section.css'));

        foreach (['.miro-doc__section', '.miro-doc__clause', '.miro-doc__package', '.miro-doc__items', '.miro-doc__toc', '.miro-doc__hnum', '.miro-page .miro-doc .miro-doc__package--private .miro-doc__price'] as $selector) {
            $this->assertStringContainsString($selector, $css, $selector);
        }
    }
}
