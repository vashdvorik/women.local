{{-- Карточка материала в списках (публикации, возможности, фото, видео, проекты).
     Разметка и стили — те же, что у карточек событий (events.css), чтобы все списки
     выглядели одинаково. $card — см. App\Support\PublicCards. --}}
@php
    $locales = ['ru', 'en', 'ro'];
    $external = $card['external'] ?? false;
    $hasLink = filled($card['href'] ?? null);
@endphp
<article class="miro-event-card">
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
        <h3>@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['title'][$l] }}</span>@endforeach</h3>
        @if(array_filter($card['excerpt'] ?? []))
            <p>@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['excerpt'][$l] }}</span>@endforeach</p>
        @endif
        @if($hasLink)
            <a href="{{ $card['href'] }}" @if($external) target="_blank" rel="noopener noreferrer" @endif class="miro-event-card__link">
                @if(! empty($card['play']))
                    <span data-lang="ru">Смотреть&nbsp;→</span><span data-lang="en">Watch&nbsp;→</span><span data-lang="ro">Vezi&nbsp;→</span>
                @else
                    <span data-lang="ru">Подробнее&nbsp;→</span><span data-lang="en">Read more&nbsp;→</span><span data-lang="ro">Află mai multe&nbsp;→</span>
                @endif
            </a>
        @endif
    </div>
</article>
