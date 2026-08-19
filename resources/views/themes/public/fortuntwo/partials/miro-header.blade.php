@php($miroCurrentPage = $miroCurrentPage ?? null)

<nav class="miro-nav" id="miro-nav">
    <div class="miro-container miro-nav__inner">
        <a href="{{ url('/') }}" class="miro-brand" aria-label="Women Entrepreneurs Platform">
            <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortuntwo') . '/images/brand/logo.png') }}" alt="Women Entrepreneurs Platform" class="miro-brand__logo">
        </a>
        <div class="miro-nav__links" id="miro-nav-links">
            <div class="miro-nav__dropdown" data-nav-dropdown>
                <button type="button" class="miro-nav__dropdown-trigger" aria-expanded="false" aria-controls="miro-about-menu">
                    <span data-lang="ru">О нас</span><span data-lang="en">About us</span><span data-lang="ro">Despre noi</span>
                </button>
                <div class="miro-nav__dropdown-menu" id="miro-about-menu" role="menu">
                    <a href="{{ route('about.priorities') }}" role="menuitem"><span data-lang="ru">Приоритеты</span><span data-lang="en">Priorities</span><span data-lang="ro">Priorități</span></a>
                    <a href="{{ route('about.leadership') }}" role="menuitem"><span data-lang="ru">Руководство</span><span data-lang="en">Leadership</span><span data-lang="ro">Conducere</span></a>
                    <a href="{{ route('about.regulations') }}" role="menuitem"><span data-lang="ru">Положение</span><span data-lang="en">Regulations</span><span data-lang="ro">Regulament</span></a>
                    <a href="{{ route('about.reports') }}" role="menuitem"><span data-lang="ru">Отчёты</span><span data-lang="en">Reports</span><span data-lang="ro">Rapoarte</span></a>
                    <a href="{{ route('partners') }}" role="menuitem"><span data-lang="ru">Партнёры</span><span data-lang="en">Partners</span><span data-lang="ro">Parteneri</span></a>
                </div>
            </div>
            <div class="miro-nav__dropdown" data-nav-dropdown>
                <button type="button" class="miro-nav__dropdown-trigger" aria-expanded="false" aria-controls="miro-people-menu">
                    <span data-lang="ru">Люди</span><span data-lang="en">People</span><span data-lang="ro">Oameni</span>
                </button>
                <div class="miro-nav__dropdown-menu" id="miro-people-menu" role="menu">
                    <a href="{{ route('members.participants') }}" role="menuitem"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a>
                    <a href="{{ route('members.honorary') }}" role="menuitem"><span data-lang="ru">Почётные члены</span><span data-lang="en">Honorary members</span><span data-lang="ro">Membre onorifice</span></a>
                    <a href="{{ route('members.experts') }}" role="menuitem"><span data-lang="ru">Эксперты</span><span data-lang="en">Experts</span><span data-lang="ro">Experți</span></a>
                    <a href="{{ route('members.join') }}" role="menuitem"><span data-lang="ru">Как стать членом</span><span data-lang="en">How to become a member</span><span data-lang="ro">Cum să devii membră</span></a>
                </div>
            </div>
            <a href="{{ route('events') }}"><span data-lang="ru">Новости</span><span data-lang="en">News</span><span data-lang="ro">Știri</span></a>
            <a href="{{ route('gala') }}"><span data-lang="ru">Gala</span><span data-lang="en">Gala</span><span data-lang="ro">Gala</span></a>
            <a href="{{ route('projects') }}"><span data-lang="ru">Проекты</span><span data-lang="en">Projects</span><span data-lang="ro">Proiecte</span></a>
            <a href="{{ route('opportunities') }}"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></a>
            <div class="miro-nav__dropdown" data-nav-dropdown>
                <button type="button" class="miro-nav__dropdown-trigger" aria-expanded="false" aria-controls="miro-media-menu">
                    <span data-lang="ru">Медиатека</span><span data-lang="en">Media library</span><span data-lang="ro">Mediatecă</span>
                </button>
                <div class="miro-nav__dropdown-menu" id="miro-media-menu" role="menu">
                    <a href="{{ route('media.photos') }}" role="menuitem"><span data-lang="ru">Фото</span><span data-lang="en">Photos</span><span data-lang="ro">Fotografii</span></a>
                    <a href="{{ route('media.videos') }}" role="menuitem"><span data-lang="ru">Видео</span><span data-lang="en">Videos</span><span data-lang="ro">Video</span></a>
                    <a href="{{ route('media.publications') }}" role="menuitem"><span data-lang="ru">Публикации</span><span data-lang="en">Publications</span><span data-lang="ro">Publicații</span></a>
                </div>
            </div>
            <a href="{{ route('contact') }}"><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></a>
            <div class="miro-nav__mobile-menu">
                <div class="miro-languages" aria-label="Language switcher"><button type="button" data-locale="ru">RU</button><button type="button" data-locale="en">EN</button><button type="button" data-locale="ro">RO</button></div>
                <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary"><span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span></a>
                <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary miro-button--brand"><span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span></a>
            </div>
        </div>
        <div class="miro-nav__actions">
            <div class="miro-languages" aria-label="Language switcher"><button type="button" data-locale="ru">RU</button><button type="button" data-locale="en">EN</button><button type="button" data-locale="ro">RO</button></div>
            <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary miro-button--small"><span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span></a>
            <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary miro-button--brand miro-button--small"><span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span></a>
            <button type="button" class="miro-mobile-toggle" id="miro-mobile-toggle" aria-label="Menu" aria-expanded="false" aria-controls="miro-nav-links">☰</button>
        </div>
    </div>
</nav>

<script src="{{ asset('themes/public/' . ($publicTheme ?? 'fortuntwo') . '/js/navigation.js') }}"></script>
<script defer src="{{ asset('themes/public/' . ($publicTheme ?? 'fortuntwo') . '/js/motion.js') }}"></script>
