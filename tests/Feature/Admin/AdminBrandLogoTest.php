<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Логотипы админки (цветные варианты: сайдбар и страница входа светлые). В шапке сайдбара — надпись «Women's Hub»
 * (logo-wordmark.webp: для рабочего инструмента монограмма не нужна, название читается крупнее), на странице входа —
 * основной логотип платформы, тот же, что в шапке публичного сайта и в кабинете участниц. Вместо маленького значка и
 * подписи «Админпанель» в шапке сайдбара.
 */
class AdminBrandLogoTest extends TestCase
{
    use RefreshDatabase;

    /** Сайдбар админки. */
    private const LOGO = 'themes/public/miro/images/brand/logo-wordmark.webp';

    /** Страница входа и кабинет участниц. */
    private const PLATFORM_LOGO = 'themes/public/miro/images/brand/logo-platform.webp';

    public function test_the_admin_sidebar_shows_the_new_logo_in_its_header(): void
    {
        $html = $this->actingAsAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();
        $aside = substr($html, (int) strpos($html, '<aside'), 900);

        $this->assertStringContainsString('class="admin-brand-logo', $aside);
        $this->assertStringContainsString(self::LOGO, $aside);
        $this->assertStringContainsString('href="'.route('admin.dashboard').'"', $aside, 'логотип ведёт на инфопанель');
        // Прежний значок сайдбара ушёл, а название панели осталось в подсказке и в альтернативном тексте.
        $this->assertStringNotContainsString('brand/favicon.png', $aside);
        $this->assertStringContainsString('Админпанель', $aside);
    }

    public function test_the_sidebar_logo_is_the_same_on_every_admin_section(): void
    {
        foreach ([route('admin.dashboard'), route('admin.cabinets.dashboard'), route('admin.events.index'), route('admin.settings.edit')] as $url) {
            $html = $this->actingAsAdmin()->get($url)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, 'admin-brand-logo'), $url);
            $this->assertStringContainsString(self::LOGO, $html, $url);
        }
    }

    public function test_the_admin_login_page_shows_the_logo_above_the_form(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString('class="admin-login-logo', $html);
        $this->assertStringContainsString(self::PLATFORM_LOGO, $html);
        $this->assertLessThan(strpos($html, 'Вход в панель</h1>'), strpos($html, 'admin-login-logo'), 'логотип над заголовком');
        $this->assertLessThan(strpos($html, '<form'), strpos($html, 'admin-login-logo'));
    }

    public function test_the_logo_files_exist_and_the_login_page_uses_the_same_one_as_the_cabinet(): void
    {
        $this->assertFileExists(public_path(self::LOGO));
        $this->assertFileExists(public_path(self::PLATFORM_LOGO));

        $cabinet = file_get_contents(resource_path('views/account/partials/brand-logo.blade.php'));
        $this->assertStringContainsString(self::PLATFORM_LOGO, $cabinet);
    }
}
