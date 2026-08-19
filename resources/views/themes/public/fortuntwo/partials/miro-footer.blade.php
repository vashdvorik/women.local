<footer class="miro-footer" id="contact">
    <div class="miro-container">
        <div class="miro-footer__top">
            <div class="miro-footer__brand">
                <a href="{{ url('/') }}" class="miro-brand" aria-label="Women Entrepreneurs Platform"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortuntwo') . '/images/brand/logo.png') }}" alt="Women Entrepreneurs Platform" class="miro-brand__logo"></a>
                <p><span data-lang="ru">Цифровое пространство для женщин-предпринимательниц из двух берегов.</span><span data-lang="en">A digital space for women entrepreneurs from both banks.</span><span data-lang="ro">Un spațiu digital pentru femeile antreprenoare de pe ambele maluri.</span></p>
            </div>
            <div>
                <h4><span data-lang="ru">Платформа</span><span data-lang="en">Platform</span><span data-lang="ro">Platformă</span></h4>
                <ul>
                    <li><a href="{{ route('about') }}"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a></li>
                    <li><a href="{{ route('members') }}"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a></li>
                    <li><a href="{{ route('events') }}"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a></li>
                    <li><a href="{{ route('partners') }}"><span data-lang="ru">Партнёры</span><span data-lang="en">Partners</span><span data-lang="ro">Parteneri</span></a></li>
                </ul>
            </div>
            <div>
                <h4><span data-lang="ru">Ресурсы</span><span data-lang="en">Resources</span><span data-lang="ro">Resurse</span></h4>
                <ul>
                    <li><a href="{{ url('/') }}#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a></li>
                    <li><a href="{{ url('/') }}#opportunities"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></a></li>
                    <li><a href="{{ url('/') }}#stories"><span data-lang="ru">Истории</span><span data-lang="en">Stories</span><span data-lang="ro">Istorii</span></a></li>
                </ul>
            </div>
            <div>
                <h4><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></h4>
                <ul>
                    <li><a href="{{ $botUrl }}" target="_blank" rel="noopener">@WomenComBot</a></li>
                    <li><a href="{{ $managerUrl }}" target="_blank" rel="noopener"><span data-lang="ru">Команда проекта</span><span data-lang="en">Project team</span><span data-lang="ro">Echipa proiectului</span></a></li>
                    <li><a href="{{ $communityUrl }}" target="_blank" rel="noopener"><span data-lang="ru">Сообщество</span><span data-lang="en">Community</span><span data-lang="ro">Comunitate</span></a></li>
                </ul>
            </div>
            <div>
                <h4><span data-lang="ru">Вход</span><span data-lang="en">Access</span><span data-lang="ro">Acces</span></h4>
                <ul>
                    <li><a href="{{ route('account.login') }}"><span data-lang="ru">Кабинет участницы</span><span data-lang="en">Participant cabinet</span><span data-lang="ro">Cabinetul membrei</span></a></li>
                    <li><a href="{{ $botUrl }}" target="_blank" rel="noopener">Telegram</a></li>
                </ul>
            </div>
        </div>
        <div class="miro-footer__bottom">
            <span>© {{ date('Y') }} Women Entrepreneurs Platform</span>
            <span><span data-lang="ru">Сделано для роста через связи</span><span data-lang="en">Made for growth through connection</span><span data-lang="ro">Creat pentru creștere prin conexiuni</span></span>
        </div>
    </div>
</footer>

