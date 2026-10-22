<?php

namespace App\Support;

/**
 * Цифры блока «Наш вклад в цифрах» на главной: оргсхема «центр → три ветви → показатели» (resources/views/themes/public/
 * miro/landing.blade.php, анимация — public/themes/public/miro/js/impact.js). Цифры и подписи даёт заказчик, они лежат
 * здесь, а не в админке: меняются редко, всегда на трёх языках.
 *
 * plus — значение со знаком «+» («1 000+»: сделано не меньше). Числа без плюса («25 бизнес-школ») точные.
 * second — вторая цифра в той же карточке («45 конференций | 15 форумов» с общей подписью).
 */
final class ImpactStats
{
    /** @return array{eyebrow: array<string, string>, title: array<string, string>, intro: array<string, string>} */
    public static function head(): array
    {
        return [
            'eyebrow' => self::t('Результаты', 'Results', 'Rezultate'),
            'title' => self::t('Наш вклад в цифрах', 'Our impact in numbers', 'Impactul nostru în cifre'),
            'intro' => self::t(
                'Что уже сделано вместе с участницами и партнёрами платформы.',
                'What has already been achieved together with the members and partners of the platform.',
                'Ce s-a realizat deja împreună cu participantele și partenerii platformei.',
            ),
        ];
    }

    /**
     * @return list<array{title: array<string, string>, nodes: list<array{value: int, plus: bool, label: array<string, string>, note: array<string, string>, second?: array{value: int, plus: bool, label: array<string, string>}}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'title' => self::t('Развитие и поддержка', 'Growth and support', 'Dezvoltare și sprijin'),
                'nodes' => [
                    self::node(1000, true, self::t('мероприятий', 'events', 'evenimente'), self::t('развитие бизнеса', 'business development', 'dezvoltarea afacerilor')),
                    self::node(5000, true, self::t('женщин обучены', 'women trained', 'femei instruite'), self::t('навыки для роста', 'skills for growth', 'abilități pentru creștere')),
                    self::node(800, true, self::t('женщин поддержаны', 'women supported', 'femei sprijinite'), self::t('гранты для бизнеса', 'grants for business', 'granturi pentru afaceri')),
                ],
            ],
            [
                'title' => self::t('Влияние и интеграция', 'Impact and integration', 'Impact și integrare'),
                'nodes' => [
                    self::node(1500, true, self::t('рабочих мест', 'jobs created', 'locuri de muncă'), self::t('экономическое влияние', 'economic impact', 'impact economic')),
                    self::node(900, true, self::t('женщин-беженок', 'refugee women', 'femei refugiate'), self::t('обучение, гранты, интеграция', 'training, grants, integration', 'instruire, granturi, integrare')),
                    self::node(30, true, self::t('поездок', 'trips', 'călătorii'), self::t('обмен опытом', 'exchange of experience', 'schimb de experiență')),
                ],
            ],
            [
                'title' => self::t('Знания и диалог', 'Knowledge and dialogue', 'Cunoștințe și dialog'),
                'nodes' => [
                    self::node(25, false, self::t('бизнес-школ', 'business schools', 'școli de afaceri'), self::t('запуск инициатив', 'launching initiatives', 'lansarea inițiativelor')),
                    self::node(45, false, self::t('конференций', 'conferences', 'conferințe'), self::t('партнёрства и диалог', 'partnerships and dialogue', 'parteneriate și dialog'), [
                        'value' => 15, 'plus' => false, 'label' => self::t('форумов', 'forums', 'forumuri'),
                    ]),
                    self::node(30, true, self::t('курсов', 'courses', 'cursuri'), self::t('цифровые и управленческие навыки', 'digital and management skills', 'abilități digitale și manageriale')),
                ],
            ],
        ];
    }

    /** Число с неразрывными пробелами между тысячами: «1 500». */
    public static function format(int $value): string
    {
        return number_format($value, 0, ',', "\u{00A0}");
    }

    /** @return array{value: int, plus: bool, label: array<string, string>, note: array<string, string>, second?: array{value: int, plus: bool, label: array<string, string>}} */
    private static function node(int $value, bool $plus, array $label, array $note, ?array $second = null): array
    {
        $node = ['value' => $value, 'plus' => $plus, 'label' => $label, 'note' => $note];

        if ($second !== null) {
            $node['second'] = $second;
        }

        return $node;
    }

    /** @return array<string, string> */
    private static function t(string $ru, string $en, string $ro): array
    {
        return ['ru' => $ru, 'en' => $en, 'ro' => $ro];
    }
}
