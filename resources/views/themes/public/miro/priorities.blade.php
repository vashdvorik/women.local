@extends('themes.public.miro.content-layout')

@php
    use Illuminate\Support\HtmlString;

    $locales = ['ru', 'en', 'ro'];
    // Название и описание нужны вкладке браузера и поисковикам (<head> каркаса), а также шапке страницы.
    $heroEyebrow = $content['eyebrow'];
    $heroTitle = $content['title'];
    $heroIntro = $content['intro'];

    // Текст на трёх языках: языки переключаются стилями, как на остальных страницах сайта.
    $tri = fn (array $text): HtmlString => new HtmlString(
        collect($locales)->map(fn (string $l) => '<span data-lang="'.$l.'">'.e($text[$l]).'</span>')->implode('')
    );
    $icon = fn (string $name) => view('themes.public.miro.partials.icon', ['name' => $name])->render();
    $about = fn (string $file) => asset('themes/public/miro/images/about/'.$file.'.webp');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('themes/public/miro/css/about-pages.css') }}">
@endpush

{{-- «Приоритеты» написаны на основании Положения о платформе (текст — App\Support\AboutContent::priorities()): миссия,
     шесть целей, принципы, кого объединяет платформа, организационная основа и партнёры, Gala. Фото — с мероприятий
     платформы (images/about, облегчённые копии фото из альбомов). --}}
