@props(['action', 'first' => false, 'last' => false])

{{-- Стрелки ↑ ↓ для ручного порядка карточек. --}}
<div class="flex gap-1">
    <form method="POST" action="{{ $action }}">
        @csrf<input type="hidden" name="direction" value="up">
        <button class="btn-icon" @disabled($first) title="Вверх" aria-label="Вверх">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12V4M4 8l4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </form>
    <form method="POST" action="{{ $action }}">
        @csrf<input type="hidden" name="direction" value="down">
        <button class="btn-icon" @disabled($last) title="Вниз" aria-label="Вниз">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 4v8M4 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </form>
</div>
