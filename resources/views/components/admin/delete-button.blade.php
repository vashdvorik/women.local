@props(['action', 'subject', 'noun' => __('запись')])

<div x-data="{ open: false }" class="inline-block">
    <button type="button" @click="open = true" class="btn-danger">{{ __('Удалить') }}</button>

    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="modal-backdrop" hidden
             @keydown.escape.window="open = false" @click.self="open = false">
            <div class="modal" @click.stop>
                <p class="modal__title">{{ __('Удалить :item?', ['item' => $noun]) }}</p>
                <p class="text-reading text-ink-muted mt-2">
                    {{ __('«:subject» будет удалено безвозвратно. Действие необратимо.', ['subject' => $subject]) }}
                </p>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="btn-quiet">{{ __('Отмена') }}</button>
                    <form method="POST" action="{{ $action }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-primary">{{ __('Удалить') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
