@props(['tag'])

@if($tag && $tag->translation()?->name)
    <span class="inline-flex items-center rounded-pill px-2.5 py-1 font-display text-[11px] font-bold uppercase tracking-wide"
          style="background: {{ $tag->color }}; color: {{ $tag->textColor() }}">
        {{ $tag->translation()->name }}
    </span>
@endif
