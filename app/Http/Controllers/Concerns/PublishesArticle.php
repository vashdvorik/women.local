<?php

namespace App\Http\Controllers\Concerns;

use App\Actions\PublishArticle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Общий хвост `store()` и `update()` новости и возможности.
 *
 * Черновик к этому моменту уже записан (SaveArticle — в транзакции), поэтому
 * ошибка публикации ничего не теряет: меняется только статус, а введённое
 * возвращается самим экраном редактирования (AGENTS.md §11). 500-й быть не может —
 * ValidationException перехватывается и превращается в redirect с ошибками.
 */
trait PublishesArticle
{
    protected function finishArticle(
        Model $article,
        Request $request,
        PublishArticle $publisher,
        string $editRoute,
        string $noun,   // «Новость» / «Возможность» — женский род
        bool $created,
    ): RedirectResponse {
        $intent = $request->string('intent', 'save')->toString();

        try {
            match ($intent) {
                'publish' => $publisher->publish($article),
                'unpublish' => $publisher->unpublish($article),
                default => null,
            };
        } catch (ValidationException $e) {
            return redirect()
                ->route($editRoute, $article)
                ->withErrors($e->errors())
                ->with('publish_failed', true);
        }

        return redirect()->route($editRoute, $article)->with('success', match ($intent) {
            'publish' => "{$noun} опубликована.",
            'unpublish' => "{$noun} снята с публикации.",
            default => $created ? 'Черновик сохранён.' : 'Изменения сохранены.',
        });
    }
}
