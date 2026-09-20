@props(['name'])

{{-- Минималистичные иконки меню: 16px, штрих 1.5, наследуют цвет пункта (DESIGN.md §1). --}}
<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"
     stroke-linecap="round" stroke-linejoin="round"
     class="w-4 h-4 shrink-0" aria-hidden="true">
    @switch($name)
        @case('dashboard')
            <rect x="2.5" y="2.5" width="4.5" height="4.5" rx="1"/>
            <rect x="9" y="2.5" width="4.5" height="4.5" rx="1"/>
            <rect x="2.5" y="9" width="4.5" height="4.5" rx="1"/>
            <rect x="9" y="9" width="4.5" height="4.5" rx="1"/>
            @break

        @case('news')
            <rect x="3" y="2.5" width="10" height="11" rx="1"/>
            <path d="M5.5 5.5h5M5.5 8h5M5.5 10.5h3"/>
            @break

        @case('opportunity')
            <circle cx="8" cy="8" r="5.5"/>
            <circle cx="8" cy="8" r="2"/>
            @break

        @case('tag')
            <path d="M8 2.5H4a1.5 1.5 0 0 0-1.5 1.5v4L8 13.5 13.5 8 8 2.5z"/>
            <circle cx="5.5" cy="5.5" r=".9" fill="currentColor" stroke="none"/>
            @break

        @case('album')
            <rect x="2.5" y="3" width="11" height="10" rx="1"/>
            <circle cx="6" cy="6.5" r="1.2"/>
            <path d="M13.5 10.5 10 7l-5 5.5"/>
            @break

        @case('video')
            <circle cx="8" cy="8" r="5.5"/>
            <path d="M6.5 5.5v5l4-2.5-4-2.5z" fill="currentColor" stroke="none"/>
            @break

        @case('inbox')
            <path d="M3 8.5 4.8 3.6A1 1 0 0 1 5.75 3h4.5a1 1 0 0 1 .95.65L13 8.5"/>
            <path d="M3 8.5V12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V8.5h-3l-1 1.5H7l-1-1.5H3z"/>
            @break

        @case('sliders')
            <path d="M2.5 5.5h5M11 5.5h2.5M2.5 10.5h2.5M9 10.5h4.5"/>
            <circle cx="9" cy="5.5" r="1.5"/>
            <circle cx="7" cy="10.5" r="1.5"/>
            @break

        @case('event')
            <rect x="2.5" y="3.5" width="11" height="10" rx="1"/>
            <path d="M2.5 6.5h11M5.5 2v3M10.5 2v3"/>
            @break

        @case('users')
            <circle cx="6" cy="5.5" r="2.2"/>
            <path d="M2 13c0-2.2 1.8-4 4-4s4 1.8 4 4"/>
            <path d="M10.5 3.6a2.2 2.2 0 0 1 0 3.8M11.5 9.2c1.6.5 2.5 1.9 2.5 3.8"/>
            @break

        @case('chart')
            <path d="M2.5 13.5h11M4.5 13.5V8M8 13.5V3.5M11.5 13.5V6.5"/>
            @break

        @case('plus')
            <path d="M8 3.5v9M3.5 8h9"/>
            @break

        @case('logout')
            <path d="M6.5 2.5H4a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h2.5"/>
            <path d="M10 5.5 12.5 8 10 10.5M12.5 8H6.5"/>
            @break
    @endswitch
</svg>
