<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Список новостей и возможностей: поиск по русскому заголовку, сортировка по
 * колонкам, пагинация (AGENTS.md §15).
 */
class ArticleList
{
    public static function for(Builder $query, Request $request, array $with = ['translations']): LengthAwarePaginator
    {
        $model = $query->getModel();
        $translationsTable = $model->translations()->getRelated()->getTable();
        $foreignKey = $model->translations()->getForeignKeyName();

        $search = trim((string) $request->string('q'));
        $sort = (string) $request->string('sort', 'recent');
        $dir = $request->string('dir', 'asc')->toString() === 'desc' ? 'desc' : 'asc';

        $query->with($with);

        if ($search !== '') {
            $query->whereHas('translations', fn (Builder $q) => $q
                ->where('locale', Locales::PRIMARY)
                ->where('title', 'like', '%'.$search.'%'));
        }

        match ($sort) {
            'title' => $query
                ->leftJoin($translationsTable, function ($join) use ($translationsTable, $foreignKey, $model) {
                    $join->on($translationsTable.'.'.$foreignKey, '=', $model->getTable().'.id')
                        ->where($translationsTable.'.locale', '=', Locales::PRIMARY);
                })
                ->orderBy($translationsTable.'.title', $dir)
                ->select($model->getTable().'.*'),
            'status' => $query->orderBy('status', $dir),
            'published_at' => $query->orderBy('published_at', $dir),
            'deadline_at' => $query->orderBy('deadline_at', $dir),
            default => $query->latest('updated_at'),
        };

        return $query->paginate(20);
    }
}
