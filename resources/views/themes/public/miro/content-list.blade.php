@extends('themes.public.miro.content-layout')

@php
    $heroEyebrow = $hero['eyebrow'];
    $heroTitle = $hero['title'];
    $heroIntro = $hero['intro'];
    // Страницы без большой шапки («Видео»): сразу карточки (см. content-layout).
    $bareHero = $bare ?? false;
@endphp

@if(! empty($catalogs))
    @push('styles')
        <link rel="stylesheet" href="{{ asset_v('themes/public/'.($publicTheme ?? 'miro').'/css/catalog.css') }}">
    @endpush
@endif

@section('content')
    @if(! empty($catalogs))
        <div class="miro-catalog-grid">
            @foreach($catalogs as $catalog)
                @include('themes.public.miro.partials.catalog-card', ['card' => $catalog])
            @endforeach
        </div>
    @endif

    @if(count($cards))
        <div class="miro-events-grid">
            @foreach($cards as $card)
                @include('themes.public.miro.partials.content-card', ['card' => $card])
            @endforeach
        </div>

        @include('themes.public.miro.partials.pager', ['paginator' => $paginator])
    @elseif(empty($catalogs))
        <div class="miro-public-placeholder">
            <span class="miro-public-placeholder__mark">✦</span>
            <h2><span data-lang="ru">Раздел готовится</span><span data-lang="en">This section is coming soon</span><span data-lang="ro">Această secțiune este în pregătire</span></h2>
            <p><span data-lang="ru">Мы уже готовим материалы. Скоро здесь появится актуальная информация.</span><span data-lang="en">We are preparing the materials. Relevant information will appear here soon.</span><span data-lang="ro">Pregătim materialele. Informațiile relevante vor apărea în curând.</span></p>
        </div>
    @endif
@endsection
