@php($miroCurrentPage = $miroCurrentPage ?? null)

<nav class="miro-nav" id="miro-nav">
    <div class="miro-container miro-nav__inner">
        <a href="{{ url('/') }}" class="miro-brand" aria-label="Women Entrepreneurs Platform">
            <img src="{{ asset('images/brand/logo.webp') }}" alt="Women Entrepreneurs Platform" class="miro-brand__logo">
        </a>
        <div class="miro-nav__links" id="miro-nav-links">
            <a href="{{ url('/') }}" class="{{ $miroCurrentPage === 'home' ? 'is-active' : '' }}"><span data-lang="ru">Главная</span><span data-lang="en">Home</span><span data-lang="ro">Acasă</span></a>
            <a href="{{ route('about') }}" class="{{ $miroCurrentPage === 'about' ? 'is-active' : '' }}"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a>
            <a href="{{ url('/') }}#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a>
            <a href="{{ route('members') }}" class="{{ $miroCurrentPage === 'members' ? 'is-active' : '' }}"><span data-lang="ru">Эксперты</span><span data-lang="en">Experts</span><span data-lang="ro">Experți</span></a>
            <a href="{{ route('events') }}" class="{{ $miroCurrentPage === 'events' ? 'is-active' : '' }}"><span data-lang="ru">Новости</span><span data-lang="en">News</span><span data-lang="ro">Știri</span></a>
            <a href="{{ route('partners') }}" class="{{ $miroCurrentPage === 'partners' ? 'is-active' : '' }}"><span data-lang="ru">Партнёры</span><span data-lang="en">Partners</span><span data-lang="ro">Parteneri</span></a>
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
