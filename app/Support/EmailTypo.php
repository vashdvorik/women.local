<?php

namespace App\Support;

use Illuminate\Contracts\Validation\Validator;

/**
 * Подсказка об опечатке в адресе почты: «anna@gmial.com» → «anna@gmail.com». Письмо на несуществующий адрес не придёт, а
 * человек останется с аккаунтом, который нельзя подтвердить. Подсказка мягкая: при первой отправке форма просит проверить
 * адрес, а если человек отправил ту же почту ещё раз, мы считаем, что адрес верный.
 */
final class EmailTypo
{
    /** Частые почтовые домены (в том числе популярные у читателей сайта). */
    private const DOMAINS = [
        'gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'icloud.com', 'proton.me', 'protonmail.com', 'live.com',
        'mail.ru', 'yandex.ru', 'yandex.com', 'ya.ru', 'bk.ru', 'inbox.ru', 'list.ru', 'rambler.ru', 'ukr.net', 'i.ua', 'meta.ua',
    ];

    /** Адрес с исправленным доменом или null, если домен не похож на опечатку. */
    public static function suggest(string $email): ?string
    {
        $email = strtolower(trim($email));

        if (substr_count($email, '@') !== 1) {
            return null;
        }

        [$local, $domain] = explode('@', $email);

        if ($local === '' || $domain === '' || in_array($domain, self::DOMAINS, true)) {
            return null;
        }

        $best = null;
        $bestDistance = PHP_INT_MAX;

        foreach (self::DOMAINS as $candidate) {
            // Короткие домены (ya.ru, i.ua) сравниваем строго: допустима одна ошибка, иначе подсказка выскакивала бы на чужие.
            $allowed = strlen($candidate) >= 8 ? 2 : 1;
            // Две соседние буквы поменяли местами («mial.ru», «gmial.com»): для человека это одна ошибка.
            $distance = self::swapsNeighbours($domain, $candidate) ? 1 : levenshtein($domain, $candidate);

            if ($distance >= 1 && $distance <= $allowed && $distance < $bestDistance) {
                $best = $candidate;
                $bestDistance = $distance;
            }
        }

        return $best === null ? null : $local.'@'.$best;
    }

    /** Совпадает ли $a с $b после перестановки двух соседних букв. */
    private static function swapsNeighbours(string $a, string $b): bool
    {
        if (strlen($a) !== strlen($b) || $a === $b) {
            return false;
        }

        for ($i = 0, $n = strlen($a) - 1; $i < $n; $i++) {
            if ($a[$i] !== $b[$i]) {
                return $a[$i] === $b[$i + 1] && $a[$i + 1] === $b[$i] && substr($a, $i + 2) === substr($b, $i + 2);
            }
        }

        return false;
    }

    /**
     * Добавляет к проверке формы мягкое замечание об опечатке (см. описание класса). Работает, только если сама форма уже
     * прошла остальные проверки, и запоминает «почта подтверждена пользователем» в сессии.
     */
    public static function guard(Validator $validator, string $email, string $message, string $field = 'email'): void
    {
        $validator->after(function (Validator $validator) use ($email, $message, $field): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $email = strtolower(trim($email));
            $suggestion = self::suggest($email);

            if ($suggestion === null || session('email_typo_ack') === $email) {
                return;
            }

            session(['email_typo_ack' => $email]);
            $validator->errors()->add($field, str_replace(':suggestion', $suggestion, $message));
        });
    }
}
