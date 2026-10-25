<?php

namespace App\Http\Controllers\Concerns;

use App\Support\Blocks;
use App\Support\Locales;
use App\Support\TranslationStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * Сохранение текста страницы (блоки) у «плоских карточек» с собственной страницей: новость, проект. Структура блоков и
 * картинки берутся из русской версии, в переводах сохраняется только их текст (Blocks::mergeTranslation): так же
 * устроены публикации.
 */
trait SyncsPageContent
{
    /** @param  array<string, array<string, mixed>>  $input  translations из запроса, по языкам */
    protected function syncContent(Model $owner, array $input): void
    {
        $owner->load('translations');

        $primary = Blocks::canonical($input[Locales::PRIMARY]['content'] ?? [], Blocks::ARTICLE_KINDS);

        foreach (Locales::ALL as $locale) {
            $content = $locale === Locales::PRIMARY
                ? $primary
                : Blocks::mergeTranslation($primary, $input[$locale]['content'] ?? []);

            $row = $owner->translations->firstWhere('locale', $locale);

            if ($row) {
                $row->update(['content' => $content ?: null]);

                continue;
            }

            // У языка нет карточки (название и описание пустые): строку создаём, только если в блоках
            // есть переведённый текст, иначе перевод, набранный в редакторе текста, потерялся бы.
            if ($locale !== Locales::PRIMARY && TranslationStatus::hasText($content)) {
                $owner->translations()->create(['locale' => $locale, 'content' => $content]);
            }
        }
    }
}
