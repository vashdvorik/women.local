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
    <title>Women's hub — Events</title>
    <link rel="icon" type="image/png" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon-w4w.png') }}">
    <link rel="apple-touch-icon" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/apple-touch-icon.png') }}">
    <meta name="description" content="Events, news and opportunities for women entrepreneurs.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/css/events.css') }}">
    <link rel="stylesheet" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'events'])
    @if(false)
    <nav class="miro-nav" id="miro-nav">
        <div class="miro-container miro-nav__inner">
            <a href="{{ url('/') }}" class="miro-brand"><span class="miro-brand__mark">W</span><span>Women</span></a>
            <div class="miro-nav__links" id="miro-nav-links">
                <a href="{{ url('/') }}"><span data-lang="ru">Главная</span><span data-lang="en">Home</span><span data-lang="ro">Acasă</span></a>
                <a href="{{ url('/') }}#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a>
                <a href="{{ url('/') }}#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a>
                <a href="{{ route('members') }}"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a>
                <a href="{{ route('events') }}" class="is-active"><span data-lang="ru">Новости</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a>
                <a href="{{ url('/') }}#contact"><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></a>
                <div class="miro-nav__mobile-menu">
                    <div class="miro-languages" aria-label="Language switcher"><button type="button" data-locale="ru">RU</button><button type="button" data-locale="en">EN</button><button type="button" data-locale="ro">RO</button></div>
                    <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary"><span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span></a>
                    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary"><span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span></a>
                </div>
            </div>
            <div class="miro-nav__actions">
                <div class="miro-languages" aria-label="Language switcher"><button type="button" data-locale="ru">RU</button><button type="button" data-locale="en">EN</button><button type="button" data-locale="ro">RO</button></div>
                <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary miro-button--small"><span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span></a>
                <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary miro-button--small"><span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span></a>
                <button type="button" class="miro-mobile-toggle" id="miro-mobile-toggle" aria-label="Menu" aria-expanded="false" aria-controls="miro-nav-links">☰</button>
            </div>
        </div>
    </nav>
    @endif

    <main class="miro-events-page">
      <section class="miro-events-section">
            <div class="miro-container">
                <div class="miro-section__head">
                    <h2><span data-lang="ru">Встречи, которые развивают</span><span data-lang="en">Stay in the loop</span><span data-lang="ro">Rămâi la curent</span></h2>
                    <p><span data-lang="ru">Воркшопы, нетворкинг, программы, объявления и партнёрские возможности для предпринимательниц.</span><span data-lang="en">Workshops, networking, programmes, announcements and partnership opportunities for women entrepreneurs.</span><span data-lang="ro">Workshopuri, networking, programe, anunțuri și oportunități de parteneriat pentru antreprenoare.</span></p>
                </div>
                <div class="miro-events-grid">
                    @include('themes.public.miro.partials.event-cards', ['events' => $events, 'locales' => $locales])
                </div>

                {{-- «Смотреть ещё»: следующая порция новостей. Без скриптов это обычная ссылка на ?page=N, со скриптом
                     (js/load-more.js) карточки подгружаются в сетку, не перезагружая страницу. --}}
                @if(! empty($paginator) && $paginator->hasMorePages())
                    <div class="miro-events-more" data-load-more data-target=".miro-events-grid">
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="miro-button miro-button--secondary"><span data-lang="ru">Смотреть ещё</span><span data-lang="en">Load more</span><span data-lang="ro">Vezi mai multe</span></a>
                    </div>
                @endif

                <section class="miro-events-cta">
                    <h2><span data-lang="ru">Не пропускайте новые возможности</span><span data-lang="en">Don’t miss the next opportunity</span><span data-lang="ro">Nu rata următoarea oportunitate</span></h2>
                    <p><span data-lang="ru">Присоединяйтесь к сообществу, чтобы получать новости, приглашения и контакты в Telegram.</span><span data-lang="en">Join the community to receive news, invitations and new connections in Telegram.</span><span data-lang="ro">Alătură-te comunității pentru noutăți, invitații și conexiuni noi pe Telegram.</span></p>
                    <div class="miro-events-cta__actions">
                        <a href="{{ $communityUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--pink"><span data-lang="ru">Открыть Telegram</span><span data-lang="en">Open Telegram</span><span data-lang="ro">Deschide Telegram</span></a>
                        <a href="{{ route('account.login') }}" class="miro-button" style="border:1px solid rgba(255,255,255,.35);color:#fff"><span data-lang="ru">Войти в кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Intră în cabinet</span></a>
                    </div>
                </section>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')
    @if(false)
    <footer class="miro-footer" id="contact">
        <div class="miro-container">
            <div class="miro-footer__top">
                <div class="miro-footer__brand"><a href="{{ url('/') }}" class="miro-brand"><span class="miro-brand__mark">W</span><span>Women's hub</span></a><p><span data-lang="ru">Цифровое пространство для женщин-предпринимательниц из двух берегов.</span><span data-lang="en">A digital space for women entrepreneurs from both banks.</span><span data-lang="ro">Un spațiu digital pentru femeile antreprenoare de pe ambele maluri.</span></p></div>
                <div><h4><span data-lang="ru">Платформа</span><span data-lang="en">Platform</span><span data-lang="ro">Platformă</span></h4><ul><li><a href="{{ url('/') }}#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a></li><li><a href="{{ route('members') }}"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a></li><li><a href="{{ route('events') }}"><span data-lang="ru">Новости</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a></li></ul></div>
                <div><h4><span data-lang="ru">Ресурсы</span><span data-lang="en">Resources</span><span data-lang="ro">Resurse</span></h4><ul><li><a href="{{ url('/') }}#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a></li><li><a href="{{ url('/') }}#opportunities"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></a></li></ul></div>
                <div><h4><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></h4><ul><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">@WomenComBot</a></li><li><a href="{{ $managerUrl }}" target="_blank" rel="noopener">Project team</a></li><li><a href="{{ $communityUrl }}" target="_blank" rel="noopener">Community</a></li></ul></div>
                <div><h4><span data-lang="ru">Вход</span><span data-lang="en">Access</span><span data-lang="ro">Acces</span></h4><ul><li><a href="{{ route('account.login') }}"><span data-lang="ru">Кабинет участницы</span><span data-lang="en">Participant cabinet</span><span data-lang="ro">Cabinetul membrei</span></a></li><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">Telegram</a></li></ul></div>
            </div>
            <div class="miro-footer__bottom"><span>© {{ date('Y') }} Women's hub</span><span><span data-lang="ru">Сделано для роста через связи</span><span data-lang="en">Made for growth through connection</span><span data-lang="ro">Creat pentru creștere prin conexiuni</span></span></div>
        </div>
    </footer>
    @endif

    <script defer src="{{ asset_v('themes/public/'.($publicTheme ?? 'miro').'/js/load-more.js') }}"></script>
</body>
</html>


