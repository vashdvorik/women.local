@props(['group', 'label', 'count' => 0])

{{-- Заголовок группы = кнопка сворачивания. Состояние в open[group]. Пока группа свёрнута, число того, что в ней ждёт
     решения ($count), показывается на заголовке: очередь не прячется вместе с группой. --}}
<button type="button" class="nav-group" data-group-toggle="{{ $group }}" @click="toggle('{{ $group }}')"
        :aria-expanded="open.{{ $group }} ? 'true' : 'false'">
    <span>{{ $label }}</span>
    @if($count > 0)
        <span class="nav-counter mr-1" data-group-counter="{{ $group }}" x-show="!open.{{ $group }}" title="{{ __('Требует внимания') }}">{{ $count }}</span>
    @endif
    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"
         stroke-linecap="round" stroke-linejoin="round"
         class="w-3 h-3 shrink-0 transition-transform" x-init="$nextTick(() => $el.style.transform = '')"
         :class="open.{{ $group }} ? '' : '-rotate-90'">
        <path d="M4 6l4 4 4-4"/>
    </svg>
</button>
