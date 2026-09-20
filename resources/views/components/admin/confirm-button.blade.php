@props([
    'action',
    'label',
    'title',
    'message' => null,
    'method' => 'POST',
    'trigger' => 'btn-quiet',   // класс кнопки, открывающей окно
    'confirm' => null,          // подпись кнопки подтверждения (по умолчанию — label)
    'danger' => false,
])

{{-- Подтверждение действия в модальном окне (как у delete-button, но для любого действия). --}}
<div x-data="{ open: false }" class="inline-block">
    <button type="button" @click="open = true" class="{{ $trigger }}">{{ $label }}</button>

    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="modal-backdrop" hidden
             @keydown.escape.window="open = false" @click.self="open = false">
            <div class="modal" @click.stop>
                <p class="modal__title">{{ $title }}</p>
                @if($message)
                    <p class="text-reading text-ink-muted mt-2">{{ $message }}</p>
                @endif
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="btn-quiet">Отмена</button>
                    <form method="POST" action="{{ $action }}">
                        @csrf
                        @if(strtoupper($method) !== 'POST') @method($method) @endif
                        <button type="submit" class="{{ $danger ? 'btn-danger' : 'btn-primary' }}">{{ $confirm ?? $label }}</button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
