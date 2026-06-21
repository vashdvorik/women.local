<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Онлайн-платформа для обучения, сотрудничества и развития женщин-предпринимательниц двух берегов.">
    <meta name="theme-color" content="#4c237e">
    <title>Women Entrepreneurs Platform</title>

    @if (is_file(public_path('build/manifest.json')) || is_file(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
        <script type="module">{!! file_get_contents(resource_path('js/app.js')) !!}</script>
    @endif
</head>
<body>
    <a class="skip-link" href="#main-content">Перейти к содержанию</a>

    <header class="site-header" data-header>
        <div class="page-shell header-inner">
            <a class="brand" href="#top" aria-label="Women Entrepreneurs Platform — главная">
                <span class="brand-symbol" aria-hidden="true">
                    <svg viewBox="0 0 58 58">
                        <path class="brand-purple" d="M26 5c-9 5-13 13-11 22 1 5 5 9 10 12-7-2-13-7-15-14C7 15 14 6 26 2v3Z"/>
                        <path class="brand-teal" d="M32 7c9 4 14 12 13 21-1 7-5 12-12 16 4-5 6-10 5-16-1-8-6-14-13-18l7-3Z"/>
                        <path class="brand-gold" d="M39 10c8 6 10 15 6 23-2 5-6 9-12 12 9-9 9-21 1-31l5-4Z"/>
                        <path class="brand-line" d="M18 9c-2 8 0 14 6 19-4 5-3 11 3 17M29 10c-5 6-5 13-1 19-4 5-4 10 1 15"/>
                    </svg>
                </span>
                <span class="brand-text"><strong>Women<br>Entrepreneurs<br>Platform</strong></span>
            </a>

            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle>
                <span class="sr-only">Открыть меню</span><span></span><span></span><span></span>
            </button>

            <nav class="primary-navigation" id="primary-navigation" aria-label="Основная навигация" data-menu>
                <a href="#about">О платформе</a>
                <a href="#learning">Обучение</a>
                <a href="#members">Сообщество</a>
                <a href="#events">События</a>
                <a href="#opportunities">Возможности</a>
            </nav>

            <div class="header-actions">
                <span class="language-button" aria-label="Текущий язык: русский">RU</span>
                <a class="button button-outline button-header" href="#join">Войти</a>
                <a class="button button-purple button-header" href="#join">Присоединиться</a>
            </div>
        </div>
    </header>

    <main id="main-content">
        <section class="hero" id="top">
            <div class="page-shell hero-grid">
                <div class="hero-copy reveal">
                    <span class="hero-eyebrow">Платформа женщин-предпринимательниц</span>
                    <h1>Развивайте бизнес вместе с сильным сообществом</h1>
                    <p>Находите партнёров и наставников, проходите практическое обучение и получайте возможности, соответствующие вашим целям.</p>
                    <div class="hero-actions">
                        <a class="button button-purple" href="#join">Создать профиль <span>›</span></a>
                        <a class="button button-teal-outline" href="#opportunities">Посмотреть возможности <span>›</span></a>
                    </div>
                    <div class="hero-goal">
                        <span class="round-icon icon-teal" aria-hidden="true">
                            <svg viewBox="0 0 32 32"><circle cx="11" cy="11" r="4"/><circle cx="22" cy="12" r="3.5"/><path d="M3 27c0-6 3-9 8-9s8 3 8 9M18 20c1-1.3 2.7-2 4.7-2 4 0 6.3 3 6.3 8"/></svg>
                        </span>
                        <span><strong>500+</strong><small>женщин планируется объединить</small></span>
                    </div>
                </div>

                <img class="hero-photo reveal reveal-delay" src="{{ asset('images/generated/hero-community.webp') }}" width="1536" height="1024" alt="Четыре женщины-предпринимательницы работают вместе за ноутбуком" decoding="async" fetchpriority="high">
            </div>
            <div class="hero-wave" aria-hidden="true"><span></span><span></span><span></span></div>
        </section>

        <section class="content-section benefits-section" id="about">
            <div class="page-shell">
                <div class="section-intro reveal"><span class="section-label">Возможности платформы</span><h2>Всё необходимое для роста бизнеса — в одном месте</h2><p>Используйте только те инструменты, которые нужны вам сейчас.</p></div>
                <div class="benefit-grid">
                    <article class="benefit-card reveal">
                        <span class="feature-circle purple"><svg viewBox="0 0 36 36"><path d="M5 9h26v18H5z"/><path d="m5 10 13 10 13-10M12 29h12"/></svg></span>
                        <h3>Центр обучения</h3><p>Онлайн-курсы, видео и практические бизнес-модули.</p>
                    </article>
                    <article class="benefit-card reveal">
                        <span class="feature-circle teal"><svg viewBox="0 0 36 36"><circle cx="13" cy="12" r="5"/><circle cx="26" cy="13" r="4"/><path d="M4 31c0-7 3-11 9-11s9 4 9 11M21 22c1-2 3-3 6-3 5 0 7 4 7 10"/></svg></span>
                        <h3>Каталог участниц</h3><p>Находите бизнесы, партнёров и полезные контакты.</p>
                    </article>
                    <article class="benefit-card reveal">
                        <span class="feature-circle gold"><svg viewBox="0 0 36 36"><path d="M18 3v30M11 8c-5 4-7 9-5 15M25 8c5 4 7 9 5 15M10 18h16M13 12l5 6 5-6M13 25l5-7 5 7"/></svg></span>
                        <h3>AI-подбор</h3><p>Рекомендации партнёров, наставников и возможностей.</p>
                    </article>
                    <article class="benefit-card reveal" id="opportunities">
                        <span class="feature-circle purple"><svg viewBox="0 0 36 36"><rect x="5" y="8" width="26" height="23" rx="2"/><path d="M5 15h26M11 4v8M25 4v8M11 21h5M21 21h5M11 26h5"/></svg></span>
                        <h3>События и возможности</h3><p>Тренинги, форумы, гранты и деловые встречи.</p>
                    </article>
                    <article class="benefit-card reveal">
                        <span class="feature-circle teal"><svg viewBox="0 0 36 36"><circle cx="18" cy="11" r="6"/><path d="M7 31c0-8 4-13 11-13s11 5 11 13M26 6l2 3 4 1-3 3 1 4-4-2-3 2"/></svg></span>
                        <h3>Наставничество</h3><p>Доступ к экспертам и опытным предпринимательницам.</p>
                    </article>
                    <article class="benefit-card reveal">
                        <span class="feature-circle gold"><svg viewBox="0 0 36 36"><path d="m4 17 28-11-8 25-8-9-6 5 1-10L28 8 9 21l-5-4Z"/></svg></span>
                        <h3>Telegram-уведомления</h3><p>Важные новости и персональные напоминания.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="content-section process-section">
            <div class="page-shell">
                <div class="section-intro centered reveal"><span class="section-label">Понятный старт</span><h2>От регистрации до новых деловых возможностей</h2></div>
                <ol class="process-grid">
                    <li class="process-step reveal">
                        <div class="process-icon teal"><svg viewBox="0 0 42 42"><circle cx="21" cy="15" r="7"/><path d="M8 38c0-10 5-16 13-16s13 6 13 16"/></svg><b>1</b></div>
                        <h3>Регистрация</h3><p>Быстро создайте аккаунт и присоединитесь к сообществу.</p>
                    </li>
                    <li class="process-step reveal">
                        <div class="process-icon purple"><svg viewBox="0 0 42 42"><rect x="7" y="8" width="28" height="26" rx="3"/><circle cx="16" cy="17" r="4"/><path d="M11 29c0-5 2-8 5-8s5 3 5 8M25 15h6M25 20h6M25 25h6"/></svg><b>2</b></div>
                        <h3>Создание профиля</h3><p>Расскажите о себе, своём бизнесе и целях.</p>
                    </li>
                    <li class="process-step reveal">
                        <div class="process-icon teal"><svg viewBox="0 0 42 42"><circle cx="14" cy="15" r="5"/><circle cx="29" cy="16" r="4"/><path d="M4 36c0-8 4-13 10-13s10 5 10 13M23 25c2-2 4-3 7-3 6 0 9 5 9 13"/></svg><b>3</b></div>
                        <h3>Обучение и связи</h3><p>Изучайте материалы и общайтесь с участницами.</p>
                    </li>
                    <li class="process-step reveal">
                        <div class="process-icon gold"><svg viewBox="0 0 42 42"><path d="M6 36h31M10 31V21h6v10M20 31V14h6v17M30 31V7h6v24M8 17l9-7 7 3 11-9"/></svg><b>4</b></div>
                        <h3>Развитие бизнеса</h3><p>Находите возможности, партнёров и новые рынки.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="content-section showcase-section" id="learning">
            <div class="page-shell showcase-grid">
                <section class="members-panel" id="members">
                    <div class="panel-heading reveal"><div><span class="section-label">Проверенные профили</span><h2>Участницы сообщества</h2></div><a href="#join">Открыть каталог <span>→</span></a></div>
                    <div class="member-card-grid">
                        <article class="member-card reveal">
                            <img class="media-image" src="{{ asset('images/generated/member-fashion.webp') }}" width="800" height="1200" alt="Анна Петрова, предпринимательница в сфере моды и дизайна" loading="lazy" decoding="async">
                            <h3>Анна Петрова</h3><p>Мода и дизайн</p>
                            <div><span class="tag purple-tag">Ищу партнёров</span><span class="tag teal-tag">Интересует экспорт</span></div>
                        </article>
                        <article class="member-card reveal">
                            <img class="media-image" src="{{ asset('images/generated/member-agrifood.webp') }}" width="569" height="1200" alt="Елена Русу, предпринимательница в агропродовольственном секторе" loading="lazy" decoding="async">
                            <h3>Елена Русу</h3><p>Агропродовольственный сектор</p>
                            <div><span class="tag gold-tag">Готова наставлять</span><span class="tag purple-tag">Ищу партнёров</span></div>
                        </article>
                        <article class="member-card reveal">
                            <img class="media-image" src="{{ asset('images/generated/member-digital.webp') }}" width="800" height="1200" alt="Марина Ионеску, предпринимательница в сфере IT и цифровых услуг" loading="lazy" decoding="async">
                            <h3>Марина Ионеску</h3><p>IT и digital-услуги</p>
                            <div><span class="tag teal-tag">Предлагаю услуги</span><span class="tag gold-tag">Готова наставлять</span></div>
                        </article>
                    </div>
                </section>

                <section class="events-panel" id="events">
                    <div class="panel-heading reveal"><div><span class="section-label teal-label">Развитие и связи</span><h2>Ближайшие события</h2></div><a class="teal-link" href="#join">Все события <span>→</span></a></div>
                    <div class="event-card-grid">
                        <article class="event-card reveal">
                            <img class="media-image" src="{{ asset('images/generated/event-workshop.webp') }}" width="1200" height="675" alt="Участницы бизнес-школы обсуждают рабочие материалы" loading="lazy" decoding="async">
                            <h3>Бизнес-школа для женщин</h3><div class="event-info"><b>18<span>ИЮН</span></b><p>Кишинёв, онлайн<br>Практические занятия</p></div><a href="#join">Регистрация</a>
                        </article>
                        <article class="event-card reveal">
                            <img class="media-image" src="{{ asset('images/generated/event-esg.webp') }}" width="1200" height="675" alt="Онлайн-вебинар об ESG и устойчивом бизнесе" loading="lazy" decoding="async">
                            <h3>ESG для малого бизнеса</h3><div class="event-info"><b>25<span>ИЮН</span></b><p>Онлайн-вебинар<br>Принципы и практика</p></div><a href="#join">Регистрация</a>
                        </article>
                        <article class="event-card reveal">
                            <img class="media-image" src="{{ asset('images/generated/event-networking.webp') }}" width="1200" height="675" alt="Участницы регионального форума общаются во время деловой встречи" loading="lazy" decoding="async">
                            <h3>Региональный форум</h3><div class="event-info"><b>09<span>ИЮН</span></b><p>Деловые знакомства<br>и партнёрства</p></div><a href="#join">Регистрация</a>
                        </article>
                    </div>
                </section>
            </div>
        </section>

        <section class="content-section stories-section" id="stories">
            <div class="page-shell">
                <div class="section-intro centered reveal"><span class="section-label">Результаты сообщества</span><h2>Истории, за которыми стоят реальные шаги</h2><p>Публикуем опыт участниц только с их согласия и после проверки фактов.</p></div>
                <div class="story-grid">
                    <article class="story-card reveal">
                        <img class="media-image" src="{{ asset('images/generated/story-mentor.webp') }}" width="1200" height="900" alt="Предпринимательница и наставница в рабочем кабинете" loading="lazy" decoding="async">
                        <div><span class="quote">“</span><h3>Как наставничество помогло расширить мой бизнес</h3><p>Поддержка наставницы помогла улучшить стратегию, выйти на новые рынки и подготовить следующий этап развития.</p><a href="#join">Читать далее <span>→</span></a></div>
                    </article>
                    <article class="story-card reveal">
                        <img class="media-image" src="{{ asset('images/generated/story-export.webp') }}" width="1200" height="900" alt="Предпринимательница в студии локального бренда" loading="lazy" decoding="async">
                        <div><span class="quote teal-quote">“</span><h3>От локального к международному вместе с платформой</h3><p>События, деловые связи и обучение помогли найти партнёров и начать подготовку к экспорту.</p><a class="teal-link" href="#join">Читать далее <span>→</span></a></div>
                    </article>
                </div>
            </div>
        </section>

        <section class="join-section" id="join">
            <div class="page-shell join-inner">
                <h2>Сделайте первый шаг к новым возможностям</h2>
                <p>Создание профиля бесплатно и занимает около восьми минут. Вы сами выбираете, какие данные будут видны другим.</p>
                <a class="button button-gold" href="#top">Создать бесплатный профиль</a>
            </div>
        </section>
    </main>

    <footer class="site-footer" id="contact">
        <div class="page-shell footer-grid">
            <div class="footer-about"><h3>О платформе</h3><p>Мы поддерживаем женщин-предпринимательниц, предоставляя доступ к знаниям, деловым связям и возможностям для устойчивого роста.</p></div>
            <div><h3>Быстрые ссылки</h3><div class="footer-links"><a href="#about">О платформе</a><a href="#learning">Обучение</a><a href="#members">Участницы</a><a href="#events">События</a><a href="#opportunities">Возможности</a><a href="#stories">Истории успеха</a><a href="#contact">Контакты</a><a href="#join">FAQ</a></div></div>
            <div><h3>Контакты</h3><ul class="contact-list"><li>Кишинёв, Молдова</li><li>info@weplatform.org</li><li>+00 123 456 7890</li><li>www.weplatform.org</li></ul></div>
            <div><h3>Мы в социальных сетях</h3><div class="socials"><a href="#contact">f</a><a href="#contact">in</a><a href="#contact">◎</a><a href="#contact">▶</a></div><div class="footer-faces" aria-hidden="true"><span></span><span></span><span></span></div></div>
        </div>
        <div class="footer-bottom">♡ &nbsp; Проект реализуется при поддержке партнёров</div>
    </footer>
</body>
</html>
