{{-- Ячейка «Порядок» в таблице с перетаскиванием строк (Alpine rowReorder, js/row-reorder.js): значок и стрелки вверх/вниз. --}}
<div class="flex items-center gap-1">
    <button type="button" class="btn-icon cursor-grab" @mousedown="arm($event)" @mouseup="disarm($event)"
            title="{{ __('Перетащить') }}" aria-label="{{ __('Перетащить') }}">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><circle cx="6" cy="4" r="1.2"/><circle cx="10" cy="4" r="1.2"/><circle cx="6" cy="8" r="1.2"/><circle cx="10" cy="8" r="1.2"/><circle cx="6" cy="12" r="1.2"/><circle cx="10" cy="12" r="1.2"/></svg>
    </button>
    <button type="button" class="btn-icon" data-move="up" @click="move($event, -1)"
            title="{{ __('Вверх') }}" aria-label="{{ __('Вверх') }}">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12V4M4 8l4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button type="button" class="btn-icon" data-move="down" @click="move($event, 1)"
            title="{{ __('Вниз') }}" aria-label="{{ __('Вниз') }}">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 4v8M4 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
</div>
