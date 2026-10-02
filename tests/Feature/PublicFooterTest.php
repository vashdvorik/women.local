<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Подвал темы miro: две колонки меню («Платформа» и «Контакты») и подписка на новости четвёртой
 * колонкой в том же ряду. Колонок «Ресурсы» и «Вход» нет: вход в кабинет лежит в «Контактах», рядом
 * с телефоном и почтой платформы (config/site.php).
 */
class PublicFooterTest extends TestCase
{
    use RefreshDatabase;

    private function footerXPath(string $url): DOMXPath
    {
        $html = $this->get($url)->assertOk()->getContent();

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    /** Заголовок колонки на русском: показывается один язык, остальные скрыты стилями. */
    private function ruHeading(\DOMNode $h4): string
    {
        return trim((new DOMXPath($h4->ownerDocument))->evaluate('string(.//span[@data-lang="ru"])', $h4));
    }

    public function test_footer_has_two_menu_columns_and_the_subscription_beside_them(): void
    {
        foreach (['/', '/events', '/contact', '/experts'] as $url) {
            $xp = $this->footerXPath($url);

            $columns = $xp->query('//footer[@id="contact"]//div[contains(@class,"miro-footer__top")]/*');
            $this->assertSame(4, $columns->length, "$url: бренд, «Платформа», «Контакты», подписка");

            // Все заголовки колонок, кроме бренда: ровно три.
            $headings = [];
            foreach ($xp->query('//footer[@id="contact"]//div[contains(@class,"miro-footer__top")]//h4') as $h4) {
                $headings[] = $this->ruHeading($h4);
            }
            $this->assertSame(['Платформа', 'Контакты', 'Новости платформы'], $headings, $url);

            // Подписка — колонка того же ряда, а не отдельная полоса ниже.
            $this->assertSame(1, $xp->query('//div[contains(@class,"miro-footer__top")]/div[@id="subscribe"]')->length, $url);
            $this->assertSame(0, $xp->query('//footer//div[contains(@class,"miro-footer__top")]/following-sibling::div[@id="subscribe"]')->length, $url);
        }
    }

    public function test_resources_and_access_columns_are_gone(): void
    {
        $html = $this->get('/events')->assertOk()->getContent();
        preg_match('#<footer class="miro-footer".*?</footer>#s', $html, $m);
        $footer = $m[0];

        foreach (['Ресурсы', 'Resources', 'Resurse', 'Обучение', 'Învățare', 'Истории', '>Вход<', '>Access<', '>Acces<'] as $gone) {
            $this->assertStringNotContainsString($gone, $footer, "в подвале не должно быть «{$gone}»");
        }
    }

    /** Ссылки колонки «Контакты» по порядку, для проверки состава. */
    private function contactHrefs(string $url): array
    {
        $links = $this->footerXPath($url)->query('//div[contains(@class,"miro-footer__top")]/div[.//h4//span[@data-lang="ru" and text()="Контакты"]]//li/a');

        return array_map(fn (\DOMElement $a) => $a->getAttribute('href'), iterator_to_array($links));
    }

    public function test_contacts_column_holds_the_cabinet_then_the_phone_and_the_email(): void
    {
        foreach (['/', '/events', '/contact'] as $url) {
            // «Кабинет участницы», телефон и почта — именно в таком порядке и ничего кроме них.
            $this->assertSame(
                [route('account.login'), 'tel:+37377798317', 'mailto:women.tiras.hub@gmail.com'],
                $this->contactHrefs($url),
                $url,
            );
        }

        // Подписи к номеру и адресу на трёх языках; ссылка на телефон без пробелов, а на экране номер с пробелами.
        $html = $this->get('/events')->assertOk()->getContent();
        preg_match('#<footer class="miro-footer".*?</footer>#s', $html, $m);

        $this->assertStringContainsString('<span data-lang="ru">Тел:</span><span data-lang="en">Tel:</span><span data-lang="ro">Tel:</span> <a href="tel:+37377798317">+373 777 983 17</a>', $m[0]);
        $this->assertStringContainsString('<span data-lang="ru">Почта:</span><span data-lang="en">Email:</span><span data-lang="ro">E-mail:</span> <a href="mailto:women.tiras.hub@gmail.com">women.tiras.hub@gmail.com</a>', $m[0]);
    }

    public function test_an_empty_phone_or_email_in_the_config_drops_that_line_from_the_footer(): void
    {
        config(['site.contacts.phone' => '']);
        $this->assertSame([route('account.login'), 'mailto:women.tiras.hub@gmail.com'], $this->contactHrefs('/events'));

        config(['site.contacts.phone' => '+373 777 983 17', 'site.contacts.email' => null]);
        $this->assertSame([route('account.login'), 'tel:+37377798317'], $this->contactHrefs('/events'));
    }

    public function test_subscription_stays_a_working_form_with_a_labelled_email_field(): void
    {
        $xp = $this->footerXPath('/events');

        $form = $xp->query('//div[@id="subscribe"]//form[@action="'.route('subscribe').'"]');
        $this->assertSame(1, $form->length);

        // Поле почты, согласие и приманка для ботов на месте; у поля есть подпись (для экранных дикторов).
        $this->assertSame(1, $xp->query('//div[@id="subscribe"]//label[contains(@class,"miro-subscribe__field")]//input[@type="email" and @name="email"]')->length);
        $this->assertSame(1, $xp->query('//div[@id="subscribe"]//label[contains(@class,"miro-subscribe__field")]/span[contains(@class,"miro-subscribe__label")]')->length);
        $this->assertSame(1, $xp->query('//div[@id="subscribe"]//input[@type="checkbox" and @name="consent"]')->length);
        $this->assertSame(1, $xp->query('//div[@id="subscribe"]//input[@name="website"]')->length);
    }
}
