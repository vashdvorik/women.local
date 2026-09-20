<?php

namespace App\Http\Controllers\Concerns;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Общая часть контроллеров «плоских карточек» (эксперты, события, как и проекты):
 * ручной порядок стрелками и сохранение переводов. Русский всегда сохраняется,
 * ro/en — только если в них что-то заполнено (AGENTS.md education3 §13).
 */
trait ManagesFlatCards
{
    /** Меняет `position` записи с соседней сверху или снизу; на краю списка ничего не делает. */
    protected function moveByPosition(Model $item, string $direction): void
    {
        $neighbour = $item::query()
            ->when($direction === 'up',
                fn ($q) => $q->where('position', '<', $item->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $item->position)->orderBy('position'))
            ->first();

        if (! $neighbour) {
            return;
        }

        DB::transaction(function () use ($item, $neighbour) {
            [$item->position, $neighbour->position] = [$neighbour->position, $item->position];
            $item->save();
            $neighbour->save();
        });
    }

    /** Следующая позиция в конце списка. */
    protected function nextPosition(string $modelClass): int
    {
        return ((int) $modelClass::max('position')) + 1;
    }

    /**
     * @param  array<string, array<string, mixed>>  $input       translations из запроса, по языкам
     * @param  list<string>  $textFields  строковые поля перевода
     * @param  list<string>  $listFields  поля-списки (теги): «а, б, в» → ['а', 'б', 'в']
     */
    protected function syncFlatTranslations(Model $owner, array $input, array $textFields, array $listFields = []): void
    {
        $owner->loadMissing('translations');

        foreach (Locales::ALL as $locale) {
            $fields = [];

            foreach ($textFields as $field) {
                $fields[$field] = trim((string) ($input[$locale][$field] ?? '')) ?: null;
            }

            foreach ($listFields as $field) {
                $fields[$field] = $this->parseList($input[$locale][$field] ?? '') ?: null;
            }

            if ($locale !== Locales::PRIMARY && ! array_filter($fields)) {
                $owner->translations->firstWhere('locale', $locale)?->delete();

                continue;
            }

            $owner->translations()->updateOrCreate(['locale' => $locale], $fields);
        }
    }

    /** @return list<string> */
    protected function parseList(mixed $value): array
    {
        if (! is_string($value)) {
            return [];
        }

        $items = preg_split('/[,\n;]+/u', $value) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $items))));
    }
}
