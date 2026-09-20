@props(['action', 'subject', 'noun' => 'запись'])

<div x-data="{ open: false }" class="inline-block">
    <button type="button" @click="open = true" class="btn-danger">Удалить</button>

    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="modal-backdrop" hidden
             @keydown.escape.window="open = false" @click.self="open = false">
            <div class="modal" @click.stop>
                <p class="modal__title">Удалить {{ $noun }}?</p>
                <p class="text-reading text-ink-muted mt-2">
                    «{{ $subject }}» будет удалено безвозвратно. Действие необратимо.
                </p>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="btn-quiet">Отмена</button>
                    <form method="POST" action="{{ $action }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-primary">Удалить</button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
