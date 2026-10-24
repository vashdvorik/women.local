<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Названия «Woman’s Hub» и «Женщина Года» не должны разрываться переносом строки: между их словами в тексте страницы ставится
 * неразрывный пробел. Делается один раз на выходе, а не в сотне шаблонов и текстов: правятся только видимые куски текста (то,
 * что лежит между тегами), а атрибуты, адреса, скрипты и стили не трогаются. Админка, кабинет и служебные адреса пропускаются.
 */
class KeepNamesTogether
{
    private const NBSP = "\u{00A0}";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('admin', 'admin/*', 'app', 'app/*', 'telegram/*', 'payment/*', 'dev/*', 'go/*', 'storage/*')) {
            return $response;
        }

        $type = (string) $response->headers->get('Content-Type');
        $content = $response->getContent();

        if ($response->getStatusCode() !== 200 || ! str_contains($type, 'text/html') || ! is_string($content) || $content === '') {
            return $response;
        }

        $response->setContent(self::apply($content));

        return $response;
    }

    public static function apply(string $html): string
    {
        // Теги, скрипты и стили остаются как есть, обрабатываются только куски текста между ними
        $parts = preg_split('~(<script\b.*?</script>|<style\b.*?</style>|<!--.*?-->|<[^>]+>)~isu', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return $html;
        }

        foreach ($parts as $i => $part) {
            if ($part === '' || $part[0] === '<') {
                continue;
            }

            $parts[$i] = preg_replace(
                [
                    '~(Wom[ae]n(?:’|\'|&#0?39;|&apos;)s) (Hub)~iu',
                    '~(Женщина) (Года)~u',
                    '~(Woman) (of) (the) (Year)~u',
                ],
                [
                    '$1'.self::NBSP.'$2',
                    '$1'.self::NBSP.'$2',
                    '$1'.self::NBSP.'$2'.self::NBSP.'$3'.self::NBSP.'$4',
                ],
                $part
            ) ?? $part;
        }

        return implode('', $parts);
    }
}
