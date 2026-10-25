@extends('themes.public.miro.content-layout')

@php
    $locales = ['ru', 'en', 'ro'];
    $themeName = $publicTheme ?? 'miro';
    $compactHero = true;
    $heroEyebrow = ['ru' => 'Публикации', 'en' => 'Publications', 'ro' => 'Publicații'];
    $heroTitle = $catalog['title'];
    $heroIntro = $catalog['description'];
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('themes/public/'.$themeName.'/css/catalog.css') }}">
@endpush

{{-- Каталог с перелистыванием. В разметке только адреса страниц-картинок (WebP, их сделал браузер администратора из PDF):
     js/catalog.js подгружает картинки по мере листания (текущая страница и несколько соседних), а не все сразу, и не
     разбирает никаких PDF на устройстве посетителя. Без скриптов остаётся ссылка на первую страницу и, если разрешено, на PDF. --}}
@section('content')
    <article class="miro-article miro-catalog">
        @if(! empty($catalog['draft']))
            <p class="miro-article__draft">Черновик: эту страницу видит только администратор.</p>
        @endif

        <h1 class="miro-article__title">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $catalog['title'][$l] }}</span>@endforeach</h1>

        @if(array_filter($catalog['description']))
            <p class="miro-catalog__lead">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $catalog['description'][$l] }}</span>@endforeach</p>
        @endif

        <div class="miro-flip" data-flip style="--r: {{ $catalog['ratio'] }}" data-ratio="{{ $catalog['ratio'] }}" data-pages="{{ json_encode($catalog['pages']) }}">
            <div class="miro-flip__stage">
                <div class="miro-flip__fit"><div class="miro-flip__book" data-flip-book></div></div>
                <noscript><a href="{{ $catalog['pages'][0] }}"><img src="{{ $catalog['pages'][0] }}" alt="" class="miro-flip__first"></a></noscript>
            </div>

            <div class="miro-flip__bar" data-flip-bar hidden>
                <button type="button" class="miro-flip__btn" data-flip-prev>
                    <span aria-hidden="true">←</span>
                    <span class="miro-visually-hidden">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ ['ru' => 'Назад', 'en' => 'Previous', 'ro' => 'Înapoi'][$l] }}</span>@endforeach</span>
                </button>
                <span class="miro-flip__count" data-flip-count aria-live="polite"></span>
                <button type="button" class="miro-flip__btn" data-flip-next>
                    <span aria-hidden="true">→</span>
                    <span class="miro-visually-hidden">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ ['ru' => 'Вперёд', 'en' => 'Next', 'ro' => 'Înainte'][$l] }}</span>@endforeach</span>
                </button>
                <button type="button" class="miro-flip__btn miro-flip__btn--text" data-flip-full>
                    @foreach($locales as $l)<span data-lang="{{ $l }}">{{ ['ru' => 'На весь экран', 'en' => 'Full screen', 'ro' => 'Ecran complet'][$l] }}</span>@endforeach
                </button>
                @if(! empty($catalog['pdf']))
                    <a class="miro-flip__btn miro-flip__btn--text" href="{{ $catalog['pdf'] }}" download>
                        @foreach($locales as $l)<span data-lang="{{ $l }}">{{ ['ru' => 'Скачать PDF', 'en' => 'Download PDF', 'ro' => 'Descarcă PDF'][$l] }}</span>@endforeach
                    </a>
                @endif
            </div>
        </div>

        <a href="{{ $catalog['back']['href'] }}" class="miro-article__back miro-article__back--wall">←&nbsp;@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $catalog['back']['label'][$l] }}</span>@endforeach</a>
    </article>
@endsection

@push('scripts')
    <script defer src="{{ asset_v('themes/public/'.$themeName.'/js/page-flip.browser.js') }}"></script>
    <script defer src="{{ asset_v('themes/public/'.$themeName.'/js/catalog.js') }}"></script>
@endpush
