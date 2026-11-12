<?php

namespace App\Models\Concerns;

use App\Support\PublicCache;
use Illuminate\Support\Facades\DB;

/**
 * Ручной порядок записей по колонке `position` (эксперты, проекты): список id в новом порядке приходит из админки
 * (перетаскивание строк, js/row-reorder.js).
 */
trait HasManualOrder
{
    /**
     * Записи, которых нет в списке, встают следом в прежнем порядке; позиции перенумеровываются с 1.
     *
     * @param  list<int>  $ids
     */
    public static function applyOrder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $ids = array_values(array_unique(array_map('intval', $ids)));
            $rest = static::query()->whereNotIn('id', $ids)->orderBy('position')->orderBy('id')->pluck('id')->all();

            foreach ([...$ids, ...$rest] as $index => $id) {
                static::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });

        PublicCache::flush();
    }
}
