@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $locales = ['ru', 'en', 'ro'];
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform — Experts</title>
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon.png') }}">
    <meta name="description" content="Public catalogue of experts and leaders of Women Entrepreneurs Platform.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/experts.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'experts'])

    <main class="miro-members-page">

        <section class="miro-members-section">
            <div class="miro-container">
                <div class="miro-section__head">
                    <h2><span data-lang="ru">Наши эксперты</span><span data-lang="en">Our experts</span><span data-lang="ro">Experții noștri</span></h2>
                    <p><span data-lang="ru">Эксперты, основательницы и лидеры, которые открыты к новым связям, идеям и совместным возможностям.</span><span data-lang="en">Experts, founders and leaders who are open to new connections, ideas and shared opportunities.</span><span data-lang="ro">Experte, fondatoare și lideri deschiși către conexiuni, idei și oportunități comune.</span></p>
                </div>
                <div class="miro-members-grid">
                    @foreach($experts as $expert)
                        <article class="miro-public-member">
                            <div class="miro-public-member__visual" style="background:var(--miro-{{ $expert->toneKey() }});">
                                @if($expert->photoUrl())<img src="{{ $expert->photoUrl() }}" alt="{{ $expert->field('name', 'en') }}" loading="lazy">@endif
                            </div>
                            <div class="miro-public-member__body">
                                <h3>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $expert->field('name', $locale) }}</span>@endforeach</h3>
                                <p class="miro-public-member__specialization">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $expert->field('role', $locale) }}</span>@endforeach</p>
                                <p class="miro-public-member__description">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $expert->field('description', $locale) }}</span>@endforeach</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <section class="miro-members-cta">
                    <h2><span data-lang="ru">Зарегистрируйтесь, чтобы связаться</span><span data-lang="en">Register to make the connection</span><span data-lang="ro">Înregistrează-te pentru a lua legătura</span></h2>
                    <p><span data-lang="ru">Создайте профиль на платформе, чтобы находить нужных людей и обращаться к ним напрямую.</span><span data-lang="en">Create your platform profile to find the right people and reach out directly.</span><span data-lang="ro">Creează-ți profilul pentru a găsi oamenii potriviți și a lua legătura direct.</span></p>
                    <div class="miro-members-cta__actions">
                        <a href="{{ route('account.login') }}" class="miro-button miro-button--pink"><span data-lang="ru">Войти в кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Intră în cabinet</span></a>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button" style="border:1px solid rgba(255,255,255,.35);color:#fff"><span data-lang="ru">Присоединиться через Telegram</span><span data-lang="en">Join via Telegram</span><span data-lang="ro">Alătură-te prin Telegram</span></a>
                    </div>
                </section>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')
</body>
</html>

