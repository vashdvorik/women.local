@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $locales = ['ru', 'en', 'ro'];
    $landingExperts = $landingExperts ?? collect();
    $landingEvents = $landingEvents ?? collect();
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform</title>
    <meta name="description" content="Пространство для женщин-предпринимательниц: обучение, контакты, AI-рекомендации и возможности роста.">
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/regular/style.css">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body class="miro-landing-page">
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'home'])
    @if(false)
    <nav class="miro-nav" id="miro-nav">
        <div class="miro-container miro-nav__inner">
            <a href="#top" class="miro-brand">
                <img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/logo-white.webp') }}" alt="Women Entrepreneurs Platform" class="miro-brand__logo">
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
                    <div class="miro-floating-accents miro-floating-accents--hero" aria-hidden="true"><span></span><span></span><span></span></div>
                    <div class="miro-hero__content">
                        <h1>
                            <span data-lang="ru"><span class="miro-hero__title-main">Women's hub</span><span class="miro-hero__title-sub">ПЛАТФОРМА ЖЕНЩИН ПРЕДПРИНИМАТЕЛЕЙ</span></span>
                            <span data-lang="en"><span class="miro-hero__title-main">Women's hub</span><span class="miro-hero__title-sub">PLATFORM FOR WOMEN ENTREPRENEURS</span></span>
                            <span data-lang="ro"><span class="miro-hero__title-main">Women's hub</span><span class="miro-hero__title-sub">PLATFORMĂ PENTRU FEMEI ANTREPRENOARE</span></span>
                        </h1>
                        <p class="miro-hero__subtitle">
                            <span data-lang="ru">Пространство для обучения, деловых связей,
