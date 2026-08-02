@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform</title>
    <meta name="description" content="Пространство для женщин-предпринимательниц: обучение, контакты, AI-рекомендации и возможности роста.">
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/regular/style.css">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.fortun.partials.miro-header', ['miroCurrentPage' => 'home'])
    @if(false)
    <nav class="miro-nav" id="miro-nav">
        <div class="miro-container miro-nav__inner">
            <a href="#top" class="miro-brand">
                <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/brand/logo.webp') }}" alt="Women Entrepreneurs Platform" class="miro-brand__logo">
                <span>Women</span>
            </a>
            <div class="miro-nav__links" id="miro-nav-links">
                <a href="#top"><span data-lang="ru">Главная</span><span data-lang="en">Home</span><span data-lang="ro">Acasă</span></a>
                <a href="#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a>
                <a href="#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a>
                <a href="{{ route('members') }}"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a>
                <a href="{{ route('events') }}"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a>
                <a href="#opportunities"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></a>
                <a href="{{ route('contact') }}"><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></a>
                <div class="miro-nav__mobile-menu">
                    <div class="miro-languages" aria-label="Language switcher">
                        <button type="button" data-locale="ru">RU</button>
                        <button type="button" data-locale="en">EN</button>
                        <button type="button" data-locale="ro">RO</button>
                    </div>
                    <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary">
                        <span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span>
                    </a>
                    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary miro-button--brand">
                        <span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span>
                    </a>
                </div>
            </div>
            <div class="miro-nav__actions">
                <div class="miro-languages" aria-label="Language switcher">
                    <button type="button" data-locale="ru">RU</button>
                    <button type="button" data-locale="en">EN</button>
                    <button type="button" data-locale="ro">RO</button>
                </div>
                <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary miro-button--small">
                    <span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span>
                </a>
                <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary miro-button--small">
                    <span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span>
                </a>
                <button type="button" class="miro-mobile-toggle" id="miro-mobile-toggle" aria-label="Menu" aria-expanded="false" aria-controls="miro-nav-links">☰</button>
            </div>
        </div>
    </nav>
    @endif

    <main id="top">
        <section class="miro-hero miro-hero--image">
            <div class="miro-container">
                <div class="miro-hero__grid">
                    <div class="miro-hero__content">
                        <h1>
                            <span data-lang="ru"><br> ПЛАТФОРМА<br>ЖЕНЩИН<br>ПРЕДПРИНИМАТЕЛЕЙ</span>
                            <span data-lang="en"><br> PLATFORM<br>FOR WOMEN<br>ENTREPRENEURS</span>
                            <span data-lang="ro"><br> PLATFORMĂ<br>PENTRU FEMEI<br>ANTREPRENOARE</span>
                        </h1>
                        <p class="miro-hero__subtitle">
                            <span data-lang="ru">Пространство для обучения, деловых связей, 
