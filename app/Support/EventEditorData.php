<?php

namespace App\Support;

use App\Actions\StoreUploadedFile;
use App\Models\Event;

/**
 * Данные для редактора текста новости (Alpine `eventEditor`): блоки на трёх языках и настройки загрузки
 * картинок и PDF. Если форма вернулась с ошибкой, блоки берутся из присланного, а не из базы, иначе всё
 * набранное пропало бы вместе с перезагрузкой страницы.
 */
class EventEditorData
{
    /** @return array<string, mixed> */
    public static function make(Event $event): array
    {
        $saved = fn (string $locale): array => $event->exists ? ($event->rawTranslation($locale)?->content ?? []) : [];

        $primary = Blocks::canonical(self::submitted(Locales::PRIMARY) ?? $saved(Locales::PRIMARY), Blocks::ARTICLE_KINDS);

        $blocks = [];

        foreach (Locales::ALL as $locale) {
            $blocks[$locale] = $locale === Locales::PRIMARY
                ? $primary
                : Blocks::mergeTranslation($primary, self::submitted($locale) ?? $saved($locale));
        }

        return [
            'blocks' => $blocks,
            'kinds' => Blocks::ARTICLE_KINDS,
            'uploadUrl' => route('admin.uploads.store'),
            'uploadFileUrl' => route('admin.uploads.file'),
            'fileMaxMb' => StoreUploadedFile::MAX_KB / 1024,
            'ratios' => AspectRatio::SLOTS,
        ];
    }

    /** Блоки языка из предыдущей отправки формы (JSON в скрытом поле) или null, если формы не было. */
    private static function submitted(string $locale): ?array
    {
        $raw = old("translations.{$locale}.content");

        if (! is_string($raw)) {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
