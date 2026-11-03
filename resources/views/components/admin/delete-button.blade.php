@props(['action', 'subject', 'noun' => __('запись'), 'compact' => false])

<div x-data="{ open: false }" class="inline-block">
    @if($compact)
        <button type="button" @click="open = true" class="btn-icon hover:text-danger hover:bg-danger-soft" title="{{ __('Удалить') }}" aria-label="{{ __('Удалить') }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 7V5h4v2m-6 0l.7 11a1 1 0 001 .9h4.6a1 1 0 001-.9L16 7M10.5 11v5m3-5v5"/></svg>
        </button>
    @else
        <button type="button" @click="open = true" class="btn-danger">{{ __('Удалить') }}</button>
    @endif

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
