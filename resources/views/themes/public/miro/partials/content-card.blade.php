{{-- Карточка материала в списках (публикации, возможности, фото, видео, проекты).
     Разметка и стили — те же, что у карточек событий (events.css), чтобы все списки
     выглядели одинаково. $card — см. App\Support\PublicCards.
     Кликабельна вся карточка: ссылка стоит в заголовке и растягивается на карточку (см. navigation.css). --}}
@php
    $locales = ['ru', 'en', 'ro'];
    $external = $card['external'] ?? false;
    $hasLink = filled($card['href'] ?? null);
@endphp
<article class="miro-event-card{{ $hasLink ? ' miro-event-card--linked' : '' }}">
    <div class="miro-event-card__visual" style="background:var(--miro-teal)">
        @if(! empty($card['image']))
            <img src="{{ $card['image'] }}" alt="" loading="lazy">
        @endif
        @if(! empty($card['play']))
            <span class="miro-card-play" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg></span>
        @endif
        @if(! empty($card['badge']))
            <div class="miro-event-card__type" @if(! empty($card['badge_style'])) style="{{ $card['badge_style'] }}" @endif>@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['badge'][$l] }}</span>@endforeach</div>
        @endif
    </div>
    <div class="miro-event-card__body">
        @if(! empty($card['date']))
            <div class="miro-event-card__date">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['date'][$l] }}</span>@endforeach</div>
        @endif
        <h3>
            @if($hasLink)<a href="{{ $card['href'] }}"@if($external) target="_blank" rel="noopener noreferrer"@endif class="miro-event-card__title-link">@endif
            @foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['title'][$l] }}</span>@endforeach
            @if($hasLink)</a>@endif
        </h3>
        @if(array_filter($card['excerpt'] ?? []))
            <p>@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['excerpt'][$l] }}</span>@endforeach</p>
        @endif
        @if($hasLink)
            @include('themes.public.miro.partials.card-more', ['kind' => ! empty($card['play']) ? 'watch' : 'more', 'external' => $external])
        @endif
    </div>
</article>