наставничества и развития бизнеса</span>
                            <span data-lang="en">A digital space for learning, networking, mentorship, and business growth across the region.</span>
                            <span data-lang="ro">Un spațiu digital pentru învățare, networking, mentorat și creșterea afacerilor în regiune.</span>
                        </p>
                        <div class="miro-hero__actions">
                            <a href="{{ route('account.login') }}" class="miro-button miro-button--primary miro-button--brand">
                                <span data-lang="ru">Присоединиться к платформе</span><span data-lang="en">Join the Platform</span><span data-lang="ro">Alătură-te platformei</span>
                            </a>
                            <a href="{{ route('members') }}" class="miro-button miro-button--secondary">
                                <span data-lang="ru">Наши эксперты</span><span data-lang="en">Our Experts</span><span data-lang="ro">Experții noștri</span>
                            </a>
                        </div>
                        <div class="miro-proof">
                            <span class="miro-proof__icon" aria-hidden="true"><i class="ph ph-users-three"></i></span>
                            <span class="miro-proof__copy">
                                <strong class="miro-proof__value">500+</strong>
                                <span class="miro-proof__label"><span data-lang="ru">женщин уже объединены в сообщество</span><span data-lang="en">women already connected through the community</span><span data-lang="ro">de femei deja conectate în comunitate</span></span>
                            </span>
                        </div>
                    </div>
                    <div class="miro-hero__visual" aria-label="Women entrepreneurs collaborating">
                        <div class="miro-hero__image">
                            <img src="{{ asset('themes/public/fortun/images/herobaner.webp') }}" alt="Women entrepreneurs collaborating around a laptop">
                        </div>
                    </div>
                </div>

            </div>
        </section>
        <section class="fortun-directions" aria-label="Platform directions">
            <div class="miro-container fortun-directions__grid">
                <article class="fortun-direction-card fortun-direction-card--support">
                    <div class="fortun-direction-card__icon fortun-direction-card__icon--support" aria-hidden="true">
                        <i class="ph ph-hand-palm fortun-support-hand fortun-support-hand--left"></i>
                        <i class="ph ph-hand-palm fortun-support-hand fortun-support-hand--right"></i>
                        <i class="ph ph-gender-female fortun-support-symbol"></i>
                    </div>
                    <h3><span data-lang="ru">Поддержка и <br>наставничество</span><span data-lang="en">Support<br>and mentorship</span><span data-lang="ro">Sprijin<br>și mentorat</span></h3>
                </article>
                <article class="fortun-direction-card fortun-direction-card--award">
                    <div class="fortun-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-crown"></i>
                    </div>
                    <h3><span data-lang="ru">Премия<br>«Женщина года»</span><span data-lang="en">Award<br>“Woman of the Year”</span><span data-lang="ro">Premiul<br>„Femeia anului”</span></h3>
                </article>
                <article class="fortun-direction-card fortun-direction-card--learning">
                    <div class="fortun-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-graduation-cap"></i>
                    </div>
                    <h3><span data-lang="ru">Обучение<br>и развитие</span><span data-lang="en">Learning<br>and growth</span><span data-lang="ro">Învățare<br>și dezvoltare</span></h3>
                </article>
                <article class="fortun-direction-card fortun-direction-card--community">
                    <div class="fortun-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-users-three"></i>
                    </div>
                    <h3><span data-lang="ru">Нетворкинг<br>и сообщество</span><span data-lang="en">Networking<br>and community</span><span data-lang="ro">Networking<br>și comunitate</span></h3>
                </article>
                <article class="fortun-direction-card fortun-direction-card--business">
                    <div class="fortun-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-briefcase"></i>
                    </div>
                    <h3><span data-lang="ru">Бизнес<br>и рост</span><span data-lang="en">Business<br>and growth</span><span data-lang="ro">Afaceri<br>și creștere</span></h3>
                </article>
                <article class="fortun-direction-card fortun-direction-card--visibility">
                    <div class="fortun-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-megaphone"></i>
                    </div>
                    <h3><span data-lang="ru">Продвижение<br>и видимость</span><span data-lang="en">Promotion<br>and visibility</span><span data-lang="ro">Promovare<br>și vizibilitate</span></h3>
                </article>
                <article class="fortun-direction-card fortun-direction-card--resources">
                    <div class="fortun-direction-card__icon fortun-direction-card__icon--resources" aria-hidden="true">
                        <i class="ph ph-file-text"></i>
                        <i class="ph ph-check fortun-resource-check"></i>
                    </div>
                    <h3><span data-lang="ru">Ресурсы<br>и эксперты</span><span data-lang="en">Resources<br>and experts</span><span data-lang="ro">Resurse<br>și experți</span></h3>
                </article>
                <article class="fortun-direction-card fortun-direction-card--partnership">
                    <div class="fortun-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-handshake"></i>
                    </div>
                    <h3><span data-lang="ru">Партнёрство<br>и проекты</span><span data-lang="en">Partnership<br>and projects</span><span data-lang="ro">Parteneriat<br>și proiecte</span></h3>
                </article>
            </div>
        </section>
        <section class="miro-logo-wall">
            <div class="miro-container miro-logo-wall__layout">
                <div class="miro-logo-wall__copy">
                    <p><span data-lang="ru">Цифровое пространство для развития бизнеса с обоих берегов</span><span data-lang="en">One digital space for women entrepreneurs across both banks</span><span data-lang="ro">Un spațiu digital pentru femei antreprenoare de pe ambele maluri</span></p>
                    <div class="miro-logo-wall__items">
                        <span><span data-lang="ru">Видимость бизнеса</span><span data-lang="en">Business visibility</span><span data-lang="ro">Vizibilitatea afacerii</span></span>
                        <span><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></span>
                        <span><span data-lang="ru">Партнёры и рынки</span><span data-lang="en">Partners &amp; markets</span><span data-lang="ro">Partenere și piețe</span></span>
                        <span><span data-lang="ru">Наставничество</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></span>
                        <span><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></span>
                        <span><span data-lang="ru">Запросы и предложения</span><span data-lang="en">Requests &amp; offers</span><span data-lang="ro">Solicitări și oferte</span></span>
                    </div>
                </div>
                <div class="miro-logo-wall__visual" aria-hidden="true">
                    <div class="miro-logo-wall__photo">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/333.png') }}" alt="">
                    </div>
                    <span class="miro-logo-wall__sticker miro-logo-wall__sticker--brand"><span data-lang="ru">Связи, которые работают</span><span data-lang="en">Connections that move business</span><span data-lang="ro">Conexiuni care dezvoltă afaceri</span></span>
                </div>
            </div>
        </section>



        <section class="miro-section" id="benefits">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Зачем присоединяться</span><span data-lang="en">Why join?</span><span data-lang="ro">De ce să te alături?</span></p>
                    <h2><span data-lang="ru">Ключевые преимущества платформы</span><span data-lang="en">Key benefits for your next step</span><span data-lang="ro">Beneficii pentru următorul tău pas</span></h2>
                    <p><span data-lang="ru">Всё необходимое для обучения, полезных знакомств и роста бизнеса — в одном понятном пространстве.</span><span data-lang="en">Everything you need to learn, connect and grow your business in one clear space.</span><span data-lang="ro">Tot ce ai nevoie pentru a învăța, a te conecta și a-ți dezvolta afacerea într-un singur spațiu clar.</span></p>
                </div>
                <div class="miro-benefits">
                    <article class="miro-benefit-card miro-benefit-card--pink">
                        <div class="miro-benefit-card__icon">↗</div>
                        <h3><span data-lang="ru">Центр обучения</span><span data-lang="en">Learning Hub</span><span data-lang="ro">Hub de învățare</span></h3>
                        <p><span data-lang="ru">Онлайн-курсы, видео и практические модули для бизнеса.</span><span data-lang="en">Online courses, videos and practical business modules.</span><span data-lang="ro">Cursuri online, videoclipuri și module practice pentru afaceri.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--teal">
                        <div class="miro-benefit-card__icon">◎</div>
                        <h3><span data-lang="ru">Каталог участниц</span><span data-lang="en">Member Directory</span><span data-lang="ro">Directorul membrelor</span></h3>
                        <p><span data-lang="ru">Находите предпринимательниц и легко открывайте полезные связи.</span><span data-lang="en">Discover women-led businesses and connect easily.</span><span data-lang="ro">Descoperă afaceri conduse de femei și conectează-te ușor.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--rose">
                        <div class="miro-benefit-card__icon">✦</div>
                        <h3><span data-lang="ru">AI-подбор</span><span data-lang="en">AI Matching</span><span data-lang="ro">Potrivire AI</span></h3>
                        <p><span data-lang="ru">Умные рекомендации партнёров, менторов и возможностей.</span><span data-lang="en">Smart recommendations for partners, mentors and opportunities.</span><span data-lang="ro">Recomandări inteligente pentru partenere, mentori și oportunități.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--coral">
                        <div class="miro-benefit-card__icon">◫</div>
                        <h3><span data-lang="ru">События и возможности</span><span data-lang="en">Events &amp; Opportunities</span><span data-lang="ro">Evenimente și oportunități</span></h3>
                        <p><span data-lang="ru">Тренинги, форумы, гранты и встречи для сотрудничества.</span><span data-lang="en">Trainings, forums, grants and meetings for collaboration.</span><span data-lang="ro">Traininguri, forumuri, granturi și întâlniri pentru colaborare.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--orange">
                        <div class="miro-benefit-card__icon">↔</div>
                        <h3><span data-lang="ru">Менторство</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></h3>
                        <p><span data-lang="ru">Получайте советы от опытных предпринимательниц и эксперток.</span><span data-lang="en">Access advice from experienced women entrepreneurs.</span><span data-lang="ro">Primește sfaturi de la antreprenoare cu experiență.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--surface">
                        <div class="miro-benefit-card__icon">➤</div>
                        <h3><span data-lang="ru">Уведомления Telegram</span><span data-lang="en">Telegram Alerts</span><span data-lang="ro">Alerte Telegram</span></h3>
                        <p><span data-lang="ru">Будьте в курсе новостей и получайте персональные уведомления.</span><span data-lang="en">Stay updated with news and tailored notifications.</span><span data-lang="ro">Rămâi la curent cu noutățile și notificările personalizate.</span></p>
                    </article>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--soft" id="how-it-works">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Как это работает</span><span data-lang="en">How it works</span><span data-lang="ro">Cum funcționează</span></p>
                    <h2><span data-lang="ru">Четыре шага от идеи к действию</span><span data-lang="en">Four steps from idea to action</span><span data-lang="ro">Patru pași de la idee la acțiune</span></h2>
                </div>
                <div class="miro-steps">
                    <article class="miro-step">
                        <div class="miro-step__number">01</div>
                        <h3><span data-lang="ru">Зарегистрируйтесь</span><span data-lang="en">Register</span><span data-lang="ro">Înregistrează-te</span></h3>
                        <p><span data-lang="ru">Быстро присоединитесь к нашему сообществу.</span><span data-lang="en">Sign up quickly and join our community.</span><span data-lang="ro">Înscrie-te rapid și alătură-te comunității.</span></p>
                    </article>
                    <article class="miro-step">
                        <div class="miro-step__number">02</div>
                        <h3><span data-lang="ru">Создайте профиль</span><span data-lang="en">Create your profile</span><span data-lang="ro">Creează-ți profilul</span></h3>
                        <p><span data-lang="ru">Расскажите о себе и своём бизнесе.</span><span data-lang="en">Tell us about yourself and your business.</span><span data-lang="ro">Spune-ne despre tine și afacerea ta.</span></p>
                    </article>
                    <article class="miro-step">
                        <div class="miro-step__number">03</div>
                        <h3><span data-lang="ru">Учитесь и знакомьтесь</span><span data-lang="en">Learn &amp; connect</span><span data-lang="ro">Învață și conectează-te</span></h3>
                        <p><span data-lang="ru">Используйте материалы и знакомьтесь с участницами и экспертами.</span><span data-lang="en">Access resources and connect with members and experts.</span><span data-lang="ro">Accesează resurse și conectează-te cu membre și experți.</span></p>
                    </article>
                    <article class="miro-step">
                        <div class="miro-step__number">04</div>
                        <h3><span data-lang="ru">Растите бизнес</span><span data-lang="en">Grow your business</span><span data-lang="ro">Crește-ți afacerea</span></h3>
                        <p><span data-lang="ru">Находите возможности, сотрудничайте и развивайтесь.</span><span data-lang="en">Find opportunities, collaborate and grow.</span><span data-lang="ro">Găsește oportunități, colaborează și crește.</span></p>
                    </article>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="features">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Возможности</span><span data-lang="en">What you can do</span><span data-lang="ro">Ce poți face</span></p>
                    <h2><span data-lang="ru">От идеи к следующему действию</span><span data-lang="en">From an idea to the next action</span><span data-lang="ro">De la idee la următoarea acțiune</span></h2>
                    <p><span data-lang="ru">Платформа помогает не просто хранить информацию, а превращать её в движение.</span><span data-lang="en">The platform turns information into momentum, not just another place to store it.</span><span data-lang="ro">Platforma transformă informația în mișcare, nu doar într-un alt loc de stocare.</span></p>
                </div>
                <div class="miro-split">
                    <div class="miro-split__copy">
                        <span class="miro-tag"><span data-lang="ru">AI-powered</span><span data-lang="en">AI-powered</span><span data-lang="ro">Cu AI</span></span>
                        <h3 style="margin-top: 18px;"><span data-lang="ru">Найдите контакт, который нужен именно сейчас</span><span data-lang="en">Find the connection you need right now</span><span data-lang="ro">Găsește conexiunea de care ai nevoie acum</span></h3>
                        <p><span data-lang="ru">Опишите свой запрос обычными словами. AI сопоставит его с профилями участниц и покажет близкие варианты.</span><span data-lang="en">Describe your request in your own words. AI matches it with member profiles and surfaces relevant options.</span><span data-lang="ro">Descrie cererea în cuvintele tale. AI o potrivește cu profilurile membrelor.</span></p>
                        <ul class="miro-list">
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Поиск партнёров, экспертов и клиентов</span><span data-lang="en">Find partners, experts and clients</span><span data-lang="ro">Găsește partenere, experte și clienți</span></li>
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Рекомендации на основе профиля</span><span data-lang="en">Recommendations based on your profile</span><span data-lang="ro">Recomandări bazate pe profil</span></li>
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Прямой контакт через Telegram</span><span data-lang="en">Direct contact through Telegram</span><span data-lang="ro">Contact direct prin Telegram</span></li>
                        </ul>
                    </div>
                    <div class="miro-mockup">
                        <div class="miro-mockup__bar"><i></i><i></i><i></i></div>
                        <div class="miro-mockup__body miro-mockup__body--ai">
                            <div class="miro-ai-card">
                                <span class="miro-ai-card__tag">AI match</span>
                                <h4>Export partner</h4>
                                <p>Three relevant profiles found in your community.</p>
                                <div class="miro-ai-card__meter"><span></span></div>
                                <p style="margin-top: 8px; color: var(--fortun-blue); font-weight: 500;">86% relevance</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="miro-split miro-split--reverse" style="margin-top: 96px;">
                    <div class="miro-split__copy">
                        <span class="miro-tag" style="background: var(--fortun-surface-featured); color: var(--fortun-blue);"><span data-lang="ru">Shared workspace</span><span data-lang="en">Shared workspace</span><span data-lang="ro">Spațiu comun</span></span>
                        <h3 style="margin-top: 18px;"><span data-lang="ru">Публикуйте возможности, а не только новости</span><span data-lang="en">Share opportunities, not just updates</span><span data-lang="ro">Distribuie oportunități, nu doar noutăți</span></h3>
                        <p><span data-lang="ru">Проект, встреча, событие или запрос на сотрудничество — публикация сразу попадает в общий поток и Telegram-уведомления участниц.</span><span data-lang="en">A project, meeting, event or collaboration request reaches the shared feed and Telegram notifications.</span><span data-lang="ro">Un proiect, o întâlnire, un eveniment sau o cerere de colaborare ajunge în fluxul comun și în Telegram.</span></p>
                        <a href="{{ route('account.login') }}" class="miro-button miro-button--primary" style="margin-top: 28px;"><span data-lang="ru">Открыть кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Deschide cabinetul</span></a>
                    </div>
                    <div class="miro-mockup">
                        <div class="miro-mockup__bar"><i></i><i></i><i></i></div>
                        <div class="miro-mockup__body">
                            <div class="miro-roadmap">
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Need</span><div class="miro-roadmap__cell is-pink">Find a mentor</div><div class="miro-roadmap__cell">New market</div><div class="miro-roadmap__cell">Local partner</div></div>
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Action</span><div class="miro-roadmap__cell is-blue">Workshop · 14 Jun</div><div class="miro-roadmap__cell">Ask the community</div><div class="miro-roadmap__cell is-pink">Post an opportunity</div></div>
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Result</span><div class="miro-roadmap__cell">New contact</div><div class="miro-roadmap__cell is-blue">Shared learning</div><div class="miro-roadmap__cell">Next step</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section" id="learning">
            <div class="miro-container">
                <div class="miro-split">
                    <div class="miro-image-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/story-mentor.webp') }}" alt="Mentoring conversation" loading="lazy">
                        <div class="miro-image-card__caption">
                            <span class="miro-tag"><span data-lang="ru">Learning & mentoring</span><span data-lang="en">Learning & mentoring</span><span data-lang="ro">Învățare și mentorat</span></span>
                            <p><span data-lang="ru">Знания становятся полезнее, когда их можно сразу обсудить и применить.</span><span data-lang="en">Knowledge becomes more useful when you can discuss and apply it right away.</span><span data-lang="ro">Cunoștințele devin mai utile când le poți discuta și aplica imediat.</span></p>
                        </div>
                    </div>
                    <div class="miro-split__copy">
                        <p class="miro-eyebrow"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></p>
                        <h2><span data-lang="ru">Учитесь в своём темпе, растите рядом с другими</span><span data-lang="en">Learn at your pace, grow with others</span><span data-lang="ro">Învață în ritmul tău, crește alături de comunitate</span></h2>
                        <p><span data-lang="ru">Материалы, практические встречи, менторство и опыт других предпринимательниц помогают переходить от вопроса к решению.</span><span data-lang="en">Materials, practical sessions, mentoring and peer experience help you move from a question to a solution.</span><span data-lang="ro">Materiale, sesiuni practice, mentorat și experiența comunității te ajută să ajungi de la întrebare la soluție.</span></p>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--pink" style="margin-top: 28px;"><span data-lang="ru">Присоединиться к сообществу</span><span data-lang="en">Join the community</span><span data-lang="ro">Alătură-te comunității</span></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--soft" id="members">
            <div class="miro-container">
                <div class="miro-section__head">
                    <p class="miro-eyebrow"><span data-lang="ru">Профили платформы</span><span data-lang="en">Platform profiles</span><span data-lang="ro">Profilurile platformei</span></p>
                    <h2><span data-lang="ru">Знакомьтесь с нашими экспертами</span><span data-lang="en">Meet registered members and experts</span><span data-lang="ro">Cunoaște participantele și expertele platformei</span></h2>
                    <p><span data-lang="ru">Здесь представлены предприниматели и эксперты, которые уже зарегистрированы на платформе, рассказывают о своей работе и открыты к сотрудничеству.</span><span data-lang="en">Meet women entrepreneurs and experts already registered on the platform, presenting their work and open to collaboration.</span><span data-lang="ro">Descoperă antreprenoarele și expertele deja înregistrate pe platformă, care își prezintă activitatea și sunt deschise colaborării.</span></p>
                </div>
                <div class="miro-grid-2">
                    <article class="miro-member-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/experts/expert-carolina.png') }}" alt="Carolina Bugaiyan" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Каролина Бугаян</span><span data-lang="en">Carolina Bugaiyan</span><span data-lang="ro">Carolina Bugaiyan</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Президент Ассоциации деловых женщин Молдовы (AFAM)</span><span data-lang="en">President of the Association of Women Entrepreneurs in Moldova (AFAM)</span><span data-lang="ro">Președinta Asociației Femeilor de Afaceri din Moldova (AFAM)</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Женское предпринимательство и развитие делового сообщества</span><span data-lang="en">Women’s entrepreneurship &amp; business community development</span><span data-lang="ro">Antreprenoriat feminin și dezvoltarea comunității de business</span></p>
                            <div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--rose">AFAM</span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Партнёрства</span><span data-lang="en">Partnerships</span><span data-lang="ro">Parteneriate</span></span></div>
                            <p><span data-lang="ru">Развивает женское предпринимательство и деловые связи в Молдове.</span><span data-lang="en">Develops women’s entrepreneurship and business connections in Moldova.</span><span data-lang="ro">Dezvoltă antreprenoriatul feminin și conexiunile de business în Moldova.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/experts/expert-aurelia.png') }}" alt="Aurelia Salicov" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Аурелия Саликов</span><span data-lang="en">Aurelia Salicov</span><span data-lang="ro">Aurelia Salicov</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Вице-президент Международного бизнес-сообщества в Молдове</span><span data-lang="en">Vice-President of the International Business Society in Moldova</span><span data-lang="ro">Vicepreședinta International Business Society din Moldova</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Международное деловое сотрудничество</span><span data-lang="en">International business cooperation</span><span data-lang="ro">Cooperare internațională de business</span></p>
                            <div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Международные связи</span><span data-lang="en">International relations</span><span data-lang="ro">Relații internaționale</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Партнёрства</span><span data-lang="en">Partnerships</span><span data-lang="ro">Parteneriate</span></span></div>
                            <p><span data-lang="ru">Развивает международное деловое сотрудничество и новые партнёрства.</span><span data-lang="en">Builds international business cooperation and new partnerships.</span><span data-lang="ro">Dezvoltă cooperarea internațională de business și parteneriate noi.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/experts/expert-vlada.png') }}" alt="Vlada Lysenko" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Влада Лысенко</span><span data-lang="en">Vlada Lysenko</span><span data-lang="ro">Vlada Lysenko</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Доктор наук, профессор, международный консультант</span><span data-lang="en">Doctor of Sciences, Professor &amp; International Consultant</span><span data-lang="ro">Doctor în științe, profesor și consultant internațional</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Наука, образование и международный консалтинг</span><span data-lang="en">Research, education &amp; international consulting</span><span data-lang="ro">Cercetare, educație și consultanță internațională</span></p>
                            <div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Образование</span><span data-lang="en">Education</span><span data-lang="ro">Educație</span></span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Консалтинг</span><span data-lang="en">Consulting</span><span data-lang="ro">Consultanță</span></span></div>
                            <p><span data-lang="ru">Объединяет академический опыт, образование и международный консалтинг.</span><span data-lang="en">Combines academic experience, education and international consulting.</span><span data-lang="ro">Combină experiența academică, educația și consultanța internațională.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/experts/expert-zinaida.png') }}" alt="Zinaida Emelyanova" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Зинаида Емельянова</span><span data-lang="en">Zinaida Emelyanova</span><span data-lang="ro">Zinaida Emelyanova</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Директор Агентства инноваций и развития</span><span data-lang="en">Director of the Agency for Innovation and Development</span><span data-lang="ro">Directoarea Agenției pentru Inovații și Dezvoltare</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Инновации и развитие проектов</span><span data-lang="en">Innovation &amp; project development</span><span data-lang="ro">Inovații și dezvoltarea proiectelor</span></p>
                            <div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Инновации</span><span data-lang="en">Innovation</span><span data-lang="ro">Inovație</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Развитие</span><span data-lang="en">Development</span><span data-lang="ro">Dezvoltare</span></span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></span></div>
                            <p><span data-lang="ru">Развивает инновационные проекты и поддерживает предпринимательские инициативы.</span><span data-lang="en">Develops innovation projects and supports entrepreneurial initiatives.</span><span data-lang="ro">Dezvoltă proiecte inovatoare și susține inițiative antreprenoriale.</span></p>
                        </div>
                    </article>
                </div>
                <div class="miro-grid-2 miro-members__legacy">
                    <article class="miro-member-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/experts/expert-diana.png') }}" alt="Diana Sakirchuk" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Диана Сакирчук</span><span data-lang="en">Diana Sakirchuk</span><span data-lang="ro">Diana Sakirchuk</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Основательница PureCup</span><span data-lang="en">Founder of PureCup</span><span data-lang="ro">Fondatoarea PureCup</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Предпринимательство и развитие продукта</span><span data-lang="en">Entrepreneurship &amp; product development</span><span data-lang="ro">Antreprenoriat și dezvoltarea produsului</span></p>
                            <div class="miro-member-card__tags">
                                <span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Основательница</span><span data-lang="en">Founder</span><span data-lang="ro">Fondatoare</span></span>
                                <span class="miro-profile-tag miro-profile-tag--rose">PureCup</span>
                                <span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Продукт</span><span data-lang="en">Product</span><span data-lang="ro">Produs</span></span>
                            </div>
                            <p><span data-lang="ru">Развивает PureCup и собственный предпринимательский проект.</span><span data-lang="en">Builds PureCup and her own entrepreneurial project.</span><span data-lang="ro">Dezvoltă PureCup și propriul proiect antreprenorial.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/experts/expert-olga-melnichuk.png') }}" alt="Olga Melnichuk" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Ольга Мельничук</span><span data-lang="en">Olga Melnichuk</span><span data-lang="ro">Olga Melnichuk</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Соосновательница Business Angels Moldova, исполнительный директор Startup Moldova</span><span data-lang="en">Co-founder of Business Angels Moldova, Executive Director of Startup Moldova</span><span data-lang="ro">Co-fondatoarea Business Angels Moldova, directoarea executivă Startup Moldova</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Стартапы, инвестиции и предпринимательство</span><span data-lang="en">Startups, investment &amp; entrepreneurship</span><span data-lang="ro">Startupuri, investiții și antreprenoriat</span></p>
                            <div class="miro-member-card__tags">
                                <span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Стартапы</span><span data-lang="en">Startups</span><span data-lang="ro">Startupuri</span></span>
                                <span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Инвестиции</span><span data-lang="en">Investment</span><span data-lang="ro">Investiții</span></span>
                                <span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Сообщества</span><span data-lang="en">Community</span><span data-lang="ro">Comunitate</span></span>
                            </div>
                            <p><span data-lang="ru">Развивает стартап- и инвестиционную экосистему Молдовы.</span><span data-lang="en">Develops Moldova’s startup and investment ecosystem.</span><span data-lang="ro">Dezvoltă ecosistemul de startupuri și investiții din Moldova.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/experts/expert-irina.png') }}" alt="Irina Pleshkova" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Ирина Плешкова</span><span data-lang="en">Irina Pleshkova</span><span data-lang="ro">Irina Pleshkova</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Эксперт по внедрению AI и цифровой эффективности</span><span data-lang="en">Expert in AI adoption and digital efficiency</span><span data-lang="ro">Expertă în implementarea AI și eficiență digitală</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">AI, цифровая трансформация и эффективность</span><span data-lang="en">AI, digital transformation &amp; efficiency</span><span data-lang="ro">AI, transformare digitală și eficiență</span></p>
                            <div class="miro-member-card__tags">
                                <span class="miro-profile-tag miro-profile-tag--coral">AI</span>
                                <span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Цифровизация</span><span data-lang="en">Digital</span><span data-lang="ro">Digital</span></span>
                                <span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Эффективность</span><span data-lang="en">Efficiency</span><span data-lang="ro">Eficiență</span></span>
                            </div>
                            <p><span data-lang="ru">Помогает предпринимателям внедрять AI и цифровые инструменты для роста эффективности.</span><span data-lang="en">Helps entrepreneurs adopt AI and digital tools to improve efficiency.</span><span data-lang="ro">Ajută antreprenorii să adopte AI și instrumente digitale pentru eficiență.</span></p>
                        </div>
                    </article>
                </div>
                <div class="miro-grid-2 miro-members__legacy">
                    <article class="miro-member-card"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/member-fashion.webp') }}" alt="Fashion and design participant profile" loading="lazy"><div><h4><span data-lang="ru">Участница · Мода и дизайн</span><span data-lang="en">Member · Fashion &amp; design</span><span data-lang="ro">Participantă · Modă și design</span></h4><div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--yellow"><span data-lang="ru">Участница</span><span data-lang="en">Member</span><span data-lang="ro">Participantă</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Дизайн</span><span data-lang="en">Design</span><span data-lang="ro">Design</span></span><span class="miro-profile-tag miro-profile-tag--teal"><span data-lang="ru">Ищет партнёров</span><span data-lang="en">Looking for a partner</span><span data-lang="ro">Caută partener</span></span></div><p><span data-lang="ru">President of the Association of Women Entrepreneurs in Moldova (AFAM).</span><span data-lang="en">Building a product and open to new sales channels and partnerships.</span><span data-lang="ro">Dezvoltă un produs și este deschisă canalelor noi de vânzare și parteneriatelor.</span></p></div></article>
                    <article class="miro-member-card"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/member-digital.webp') }}" alt="Digital services expert profile" loading="lazy"><div><h4><span data-lang="ru">Каролина Бугаян · Президент Ассоциации </span><span data-lang="en">Expert · Digital services</span><span data-lang="ro">Expertă · Servicii digitale</span></h4><div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Бизнесмен</span><span data-lang="en">Marketing</span><span data-lang="ro">Marketing</span></span><span class="miro-profile-tag miro-profile-tag--yellow"><span data-lang="ru">Ищет партнеров</span><span data-lang="en">Offers services</span><span data-lang="ro">Oferă servicii</span></span></div><p><span data-lang="ru">President of the Association of Women Entrepreneurs in Moldova (AFAM).</span><span data-lang="en">Helping businesses become more visible, clear and effective.</span><span data-lang="ro">Ajută afacerile să devină mai vizibile, mai clare și mai eficiente.</span></p></div></article>
                    <article class="miro-member-card"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/member-agrifood.webp') }}" alt="Agrifood participant profile" loading="lazy"><div><h4><span data-lang="ru">Участница · Агро и продукты</span><span data-lang="en">Member · Agri &amp; food</span><span data-lang="ro">Participantă · Agri și produse</span></h4><div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--teal"><span data-lang="ru">Участница</span><span data-lang="en">Member</span><span data-lang="ro">Participantă</span></span><span class="miro-profile-tag miro-profile-tag--orange"><span data-lang="ru">Агро и продукты</span><span data-lang="en">Agri &amp; food</span><span data-lang="ro">Agri și produse</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Ищет новые рынки</span><span data-lang="en">Looking for new markets</span><span data-lang="ro">Caută piețe noi</span></span></div><p><span data-lang="ru">Развивает локальный бизнес и ищет устойчивые деловые связи.</span><span data-lang="en">Growing a local business and building sustainable business connections.</span><span data-lang="ro">Dezvoltă o afacere locală și construiește conexiuni de business durabile.</span></p></div></article>
                    <article class="miro-member-card"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/hero-community.webp') }}" alt="Women entrepreneurs community profile" loading="lazy"><div><h4><span data-lang="ru">Эксперт · Развитие сообщества</span><span data-lang="en">Expert · Community building</span><span data-lang="ro">Expertă · Dezvoltarea comunității</span></h4><div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--teal"><span data-lang="ru">Менторство</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></span><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Открыта к сотрудничеству</span><span data-lang="en">Open to collaboration</span><span data-lang="ro">Deschisă colaborării</span></span></div><p><span data-lang="ru">Соединяет людей, идеи и возможности для общего результата.</span><span data-lang="en">Connecting people, ideas and opportunities for a shared result.</span><span data-lang="ro">Conectează oameni, idei și oportunități pentru rezultate comune.</span></p></div></article>
                </div>
                <div class="miro-members__cta">
                    <a class="miro-button miro-button--primary" href="{{ route('members') }}"><span data-lang="ru">Найти похожие профили&nbsp;→</span><span data-lang="en">Find similar profiles&nbsp;→</span><span data-lang="ro">Găsește profiluri similare&nbsp;→</span></a>
                    <a class="miro-button miro-button--primary" href="{{ route('account.login') }}"><span data-lang="ru">Найти похожие профили&nbsp;→</span><span data-lang="en">Find similar profiles&nbsp;→</span><span data-lang="ro">Găsește profiluri similare&nbsp;→</span></a>
                </div>
            </div>
        </section>

        <section class="miro-section" id="events">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Новости и возможности</span><span data-lang="en">News &amp; opportunities</span><span data-lang="ro">Noutăți și oportunități</span></p>
                    <h2><span data-lang="ru">Мероприятия, встречи и новые возможности</span><span data-lang="en">Meet, learn and discover new opportunities</span><span data-lang="ro">Întâlnește-te, învață și descoperă oportunități</span></h2>
                    <p><span data-lang="ru">Практические воркшопы, нетворкинг, объявления, гранты и партнёрские возможности — всё, что помогает двигаться дальше.</span><span data-lang="en">Practical workshops, networking, announcements, grants and partner opportunities to help you move forward.</span><span data-lang="ro">Workshopuri practice, networking, anunțuri, granturi și oportunități de parteneriat pentru următorul tău pas.</span></p>
                </div>
                <div class="miro-grid-3">
                    <article class="miro-event-card"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/news/news-white-noise.jpg') }}" alt="White Noise — where creativity meets entrepreneurship" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag" style="background:var(--fortun-pink);color:var(--fortun-primary)"><span data-lang="ru">Новости</span><span data-lang="en">News</span><span data-lang="ro">Noutăți</span></span><div class="miro-event-card__date">20.05.2026</div><h3><span data-lang="ru">«Белый Шум» — встреча креатива и предпринимательства</span><span data-lang="en">White Noise — where creativity meets entrepreneurship</span><span data-lang="ro">„White Noise” — întâlnirea dintre creativitate și antreprenoriat</span></h3><p><span data-lang="ru">Арт-выставка, где встретились искусство, мода и предпринимательство.</span><span data-lang="en">An art exhibition where art, fashion and entrepreneurship came together.</span><span data-lang="ro">O expoziție de artă în care s-au întâlnit arta, moda și antreprenoriatul.</span></p><a href="https://women.creativity.md/2026/05/20/%d0%b1%d0%b5%d0%bb%d1%8b%d0%b9-%d1%88%d1%83%d0%bc-%d0%b2%d1%81%d1%82%d1%80%d0%b5%d1%87%d0%b0-%d0%ba%d1%80%d0%b5%d0%b0%d1%82%d0%b8%d0%b2%d0%b0-%d0%b8-%d0%bf%d1%80%d0%b5%d0%b4/" target="_blank" rel="noopener" class="miro-event-card__link"><span data-lang="ru">Подробнее&nbsp;→</span><span data-lang="en">Read more&nbsp;→</span><span data-lang="ro">Află mai multe&nbsp;→</span></a></div></article>
                    <article class="miro-event-card"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/news/news-conference.jpg') }}" alt="International conference for women entrepreneurs" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag" style="background:var(--fortun-surface-featured);color:var(--fortun-blue)"><span data-lang="ru">Конференция</span><span data-lang="en">Conference</span><span data-lang="ro">Conferință</span></span><div class="miro-event-card__date">20.05.2026</div><h3><span data-lang="ru">Международная конференция для женщин-предпринимателей</span><span data-lang="en">International conference for women entrepreneurs</span><span data-lang="ro">Conferință internațională pentru femei antreprenoare</span></h3><p><span data-lang="ru">Конференция о лидерстве, инновациях и развитии женского предпринимательства.</span><span data-lang="en">A conference about leadership, innovation and women’s entrepreneurship.</span><span data-lang="ro">O conferință despre leadership, inovație și antreprenoriat feminin.</span></p><a href="https://women.creativity.md/2026/05/20/%d0%bc%d0%b5%d0%b6%d0%b4%d1%83%d0%bd%d0%b0%d1%80%d0%be%d0%b4%d0%bd%d0%b0%d1%8f-%d0%ba%d0%be%d0%bd%d1%84%d0%b5%d1%80%d0%b5%d0%bd%d1%86%d0%b8%d1%8f-%d0%b4%d0%bb%d1%8f-%d0%b6%d0%b5%d0%bd%d1%89%d0%b8/" target="_blank" rel="noopener" class="miro-event-card__link"><span data-lang="ru">Подробнее&nbsp;→</span><span data-lang="en">Read more&nbsp;→</span><span data-lang="ro">Află mai multe&nbsp;→</span></a></div></article>
                    <article class="miro-event-card"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/news/news-networking.jpg') }}" alt="Dream Takes Flight networking event at Glia Impact Hub" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag" style="background:var(--fortun-coral);color:var(--fortun-primary)"><span data-lang="ru">Нетворкинг</span><span data-lang="en">Networking</span><span data-lang="ro">Networking</span></span><div class="miro-event-card__date">20.05.2026</div><h3><span data-lang="ru">В Glia Impact Hub состоялось нетворкинг-мероприятие</span><span data-lang="en">“Dream Takes Flight” networking event at Glia Impact Hub</span><span data-lang="ro">Evenimentul de networking „Visul își ia zborul” la Glia Impact Hub</span></h3><p><span data-lang="ru">Встреча предпринимательниц, организованная AFAM вместе с партнёрами.</span><span data-lang="en">A gathering of women entrepreneurs organised by AFAM and community partners.</span><span data-lang="ro">O întâlnire a femeilor antreprenoare organizată de AFAM și partenerii comunității.</span></p><a href="https://women.creativity.md/2026/05/20/%d0%b2-glia-impact-hub-%d1%81%d0%be%d1%81%d1%82%d0%be%d1%8f%d0%bb%d0%be%d1%81%d1%8c-%d0%bd%d0%b5%d1%82%d0%b2%d0%be%d1%80%d0%ba%d0%b8%d0%bd%d0%b3-%d0%bc%d0%b5%d1%80%d0%be%d0%bf%d1%80%d0%b8%d1%8f%d1%82/" target="_blank" rel="noopener" class="miro-event-card__link"><span data-lang="ru">Подробнее&nbsp;→</span><span data-lang="en">Read more&nbsp;→</span><span data-lang="ro">Află mai multe&nbsp;→</span></a></div></article>
                </div>
                <div class="miro-events__footer">
                    <a href="{{ route('events') }}" class="miro-events__all-link"><span data-lang="ru">Все новости&nbsp;→</span><span data-lang="en">All news&nbsp;→</span><span data-lang="ro">Toate noutățile&nbsp;→</span></a>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="stories">
            <div class="miro-container">
                <div class="miro-story">
                    <img src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/images/story-export.webp') }}" alt="Women entrepreneurs collaborating" loading="lazy">
                    <div class="miro-story__body">
                        <span class="miro-tag" style="width: fit-content; background: var(--fortun-pink); color: var(--fortun-primary);">Member story</span>
                        <blockquote><span data-lang="ru">«Нужный контакт оказался не где-то далеко — он уже был внутри сообщества.»</span><span data-lang="en">“The right connection was not far away — it was already inside the community.”</span><span data-lang="ro">„Conexiunea potrivită nu era departe — era deja în comunitate.”</span></blockquote>
                        <p>Women Entrepreneurs Platform member</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section">
            <div class="miro-container">
                <div class="miro-cta">
                    <h2><span data-lang="ru">Готовы собрать свой следующий шаг?</span><span data-lang="en">Ready to bring your next step together?</span><span data-lang="ro">Ești gata să construiești următorul pas?</span></h2>
                    <p><span data-lang="ru">Присоединяйтесь к платформе через Telegram и начните с простого профиля.</span><span data-lang="en">Join through Telegram and start with a simple profile.</span><span data-lang="ro">Alătură-te prin Telegram și începe cu un profil simplu.</span></p>
                    <div class="miro-hero__actions">
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--on-dark"><span data-lang="ru">Начать в Telegram</span><span data-lang="en">Start in Telegram</span><span data-lang="ro">Începe în Telegram</span></a>
                        <a href="{{ route('account.login') }}" class="miro-button" style="border: 1px solid rgba(255,255,255,.35); color: #fff;"><span data-lang="ru">У меня уже есть доступ</span><span data-lang="en">I already have access</span><span data-lang="ro">Am deja acces</span></a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('themes.public.fortun.partials.miro-footer')
    @if(false)
    <footer class="miro-footer" id="contact">
        <div class="miro-container">
            <div class="miro-footer__top">
                <div class="miro-footer__brand">
                    <a href="#top" class="miro-brand"><span class="miro-brand__mark">W</span><span>Women Entrepreneurs Platform</span></a>
                    <p><span data-lang="ru">Цифровое пространство для женщин-предпринимательниц из двух берегов.</span><span data-lang="en">A digital space for women entrepreneurs from both banks.</span><span data-lang="ro">Un spațiu digital pentru femeile antreprenoare de pe ambele maluri.</span></p>
                </div>
                <div><h4><span data-lang="ru">Платформа</span><span data-lang="en">Platform</span><span data-lang="ro">Platformă</span></h4><ul><li><a href="#about">About</a></li><li><a href="#features">AI matching</a></li><li><a href="{{ route('members') }}">Members</a></li></ul></div>
                <div><h4><span data-lang="ru">Ресурсы</span><span data-lang="en">Resources</span><span data-lang="ro">Resurse</span></h4><ul><li><a href="#learning">Learning</a></li><li><a href="{{ route('events') }}">Events</a></li><li><a href="#stories">Stories</a></li></ul></div>
                <div><h4><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></h4><ul><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">@WomenComBot</a></li><li><a href="{{ $managerUrl }}" target="_blank" rel="noopener">Project team</a></li><li><a href="{{ $communityUrl }}" target="_blank" rel="noopener">Community</a></li></ul></div>
                <div><h4><span data-lang="ru">Вход</span><span data-lang="en">Access</span><span data-lang="ro">Acces</span></h4><ul><li><a href="{{ route('account.login') }}"><span data-lang="ru">Кабинет участницы</span><span data-lang="en">Participant cabinet</span><span data-lang="ro">Cabinetul membrei</span></a></li><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">Telegram</a></li></ul></div>
            </div>
            <div class="miro-footer__bottom"><span>© {{ date('Y') }} Women Entrepreneurs Platform</span><span><span data-lang="ru">Сделано для роста через связи</span><span data-lang="en">Made for growth through connection</span><span data-lang="ro">Creat pentru creștere prin conexiuni</span></span></div>
        </div>
    </footer>
    @endif

    <script src="{{ asset('themes/public/' . ($publicTheme ?? 'fortun') . '/js/landing.js') }}"></script>
</body>
</html>
