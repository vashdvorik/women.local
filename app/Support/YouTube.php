<?php

namespace App\Support;

class YouTube
{
    /**
     * Извлечь идентификатор из ссылки. Проверяется не «похоже на ссылку», а
     * извлекается ли реальный идентификатор (AGENTS.md §15).
     *
     * Поддерживаются watch?v=, youtu.be/, /shorts/, /embed/, /live/.
     */
    public static function id(string $url): ?string
    {
        $url = trim($url);

        $patterns = [
            '#youtu\.be/([A-Za-z0-9_-]{11})#',
            '#[?&]v=([A-Za-z0-9_-]{11})#',
            '#/shorts/([A-Za-z0-9_-]{11})#',
            '#/embed/([A-Za-z0-9_-]{11})#',
            '#/live/([A-Za-z0-9_-]{11})#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        return null;
    }
}
