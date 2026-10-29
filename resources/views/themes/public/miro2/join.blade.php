@extends('themes.public.miro2.content-layout')

@php
    use App\Enums\Plan;
    use Illuminate\Support\HtmlString;

    $locales = ['ru', 'en', 'ro'];
    $botUrl = route('account.register');
    // Название и описание нужны вкладке браузера и поисковикам (<head> каркаса), а также шапке страницы.
    $heroEyebrow = $content['eyebrow'];
    $heroTitle = $content['title'];
    $heroIntro = $content['intro'];

    // Текст на трёх языках: языки переключаются стилями, как на остальных страницах сайта.
    $tri = fn (array $text): HtmlString => new HtmlString(
        collect($locales)->map(fn (string $l) => '<span data-lang="'.$l.'">'.e($text[$l]).'</span>')->implode('')
    );
    // Строка из lang/{ru,en,ro}/subscription.php на трёх языках (тексты пакетов — слово в слово те же, что в кабинете).
    $lang = fn (string $key): HtmlString => new HtmlString(
        collect($locales)->map(fn (string $l) => '<span data-lang="'.$l.'">'.e(__($key, [], $l)).'</span>')->implode('')
    );
    // «Всего 50 рублей в месяц!» и кнопка «Выбрать COMMUNITY — 600 руб./год» на трёх языках (цена из настроек сайта).
    $monthly = fn (Plan $plan): HtmlString => new HtmlString(
        collect($locales)->map(fn (string $l) => '<span data-lang="'.$l.'">'.e(__("subscription.plans.{$plan->value}.monthly", ['price' => $plan->pricePerMonth()], $l)).'</span>')->implode('')
    );
    $cta = fn (Plan $plan): HtmlString => new HtmlString(
        collect($locales)->map(fn (string $l) => '<span data-lang="'.$l.'">'.e(__("subscription.plans.{$plan->value}.cta", ['price' => number_format($plan->price(), 0, ',', "\u{00A0}"), 'currency' => __('subscription.currency', [], $l)], $l)).'</span>')->implode('')
    );
    // Пункт списка пакета с номером $i на трёх языках.
    $feature = fn (string $plan, int $i): HtmlString => new HtmlString(
        collect($locales)->map(fn (string $l) => '<span data-lang="'.$l.'">'.e(__("subscription.plans.{$plan}.features", [], $l)[$i] ?? '').'</span>')->implode('')
    );
    $icon = fn (string $name) => view('themes.public.miro2.partials.icon', ['name' => $name])->render();
    $about = fn (string $file) => asset_v('themes/public/miro2/images/about/'.$file.'.webp');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('themes/public/miro2/css/about-pages.css') }}">
@endpush

@section('hero_actions')
    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary">{{ $tri($content['apply']) }}</a>
    <a href="{{ route('about.regulations') }}" class="miro-button miro-button--secondary">{{ $tri($content['regulations']) }}</a>
@endsection

{{-- «Как стать участницей» написана на основании Положения о платформе (текст — App\Support\AboutContent::join()):
     четыре шага, пакеты участия (тексты и цены те же, что в кабинете), условия, возможности кабинета, частые вопросы.
     Картинки: фото с мероприятий платформы (images/about) и иллюстрация кабинета (images/illustrations/cabinet.svg). --}}
