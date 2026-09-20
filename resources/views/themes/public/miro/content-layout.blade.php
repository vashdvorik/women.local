{{-- Каркас страниц с материалами из админки (публикации, возможности, фото, видео, проекты):
     шапка сайта, «герой» с заголовком, содержимое, подвал. Наследники задают $heroEyebrow,
     $heroTitle (по трём языкам), необязательный $heroIntro и секцию `content`. --}}
@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $locales = ['ru', 'en', 'ro'];
    $theme = $publicTheme ?? 'miro';
    $heroIntro = $heroIntro ?? null;
@endphp
<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heroTitle['ru'] }} — Women Entrepreneurs Platform</title>
    <meta name="description" content="{{ $heroIntro['ru'] ?? $heroTitle['ru'] }}">
    <link rel="icon" type="image/png" href="{{ asset('themes/public/'.$theme.'/images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/'.$theme.'/css/events.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/'.$theme.'/css/public-section.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/'.$theme.'/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => null])

    <main class="miro-public-page">
        <section class="miro-public-hero">
            <div class="miro-container miro-public-hero__inner">
                <div class="miro-public-hero__copy">
                    <p class="miro-eyebrow">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $heroEyebrow[$l] }}</span>@endforeach</p>
                    <h1>@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $heroTitle[$l] }}</span>@endforeach</h1>
                    @if($heroIntro && array_filter($heroIntro))
                        <p class="miro-public-hero__intro">@foreach($locales as $l)<span data-lang="{{ $l }}">{{ $heroIntro[$l] }}</span>@endforeach</p>
                    @endif
                </div>
                <div class="miro-public-hero__accent" aria-hidden="true"><span></span><span></span><span></span></div>
            </div>
        </section>

        <section class="miro-public-content">
            <div class="miro-container">
                @yield('content')
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')
</body>
</html>
