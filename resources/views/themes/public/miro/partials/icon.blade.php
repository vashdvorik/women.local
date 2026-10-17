{{-- Набор линейных значков 24×24 (рисунок сайта, цвет берётся из currentColor). Используется на страницах
     «Приоритеты» и «Как стать участницей»: @include('...partials.icon', ['name' => 'users']). Неизвестное имя даёт круг. --}}
@php
    $icons = [
        'sprout' => '<path d="M12 21v-9"/><path d="M12 12c0-3.5-2.5-6-6.5-6 0 3.8 2.6 6 6.5 6Z"/><path d="M12 14c0-3 2.2-5.5 6-5.5 0 3.3-2.3 5.5-6 5.5Z"/>',
        'users' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><circle cx="17" cy="9" r="2.5"/><path d="M17 14.5c2.4 0 4.5 1.6 4.5 4.5"/>',
        'link' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
        'handshake' => '<circle cx="9" cy="12" r="5"/><circle cx="15" cy="12" r="5"/>',
        'graduation' => '<path d="M3 9l9-4.5L21 9l-9 4.5L3 9Z"/><path d="M7 11.5V16c0 1.4 2.2 2.5 5 2.5s5-1.1 5-2.5v-4.5"/><path d="M21 9v5"/>',
        'megaphone' => '<path d="M4 10v4h3l7 4V6L7 10H4Z"/><path d="M17.5 9.5a4 4 0 0 1 0 5"/><path d="M7 14l1 5h2.5l-1-4"/>',
        'sparkles' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15Z"/>',
        'heart' => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/>',
        'shield' => '<path d="M12 3l7 3v5c0 4.5-3 8.3-7 10-4-1.7-7-5.5-7-10V6l7-3Z"/><path d="M9 12l2.2 2.2L15.5 10"/>',
        'smile' => '<circle cx="12" cy="12" r="9"/><path d="M8.5 14c.9 1.3 2.1 2 3.5 2s2.6-.7 3.5-2"/><path d="M9 9.5h.01M15 9.5h.01"/>',
        'bulb' => '<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3Z"/>',
        'building' => '<rect x="5" y="3.5" width="14" height="17" rx="1.5"/><path d="M9 8h2M13 8h2M9 12h2M13 12h2M10 20.5v-4h4v4"/>',
        'telegram' => '<path d="M21 4L3 11.2l5.5 2L10 19l3-3.7 4.5 3.4L21 4Z"/><path d="M8.5 13.2L21 4"/>',
        'idcard' => '<rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="9" cy="11" r="2.2"/><path d="M5.8 16c.5-1.6 1.8-2.4 3.2-2.4s2.7.8 3.2 2.4"/><path d="M15 10h3M15 13.5h3"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
        'chat' => '<path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v7a2.5 2.5 0 0 1-2.5 2.5H11l-4.5 4v-4A2.5 2.5 0 0 1 4 13.5v-7Z"/><path d="M8.5 9h7M8.5 12h4.5"/>',
        'flag' => '<path d="M6 21V4"/><path d="M6 5h11l-2.5 4 2.5 4H6"/>',
        'card' => '<rect x="3" y="6" width="18" height="12" rx="3"/><path d="M3 10.5h18M7 15h3"/>',
        'key' => '<circle cx="8" cy="14" r="3.5"/><path d="M10.5 11.5L20 3M16 7l2.5 2.5M13.5 9.5l2 2"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="15" rx="2.5"/><path d="M4 10h16M9 3.5v4M15 3.5v4"/>',
        'star' => '<path d="M12 3.5l2.6 5.4 5.9.8-4.3 4.1 1 5.9L12 17l-5.2 2.7 1-5.9L3.5 9.7l5.9-.8L12 3.5Z"/>',
        'book' => '<path d="M4 5.5C4 4.7 4.7 4 5.5 4H11v15H5.5A1.5 1.5 0 0 1 4 17.5v-12Z"/><path d="M20 5.5c0-.8-.7-1.5-1.5-1.5H13v15h5.5a1.5 1.5 0 0 0 1.5-1.5v-12Z"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];
@endphp
<svg class="miro-icon{{ ! empty($class) ? ' '.$class : '' }}" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $icons[$name ?? ''] ?? '<circle cx="12" cy="12" r="8"/>' !!}</svg>
