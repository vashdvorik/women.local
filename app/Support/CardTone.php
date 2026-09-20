<?php

namespace App\Support;

/**
 * Цвет подложки карточки эксперта и новости на публичном сайте. Значения — это
 * имена CSS-переменных темы miro (`--miro-pink`, `--miro-teal`, …), то есть дизайн-токены,
 * а не произвольный цвет: редактор выбирает из палитры, а не подбирает оттенок.
 */
class CardTone
{
    /** @var array<string, string> */
    public const OPTIONS = [
        'pink' => 'Розовый',
        'teal' => 'Бирюзовый',
        'coral' => 'Коралловый',
        'blue' => 'Синий',
        'orange' => 'Оранжевый',
        'rose' => 'Пудровый',
    ];

    public const DEFAULT = 'pink';

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::OPTIONS);
    }

    public static function normalize(?string $tone): string
    {
        return in_array($tone, self::keys(), true) ? $tone : self::DEFAULT;
    }
}
