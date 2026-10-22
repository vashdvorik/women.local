<?php

namespace App\Support;

use App\Enums\Plan;
use App\Models\SiteSetting;

/**
 * Инструкция AI-помощника в боте: единственный источник его знаний о боте, кабинете и тарифах. Эталон лежит в
 * resources/data/bot_help.php, правка администратора — в site_settings.bot_help (по языкам) поверх эталона, поэтому
 * не затирается при git pull; пустая правка возвращает эталон. Переменные ({site_url}, цены подписки, {contact})
 * подставляются при каждом вопросе, чтобы в ответах не устаревали цены и адреса.
 */
final class BotHelp
{
    public const KEY = 'bot_help';

    /** Предел длины инструкции одного языка (знаков): всё это уходит модели с каждым вопросом. */
    public const MAX_LENGTH = 12000;

    /** @return array<string, string> эталонные тексты по языкам */
    public static function defaults(): array
    {
        /** @var array<string, string> $texts */
        $texts = require resource_path('data/bot_help.php');

        return $texts;
    }

    public static function default(string $locale): string
    {
        return self::defaults()[BotMessages::locale($locale)] ?? self::defaults()[BotMessages::DEFAULT_LOCALE];
    }

    /** Текст инструкции на языке (правка администратора, иначе эталон), переменные не подставлены. */
    public static function template(string $locale): string
    {
        $locale = BotMessages::locale($locale);
        $custom = trim((string) (SiteSetting::read(self::KEY, [])[$locale] ?? ''));

        return $custom !== '' ? $custom : self::default($locale);
    }

    public static function isCustomized(string $locale): bool
    {
        return trim((string) (SiteSetting::read(self::KEY, [])[BotMessages::locale($locale)] ?? '')) !== '';
    }

    /** Инструкция с подставленными адресом сайта, ценами подписки и контактом команды. */
    public static function render(string $locale): string
    {
        return strtr(self::template($locale), [
            '{site_url}' => (string) config('app.url'),
            '{community_price}' => Plan::Community->priceLabel($locale),
            '{private_price}' => Plan::Private->priceLabel($locale),
            '{contact}' => (string) config('site.contacts.telegram'),
        ]);
    }

    /**
     * Сохраняет правки по языкам. Текст, совпавший с эталоном, или пустой — это возврат к эталону (ничего не хранится).
     *
     * @param  array<string, ?string>  $texts
     */
    public static function save(array $texts): void
    {
        $stored = [];

        foreach (BotMessages::LOCALES as $locale) {
            $text = trim(str_replace("\r\n", "\n", (string) ($texts[$locale] ?? '')));

            if ($text !== '' && $text !== trim(self::default($locale))) {
                $stored[$locale] = $text;
            }
        }

        SiteSetting::write(self::KEY, $stored);
    }
}
