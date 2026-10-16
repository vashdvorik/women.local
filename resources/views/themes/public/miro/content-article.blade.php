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

{{-- Статья идёт по сетке сайта: на всю ширину контейнера, как шапка и подвал. Название сверху на всю ширину, ниже слева
     (8 колонок из 12) обложка и текст, справа (4 колонки) боковая колонка: тип, дата и «все новости».
     Фото открываются по клику (themes/public/miro/js/lightbox.js); без скриптов ссылка ведёт на сам файл.
     Меньше 1024 px: одна колонка, тип и дата под названием, ссылка «все новости» под текстом. --}}
@section('content')
    <article class="miro-article">
        @if(! empty($article['draft']))
            <p class="miro-article__draft">Черновик: эту страницу видит только администратор.</p>
        @endif

        <h1 class="miro-article__title">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $article['title'][$l] }}</span>@endforeach</h1>

        <div class="miro-article__layout">
            <div class="miro-article__main">
                @if(! empty($article['image']))
                    <a class="miro-zoom miro-article__cover-link" href="{{ $article['image'] }}" data-lightbox><img class="miro-article__cover" src="{{ $article['image'] }}" alt="" loading="eager"></a>
                @endif

                @foreach($locales as $l)
                    <div data-lang="{{ $l }}">
                        @include('themes.public.miro.partials.blocks', ['blocks' => $article['blocks'][$l] ?? [], 'lang' => $l])
                    </div>
                @endforeach
            </div>

            <aside class="miro-article__aside">
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

                <a href="{{ $article['back']['href'] }}" class="miro-article__back">←&nbsp;@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $article['back']['label'][$l] }}</span>@endforeach</a>
            </aside>
        </div>
    </article>
@endsection
