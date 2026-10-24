@extends('themes.public.miro2.content-layout')

@php
    $heroEyebrow = $hero['eyebrow'];
    $heroTitle = $hero['title'];
    $heroIntro = $hero['intro'];
    // Страница «Фото» без большой шапки: сразу полотно из альбомов (см. content-layout).
    $bareHero = true;
@endphp

{{-- Страница «Фото»: не карточки-обложки, а полотно. Альбомы идут один за другим: название со ссылкой на альбом и
     все его фото рядами. Альбомы показываются порциями; кнопка «Смотреть ещё» без скриптов ведёт на ?page=N, со
     скриптом (js/load-more.js) подгружает следующие альбомы в конец списка. Фото открываются по клику
     (js/lightbox.js) в полном размере, листаются внутри своего альбома. --}}
@section('content')
    @if(count($albums))
        <div class="miro-walls" data-wall-list>
            @include('themes.public.miro2.partials.album-walls', ['albums' => $albums])
        </div>

        @if($paginator->hasMorePages())
            <div class="miro-events-more" data-load-more data-target="[data-wall-list]">
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="miro-button miro-button--secondary"><span data-lang="ru">Смотреть ещё</span><span data-lang="en">Load more</span><span data-lang="ro">Vezi mai multe</span></a>
            </div>
        @endif
    @else
        <div class="miro-public-placeholder">
            <span class="miro-public-placeholder__mark">✦</span>
            <h2><span data-lang="ru">Раздел готовится</span><span data-lang="en">This section is coming soon</span><span data-lang="ro">Această secțiune este în pregătire</span></h2>
            <p><span data-lang="ru">Мы уже готовим материалы. Скоро здесь появится актуальная информация.</span><span data-lang="en">We are preparing the materials. Relevant information will appear here soon.</span><span data-lang="ro">Pregătim materialele. Informațiile relevante vor apărea în curând.</span></p>
        </div>
    @endif
@endsection