@section('bands')
<div class="miro-about">

    {{-- Четыре шага --}}
    <section class="miro-about__band">
        <div class="miro-container">
            <div class="miro-about__head">
                <p class="miro-about__eyebrow">{{ $tri($content['steps']['eyebrow']) }}</p>
                <h2 class="miro-about__title">{{ $tri($content['steps']['title']) }}</h2>
            </div>
            <div class="miro-about__grid miro-about__grid--4">
                @foreach($content['steps']['items'] as $step)
                    <article class="miro-about__card miro-about__step">
                        <span class="miro-about__step-num">{{ $loop->iteration }}</span>
                        <span class="miro-about__icon">{!! $icon($step['icon']) !!}</span>
                        <h3>{{ $tri($step['title']) }}</h3>
                        <p>{{ $tri($step['text']) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Пакеты участия: тексты из lang/subscription.php, цены из настроек сайта (Plan::price()) --}}
    <section class="miro-about__band miro-about__band--surface" id="packages">
        <div class="miro-container">
            <div class="miro-about__head">
                <p class="miro-about__eyebrow">{{ $tri($content['plans']['eyebrow']) }}</p>
                <h2 class="miro-about__title">{{ $tri($content['plans']['title']) }}</h2>
                <p class="miro-about__lead">{{ $tri($content['plans']['intro']) }}</p>
            </div>
            <div class="miro-about__plans">
                @foreach([Plan::Community, Plan::Active, Plan::Private] as $plan)
                    <article class="miro-about__plan miro-about__plan--{{ $plan->value }}">
                        <h3 class="miro-about__plan-name">{{ $plan->title('ru') }}</h3>
                        <p class="miro-about__plan-tag">{{ $lang("subscription.plans.{$plan->value}.tagline") }}</p>
                        <p class="miro-about__plan-note">{{ $lang("subscription.plans.{$plan->value}.audience") }}</p>
                        <div class="miro-about__plan-price">
                            <span class="miro-about__plan-amount">{{ number_format($plan->price(), 0, ',', "\u{00A0}") }}</span>
                            <span class="miro-about__plan-unit">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ __('subscription.currency', [], $l) }} / {{ __('subscription.per_year', [], $l) }}</span>@endforeach</span>
                        </div>
                        @if(\Illuminate\Support\Facades\Lang::has("subscription.plans.{$plan->value}.monthly"))
                            <p class="miro-about__plan-month">{{ $monthly($plan) }}</p>
                        @endif
                        <p class="miro-about__plan-lead">{{ $lang("subscription.plans.{$plan->value}.lead") }}</p>
                        <ul class="miro-about__plan-list">
                            @foreach(__("subscription.plans.{$plan->value}.features", [], 'ru') as $i => $unused)
                                <li>{{ $feature($plan->value, $i) }}</li>
                            @endforeach
                        </ul>
                        <p class="miro-about__plan-closing">{{ $lang("subscription.plans.{$plan->value}.closing") }}</p>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-about__plan-cta {{ $plan === Plan::Private ? 'miro-button--pink' : 'miro-button--primary' }}">{{ $cta($plan) }}</a>
                    </article>
                @endforeach
            </div>
            <div class="miro-about__plans-foot">
                <p>{{ $tri($content['plans']['note']) }}</p>
                <a href="{{ route('about.regulations') }}#packages" class="miro-about__link">{{ $tri($content['regulations']) }} {!! $icon('arrow') !!}</a>
            </div>
        </div>
    </section>

    {{-- Условия участия --}}
    <section class="miro-about__band">
        <div class="miro-container">
            <div class="miro-about__head">
                <p class="miro-about__eyebrow">{{ $tri($content['terms']['eyebrow']) }}</p>
                <h2 class="miro-about__title">{{ $tri($content['terms']['title']) }}</h2>
                <p class="miro-about__lead">{{ $tri($content['terms']['intro']) }}</p>
            </div>
            <div class="miro-about__grid">
                @foreach($content['terms']['items'] as $item)
                    <article class="miro-about__card">
                        <span class="miro-about__icon">{!! $icon($item['icon']) !!}</span>
                        <h3>{{ $tri($item['title']) }}</h3>
                        <p>{{ $tri($item['text']) }}</p>
                    </article>
                @endforeach
            </div>
            <p style="margin-top: 32px; text-align: center;">
                <a href="{{ route('about.regulations') }}" class="miro-about__link">{{ $tri($content['terms']['link']) }} {!! $icon('arrow') !!}</a>
            </p>
        </div>
    </section>

    {{-- Возможности кабинета --}}
    <section class="miro-about__band miro-about__band--surface">
        <div class="miro-container">
            <div class="miro-about__split miro-about__cabinet-top">
                <div class="miro-about__copy">
                    <p class="miro-about__eyebrow">{{ $tri($content['cabinet']['eyebrow']) }}</p>
                    <h2 class="miro-about__title">{{ $tri($content['cabinet']['title']) }}</h2>
                    <p class="miro-about__lead">{{ $tri($content['cabinet']['intro']) }}</p>
                    <div class="miro-about__actions">
                        <a href="{{ route('account.login') }}" target="_blank" rel="noopener" class="miro-button miro-button--secondary">{{ $tri($content['cabinet']['login']) }}</a>
                    </div>
                </div>
                <figure class="miro-about__figure miro-about__figure--plain">
                    <img src="{{ asset_v('themes/public/miro2/images/illustrations/cabinet.svg') }}" width="640" height="460" alt="" loading="lazy">
                </figure>
            </div>

            @foreach($content['cabinet']['groups'] as $group)
                <div class="miro-about__group {{ $loop->last ? 'miro-about__group--closed' : '' }}">
                    <p class="miro-about__badge">{{ $tri($group['badge']) }}</p>
                    <div class="miro-about__grid">
                        @foreach($group['items'] as $item)
                            <article class="miro-about__card">
                                <span class="miro-about__icon">{!! $icon($item['icon']) !!}</span>
                                <h3>{{ $tri($item['title']) }}</h3>
                                <p>{{ $tri($item['text']) }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Сообщество вживую --}}
    <section class="miro-about__band">
        <div class="miro-container">
            <div class="miro-about__head">
                <h2 class="miro-about__title">{{ $tri($content['photos']['title']) }}</h2>
                <p class="miro-about__lead">{{ $tri($content['photos']['text']) }}</p>
            </div>
            <div class="miro-about__photos">
                <figure><img src="{{ $about('join-1') }}" width="900" height="600" alt="" loading="lazy"></figure>
                <figure><img src="{{ $about('join-2') }}" width="900" height="600" alt="" loading="lazy"></figure>
                <figure><img src="{{ $about('join-3') }}" width="900" height="600" alt="" loading="lazy"></figure>
            </div>
            <p style="margin-top: 28px; text-align: center;">
                <a href="{{ route('media.photos') }}" class="miro-about__link">{{ $tri($content['photos']['link']) }} {!! $icon('arrow') !!}</a>
            </p>
        </div>
    </section>

    {{-- Частые вопросы --}}
    <section class="miro-about__band miro-about__band--surface">
        <div class="miro-container">
            <div class="miro-about__head">
                <h2 class="miro-about__title">{{ $tri($content['faq']['title']) }}</h2>
            </div>
            <div class="miro-about__faq">
                @foreach($content['faq']['items'] as $item)
                    <details class="miro-about__faq-item">
                        <summary><span class="miro-about__faq-q">{{ $tri($item['q']) }}</span></summary>
                        <p class="miro-about__faq-a">{{ $tri($item['a']) }}</p>
                    </details>
                @endforeach
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
                    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--pink">{{ $tri($content['apply']) }}</a>
                    <a href="{{ route('account.login') }}" target="_blank" rel="noopener" class="miro-button miro-about__ghost">{{ $tri($content['cabinet']['login']) }}</a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
