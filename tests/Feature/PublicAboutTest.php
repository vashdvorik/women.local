<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAboutTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_about_page_renders_miro_content(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk()
            ->assertSee('miro-about-page', false)
            ->assertSee('A place where women grow business together')
            ->assertSee('From profile to real collaboration')
            ->assertSee('500+');

        $this->assertSame(1, substr_count($response->getContent(), 'id="miro-nav"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="miro-footer"'));
    }

    public function test_how_to_become_a_member_is_in_both_the_about_and_the_people_menus(): void
    {
        foreach (['miro', 'fortun', 'fortuntwo'] as $theme) {
            \App\Models\SiteSetting::setLandingTheme($theme);

            $html = $this->get(route('about'))->assertOk()->getContent();

            foreach (['miro-about-menu', 'miro-people-menu'] as $menu) {
                $this->assertSame(1, preg_match('#id="'.$menu.'"[^>]*>(.*?)</div>#s', $html, $m), "$theme: меню $menu не найдено");
                $this->assertStringContainsString(route('members.join'), $m[1], "$theme: в меню $menu нет «Как стать членом»");
                $this->assertStringContainsString('Как стать членом', $m[1]);
            }
        }
    }

    public function test_gala_is_a_dropdown_with_a_separate_site_for_each_year(): void
    {
        $expected = [
            '2026' => 'https://gala2026.innovation.md/',
            '2025' => 'https://gala2025.innovation.md/',
            '2024' => 'https://gala2024.innovation.md/',
        ];

        foreach (['miro', 'fortun', 'fortuntwo'] as $theme) {
            \App\Models\SiteSetting::setLandingTheme($theme);

            $html = $this->get(route('about'))->assertOk()->getContent();

            // «Gala» — кнопка выпадающего меню, а не прямая ссылка на страницу проекта.
            $this->assertStringContainsString('aria-controls="miro-gala-menu"', $html, $theme);
            $this->assertStringNotContainsString('href="'.route('gala').'"', $html, $theme);

            // В списке по сайту на год, новые сверху; сайты свои, поэтому в новой вкладке.
            $this->assertSame(1, preg_match('#id="miro-gala-menu"[^>]*>(.*?)</div>#s', $html, $menu), "$theme: меню Gala не найдено");
            preg_match_all('#<a href="([^"]+)" target="_blank" rel="noopener" role="menuitem">Gala (\d{4})#', $menu[1], $links, PREG_SET_ORDER);

            $this->assertSame($expected, array_column($links, 1, 2), $theme);
            $this->assertSame(['2026', '2025', '2024'], array_column($links, 2), "$theme: порядок пунктов");
        }
    }

    public function test_reports_item_is_gone_from_the_about_menu_and_its_page_no_longer_exists(): void
    {
        // Пункта «Отчёты» нет ни в одной теме сайта, а страница-заглушка убрана вместе с ним.
        foreach (['miro', 'fortun', 'fortuntwo'] as $theme) {
            \App\Models\SiteSetting::setLandingTheme($theme);

            $html = $this->get(route('about'))->assertOk()->getContent();

            $this->assertStringNotContainsString('/about/reports', $html, $theme);
            $this->assertStringContainsString('/about/regulations', $html, 'остальные пункты меню «О нас» на месте: '.$theme);
        }

        $this->get('/about/reports')->assertNotFound();
    }
}
