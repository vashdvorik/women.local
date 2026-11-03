@props([
    'action',
    'label',
    'title',
    'message' => null,
    'method' => 'POST',
    'trigger' => 'btn-quiet',   // класс кнопки, открывающей окно
    'confirm' => null,          // подпись кнопки подтверждения (по умолчанию — label)
    'danger' => false,
    'hint' => null,             // подсказка при наведении на кнопку: что произойдёт
    'toggle' => null,           // null — обычная кнопка; true / false — переключатель «вкл / выкл» с подписью label
])

{{-- Подтверждение действия в модальном окне (как у delete-button, но для любого действия). --}}
<div x-data="{ open: false }" class="inline-block">
    @if($toggle === null)
        <button type="button" @click="open = true" class="{{ $trigger }}"@if($hint) title="{{ $hint }}"@endif>{{ $label }}</button>
    @else
        {{-- Переключатель: состояние видно сразу, нажатие открывает окно подтверждения с пояснением --}}
        <button type="button" role="switch" aria-checked="{{ $toggle ? 'true' : 'false' }}" @click="open = true" class="switch-row"@if($hint) title="{{ $hint }}"@endif>
            <span class="switch" aria-hidden="true"></span><span>{{ $label }}</span>
        </button>
    @endif

    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="modal-backdrop" hidden
             @keydown.escape.window="open = false" @click.self="open = false">
            <div class="modal" @click.stop>
                <p class="modal__title">{{ $title }}</p>
                @if($message)
                    <p class="text-reading text-ink-muted mt-2">{{ $message }}</p>
                @endif
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="btn-quiet">{{ __('Отмена') }}</button>
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
