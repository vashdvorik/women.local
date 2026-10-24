{{-- Карточки новостей для сетки на странице /events. Отдельный файл, потому что те же карточки приходят и при
     подгрузке по кнопке «Смотреть ещё» (EventsController, ответ с ?partial=1), и они должны быть устроены одинаково. --}}
@php $locales = $locales ?? ['ru', 'en', 'ro']; @endphp
@foreach($events as $event)
    @php $link = $event->cardLink(); @endphp
    <article class="miro-event-card{{ $link ? ' miro-event-card--linked' : '' }}">
        <div class="miro-event-card__visual" style="background:var(--miro-{{ $event->toneKey() }});">
            @if($event->imageUrl())<img src="{{ $event->imageUrl() }}" alt="{{ $event->field('title', 'en') }}" loading="lazy">@endif
            @if(filled($event->field('type', 'ru')))<div class="miro-event-card__type">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event->field('type', $locale) }}</span>@endforeach</div>@endif
        </div>
        <div class="miro-event-card__body">
            <div class="miro-event-card__date">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event->dateLabel($locale) }}</span>@endforeach</div>
            <h3>
                @if($link)<a href="{{ $link['href'] }}"@if($link['external']) target="_blank" rel="noopener"@endif class="miro-event-card__title-link">@endif
                @foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event->field('title', $locale) }}</span>@endforeach
                @if($link)</a>@endif
            </h3>
            <p>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event->field('description', $locale) }}</span>@endforeach</p>
            @if($link)@include('themes.public.miro2.partials.card-more', ['external' => $link['external']])@endif
        </div>
    </article>
@endforeach
