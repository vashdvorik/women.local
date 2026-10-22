<?php

namespace App\Support;

/**
 * Логотипы партнёров для главной и страницы «Партнёры»: две группы, у знаков нет ни подписей, ни ссылок. Файлы лежат в
 * public/themes/miro/images/partners/home (обрезанные прозрачные WebP). ratio — ширина к высоте знака: по ней считается
 * ширина картинки, чтобы узкие надписи и квадратные эмблемы занимали на странице примерно одну площадь.
 */
final class PartnerLogos
{
    /** @return list<array{title: array<string, string>, description: array<string, string>, logos: list<array{file: string, ratio: float, alt: string}>}> */
    public static function groups(): array
    {
        return [
            [
                'title' => ['ru' => 'Международные партнёры', 'en' => 'International partners', 'ro' => 'Parteneri internaționali'],
                'description' => ['ru' => 'Международные организации, поддерживающие развитие, устойчивость и расширение возможностей сообщества.', 'en' => 'International organisations supporting the community’s development, resilience and opportunities.', 'ro' => 'Organizații internaționale care susțin dezvoltarea, reziliența și oportunitățile comunității.'],
                'logos' => [
                    ['file' => 'intl-netherlands', 'ratio' => 2.37, 'alt' => 'Kingdom of the Netherlands'],
                    ['file' => 'intl-un-women', 'ratio' => 0.96, 'alt' => 'UN Women Moldova'],
                    ['file' => 'intl-un-moldova', 'ratio' => 0.91, 'alt' => 'United Nations Moldova'],
                    ['file' => 'intl-undp', 'ratio' => 0.49, 'alt' => 'UNDP'],
                    ['file' => 'intl-nrc', 'ratio' => 3.93, 'alt' => 'Norwegian Refugee Council'],
                ],
            ],
            [
                'title' => ['ru' => 'Партнёры и вдохновители', 'en' => 'Partners and inspirers', 'ro' => 'Parteneri și inspiratori'],
                'description' => ['ru' => 'Организации, сообщества и инициативы, с которыми мы делимся опытом и у которых учимся сами.', 'en' => 'Organisations, communities and initiatives we share experience with and learn from.', 'ro' => 'Organizații, comunități și inițiative cu care ne împărtășim experiența și de la care învățăm.'],
                'logos' => [
                    ['file' => 'p01-eba', 'ratio' => 1.90, 'alt' => 'European Business Association Moldova'],
                    ['file' => 'p02-afam', 'ratio' => 2.86, 'alt' => 'AFAM'],
                    ['file' => 'p03-youth-4-innovation', 'ratio' => 2.65, 'alt' => 'Youth 4 Innovation'],
                    ['file' => 'p04-glia', 'ratio' => 1.44, 'alt' => 'Glia Impact Hub'],
                    ['file' => 'p05-progen', 'ratio' => 3.14, 'alt' => 'Centrul Parteneriat pentru Dezvoltare'],
                    ['file' => 'p06-artcor', 'ratio' => 0.84, 'alt' => 'ARTCOR'],
                    ['file' => 'p07-contact', 'ratio' => 3.89, 'alt' => 'CONTACT'],
                    ['file' => 'p08-fintech-moldova', 'ratio' => 2.47, 'alt' => 'Fintech Moldova'],
                    ['file' => 'p09-ziphouse', 'ratio' => 2.88, 'alt' => 'ZIPHOUSE fashion hub'],
                    ['file' => 'p10-mark', 'ratio' => 0.87, 'alt' => 'Partner logo'],
                    ['file' => 'p11-ida', 'ratio' => 1.16, 'alt' => 'Innovation Development Agency'],
                    ['file' => 'p12-creative', 'ratio' => 1.19, 'alt' => 'Creative Industries Association'],
                    ['file' => 'p13-social-platform', 'ratio' => 1.46, 'alt' => 'Social Entrepreneurship Platform'],
                    ['file' => 'p14-woman-up', 'ratio' => 0.88, 'alt' => 'Woman Up'],
                    ['file' => 'p15-startup-moldova', 'ratio' => 3.08, 'alt' => 'Startup Moldova'],
                ],
            ],
        ];
    }

    /** Ширина знака в пикселях: одинаковая площадь при разных пропорциях, но не шире $cap. */
    public static function width(float $ratio, int $area = 9500, int $cap = 170): int
    {
        return (int) round(min($cap, sqrt($area * $ratio)));
    }
}
