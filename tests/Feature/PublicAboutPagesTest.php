<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\AboutContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Страницы «Приоритеты» (/about/priorities) и «Как стать участницей» (/members/join): написаны заново на основании
 * Положения о платформе (App\Support\AboutContent). Тексты пакетов и цены берутся оттуда же, откуда в кабинете.
 */
class PublicAboutPagesTest extends TestCase
{
    use RefreshDatabase;

    private function nbsp(int $price): string
    {
        return number_format($price, 0, ',', "\u{00A0}");
    }

    // ---------------------------------------------------------------- «Приоритеты»

    public function test_the_priorities_page_is_built_from_the_regulations_and_has_no_demo_content(): void
    {
        $html = $this->get(route('about.priorities'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h1><span data-lang="ru">Приоритеты</span><span data-lang="en">Priorities</span><span data-lang="ro">Priorități</span></h1>#', $html);
        $this->assertStringContainsString('<title>Приоритеты — Women', $html);

        // Прежний демо-текст ушёл: макет «найдите партнёра» с «86 % релевантности» и абстрактные «возможности».
        foreach (['Export partner', '86% relevance', 'AI match', 'miro-mockup', 'miro-logo-wall', 'Видимость бизнеса', 'Доступ к знаниям и развитию'] as $demo) {
            $this->assertStringNotContainsString($demo, $html, $demo);
        }

        // Миссия, цели, принципы, кого объединяет, основа, Gala: дословно из Положения.
        foreach ([
            'Woman’s Hub — платформа для женщин-предпринимателей и специализированная площадка для объединения женщин',
            'Платформа создаётся и развивается на базе Агентства Инноваций и Развития',
            'Развитие женского предпринимательства',
            'Взаимная поддержка', 'Профессиональная этика', 'Уважение', 'Сотрудничество', 'Предпринимательская инициатива',
            'женщины-предприниматели', 'другие активные участницы деловой и общественной жизни',
            'Ассоциация социальных предпринимателей', 'Ассоциация креативных индустрий', 'EBA и AFAM',
            'Участие в Gala входит в оба пакета членства',
        ] as $phrase) {
            $this->assertStringContainsString($phrase, $html, $phrase);
        }
    }

    public function test_the_priorities_page_has_six_goals_five_principles_and_the_partners(): void
    {
        $html = $this->get(route('about.priorities'))->assertOk()->getContent();

        $this->assertSame(6, substr_count($html, 'class="miro-about__card-num"'), 'шесть целей по Положению');
        $this->assertSame(5, substr_count($html, 'class="miro-about__principle"'), 'пять принципов из п. 1.4');
        $this->assertSame(4, substr_count($html, 'class="miro-about__card"') - 6, 'четыре карточки основы: агентство и партнёры');
        $this->assertSame(12, substr_count($html, 'miro-about__chip">'), 'шесть видов участниц (п. 1.3) и шесть форм партнёрства (п. 3.4)');
    }

    public function test_the_priorities_page_links_to_the_related_pages_and_uses_real_photos(): void
    {
        $html = $this->get(route('about.priorities'))->assertOk()->getContent();

        foreach ([route('about.regulations'), route('members.join'), route('partners'), route('gala.regulations'), route('media.photos')] as $url) {
            $this->assertStringContainsString('href="'.$url.'"', $html, $url);
        }

        foreach (['mission', 'gala-1', 'gala-2', 'gala-3'] as $photo) {
            $this->assertStringContainsString("/themes/public/miro/images/about/{$photo}.webp", $html, $photo);
            $this->assertFileExists(public_path("themes/public/miro/images/about/{$photo}.webp"));
        }
    }

    // ---------------------------------------------------------------- «Как стать участницей»

    public function test_the_join_page_explains_the_four_steps_in_the_order_the_platform_really_works(): void
    {
        $html = $this->get(route('members.join'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h1><span data-lang="ru">Как стать участницей</span><span data-lang="en">How to become a member</span><span data-lang="ro">Cum să devii membră</span></h1>#', $html);
        $this->assertSame(4, substr_count($html, 'class="miro-about__step-num"'));

        // Заявка в Telegram, Комиссия, выбор пакета и оплата в кабинете, затем возможности.
        $positions = array_map(fn (string $text) => strpos($html, $text), [
            'Подайте заявку', 'Заявку рассматривает Комиссия', 'Выберите пакет и оплатите', 'Пользуйтесь возможностями',
        ]);
        $this->assertNotContains(false, $positions);
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);

        $this->assertStringContainsString('Комиссия по отбору участниц из шести человек', $html);
        $this->assertStringContainsString('откройте раздел «Подписка»', $html);
        $this->assertStringContainsString('Данные карты платформа не получает.', $html);

        // Нет прежней заглушки.
        $this->assertStringNotContainsString('Готовы присоединиться?</span><span data-lang="en">Ready to join?</span><span data-lang="ro">Ești gata să te alături?</span></h2>'."\n".'                        <p>', $html);
        $this->assertStringNotContainsString('miro-public-steps', $html);
    }

    public function test_the_join_page_applications_go_to_the_telegram_bot(): void
    {
        $html = $this->get(route('members.join'))->assertOk()->getContent();
        $bot = config('site.bot_url');

        $this->assertSame('https://t.me/WomenComBot', $bot);
        // Кнопка в шапке, две кнопки пакетов Community и Private и кнопка внизу.
        $this->assertSame(4, substr_count($html, 'href="'.$bot.'" target="_blank" rel="noopener"'));
        foreach (['Подать заявку в Telegram', 'Apply in Telegram', 'Depune cererea în Telegram'] as $label) {
            $this->assertStringContainsString($label, $html, $label);
        }
        // Вход для тех, кто уже участница.
        $this->assertStringContainsString('href="'.route('account.login').'"', $html);
    }

    public function test_the_three_packages_use_the_texts_and_the_prices_of_the_cabinet(): void
    {
        $html = $this->get(route('members.join'))->assertOk()->getContent();

        foreach (['WOMEN’S HUB OPEN', 'WOMEN’S HUB COMMUNITY', 'WOMEN’S HUB PRIVATE'] as $name) {
            $this->assertStringContainsString($name, $html, $name);
        }
        $this->assertSame(3, substr_count($html, 'class="miro-about__plan '));

        // Перечни возможностей слово в слово из lang/subscription.php: 7, 10 и 10 пунктов на каждом языке.
        preg_match_all('#<ul class="miro-about__plan-list">(.*?)</ul>#s', $html, $lists);
        $this->assertSame([7, 10, 10], array_map(fn (string $list) => substr_count($list, '<li>'), $lists[1]));
        foreach (array_merge(__('subscription.plans.community.features', [], 'ru'), __('subscription.plans.private.features', [], 'ru'), __('subscription.plans.community.features', [], 'en'), __('subscription.plans.private.features', [], 'ro')) as $feature) {
            $this->assertStringContainsString(e($feature), $html, $feature);
        }

        // Цены по умолчанию из config/subscription.php.
        $this->assertStringContainsString('class="miro-about__plan-amount">600<', $html);
        $this->assertStringContainsString('class="miro-about__plan-amount">'.$this->nbsp(20000).'<', $html);
        $this->assertStringContainsString('руб. / в год', $html);
        $this->assertStringContainsString('rub. / per year', $html);
        $this->assertStringContainsString('rub. / pe an', $html);
        // Открытый доступ бесплатен и не даёт статуса резидента.
        $this->assertStringContainsString('Открытый доступ не даёт статуса резидента', $html);
    }

    public function test_package_prices_follow_the_admin_settings(): void
    {
        SiteSetting::setSubscriptionPrices(['community' => 700, 'private' => 19000]);

        $html = $this->get(route('members.join'))->assertOk()->getContent();

        $this->assertStringContainsString('class="miro-about__plan-amount">700<', $html);
        $this->assertStringContainsString('class="miro-about__plan-amount">'.$this->nbsp(19000).'<', $html);
        $this->assertStringNotContainsString($this->nbsp(20000), $html);
    }

    public function test_the_join_page_tells_about_the_terms_the_cabinet_and_the_questions(): void
    {
        $html = $this->get(route('members.join'))->assertOk()->getContent();

        // Шесть условий из Положения, ссылка на него и на раздел пакетов.
        foreach (['Индивидуальное членство', 'Заявка и взнос', 'Срок действия', 'Комиссия по отбору', 'Права резидентов', 'Обязанности резидентов'] as $term) {
            $this->assertStringContainsString($term, $html, $term);
        }
        $this->assertStringContainsString('href="'.route('about.regulations').'#packages"', $html);

        // Возможности кабинета: три доступны после одобрения (Open), семь с Community и Private (как в routes: plan:community).
        preg_match_all('#<div class="miro-about__group[^"]*">(.*?)</div>\s*</div>#s', $html, $groups);
        $this->assertCount(2, $groups[1]);
        $this->assertSame([3, 7], array_map(fn (string $group) => substr_count($group, 'class="miro-about__card"'), $groups[1]));
        foreach (['Мой профиль', 'Каталог участниц', 'Подбор контактов', 'Поиск по запросу', 'ИИ-помощник', 'Возможности', 'Обучение', 'Telegram-бот', 'Подписка и оплаты'] as $feature) {
            $this->assertStringContainsString($feature, $html, $feature);
        }
        $this->assertStringContainsString('/themes/public/miro/images/illustrations/cabinet.svg', $html);
        $this->assertFileExists(public_path('themes/public/miro/images/illustrations/cabinet.svg'));

        // Частые вопросы: свёрнутые, без скриптов.
        $this->assertSame(6, substr_count($html, '<details class="miro-about__faq-item">'));
        $this->assertStringContainsString('Автоплатежей нет', $html);
        $this->assertStringContainsString('защищённой странице Агропромбанка', $html);

        foreach (['join-1', 'join-2', 'join-3'] as $photo) {
            $this->assertFileExists(public_path("themes/public/miro/images/about/{$photo}.webp"));
        }
    }

    // ---------------------------------------------------------------- общее

    public function test_both_pages_load_their_styles_before_the_shared_stylesheet(): void
    {
        foreach ([route('about.priorities'), route('members.join')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            preg_match_all('#<link rel="stylesheet" href="[^"]*/css/([a-z-]+)\.css(?:\?v=\d+)?"#', $html, $m);
            $this->assertSame(['events', 'public-section', 'about-pages', 'navigation'], $m[1], $url);
        }

        $css = file_get_contents(public_path('themes/public/miro/css/about-pages.css'));
        foreach (['.miro-about__band', '.miro-about__plan--private', '.miro-about__faq-item', '.miro-about__cta', '.miro-page .miro-about .miro-about__group--closed .miro-about__badge'] as $selector) {
            $this->assertStringContainsString($selector, $css, $selector);
        }
    }

    public function test_every_text_is_translated_into_all_languages(): void
    {
        $walk = function (array $node, string $path) use (&$walk): void {
            if (isset($node['ru'], $node['en'], $node['ro']) && is_string($node['ru'])) {
                foreach (['ru', 'en', 'ro'] as $locale) {
                    $this->assertNotSame('', trim($node[$locale]), "{$path}: {$locale}");
                }
                if (preg_match('/[А-Яа-яЁё]/u', $node['ru'])) {
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

        $walk(AboutContent::priorities(), 'приоритеты');
        $walk(AboutContent::join(), 'как стать участницей');
    }

    public function test_the_icons_used_by_the_pages_exist_in_the_icon_set(): void
    {
        $set = file_get_contents(resource_path('views/themes/public/miro/partials/icon.blade.php'));

        $used = [];
        foreach ([AboutContent::priorities(), AboutContent::join()] as $content) {
            array_walk_recursive($content, function ($value, $key) use (&$used) {
                if ($key === 'icon') {
                    $used[$value] = true;
                }
            });
        }

        $this->assertGreaterThan(10, count($used), 'значков на страницах много');
        foreach (array_keys($used) as $name) {
            $this->assertStringContainsString("'{$name}' =>", $set, "значок «{$name}» нарисован");
        }
    }

    public function test_the_menu_still_points_to_both_pages(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="'.route('about.priorities').'"', $html);
        $this->assertStringContainsString('href="'.route('members.join').'"', $html);
    }
}
