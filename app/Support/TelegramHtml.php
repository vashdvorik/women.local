<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Проверка и приведение к безопасному виду текста в Telegram-HTML.
 *
 * Бот отправляет сообщения с parse_mode=HTML, а Telegram отвергает весь текст целиком, если в нём
 * есть незакрытый тег или голый «<»: участница ничего не получит. Поэтому текст, введённый
 * администратором, проверяется до сохранения. Понимаются только теги из TAGS; в ссылках допустим
 * только href. Голые «&» и «>» превращаются в &amp; и &gt; сами, а «<» вне тега — ошибка: непонятно,
 * опечатка это или символ.
 */
final class TelegramHtml
{
    /** Теги, которые Telegram понимает и которые разрешены в текстах бота. */
    public const TAGS = ['b', 'strong', 'i', 'em', 'u', 's', 'code', 'pre', 'a'];

    /** Именованные сущности, которые понимает Telegram; числовые (&#8212;) понимаются все. */
    private const ENTITIES = ['lt', 'gt', 'amp', 'quot'];

    /**
     * @return array{0: string, 1: list<string>} приведённый текст и список ошибок (по-русски, для администратора)
     */
    public static function check(string $html): array
    {
        $tokens = preg_split(
            '/(<\/?[A-Za-z][A-Za-z0-9-]*(?:\s[^<>]*)?>|&(?:[A-Za-z][A-Za-z0-9]*|#\d+|#x[0-9A-Fa-f]+);|[<>&])/u',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY,
        ) ?: [];

        $errors = [];
        $stack = [];
        $out = '';

        foreach ($tokens as $token) {
            if ($token === '<') {
                $errors[] = __('Символ «<» допустим только в начале тега. Если он нужен как обычный знак, напишите &lt;. Проверьте также, что у тегов есть закрывающая скобка «>».');
                $out .= '&lt;';
            } elseif ($token === '>') {
                $out .= '&gt;';
            } elseif ($token === '&') {
                $out .= '&amp;';
            } elseif ($token[0] === '&') {
                $name = substr($token, 1, -1);

                if ($name[0] !== '#' && ! in_array(strtolower($name), self::ENTITIES, true)) {
                    $errors[] = __('Сущность :entity Telegram не понимает. Напишите символ прямо в тексте (допустимы только &lt; &gt; &amp; &quot;).', ['entity' => $token]);
                }

                $out .= $token;
            } elseif ($token[0] === '<') {
                $out .= $token;
                self::checkTag($token, $stack, $errors);
            } else {
                $out .= $token;
            }
        }

        foreach ($stack as $open) {
            $errors[] = __('Тег <:tag> не закрыт: добавьте </:tag>.', ['tag' => $open]);
        }

        return [$out, array_values(array_unique($errors))];
    }

    /** Видимая длина текста: без тегов, сущности как один символ. */
    public static function visibleLength(string $html): int
    {
        return mb_strlen(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @param  list<string>  $stack
     * @param  list<string>  $errors
     */
    private static function checkTag(string $tag, array &$stack, array &$errors): void
    {
        preg_match('/^<(\/?)([A-Za-z][A-Za-z0-9-]*)(.*)>$/su', $tag, $m);
        [, $closing, $name, $attributes] = $m;
        $name = strtolower($name);

        if (! in_array($name, self::TAGS, true)) {
            $errors[] = __('Тег <:tag> не поддерживается. Разрешены: :list.', ['tag' => $name, 'list' => implode(', ', array_map(fn (string $t): string => "<{$t}>", self::TAGS))]);

            return;
        }

        if ($closing !== '') {
            if (end($stack) !== $name) {
                $errors[] = __('Тег </:tag> закрыт не там: теги должны закрываться в обратном порядке (<b><i>…</i></b>).', ['tag' => $name]);

                return;
            }

            array_pop($stack);

            return;
        }

        $attributes = trim($attributes);

        if ($name === 'a') {
            if (! preg_match('/^href\s*=\s*(["\'])(https?:\/\/|tg:\/\/|mailto:)[^"\'<>\s]+\1$/i', $attributes)) {
                $errors[] = __('У ссылки должен быть адрес вида <a href="https://…">текст</a> (допустимы http, https, tg и mailto), и больше никаких параметров.');
            }
        } elseif ($attributes !== '') {
            $errors[] = __('У тега <:tag> не должно быть параметров.', ['tag' => $name]);
        }

        $stack[] = $name;
    }
}