@section('bands')
<div class="miro-about">

    {{-- Миссия --}}
    <section class="miro-about__band">
        <div class="miro-container miro-about__split">
            <div class="miro-about__copy">
                <p class="miro-about__eyebrow">{{ $tri($content['mission']['eyebrow']) }}</p>
                <h2 class="miro-about__title">{{ $tri($content['mission']['title']) }}</h2>
                <div style="margin-top: 24px;">
                    @foreach($content['mission']['text'] as $paragraph)
                        <p class="miro-about__text">{{ $tri($paragraph) }}</p>
                    @endforeach
                </div>
                <div class="miro-about__actions">
                    <a href="{{ route('about.regulations') }}" class="miro-button miro-button--secondary">{{ $tri($content['mission']['link']) }}</a>
                </div>
            </div>
            <figure class="miro-about__figure">
                <img src="{{ $about('mission') }}" width="900" height="600" alt="" loading="lazy">
            </figure>
        </div>
    </section>

    {{-- Шесть целей --}}
    <section class="miro-about__band miro-about__band--surface">
        <div class="miro-container">
            <div class="miro-about__head">
                <p class="miro-about__eyebrow">{{ $tri($content['goals']['eyebrow']) }}</p>
                <h2 class="miro-about__title">{{ $tri($content['goals']['title']) }}</h2>
                <p class="miro-about__lead">{{ $tri($content['goals']['intro']) }}</p>
            </div>
            <div class="miro-about__grid">
                @foreach($content['goals']['items'] as $goal)
                    <article class="miro-about__card">
                        <span class="miro-about__card-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="miro-about__icon">{!! $icon($goal['icon']) !!}</span>
                        <h3>{{ $tri($goal['title']) }}</h3>
                        <p>{{ $tri($goal['text']) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Принципы --}}
    <section class="miro-about__band">
        <div class="miro-container">
            <div class="miro-about__head">
                <h2 class="miro-about__title">{{ $tri($content['principles']['title']) }}</h2>
            </div>
            <div class="miro-about__principles">
                @foreach($content['principles']['items'] as $principle)
                    <div class="miro-about__principle">
                        <span class="miro-about__icon">{!! $icon($principle['icon']) !!}</span>
                        <h3>{{ $tri($principle['title']) }}</h3>
                        <p>{{ $tri($principle['text']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Кого объединяет --}}
    <section class="miro-about__band miro-about__band--surface">
        <div class="miro-container miro-about__split">
            <div class="miro-about__copy">
                <h2 class="miro-about__title">{{ $tri($content['people']['title']) }}</h2>
                <p class="miro-about__lead">{{ $tri($content['people']['intro']) }}</p>
                <div class="miro-about__actions">
                    <a href="{{ route('members.join') }}" class="miro-button miro-button--primary">{{ $tri($content['people']['link']) }}</a>
                </div>
            </div>
            <ul class="miro-about__chips" style="margin-top: 0;">
                @foreach($content['people']['items'] as $item)
                    <li class="miro-about__chip">{{ $tri($item) }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Организационная основа и партнёры --}}
    <section class="miro-about__band">
        <div class="miro-container">
            <div class="miro-about__head">
                <h2 class="miro-about__title">{{ $tri($content['foundation']['title']) }}</h2>
                <p class="miro-about__lead">{{ $tri($content['foundation']['intro']) }}</p>
            </div>
            <div class="miro-about__grid miro-about__grid--4">
                @foreach($content['foundation']['items'] as $item)
                    <article class="miro-about__card">
                        <span class="miro-about__icon">{!! $icon($item['icon']) !!}</span>
                        <h3>{{ $tri($item['title']) }}</h3>
                        <p>{{ $tri($item['text']) }}</p>
                    </article>
                @endforeach
            </div>
            <div style="margin-top: 44px; text-align: center;">
                <p class="miro-about__eyebrow">{{ $tri($content['foundation']['includes_title']) }}</p>
                <ul class="miro-about__chips miro-about__chips--soft" style="justify-content: center;">
                    @foreach($content['foundation']['includes'] as $item)
                        <li class="miro-about__chip">{{ $tri($item) }}</li>
                    @endforeach
                </ul>
                <p style="margin-top: 28px;">
                    <a href="{{ route('partners') }}" class="miro-about__link">{{ $tri($content['foundation']['link']) }} {!! $icon('arrow') !!}</a>
                </p>
            </div>
        </div>
    </section>

    {{-- Gala --}}
    <section class="miro-about__band miro-about__band--surface">
        <div class="miro-container miro-about__split">
            <div class="miro-about__copy">
                <p class="miro-about__eyebrow">{{ $tri($content['gala']['eyebrow']) }}</p>
                <h2 class="miro-about__title">{{ $tri($content['gala']['title']) }}</h2>
                <div style="margin-top: 24px;">
                    @foreach($content['gala']['text'] as $paragraph)
                        <p class="miro-about__text">{{ $tri($paragraph) }}</p>
                    @endforeach
                </div>
                <div class="miro-about__actions">
                    <a href="{{ route('gala.regulations') }}" class="miro-button miro-button--primary">{{ $tri($content['gala']['links']['regulations']) }}</a>
                    <a href="{{ route('media.photos') }}" class="miro-button miro-button--secondary">{{ $tri($content['gala']['links']['photos']) }}</a>
                </div>
            </div>
            <div class="miro-about__photos miro-about__photos--mosaic">
                <figure><img src="{{ $about('gala-1') }}" width="900" height="600" alt="" loading="lazy"></figure>
                <figure><img src="{{ $about('gala-2') }}" width="900" height="600" alt="" loading="lazy"></figure>
                <figure><img src="{{ $about('gala-3') }}" width="900" height="600" alt="" loading="lazy"></figure>
            </div>
        </div>
    </section>

    {{-- Призыв --}}
    <section class="miro-about__band">
        <div class="miro-container">
            <div class="miro-about__cta">
                <h2>{{ $tri($content['cta']['title']) }}</h2>
                <p>{{ $tri($content['cta']['text']) }}</p>
                <div class="miro-about__actions">
                    <a href="{{ route('members.join') }}" class="miro-button miro-button--pink">{{ $tri($content['cta']['join']) }}</a>
                    <a href="{{ route('about.regulations') }}" class="miro-button miro-about__ghost">{{ $tri($content['cta']['regulations']) }}</a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
