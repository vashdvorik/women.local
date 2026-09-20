<?php

namespace App\Support;

class Contrast
{
    /**
     * Цвет текста на плашке вычисляется из яркости фона — одной функцией
     * и в предпросмотре, и на публичной странице (AGENTS.md, правило 27).
     */
    public static function textOn(string $hex): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255 > 0.6
            ? '#1d1d1f'
            : '#ffffff';
    }
}
