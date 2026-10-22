<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Broadcasts\BroadcastDrafts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Галочка «Создать черновик для рассылки» в формах новости, публикации и возможности: после сохранения материала
 * создаёт черновик рассылки участницам (ничего не отправляет) и возвращает фразу для сообщения об успехе.
 */
trait CreatesBroadcastDraft
{
    protected function broadcastDraftNote(Request $request, Model $item): ?string
    {
        if (! $request->boolean('broadcast_draft')) {
            return null;
        }

        $draft = app(BroadcastDrafts::class)->create($item, $request->string('broadcast_audience')->toString(), $request->user()?->id);

        return $draft !== null
            ? __('Черновик рассылки создан: откройте «Рассылка» в разделе «Кабинеты участниц», поправьте текст и отправьте.')
            : __('Черновик рассылки по этому материалу уже есть.');
    }
}
