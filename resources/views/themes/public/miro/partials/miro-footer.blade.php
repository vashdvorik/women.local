<footer class="miro-footer" id="contact">
    <div class="miro-container">
        <div class="miro-footer__top">
            <div class="miro-footer__brand">
                <a href="{{ url('/') }}" class="miro-brand" aria-label="Women Entrepreneurs Platform"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/logo-white.webp') }}" alt="Women Entrepreneurs Platform" class="miro-brand__logo"></a>
            <p><span data-lang="ru">Пространство для обучения, деловых связей, наставничества и развития бизнеса</span><span data-lang="en">A space for learning, business connections, mentorship and business growth.</span><span data-lang="ro">Un spațiu pentru învățare, conexiuni de afaceri, mentorat și dezvoltarea afacerii.</span></p>
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
                <h4><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></h4>
                @php
                    $footerPhone = config('site.contacts.phone');
                    $footerEmail = config('site.contacts.email');
                @endphp
                <ul>
                    <li><a href="{{ route('account.login') }}"><span data-lang="ru">Кабинет участницы</span><span data-lang="en">Participant cabinet</span><span data-lang="ro">Cabinetul membrei</span></a></li>
                    @if($footerPhone)
                        <li><span data-lang="ru">Тел:</span><span data-lang="en">Tel:</span><span data-lang="ro">Tel:</span> <a href="tel:{{ preg_replace('/[^\d+]/', '', $footerPhone) }}">{{ $footerPhone }}</a></li>
                    @endif
                    @if($footerEmail)
                        <li><span data-lang="ru">Почта:</span><span data-lang="en">Email:</span><span data-lang="ro">E-mail:</span> <a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a></li>
                    @endif
                </ul>
            </div>
            {{-- Подписка на новости: четвёртая колонка того же ряда, а не отдельная полоса. --}}
            @include('themes.public.miro.partials.subscribe-form')
        </div>
        <div class="miro-footer__bottom">
            <span>© {{ date('Y') }} Women Entrepreneurs Platform</span>
            <span><span data-lang="ru">Сделано для роста через связи</span><span data-lang="en">Made for growth through connection</span><span data-lang="ro">Creat pentru creștere prin conexiuni</span></span>
        </div>
    </div>
</footer>

