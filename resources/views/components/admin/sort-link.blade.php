@props(['column', 'label'])

@php
    $current = request()->string('sort')->toString();
    $dir = request()->string('dir', 'asc')->toString();
    $active = $current === $column;
    $nextDir = $active && $dir === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'dir' => $nextDir, 'page' => null]);
@endphp

<a href="{{ $url }}" class="inline-flex items-center gap-1 hover:text-ink">
    {{ $label }}
    @if($active)
        <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor"
             class="{{ $dir === 'desc' ? 'rotate-180' : '' }}">
            <path d="M6 3l3 4H3z"/>
        </svg>
    @endif
</a>
