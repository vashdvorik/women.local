{{-- Каркас страниц с материалами из админки (публикации, возможности, фото, видео, проекты):
     шапка сайта, «герой» с заголовком, содержимое, подвал. Наследники задают $heroEyebrow,
     $heroTitle (по трём языкам), необязательный $heroIntro и секцию `content`. --}}
@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $locales = ['ru', 'en', 'ro'];
    $theme = $publicTheme ?? 'miro2';
    $heroIntro = $heroIntro ?? null;
@endphp
<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heroTitle['ru'] }} — Women's hub</title>
    <meta name="description" content="{{ $heroIntro['ru'] ?? $heroTitle['ru'] }}">
    <link rel="icon" type="image/png" href="{{ asset_v('themes/public/'.$theme.'/images/brand/favicon-w4w.png') }}">
    <link rel="apple-touch-icon" href="{{ asset_v('themes/public/'.$theme.'/images/brand/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset_v('themes/public/'.$theme.'/css/events.css') }}">
    <link rel="stylesheet" href="{{ asset_v('themes/public/'.$theme.'/css/public-section.css') }}">
    {{-- Стили отдельных страниц (@push('styles')): перед navigation.css, он остаётся последним (тест PublicButtonsTest). --}}
    @stack('styles')
    <link rel="stylesheet" href="{{ asset_v('themes/public/'.$theme.'/css/navigation.css') }}">
    <link rel="stylesheet" href="{{ asset_v('themes/public/'.$theme.'/css/light.css') }}">
</head>
<body>
    @include('themes.public.miro2.partials.miro-header', ['miroCurrentPage' => null])

    <main class="miro-public-page">
        {{-- У страницы материала (новость, публикация, возможность) большой шапки нет: название стоит над
             текстом, а кружки-украшения шапки переехали в блок с текстом (см. ниже). У страниц со списком без шапки
             ($bareHero: «Фото», «Видео») нет ни шапки, ни кружков: сразу содержимое, название страницы остаётся
             в разметке скрытым заголовком h1 для экранных дикторов и поисковиков. --}}
        @unless(($compactHero ?? false) || ($bareHero ?? false))
        <section class="miro-public-hero">
            <div class="miro-container miro-public-hero__inner">
                <div class="miro-public-hero__copy">
                    {{-- Надпись над заголовком необязательна: пустая (как «Платформа» на «Проектах» и «Возможностях») не выводится. --}}
                    @if(array_filter((array) ($heroEyebrow ?? [])))
                        <p class="miro-eyebrow">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $heroEyebrow[$l] }}</span>@endforeach</p>
                    @endif
                    <h1>@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $heroTitle[$l] }}</span>@endforeach</h1>
                    @if($heroIntro && array_filter($heroIntro))
                        <p class="miro-public-hero__intro">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $heroIntro[$l] }}</span>@endforeach</p>
                    @endif
                    {{-- Кнопки под описанием (необязательно): секция hero_actions страницы. --}}
                    @hasSection('hero_actions')
                        <div class="miro-public-hero__actions">@yield('hero_actions')</div>
                    @endif
                </div>
                <div class="miro-public-hero__accent" aria-hidden="true"><span></span><span></span><span></span></div>
            </div>
        </section>
        @endunless

        {{-- Страница из полноширинных полос (секция bands: «Приоритеты», «Как стать участницей») обходится без этого блока. --}}
        @if(trim($__env->yieldContent('content')) !== '')
        <section class="miro-public-content @if($compactHero ?? false) miro-public-content--article @endif @if($bareHero ?? false) miro-public-content--bare @endif">
            <div class="miro-container">
                @if($bareHero ?? false)
                    <h1 class="miro-visually-hidden">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $heroTitle[$l] }}</span>@endforeach</h1>
                @endif
                @if(($compactHero ?? false) && ! ($bareHero ?? false))
                    <div class="miro-public-hero__accent" aria-hidden="true"><span></span><span></span><span></span></div>
                @endif
                @yield('content')
            </div>
        </section>
        @endif

        @yield('bands')
    </main>

    @include('themes.public.miro2.partials.miro-footer')

    {{-- Просмотр фото по клику: фото статей и альбомов (a.miro-zoom). --}}
    <script defer src="{{ asset_v('themes/public/'.$theme.'/js/lightbox.js') }}"></script>
    {{-- Проигрыватель видео по клику (карточки a[data-video] на странице «Видео»). --}}
    <script defer src="{{ asset_v('themes/public/'.$theme.'/js/video-player.js') }}"></script>
    {{-- Кнопка «Смотреть ещё» (там, где есть [data-load-more], например на странице «Фото»). --}}
    <script defer src="{{ asset_v('themes/public/'.$theme.'/js/load-more.js') }}"></script>
</body>
</html>
