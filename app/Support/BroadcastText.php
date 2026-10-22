<?php

namespace App\Support;

/**
 * Текст рассылки для Telegram. Админ пишет обычный текст; из разметки разрешены только теги, которые понимает Telegram
 * (<b>, <i>, <u>, <s>, <a href="https://…">). Всё остальное экранируется, поэтому случайный «<» или «&» не ломает
 * сообщение. Теги расставлены неправильно (не закрыт <b>) — они не применяются, и сообщение уходит чистым текстом.
 */
final class BroadcastText
{
    /** Предел Telegram: текст сообщения 4096 знаков, подпись к фото 1024. Запас — на экранирование. */
    public const MAX_TEXT = 3800;
    public const MAX_CAPTION = 1000;

    public static function html(string $raw): string
    {
        $escaped = htmlspecialchars(trim($raw), ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $text = (string) preg_replace('~&lt;(/?)(b|i|u|s)&gt;~i', '<$1$2>', $escaped);
        $text = (string) preg_replace('~&lt;a href="(https?://[^"\s<>]+)"&gt;~i', '<a href="$1">', $text);
        $text = (string) preg_replace('~&lt;/a&gt;~i', '</a>', $text);

        return self::balanced($text) ? $text : $escaped;
    }

    /** Тот же текст без разметки, как резерв, если Telegram не принял HTML. */
    public static function plain(string $raw): string
    {
        return trim(strip_tags($raw));
    }

    /** Длина текста без тегов: именно она идёт в счёт предела подписи. */
    public static function length(string $raw): int
    {
        return mb_strlen(self::plain($raw));
    }

    private static function balanced(string $html): bool
    {
        preg_match_all('~<(/?)(b|i|u|s|a)\b[^>]*>~', $html, $matches, PREG_SET_ORDER);
        $stack = [];

        foreach ($matches as [, $closing, $tag]) {
            if ($closing === '') {
                $stack[] = $tag;
            } elseif (array_pop($stack) !== $tag) {
                return false;
            }
        }

        return $stack === [];
    }
}
