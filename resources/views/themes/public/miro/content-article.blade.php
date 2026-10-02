@extends('themes.public.miro.content-layout')

@php
    $locales = ['ru', 'en', 'ro'];
    // Большая шапка списков здесь не нужна: название выводится в самой статье, а краткое описание на странице
    // не показывается (оно для карточек в списках). Переменные остаются для заголовка вкладки и описания
    // страницы в <head> (см. content-layout).
    $compactHero = true;
    $heroEyebrow = $eyebrow;
    $heroTitle = $article['title'];
    $heroIntro = $article['excerpt'];
@endphp

@section('content')
    <article class="miro-article">
        @if(! empty($article['draft']))
            <p class="miro-article__draft">Черновик: эту страницу видит только администратор.</p>
        @endif

        <h1 class="miro-article__title">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $article['title'][$l] }}</span>@endforeach</h1>

        @if(! empty($article['badge']) || ! empty($article['meta']))
            <div class="miro-article__meta">
                @if(! empty($article['badge']))
                    <span class="miro-tag" @if(! empty($article['badge_style'])) style="{{ $article['badge_style'] }}" @endif>@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $article['badge'][$l] }}</span>@endforeach</span>
                @endif
                @if(! empty($article['meta']))
                    <span class="miro-article__date">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $article['meta'][$l] }}</span>@endforeach</span>
                @endif
            </div>
        @endif

        @if(! empty($article['image']))
            <img class="miro-article__cover" src="{{ $article['image'] }}" alt="" loading="eager">
        @endif

        @foreach($locales as $l)
            <div data-lang="{{ $l }}">
                @include('themes.public.miro.partials.blocks', ['blocks' => $article['blocks'][$l] ?? [], 'lang' => $l])
            </div>
        @endforeach

        @if(! empty($article['source']))
            <p class="miro-article__source"><a href="{{ $article['source']['href'] }}" target="_blank" rel="noopener" class="miro-button miro-button--secondary">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $article['source']['label'][$l] }}</span>@endforeach</a></p>
        @endif

        <a href="{{ $article['back']['href'] }}" class="miro-article__back">←&nbsp;@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $article['back']['label'][$l] }}</span>@endforeach</a>
    </article>
@endsection