наставничества и развития бизнеса</span>
                            <span data-lang="en">A digital space for learning, networking, mentorship, and business growth across the region.</span>
                            <span data-lang="ro">Un spațiu digital pentru învățare, networking, mentorat și creșterea afacerilor în regiune.</span>
                        </p>
                        <div class="miro-hero__actions">
                            <a href="{{ route('account.login') }}" class="miro-button miro-button--primary miro-button--brand">
                                <span data-lang="ru">Присоединиться</span><span data-lang="en">Join the Platform</span><span data-lang="ro">Alătură-te platformei</span>
                            </a>
                            <a href="{{ route('experts') }}" class="miro-button miro-button--secondary">
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
                            <img src="{{ asset('themes/public/miro/images/bannerhero.webp') }}" alt="Women entrepreneurs collaborating around a laptop">
                        </div>
                    </div>
                    <svg class="miro-hero__bottom-curve" viewBox="0 0 1440 150" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="miro-hero-caramel-ribbon" x1="760" y1="0" x2="1440" y2="0" gradientUnits="userSpaceOnUse">
                                <stop offset="0" stop-color="#C99A73" stop-opacity="0" />
                                <stop offset=".28" stop-color="#C99A73" stop-opacity=".78" />
                                <stop offset="1" stop-color="#E6CFC1" stop-opacity=".98" />
                            </linearGradient>
                        </defs>
                        <path class="miro-hero__bottom-curve-fill" d="M0 104C166 121 328 124 500 102C716 75 932 65 1134 84C1263 97 1366 91 1440 70V150H0Z" />
                        <path class="miro-hero__bottom-curve-line" d="M0 104C166 121 328 124 500 102C716 75 932 65 1134 84C1263 97 1366 91 1440 70" />
                        <path class="miro-hero__bottom-curve-intersection" d="M748 113C972 60 1178 113 1440 39V56C1175 132 971 85 748 129Z" />
                        <path class="miro-hero__bottom-curve-cut" d="M0 123C170 134 337 135 510 112C729 90 933 86 1139 102C1265 113 1368 111 1440 95V150H0Z" />
                    </svg>
                </div>

            </div>
        </section>
        <section class="miro-directions" aria-label="Platform directions">
            <div class="miro-container miro-directions__grid">
                <article class="miro-direction-card miro-direction-card--support">
                    <div class="miro-direction-card__icon miro-direction-card__icon--support" aria-hidden="true">
                        <i class="ph ph-hand-palm miro-support-hand miro-support-hand--left"></i>
                        <i class="ph ph-hand-palm miro-support-hand miro-support-hand--right"></i>
                        <i class="ph ph-gender-female miro-support-symbol"></i>
                    </div>
                    <h3><span data-lang="ru">Поддержка и <br>наставничество</span><span data-lang="en">Support<br>and mentorship</span><span data-lang="ro">Sprijin<br>și mentorat</span></h3>
                </article>
                <article class="miro-direction-card miro-direction-card--award">
                    <div class="miro-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-crown"></i>
                    </div>
                    <h3><span data-lang="ru">Премия<br>«Женщина года»</span><span data-lang="en">Award<br>“Woman of the Year”</span><span data-lang="ro">Premiul<br>„Femeia anului”</span></h3>
                </article>
                <article class="miro-direction-card miro-direction-card--learning">
                    <div class="miro-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-graduation-cap"></i>
                    </div>
                    <h3><span data-lang="ru">Обучение<br>и развитие</span><span data-lang="en">Learning<br>and growth</span><span data-lang="ro">Învățare<br>și dezvoltare</span></h3>
                </article>
                <article class="miro-direction-card miro-direction-card--community">
                    <div class="miro-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-users-three"></i>
                    </div>
                    <h3><span data-lang="ru">Нетворкинг<br>и сообщество</span><span data-lang="en">Networking<br>and community</span><span data-lang="ro">Networking<br>și comunitate</span></h3>
                </article>
                <article class="miro-direction-card miro-direction-card--business">
                    <div class="miro-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-briefcase"></i>
                    </div>
                    <h3><span data-lang="ru">Бизнес<br>и рост</span><span data-lang="en">Business<br>and growth</span><span data-lang="ro">Afaceri<br>și creștere</span></h3>
                </article>
                <article class="miro-direction-card miro-direction-card--visibility">
                    <div class="miro-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-megaphone"></i>
                    </div>
                    <h3><span data-lang="ru">Продвижение<br>и видимость</span><span data-lang="en">Promotion<br>and visibility</span><span data-lang="ro">Promovare<br>și vizibilitate</span></h3>
                </article>
                <article class="miro-direction-card miro-direction-card--resources">
                    <div class="miro-direction-card__icon miro-direction-card__icon--resources" aria-hidden="true">
                        <i class="ph ph-file-text"></i>
                        <i class="ph ph-check miro-resource-check"></i>
                    </div>
                    <h3><span data-lang="ru">Ресурсы<br>и эксперты</span><span data-lang="en">Resources<br>and experts</span><span data-lang="ro">Resurse<br>și experți</span></h3>
                </article>
                <article class="miro-direction-card miro-direction-card--partnership">
                    <div class="miro-direction-card__icon" aria-hidden="true">
                        <i class="ph ph-handshake"></i>
                    </div>
                    <h3><span data-lang="ru">Партнёрство<br>и проекты</span><span data-lang="en">Partnership<br>and projects</span><span data-lang="ro">Parteneriat<br>și proiecte</span></h3>
                </article>
            </div>
        </section>
        <section class="miro-director-note" aria-label="Message from the director">
            <div class="miro-container miro-director-note__grid">
                <div class="miro-director-note__copy">
                    <div class="miro-director-note__meta">
                        <p><strong>Валерия Зелинская</strong><span data-lang="ru"> · Руководитель Платформы женщин-предпринимателей</span><span data-lang="en"> · Head of the Women Entrepreneurs Platform</span><span data-lang="ro"> · Conducătoarea Platformei Femeilor Antreprenoare</span></p>
                    </div>

                    <div data-lang="ru">
                        <p>Уважаемые коллеги и партнёры!</p>
                        <p>Платформа женщин-предпринимателей — это пространство поддержки и развития для женщин, создающих и развивающих собственный бизнес. Здесь женщины-предпринимательницы находят профессиональное сообщество, практические знания, экспертную поддержку и возможности для обмена опытом, необходимые для устойчивого роста и реализации бизнес-идей.</p>
                        <p>Наша цель — укреплять женское предпринимательство, развивать лидерские и управленческие компетенции, способствовать финансовой самостоятельности женщин и созданию конкурентоспособных бизнесов. Мы объединяем женщин-предпринимательниц, помогая им действовать сообща и усиливать голос женского бизнес-сообщества через обучающие программы, консультации, тематические мероприятия и конференции.</p>
                        <p>Если вы ищете новые возможности для развития бизнеса и профессионального роста, мы будем рады видеть вас в сообществе Платформы женщин-предпринимателей.</p>
                    </div>
                    <div data-lang="en">
                        <p>Dear colleagues and partners!</p>
                        <p>The Women Entrepreneurs Platform is a space of support and development for women who create and grow their own businesses. Here, women entrepreneurs find a professional community, practical knowledge, expert support and opportunities to exchange experience — everything needed for sustainable growth and the implementation of business ideas.</p>
                        <p>Our goal is to strengthen women’s entrepreneurship, develop leadership and management skills, support women’s financial independence and help create competitive businesses. We bring women entrepreneurs together, helping them act collectively and amplify the voice of the women’s business community through educational programmes, consultations, thematic events and conferences.</p>
                        <p>If you are looking for new opportunities for business development and professional growth, we will be glad to welcome you to the Women Entrepreneurs Platform community.</p>
                    </div>
                    <div data-lang="ro">
                        <p>Dragi colegi și parteneri!</p>
                        <p>Platforma Femeilor Antreprenoare este un spațiu de sprijin și dezvoltare pentru femeile care creează și își dezvoltă propria afacere. Aici, femeile antreprenoare găsesc o comunitate profesională, cunoștințe practice, sprijin din partea experților și oportunități de schimb de experiență — tot ce este necesar pentru o creștere durabilă și realizarea ideilor de afaceri.</p>
                        <p>Scopul nostru este să consolidăm antreprenoriatul feminin, să dezvoltăm competențele de leadership și management, să contribuim la independența financiară a femeilor și la crearea unor afaceri competitive. Reunim femeile antreprenoare, ajutându-le să acționeze împreună și să consolideze vocea comunității de business feminin prin programe educaționale, consultații, evenimente tematice și conferințe.</p>
                        <p>Dacă sunteți în căutarea unor noi oportunități pentru dezvoltarea afacerii și creșterea profesională, ne vom bucura să vă avem în comunitatea Platformei Femeilor Antreprenoare.</p>
                    </div>
                </div>
                <figure class="miro-director-note__portrait">
                    <span class="miro-director-note__accent" aria-hidden="true"></span>
                    <img src="{{ asset('themes/public/miro/images/director.webp') }}" alt="Валерия Зелинская">
                </figure>
            </div>
        </section>
        {{-- .miro-logo-wall moved to priorities.blade.php (see /about/priorities). --}}



        <section class="miro-section" id="benefits">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <h2><span data-lang="ru">Преимущества платформы</span><span data-lang="en">Key benefits for your next step</span><span data-lang="ro">Beneficii pentru următorul tău pas</span></h2>
                    <p><span data-lang="ru">Всё необходимое для обучения, полезных знакомств и роста бизнеса — в одном понятном пространстве.</span><span data-lang="en">Everything you need to learn, connect and grow your business in one clear space.</span><span data-lang="ro">Tot ce ai nevoie pentru a învăța, a te conecta și a-ți dezvolta afacerea într-un singur spațiu clar.</span></p>
                </div>
                <div class="miro-benefits">
                    <article class="miro-benefit-card miro-benefit-card--rose miro-benefit-card--featured">
                        <div class="miro-benefit-card__icon" aria-hidden="true"><i class="ph ph-sparkle"></i></div>
                        <div class="miro-benefit-card__copy">
                            <h3><span data-lang="ru">AI-подбор</span><span data-lang="en">AI Matching</span><span data-lang="ro">Potrivire AI</span></h3>
                            <p><span data-lang="ru">Умные рекомендации партнёров, менторов и возможностей.</span><span data-lang="en">Smart recommendations for partners, mentors and opportunities.</span><span data-lang="ro">Recomandări inteligente pentru partenere, mentori și oportunități.</span></p>
                        </div>
                    </article>
                    <div class="miro-benefits__grid">
                    <article class="miro-benefit-card miro-benefit-card--pink">
                        <div class="miro-benefit-card__icon" aria-hidden="true"><i class="ph ph-book-open"></i></div>
                        <h3><span data-lang="ru">Центр обучения</span><span data-lang="en">Learning Hub</span><span data-lang="ro">Hub de învățare</span></h3>
                        <p><span data-lang="ru">Онлайн-курсы, видео и практические модули для бизнеса.</span><span data-lang="en">Online courses, videos and practical business modules.</span><span data-lang="ro">Cursuri online, videoclipuri și module practice pentru afaceri.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--teal">
                        <div class="miro-benefit-card__icon" aria-hidden="true"><i class="ph ph-address-book"></i></div>
                        <h3><span data-lang="ru">Каталог участниц</span><span data-lang="en">Member Directory</span><span data-lang="ro">Directorul membrelor</span></h3>
                        <p><span data-lang="ru">Находите предпринимательниц и легко открывайте полезные связи.</span><span data-lang="en">Discover women-led businesses and connect easily.</span><span data-lang="ro">Descoperă afaceri conduse de femei și conectează-te ușor.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--coral">
                        <div class="miro-benefit-card__icon" aria-hidden="true"><i class="ph ph-calendar-check"></i></div>
                        <h3><span data-lang="ru">События и возможности</span><span data-lang="en">Events &amp; Opportunities</span><span data-lang="ro">Evenimente și oportunități</span></h3>
                        <p><span data-lang="ru">Тренинги, форумы, гранты и встречи для сотрудничества.</span><span data-lang="en">Trainings, forums, grants and meetings for collaboration.</span><span data-lang="ro">Traininguri, forumuri, granturi și întâlniri pentru colaborare.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--orange">
                        <div class="miro-benefit-card__icon" aria-hidden="true"><i class="ph ph-hand-heart"></i></div>
                        <h3><span data-lang="ru">Менторство</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></h3>
                        <p><span data-lang="ru">Получайте советы от опытных предпринимательниц и эксперток.</span><span data-lang="en">Access advice from experienced women entrepreneurs.</span><span data-lang="ro">Primește sfaturi de la antreprenoare cu experiență.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--surface">
                        <div class="miro-benefit-card__icon" aria-hidden="true"><i class="ph ph-bell-ringing"></i></div>
                        <h3><span data-lang="ru">Уведомления Telegram</span><span data-lang="en">Telegram Alerts</span><span data-lang="ro">Alerte Telegram</span></h3>
                        <p><span data-lang="ru">Будьте в курсе новостей и получайте персональные уведомления.</span><span data-lang="en">Stay updated with news and tailored notifications.</span><span data-lang="ro">Rămâi la curent cu noutățile și notificările personalizate.</span></p>
                    </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="how-it-works">
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

        <section class="miro-section" id="register-cta">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <h2><span data-lang="ru">Присоединяйтесь к платформе</span><span data-lang="en">Join the platform</span><span data-lang="ro">Alătură-te platformei</span></h2>
                    <p><span data-lang="ru">Создайте профиль за несколько минут и получите доступ к сообществу, знаниям и новым возможностям.</span><span data-lang="en">Create your profile in minutes and get access to the community, resources and new opportunities.</span><span data-lang="ro">Creează-ți profilul în câteva minute și obții acces la comunitate, resurse și oportunități noi.</span></p>
                </div>
                <div class="miro-register-cta__actions">
                    <a href="{{ route('account.login') }}" class="miro-button miro-button--primary"><span data-lang="ru">Зарегистрироваться&nbsp;→</span><span data-lang="en">Register now&nbsp;→</span><span data-lang="ro">Înregistrează-te acum&nbsp;→</span></a>
                    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button" style="border: 1px solid var(--miro-hairline-strong); color: var(--miro-ink-deep);"><span data-lang="ru">Начать в Telegram</span><span data-lang="en">Start in Telegram</span><span data-lang="ro">Începe în Telegram</span></a>
                </div>
            </div>
        </section>

        {{-- #features moved to priorities.blade.php (see /about/priorities). --}}

        {{-- #learning moved to priorities.blade.php (see /about/priorities). --}}

        <section class="miro-section miro-section--soft" id="members">
            <div class="miro-container">
                <div class="miro-section__head">
                    <h2><span data-lang="ru">Знакомьтесь с нашими экспертами</span><span data-lang="en">Meet registered members and experts</span><span data-lang="ro">Cunoaște participantele și expertele platformei</span></h2>
                    <p><span data-lang="ru">Здесь представлены предприниматели и эксперты, которые уже зарегистрированы на платформе, рассказывают о своей работе и открыты к сотрудничеству.</span><span data-lang="en">Meet women entrepreneurs and experts already registered on the platform, presenting their work and open to collaboration.</span><span data-lang="ro">Descoperă antreprenoarele și expertele deja înregistrate pe platformă, care își prezintă activitatea și sunt deschise colaborării.</span></p>
                </div>
                <div class="miro-grid-2">
                    @foreach($landingExperts as $expert)
                        <article class="miro-member-card">
                            @if($expert->photoUrl())
                                <img src="{{ $expert->photoUrl() }}" alt="{{ $expert->field('name', 'en') }}" loading="lazy">
                            @endif
                            <div>
                                <h4>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $expert->field('name', $locale) }}</span>@endforeach</h4>
                                <p class="miro-member-card__role">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $expert->field('role', $locale) }}</span>@endforeach</p>
                                <p class="miro-member-card__specialization">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $expert->field('specialization', $locale) }}</span>@endforeach</p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="miro-members__cta">
                    <a class="miro-button miro-button--primary" href="{{ route('experts') }}"><span data-lang="ru">Найти похожие профили&nbsp;→</span><span data-lang="en">Find similar profiles&nbsp;→</span><span data-lang="ro">Găsește profiluri similare&nbsp;→</span></a>
                </div>
            </div>
        </section>

        {{-- Участницы платформы — карусель на нативном scroll-snap, без библиотек. Одна страница — это то, что
             помещается в видимую область (4×4 на десктопе, 2×4 на планшете, 1×4 на телефоне): раскладку и точки
             привязки задаёт CSS (landing.css), а participants.js добавляет кнопки, точки, клавиши и заранее
             подгружает фото следующих страниц. Без JS остаётся обычная листалка пальцем.
             Фото — миниатюры 128 px (php artisan participants:thumbnails), все с loading="lazy":
             секция далеко от начала страницы, и ни один байт фото не мешает первой отрисовке. --}}
        @php
            $participants = \App\Support\Participants::forCarousel();
            $participantImages = asset('themes/public/' . ($publicTheme ?? 'miro') . '/images');
            $participantLocales = ['ru', 'en', 'ro'];
        @endphp
        <section class="miro-section miro-section--surface" id="participants">
            <div class="miro-container">
                <div class="miro-participants" data-participants>
                    <div class="miro-section__head miro-participants__head">
                        <h2 id="miro-participants-title"><span data-lang="ru">Участницы платформы</span><span data-lang="en">Platform participants</span><span data-lang="ro">Participantele platformei</span></h2>
                        <p><span data-lang="ru">Предпринимательницы, которые уже развивают собственное дело при поддержке платформы — от пищевого производства до туризма и ремёсел.</span><span data-lang="en">Entrepreneurs already growing their businesses with the platform’s support — from food production to tourism and crafts.</span><span data-lang="ro">Antreprenoare care își dezvoltă deja afacerea cu sprijinul platformei — de la producție alimentară la turism și meșteșuguri.</span></p>
                    </div>

                    <button type="button" class="miro-carousel-btn miro-carousel-btn--prev" data-participants-prev aria-controls="miro-participants-track" aria-disabled="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                        <span class="miro-visually-hidden"><span data-lang="ru">Назад</span><span data-lang="en">Previous</span><span data-lang="ro">Înapoi</span></span>
                    </button>
                    <button type="button" class="miro-carousel-btn miro-carousel-btn--next" data-participants-next aria-controls="miro-participants-track">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                        <span class="miro-visually-hidden"><span data-lang="ru">Вперёд</span><span data-lang="en">Next</span><span data-lang="ro">Înainte</span></span>
                    </button>

                    <div class="miro-participants__track" id="miro-participants-track" data-participants-track tabindex="0" role="region" aria-roledescription="carousel" aria-labelledby="miro-participants-title">
                        @foreach($participants as $participant)
                            <article class="miro-participant-card">
                                @if($participant['photo'])
                                    <img class="miro-participant-card__avatar" src="{{ $participantImages }}/{{ \App\Support\Participants::thumb($participant['photo']) }}" alt="" width="56" height="56" loading="lazy" decoding="async">
                                @else
                                    @php
                                        $initialsSource = preg_split('/\s+/u', trim($participant['name']['ru']));
                                        $initials = mb_strtoupper(mb_substr($initialsSource[0] ?? '', 0, 1) . mb_substr($initialsSource[1] ?? '', 0, 1));
                                    @endphp
                                    <span class="miro-participant-card__avatar miro-participant-card__avatar--placeholder" aria-hidden="true">{{ $initials }}</span>
                                @endif
                                <div class="miro-participant-card__body">
                                    <span class="miro-participant-card__tag">@foreach($participantLocales as $locale)<span data-lang="{{ $locale }}">{{ $participant['tag'][$locale] }}</span>@endforeach</span>
                                    <h4>@foreach($participantLocales as $locale)<span data-lang="{{ $locale }}">{{ $participant['name'][$locale] }}</span>@endforeach</h4>
                                    <p>@foreach($participantLocales as $locale)<span data-lang="{{ $locale }}">{{ $participant['summary'][$locale] }}</span>@endforeach</p>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="miro-participants__dots" data-participants-dots></div>
                    <p class="miro-visually-hidden" data-participants-status aria-live="polite"></p>

                    <div class="miro-participants__cta">
                        <a class="miro-button miro-button--secondary" href="{{ route('members') }}"><span data-lang="ru">Все участницы&nbsp;→</span><span data-lang="en">All participants&nbsp;→</span><span data-lang="ro">Toate participantele&nbsp;→</span></a>
                    </div>
                </div>
            </div>
        </section>
        <section class="miro-section miro-section--tint" id="events">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Новости и возможности</span><span data-lang="en">News &amp; opportunities</span><span data-lang="ro">Noutăți și oportunități</span></p>
                    <h2><span data-lang="ru">Мероприятия, встречи и новые возможности</span><span data-lang="en">Meet, learn and discover new opportunities</span><span data-lang="ro">Întâlnește-te, învață și descoperă oportunități</span></h2>
                    <p><span data-lang="ru">Практические воркшопы, нетворкинг, объявления, гранты и партнёрские возможности — всё, что помогает двигаться дальше.</span><span data-lang="en">Practical workshops, networking, announcements, grants and partner opportunities to help you move forward.</span><span data-lang="ro">Workshopuri practice, networking, anunțuri, granturi și oportunități de parteneriat pentru următorul tău pas.</span></p>
                </div>
                <div class="miro-grid-3">
                    @foreach($landingEvents as $event)
                        @php
                            $tone = $event->toneKey();
                            $tagStyle = $tone === 'blue'
                                ? 'background:var(--miro-surface-featured);color:var(--miro-blue)'
                                : 'background:var(--miro-'.$tone.');color:var(--miro-primary)';
                        @endphp
                        @php $link = $event->cardLink(); @endphp
                        <article class="miro-event-card{{ $link ? ' miro-event-card--linked' : '' }}">
                            @if($event->imageUrl())
                                <img src="{{ $event->imageUrl() }}" alt="{{ $event->field('title', 'en') }}" loading="lazy">
                            @endif
                            <div class="miro-event-card__body">
                                <span class="miro-tag" style="{{ $tagStyle }}">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event->field('type', $locale) }}</span>@endforeach</span>
                                <div class="miro-event-card__date">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event->dateLabel($locale) }}</span>@endforeach</div>
                                <h3>
                                    @if($link)<a href="{{ $link['href'] }}"@if($link['external']) target="_blank" rel="noopener"@endif class="miro-event-card__title-link">@endif
                                    @foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event->field('title', $locale) }}</span>@endforeach
                                    @if($link)</a>@endif
                                </h3>
                                <p>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event->field('description', $locale) }}</span>@endforeach</p>
                                @if($link)@include('themes.public.miro.partials.card-more', ['external' => $link['external']])@endif
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="miro-events__footer">
                    <a href="{{ route('events') }}" class="miro-events__all-link"><span data-lang="ru">Все новости&nbsp;→</span><span data-lang="en">All news&nbsp;→</span><span data-lang="ro">Toate noutățile&nbsp;→</span></a>
                </div>
            </div>
        </section>

        {{-- #stories and the closing "Готовы собрать свой следующий шаг?" CTA moved to
             priorities.blade.php (see /about/priorities). --}}

        <section class="miro-section miro-section--surface" id="partners-strip">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <h2><span data-lang="ru">Наши партнёры</span><span data-lang="en">Our partners</span><span data-lang="ro">Partenerii noștri</span></h2>
                </div>
                <div class="miro-partners-strip">
                    <a class="miro-partner-chip" href="https://innovation.md/" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/coordinator-ida.png') }}" alt="Агентство инноваций и развития" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://creativity.md/" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/coordinator-creative.png') }}" alt="Ассоциация креативных индустрий Приднестровья" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://social.innovation.md/" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/coordinator-platform.png') }}" alt="Платформа социального предпринимательства" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://eba.md/" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/local-eba.png') }}" alt="European Business Association Moldova" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://afam.md/" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/local-afam.png') }}" alt="AFAM" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://glia.md/" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/local-glia.png') }}" alt="Glia Impact Hub" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://progen.md/" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/local-progen.png') }}" alt="Centrul Parteneriat pentru Dezvoltare" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://www.netherlandsandyou.nl/web/moldova" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/intl-netherlands.png') }}" alt="Королевство Нидерландов" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://www.nrc.no/moldova" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/intl-nrc.png') }}" alt="Норвежский совет по делам беженцев" loading="lazy"></a>
                    <a class="miro-partner-chip" href="https://moldova.unwomen.org/" target="_blank" rel="noopener"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/intl-unwomen.png') }}" alt="ООН-женщины" loading="lazy"></a>
                </div>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')
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

    <script defer src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/js/participants.js') }}"></script>
</body>
</html>
