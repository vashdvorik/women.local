@extends('themes.public.miro.content-layout')

@php
    $locales = ['ru', 'en', 'ro'];
    $heroEyebrow = $eyebrow;
    $heroTitle = $article['title'];
    $heroIntro = $article['excerpt'];
@endphp

@section('content')
    <article class="miro-article">
        @if(! empty($article['draft']))
            <p class="miro-article__draft">Черновик: эту страницу видит только администратор.</p>
        @endif

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
                @include('themes.public.miro.partials.blocks', ['blocks' => $article['blocks'][$l] ?? []])
            </div>
        @endforeach

        <a href="{{ $article['back']['href'] }}" class="miro-article__back">←&nbsp;@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $article['back']['label'][$l] }}</span>@endforeach</a>
    </article>
@endsection
