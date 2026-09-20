@props(['group', 'label'])

{{-- Заголовок группы = кнопка сворачивания. Состояние в open[group]. --}}
<button type="button" class="nav-group" @click="toggle('{{ $group }}')"
        :aria-expanded="open.{{ $group }} ? 'true' : 'false'">
    <span>{{ $label }}</span>
    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"
         stroke-linecap="round" stroke-linejoin="round"
         class="w-3 h-3 shrink-0 transition-transform"
         :class="open.{{ $group }} ? '' : '-rotate-90'">
        <path d="M4 6l4 4 4-4"/>
    </svg>
</button>
