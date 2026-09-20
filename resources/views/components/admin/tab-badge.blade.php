@props(['locale'])

{{-- Ровно один значок — либо ✓, либо ⚠, никогда оба (AGENTS.md §13). --}}
<span aria-hidden="true"
      :class="{
        'tab-badge--done': badges.{{ $locale }} === 'done',
        'tab-badge--partial': badges.{{ $locale }} === 'partial',
        'tab-badge--empty': badges.{{ $locale }} === 'empty',
      }"
      class="tab-badge">
    <svg x-show="badges.{{ $locale }} === 'done'" width="16" height="16" viewBox="0 0 16 16"
         fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M3 8.5l3.5 3.5L13 5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <svg x-show="badges.{{ $locale }} === 'partial'" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
        <path d="M8 2l6.5 11.5h-13z"/>
    </svg>
    <svg x-show="badges.{{ $locale }} === 'empty'" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
        <circle cx="8" cy="8" r="3"/>
    </svg>
</span>
