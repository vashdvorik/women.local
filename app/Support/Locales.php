<?php

namespace App\Support;

class Locales
{
    /** Русский обязателен и всегда первый; остальные — по желанию редактора. */
    public const PRIMARY = 'ru';

    /** @var list<string> */
    public const ALL = ['ru', 'ro', 'en'];

    /** @var array<string, string> */
    public const NAMES = [
        'ru' => 'Русский',
        'ro' => 'Română',
        'en' => 'English',
    ];

    /** Языки перевода без основного. @return list<string> */
    public static function secondary(): array
    {
        return ['ro', 'en'];
    }
}
