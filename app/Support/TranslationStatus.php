<?php

namespace App\Support;

/**
 * Индикатор заполненности языковой вкладки (AGENTS.md §13). У вкладки ровно
 * один значок — либо ✓, либо ⚠, никогда оба.
 *
 * Разница между «частично» и «пусто» существенна: пустая вкладка — осознанный
 * отказ от языка, наполовину заполненная — почти всегда забытая работа.
 */
class TranslationStatus
{
    public const DONE = 'done';
    public const PARTIAL = 'partial';
    public const EMPTY = 'empty';

    /**
     * Русская вкладка: ✓ если есть заголовок, описание и содержимое; иначе — серо.
     *
     * @param  array{title?: ?string, excerpt?: ?string, content?: ?array}  $ru
     */
    public static function primary(array $ru): string
    {
        $hasContent = collect($ru['content'] ?? [])->contains(fn ($b) => self::blockHasContent($b));

        return filled($ru['title'] ?? null) && filled($ru['excerpt'] ?? null) && $hasContent
            ? self::DONE
            : self::EMPTY;
    }

    /**
     * Вкладка перевода: поблочное сравнение с русской версией.
     *
     * @param  array{title?: ?string, excerpt?: ?string, content?: ?array}  $ru
     * @param  array{title?: ?string, excerpt?: ?string, content?: ?array}  $translation
     */
    public static function secondary(array $ru, array $translation): string
    {
        $expected = 0;
        $filled = 0;

        foreach (['title', 'excerpt'] as $field) {
            if (filled($ru[$field] ?? null)) {
                $expected++;
                $filled += filled($translation[$field] ?? null) ? 1 : 0;
            }
        }

        $translatedText = collect($translation['content'] ?? [])
            ->mapWithKeys(fn ($b) => [($b['uid'] ?? '') => $b])
            ->all();

        foreach ($ru['content'] ?? [] as $block) {
            if (! self::blockHasText($block)) {
                continue;
            }

            $expected++;
            $counterpart = $translatedText[$block['uid'] ?? ''] ?? null;
            $filled += $counterpart && self::blockHasText($counterpart) ? 1 : 0;
        }

        // Учитываем текст перевода, которого нет в оригинале (редактор что-то ввёл).
        $anyTranslationInput = $filled > 0
            || filled($translation['title'] ?? null)
            || filled($translation['excerpt'] ?? null)
            || collect($translation['content'] ?? [])->contains(fn ($b) => self::blockHasText($b));

        if (! $anyTranslationInput) {
            return self::EMPTY;
        }

        return $expected > 0 && $filled >= $expected ? self::DONE : self::PARTIAL;
    }

    /** Есть ли среди блоков хоть один с содержимым: текст, картинка, файл, код. */
    public static function hasContent(array $blocks): bool
    {
        return collect($blocks)->contains(fn ($b) => is_array($b) && self::blockHasContent($b));
    }

    /** Есть ли среди блоков хоть один с текстом (текст, заголовок, название файла). */
    public static function hasText(array $blocks): bool
    {
        return collect($blocks)->contains(fn ($b) => is_array($b) && self::blockHasText($b));
    }

    private static function blockHasText(array $block): bool
    {
        return match ($block['type'] ?? null) {
            'text' => filled(trim(strip_tags($block['data']['html'] ?? ''))),
            'heading' => filled($block['data']['text'] ?? null),
            // Название файла переводится, сам файл — нет.
            'file' => filled($block['data']['title'] ?? null),
            default => false,
        };
    }

    private static function blockHasContent(array $block): bool
    {
        // Название без файла — не содержимое: на странице такой блок ничего не покажет.
        if (($block['type'] ?? null) === 'file') {
            return filled($block['data']['path'] ?? null);
        }

        if (self::blockHasText($block)) {
            return true;
        }

        return match ($block['type'] ?? null) {
            'embed' => filled(trim($block['data']['html'] ?? '')),
            'image' => filled($block['data']['path'] ?? null),
            'gallery_2', 'gallery_3', 'gallery_4' => collect($block['data']['images'] ?? [])->filter()->isNotEmpty(),
            default => false,
        };
    }
}
