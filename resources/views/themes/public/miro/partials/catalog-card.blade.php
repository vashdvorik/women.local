{{-- Карточка каталога с перелистыванием: обложка в виде книги (пропорции страницы), название, число страниц и две кнопки —
     «Листать» (ведёт на страницу просмотра) и «Скачать PDF» (если PDF разрешён). $card — см. App\Support\PublicCards::catalog. --}}
@php
    $locales = ['ru', 'en', 'ro'];
    $pagesWord = ['ru' => 'стр.', 'en' => 'pages', 'ro' => 'pag.'];
@endphp
<article class="miro-catalog-card" style="--r: {{ $card['ratio'] }}">
    <a class="miro-catalog-card__cover" href="{{ $card['href'] }}" tabindex="-1" aria-hidden="true">
        <span class="miro-catalog-card__book">
            @if(! empty($card['image']))<img src="{{ $card['image'] }}" alt="" loading="lazy" decoding="async">@endif
        </span>
        <span class="miro-catalog-card__badge">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['badge'][$l] }}</span>@endforeach</span>
    </a>

    <div class="miro-catalog-card__body">
        <h3 class="miro-catalog-card__title"><a href="{{ $card['href'] }}">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['title'][$l] }}</span>@endforeach</a></h3>
        @if(array_filter($card['excerpt']))
            <p class="miro-catalog-card__text">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['excerpt'][$l] }}</span>@endforeach</p>
        @endif
        <p class="miro-catalog-card__meta">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $card['pages'] }} {{ $pagesWord[$l] }}</span>@endforeach</p>

        <div class="miro-catalog-card__actions">
            <a href="{{ $card['href'] }}" class="miro-button miro-button--primary miro-button--small">
                @foreach($locales as $l)<span data-lang="{{ $l }}">{{ ['ru' => 'Листать', 'en' => 'Browse', 'ro' => 'Răsfoiește'][$l] }}</span>@endforeach
            </a>
            @if(! empty($card['pdf']))
                <a href="{{ $card['pdf'] }}" class="miro-button miro-button--secondary miro-button--small" download>
                    @foreach($locales as $l)<span data-lang="{{ $l }}">{{ ['ru' => 'Скачать PDF', 'en' => 'Download PDF', 'ro' => 'Descarcă PDF'][$l] }}</span>@endforeach
                </a>
            @endif
        </div>
    </div>
</article>
