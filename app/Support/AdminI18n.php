<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Тексты для скриптов админки (resources/js, функция t()) на языке интерфейса. Остальные тексты админки идут через
 * __() и lang/<язык>.json; скриптам нужен отдельный небольшой словарь — lang/<язык>/adminjs.php, чтобы не отправлять
 * в браузер весь файл переводов. На русском словаря нет: русский текст и есть ключ.
 */
final class AdminI18n
{
    /** @return array<string, string> */
    public static function scriptDictionary(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ru') {
            return [];
        }

        $file = lang_path($locale.'/adminjs.php');

        return is_file($file) ? (array) require $file : [];
    }
}
