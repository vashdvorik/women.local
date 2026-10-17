@extends('themes.public.miro.content-layout')

@php
    use App\Enums\Plan;
    use Illuminate\Support\HtmlString;

    $locales = ['ru', 'en', 'ro'];
    // Название и описание нужны вкладке браузера и поисковикам (<head> каркаса); большой шапки у страницы нет, как у
    // новостей: название стоит над текстом, кружки-украшения переехали в блок с текстом.
    $compactHero = true;
    $heroEyebrow = $content['eyebrow'] ?? ['ru' => 'О нас', 'en' => 'About us', 'ro' => 'Despre noi'];
    $heroTitle = $content['title'];
    $heroIntro = $content['intro'];

    // Текст на трёх языках: языки переключаются стилями, как на остальных страницах сайта.
    $tri = fn (array $text): HtmlString => new HtmlString(
        collect($locales)->map(fn (string $l) => '<span data-lang="'.$l.'">'.e($text[$l]).'</span>')->implode('')
    );

    // Заголовок раздела: номер («1.») отдельным span обычным шрифтом, иначе в шрифте заголовков единица выглядит как римская I.
    $heading = fn (array $text): HtmlString => new HtmlString(
        collect($locales)->map(function (string $l) use ($text) {
            preg_match('/^(\d+\.)\s*(.*)$/u', $text[$l], $m);

            return '<span data-lang="'.$l.'"><span class="miro-doc__hnum">'.e($m[1] ?? '').'</span> '.e($m[2] ?? $text[$l]).'</span>';
        })->implode('')
    );

    // Цена пакета: из настроек сайта («Подписки → Цена подписки»), чтобы положение и оплата не расходились.
    $price = fn (string $plan): string => number_format(Plan::from($plan)->price(), 0, ',', "\u{00A0}");
    $priceText = fn (string $plan): HtmlString => new HtmlString(
        collect($locales)->map(fn (string $l) => '<span data-lang="'.$l.'">'.e(str_replace(':price', $price($plan), $content['price'][$l])).'</span>')->implode('')
    );
@endphp

{{-- Документ-положение (Положение о платформе /about/regulations, Положение о Гала-премии /gala/regulations): название,
     затем слева (8 колонок из 12) разделы, справа (4) содержание со ссылками на разделы. Меньше 1024 px: одна колонка,
     содержание сразу под названием. Текст — App\Support\Regulations и App\Support\GalaRegulations (подзаголовок в них
     необязателен). --}}
@section('content')
    <article class="miro-article miro-doc">
        <h1 class="miro-article__title">{{ $tri($content['title']) }}</h1>
        @if(! empty($content['subtitle']))
            <p class="miro-doc__subtitle">{{ $tri($content['subtitle']) }}</p>
        @endif

        <div class="miro-article__layout">
            <div class="miro-article__main miro-doc__main">
                @foreach($content['sections'] as $section)
                    <section class="miro-doc__section" id="{{ $section['id'] }}">
                        <h2 class="miro-doc__heading">{{ $heading($section['title']) }}</h2>

                        @foreach($section['blocks'] as $block)
                            @switch($block['type'])
                                @case('clause')
                                    <p class="miro-doc__clause"><span class="miro-doc__num">{{ $block['number'] }}</span> {{ $tri($block['text']) }}</p>
                                    @break

                                @case('lead')
                                    <p class="miro-doc__clause">{{ $tri($block['text']) }}</p>
                                    @break

                                @case('list')
                                    <ul class="miro-doc__list">
                                        @foreach($block['items'] as $item)
                                            <li>{{ $tri($item) }}</li>
                                        @endforeach
                                    </ul>
                                    @break

                                @case('package')
                                    <div class="miro-doc__package miro-doc__package--{{ $block['plan'] }}">
                                        <div class="miro-doc__package-head">
                                            <h3 class="miro-doc__package-name"><span class="miro-doc__num">{{ $block['number'] }}</span> {{ $block['name'] }}</h3>
                                            <p class="miro-doc__price">{{ $priceText($block['plan']) }}</p>
                                        </div>
                                        <p class="miro-doc__includes">{{ $tri($content['includes']) }}</p>
                                        <ol class="miro-doc__items">
                                            @foreach($block['items'] as $item)
                                                <li>{{ $tri($item) }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                    @break
                            @endswitch
                        @endforeach
                    </section>
                @endforeach
            </div>

            <aside class="miro-article__aside miro-doc__aside">
                <nav class="miro-doc__toc" aria-label="{{ $content['contents']['ru'] }}">
                    <p class="miro-doc__toc-title">{{ $tri($content['contents']) }}</p>
                    <ol class="miro-doc__toc-list">
                        @foreach($content['sections'] as $section)
                            <li><a href="#{{ $section['id'] }}">{{ $tri($section['title']) }}</a></li>
                        @endforeach
                    </ol>
                </nav>
            </aside>
        </div>
    </article>
@endsection
