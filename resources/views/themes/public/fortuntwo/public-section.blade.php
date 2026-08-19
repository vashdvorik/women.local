@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $placeholder = $placeholder ?? false;
    $items = $items ?? [];
    $steps = $steps ?? [];
    $sectionTheme = View::exists('themes.public.' . ($publicTheme ?? 'fortuntwo') . '.partials.miro-header')
        ? ($publicTheme ?? 'fortuntwo')
        : 'miro';
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title['ru'] }} — Women Entrepreneurs Platform</title>
    <meta name="description" content="{{ $intro['ru'] }}">
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . $sectionTheme . '/images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/' . $sectionTheme . '/css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . $sectionTheme . '/css/public-section.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . $sectionTheme . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.' . $sectionTheme . '.partials.miro-header', ['miroCurrentPage' => null])

    <main class="fortun-public-page">
        <section class="fortun-public-hero">
            <div class="miro-container fortun-public-hero__inner">
                <div class="fortun-public-hero__copy">
                    <p class="miro-eyebrow"><span data-lang="ru">{{ $eyebrow['ru'] }}</span><span data-lang="en">{{ $eyebrow['en'] }}</span><span data-lang="ro">{{ $eyebrow['ro'] }}</span></p>
                    <h1><span data-lang="ru">{{ $title['ru'] }}</span><span data-lang="en">{{ $title['en'] }}</span><span data-lang="ro">{{ $title['ro'] }}</span></h1>
                    <p class="fortun-public-hero__intro"><span data-lang="ru">{{ $intro['ru'] }}</span><span data-lang="en">{{ $intro['en'] }}</span><span data-lang="ro">{{ $intro['ro'] }}</span></p>
                </div>
                <div class="fortun-public-hero__accent" aria-hidden="true"><span></span><span></span><span></span></div>
            </div>
        </section>

        <section class="fortun-public-content">
            <div class="miro-container">
                @if ($placeholder)
                    <div class="fortun-public-placeholder">
                        <span class="fortun-public-placeholder__mark">✦</span>
                        <h2><span data-lang="ru">Раздел готовится</span><span data-lang="en">This section is coming soon</span><span data-lang="ro">Această secțiune este în pregătire</span></h2>
                        <p><span data-lang="ru">Мы уже готовим материалы. Скоро здесь появится актуальная информация.</span><span data-lang="en">We are preparing the materials. Relevant information will appear here soon.</span><span data-lang="ro">Pregătim materialele. Informațiile relevante vor apărea în curând.</span></p>
                    </div>
                @endif

                @if (count($items))
                    <div class="fortun-public-items">
                        @foreach ($items as $item)
                            <article class="fortun-public-item">
                                <span class="fortun-public-item__number">{{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <h2><span data-lang="ru">{{ $item['ru'] }}</span><span data-lang="en">{{ $item['en'] }}</span><span data-lang="ro">{{ $item['ro'] }}</span></h2>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if (count($steps))
                    <div class="fortun-public-steps">
                        @foreach ($steps as $step)
                            <article class="fortun-public-step">
                                <span class="fortun-public-step__number">{{ $loop->iteration }}</span>
                                <h2><span data-lang="ru">{{ $step['title']['ru'] }}</span><span data-lang="en">{{ $step['title']['en'] }}</span><span data-lang="ro">{{ $step['title']['ro'] }}</span></h2>
                                <p><span data-lang="ru">{{ $step['text']['ru'] }}</span><span data-lang="en">{{ $step['text']['en'] }}</span><span data-lang="ro">{{ $step['text']['ro'] }}</span></p>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if (!empty($cta))
                    <div class="fortun-public-cta">
                        <h2><span data-lang="ru">Готовы присоединиться?</span><span data-lang="en">Ready to join?</span><span data-lang="ro">Ești gata să te alături?</span></h2>
                        <p><span data-lang="ru">Создайте профиль и начните находить свои связи и возможности.</span><span data-lang="en">Create your profile and start finding the right connections and opportunities.</span><span data-lang="ro">Creează-ți profilul și începe să găsești conexiunile și oportunitățile potrivite.</span></p>
                        <a href="{{ route('account.login') }}" class="miro-button miro-button--primary"><span data-lang="ru">Начать регистрацию</span><span data-lang="en">Start registration</span><span data-lang="ro">Începe înregistrarea</span></a>
                    </div>
                @endif
            </div>
        </section>
    </main>

    @include('themes.public.' . $sectionTheme . '.partials.miro-footer')
    <script src="{{ asset('themes/public/' . $sectionTheme . '/js/landing.js') }}"></script>
</body>
</html>
