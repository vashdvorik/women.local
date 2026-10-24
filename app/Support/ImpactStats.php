<?php

namespace App\Support;

/**
 * Цифры блока «Наш вклад в цифрах» на главной: одна сетка из десяти показателей (resources/views/themes/public/miro/
 * landing.blade.php, счёт от нуля — public/themes/public/miro/js/impact.js). Цифры и подписи даёт заказчик, они лежат
 * здесь, а не в админке: меняются редко, всегда на трёх языках.
 *
 * plus — значение со знаком «+» («1 000+»: сделано не меньше). Числа без плюса («25 бизнес-школ») точные.
 */
final class ImpactStats
{
    /** @return array{title: array<string, string>, intro: array<string, string>} */
    public static function head(): array
    {
        return [
            'title' => self::t('Наш вклад в цифрах', 'Our impact in numbers', 'Impactul nostru în cifre'),
            'intro' => self::t(
                'Что уже сделано вместе с участницами и партнёрами платформы.',
                'What has already been achieved together with the members and partners of the platform.',
                'Ce s-a realizat deja împreună cu participantele și partenerii platformei.',
            ),
        ];
    }

    /** @return list<array{value: int, plus: bool, label: array<string, string>}> */
    public static function items(): array
    {
        return [
            self::item(1000, true, self::t('мероприятий', 'events', 'evenimente')),
            self::item(5000, true, self::t('женщин обучены', 'women trained', 'femei instruite')),
            self::item(800, true, self::t('женщин поддержаны', 'women supported', 'femei sprijinite')),
            self::item(1500, true, self::t('рабочих мест', 'jobs created', 'locuri de muncă')),
            self::item(900, true, self::t('женщин-беженок', 'refugee women', 'femei refugiate')),
            self::item(30, true, self::t('поездок', 'trips', 'călătorii')),
            self::item(25, false, self::t('бизнес-школ', 'business schools', 'școli de afaceri')),
            self::item(45, false, self::t('конференций', 'conferences', 'conferințe')),
            self::item(15, false, self::t('форумов', 'forums', 'forumuri')),
            self::item(30, true, self::t('курсов', 'courses', 'cursuri')),
        ];
    }

    /** Число с неразрывными пробелами между тысячами: «1 500». */
    public static function format(int $value): string
    {
        return number_format($value, 0, ',', "\u{00A0}");
    }

    /** @return array{value: int, plus: bool, label: array<string, string>} */
    private static function item(int $value, bool $plus, array $label): array
    {
        return ['value' => $value, 'plus' => $plus, 'label' => $label];
    }

    /** @return array<string, string> */
    private static function t(string $ru, string $en, string $ro): array
    {
        return ['ru' => $ru, 'en' => $en, 'ro' => $ro];
    }
}
