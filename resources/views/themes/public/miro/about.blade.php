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
    <title>Women Entrepreneurs Platform — About</title>
    <meta name="description" content="About Women Entrepreneurs Platform: mission, community and opportunities for women entrepreneurs from both banks.">
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/about.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'about'])

    <main class="miro-about-page" id="about">
        <section class="miro-about-hero">
            <div class="miro-container miro-about-hero__inner">
                <div>
                    <p class="miro-eyebrow"><span data-lang="ru">О платформе</span><span data-lang="en">About the platform</span><span data-lang="ro">Despre platformă</span></p>
                    <h1><span data-lang="ru">Место, где женщины развивают бизнес вместе</span><span data-lang="en">A place where women grow business together</span><span data-lang="ro">Un loc în care femeile dezvoltă afaceri împreună</span></h1>
                    <p class="miro-about-hero__copy"><span data-lang="ru">Women Entrepreneurs Platform — это цифровая экосистема для обучения, деловых связей, наставничества и новых возможностей женщин-предпринимательниц с обоих берегов.</span><span data-lang="en">Women Entrepreneurs Platform is a digital ecosystem for learning, business connections, mentorship and new opportunities for women entrepreneurs from both banks.</span><span data-lang="ro">Women Entrepreneurs Platform este un ecosistem digital pentru învățare, conexiuni de business, mentorat și oportunități noi pentru femeile antreprenoare de pe ambele maluri.</span></p>
                </div>
                <div class="miro-about-hero__visual">
                    <img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/hero-community.webp') }}" alt="Women entrepreneurs collaborating">
                    <span class="miro-about-hero__sticker"><span data-lang="ru">Знания · Связи · Рост</span><span data-lang="en">Knowledge · Connection · Growth</span><span data-lang="ro">Cunoștințe · Conexiuni · Creștere</span></span>
                </div>
            </div>
        </section>

        <section class="miro-about-section">
            <div class="miro-container miro-about-split">
                <div class="miro-about-split__image"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/story-mentor.webp') }}" alt="Mentorship and professional growth" loading="lazy"></div>
                <div class="miro-about-split__copy">
                    <p class="miro-eyebrow"><span data-lang="ru">Наша миссия</span><span data-lang="en">Our mission</span><span data-lang="ro">Misiunea noastră</span></p>
                    <h2><span data-lang="ru">Сделать рост более доступным</span><span data-lang="en">Make growth more accessible</span><span data-lang="ro">Facem creșterea mai accesibilă</span></h2>
                    <p><span data-lang="ru">Платформа создаёт практическое пространство, где предпринимательница может представить свой бизнес, получить знания, найти полезные контакты и сделать следующий шаг увереннее.</span><span data-lang="en">The platform creates a practical space where an entrepreneur can present her business, gain knowledge, find useful connections and take her next step with confidence.</span><span data-lang="ro">Platforma creează un spațiu practic în care o antreprenoare își poate prezenta afacerea, poate învăța, găsi contacte utile și face următorul pas cu mai multă încredere.</span></p>
                    <ul class="miro-about-list">
                        <li><span class="miro-about-list__mark">✓</span><span data-lang="ru">Поддерживать женщин на этапе запуска, развития и масштабирования бизнеса.</span><span data-lang="en">Support women as they start, develop and scale their businesses.</span><span data-lang="ro">Susținem femeile la lansarea, dezvoltarea și extinderea afacerii.</span></li>
                        <li><span class="miro-about-list__mark">✓</span><span data-lang="ru">Укреплять доступ к знаниям, рынкам, экспертам и партнёрам.</span><span data-lang="en">Strengthen access to knowledge, markets, experts and partners.</span><span data-lang="ro">Consolidăm accesul la cunoștințe, piețe, experți și parteneri.</span></li>
                        <li><span class="miro-about-list__mark">✓</span><span data-lang="ru">Создавать сотрудничество между женщинами с обоих берегов.</span><span data-lang="en">Create cooperation between women from both banks.</span><span data-lang="ro">Creăm cooperare între femeile de pe ambele maluri.</span></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="miro-about-section miro-about-section--soft">
            <div class="miro-container">
                <div class="miro-about-section__head miro-about-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Не просто информационный сайт</span><span data-lang="en">More than an information website</span><span data-lang="ro">Mai mult decât un site informativ</span></p>
                    <h2><span data-lang="ru">Всё необходимое для следующего шага</span><span data-lang="en">Everything for your next step</span><span data-lang="ro">Totul pentru următorul tău pas</span></h2>
                    <p><span data-lang="ru">Платформа объединяет инструменты, которые помогают превращать знания и контакты в реальные действия.</span><span data-lang="en">The platform brings together tools that turn knowledge and connections into real action.</span><span data-lang="ro">Platforma reunește instrumente care transformă cunoștințele și conexiunile în acțiuni reale.</span></p>
                </div>
                <div class="miro-about-cards">
                    <article class="miro-about-card"><span class="miro-about-card__number">01</span><h3><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></h3><p><span data-lang="ru">Практические модули, материалы и знания для бизнеса.</span><span data-lang="en">Practical modules, materials and knowledge for business.</span><span data-lang="ro">Module practice, materiale și cunoștințe pentru business.</span></p></article>
                    <article class="miro-about-card"><span class="miro-about-card__number">02</span><h3><span data-lang="ru">Видимость бизнеса</span><span data-lang="en">Business visibility</span><span data-lang="ro">Vizibilitatea afacerii</span></h3><p><span data-lang="ru">Профиль, через который проще рассказать о своей работе.</span><span data-lang="en">A profile that makes it easier to present your work.</span><span data-lang="ro">Un profil prin care îți prezinți mai ușor activitatea.</span></p></article>
                    <article class="miro-about-card"><span class="miro-about-card__number">03</span><h3><span data-lang="ru">Партнёры и рынки</span><span data-lang="en">Partners &amp; markets</span><span data-lang="ro">Parteneri și piețe</span></h3><p><span data-lang="ru">Связи для сотрудничества, выхода на новые рынки и роста.</span><span data-lang="en">Connections for cooperation, new markets and growth.</span><span data-lang="ro">Conexiuni pentru cooperare, piețe noi și creștere.</span></p></article>
                    <article class="miro-about-card"><span class="miro-about-card__number">04</span><h3><span data-lang="ru">Наставничество</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></h3><p><span data-lang="ru">Доступ к опыту предпринимательниц, эксперток и консультантов.</span><span data-lang="en">Access to experienced entrepreneurs, experts and advisors.</span><span data-lang="ro">Acces la experiența antreprenoarelor, experților și consultanților.</span></p></article>
                    <article class="miro-about-card"><span class="miro-about-card__number">05</span><h3><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></h3><p><span data-lang="ru">Воркшопы, форумы, встречи, консультации и новости.</span><span data-lang="en">Workshops, forums, meetings, consultations and news.</span><span data-lang="ro">Workshopuri, forumuri, întâlniri, consultanță și noutăți.</span></p></article>
                    <article class="miro-about-card"><span class="miro-about-card__number">06</span><h3><span data-lang="ru">AI-рекомендации</span><span data-lang="en">AI recommendations</span><span data-lang="ro">Recomandări AI</span></h3><p><span data-lang="ru">Навигация по контактам, материалам и возможностям на основе потребностей.</span><span data-lang="en">Navigate contacts, materials and opportunities based on your needs.</span><span data-lang="ro">Găsește contacte, materiale și oportunități potrivite nevoilor tale.</span></p></article>
                </div>
            </div>
        </section>

        <section class="miro-about-section">
            <div class="miro-container">
                <div class="miro-about-section__head">
                    <p class="miro-eyebrow"><span data-lang="ru">Для кого платформа</span><span data-lang="en">Who it is for</span><span data-lang="ro">Pentru cine este platforma</span></p>
                    <h2><span data-lang="ru">Для разных этапов и разных бизнесов</span><span data-lang="en">For different stages and businesses</span><span data-lang="ro">Pentru etape și afaceri diferite</span></h2>
                </div>
                <div class="miro-about-audience">
                    <div class="miro-about-audience__item"><strong><span data-lang="ru">Предпринимательницы</span><span data-lang="en">Women entrepreneurs</span><span data-lang="ro">Femei antreprenoare</span></strong><span data-lang="ru">Для тех, кто уже развивает собственный бизнес.</span><span data-lang="en">For women already running their own business.</span><span data-lang="ro">Pentru femeile care dezvoltă deja o afacere.</span></div>
                    <div class="miro-about-audience__item"><strong><span data-lang="ru">Начинающие предпринимательницы</span><span data-lang="en">Aspiring entrepreneurs</span><span data-lang="ro">Viitoare antreprenoare</span></strong><span data-lang="ru">Для тех, кто проверяет идею и делает первые шаги.</span><span data-lang="en">For women validating an idea and taking their first steps.</span><span data-lang="ro">Pentru femeile care își verifică ideea și fac primii pași.</span></div>
                    <div class="miro-about-audience__item"><strong><span data-lang="ru">Самозанятые и фрилансерки</span><span data-lang="en">Self-employed &amp; freelancers</span><span data-lang="ro">Freelanceri și persoane independente</span></strong><span data-lang="ru">Для специалистов, которые хотят укрепить свою практику.</span><span data-lang="en">For specialists who want to strengthen their practice.</span><span data-lang="ro">Pentru specialistele care vor să-și consolideze activitatea.</span></div>
                    <div class="miro-about-audience__item"><strong><span data-lang="ru">Эксперты и наставники</span><span data-lang="en">Experts &amp; mentors</span><span data-lang="ro">Experți și mentori</span></strong><span data-lang="ru">Для тех, кто готов делиться опытом и поддерживать других.</span><span data-lang="en">For people ready to share experience and support others.</span><span data-lang="ro">Pentru cei gata să împărtășească experiență și să susțină alte femei.</span></div>
                    <div class="miro-about-audience__item"><strong><span data-lang="ru">Женщины из малых городов и сёл</span><span data-lang="en">Women from small towns and rural areas</span><span data-lang="ro">Femei din orașe mici și zone rurale</span></strong><span data-lang="ru">Для тех, кому важны доступ к знаниям и новые связи.</span><span data-lang="en">For women who need access to knowledge and new connections.</span><span data-lang="ro">Pentru femeile care au nevoie de cunoștințe și conexiuni noi.</span></div>
                    <div class="miro-about-audience__item"><strong><span data-lang="ru">Партнёры и организации</span><span data-lang="en">Partners &amp; organisations</span><span data-lang="ro">Parteneri și organizații</span></strong><span data-lang="ru">Для тех, кто хочет создавать программы и возможности вместе.</span><span data-lang="en">For organisations that want to create programmes and opportunities together.</span><span data-lang="ro">Pentru organizațiile care vor să creeze împreună programe și oportunități.</span></div>
                </div>
            </div>
        </section>

        <section class="miro-about-section miro-about-section--soft">
            <div class="miro-container">
                <div class="miro-about-section__head miro-about-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Как это работает</span><span data-lang="en">How it works</span><span data-lang="ro">Cum funcționează</span></p>
                    <h2><span data-lang="ru">От профиля к реальному сотрудничеству</span><span data-lang="en">From profile to real collaboration</span><span data-lang="ro">De la profil la colaborare reală</span></h2>
                </div>
                <div class="miro-about-steps">
                    <div class="miro-about-step"><div class="miro-about-step__number">1</div><h3><span data-lang="ru">Зарегистрируйтесь</span><span data-lang="en">Register</span><span data-lang="ro">Înregistrează-te</span></h3><p><span data-lang="ru">Создайте доступ через сайт или Telegram.</span><span data-lang="en">Create access through the website or Telegram.</span><span data-lang="ro">Creează acces prin site sau Telegram.</span></p></div>
                    <div class="miro-about-step"><div class="miro-about-step__number">2</div><h3><span data-lang="ru">Расскажите о себе</span><span data-lang="en">Build your profile</span><span data-lang="ro">Completează profilul</span></h3><p><span data-lang="ru">Укажите, что представляете, ищете и можете предложить.</span><span data-lang="en">Share what you represent, seek and can offer.</span><span data-lang="ro">Spune ce reprezinți, ce cauți și ce poți oferi.</span></p></div>
                    <div class="miro-about-step"><div class="miro-about-step__number">3</div><h3><span data-lang="ru">Получайте рекомендации</span><span data-lang="en">Get recommendations</span><span data-lang="ro">Primește recomandări</span></h3><p><span data-lang="ru">Находите материалы, людей, события и возможности под свои задачи.</span><span data-lang="en">Discover people, materials, events and opportunities for your needs.</span><span data-lang="ro">Descoperă oameni, materiale, evenimente și oportunități potrivite.</span></p></div>
                    <div class="miro-about-step"><div class="miro-about-step__number">4</div><h3><span data-lang="ru">Связывайтесь и растите</span><span data-lang="en">Connect &amp; grow</span><span data-lang="ro">Conectează-te și crește</span></h3><p><span data-lang="ru">Находите партнёров, наставников, клиентов и поддержку.</span><span data-lang="en">Find partners, mentors, clients and support.</span><span data-lang="ro">Găsește parteneri, mentori, clienți și suport.</span></p></div>
                </div>
            </div>
        </section>

        <section class="miro-about-section">
            <div class="miro-container miro-about-bridge">
                <div class="miro-about-bridge__inner">
                    <div>
                        <p class="miro-eyebrow"><span data-lang="ru">Два берега — больше возможностей</span><span data-lang="en">Two banks — more opportunities</span><span data-lang="ro">Două maluri — mai multe oportunități</span></p>
                        <h2><span data-lang="ru">Сотрудничество, которое становится сильнее вместе</span><span data-lang="en">Cooperation grows stronger together</span><span data-lang="ro">Cooperarea devine mai puternică împreună</span></h2>
                        <p><span data-lang="ru">Платформа помогает превращать профессиональные связи между женщинами с обоих берегов в совместные проекты, обмен опытом и устойчивый рост.</span><span data-lang="en">The platform turns professional connections between women from both banks into joint projects, shared experience and sustainable growth.</span><span data-lang="ro">Platforma transformă conexiunile profesionale dintre femeile de pe ambele maluri în proiecte comune, schimb de experiență și creștere sustenabilă.</span></p>
                    </div>
                    <div class="miro-about-proof"><strong>500+</strong><span data-lang="ru">женщин — ориентир сообщества и долгосрочная цель платформы</span><span data-lang="en">women — the community target and long-term platform goal</span><span data-lang="ro">de femei — obiectivul comunității și ținta pe termen lung a platformei</span></div>
                </div>
            </div>
        </section>

        <section class="miro-container miro-about-cta">
            <h2><span data-lang="ru">Станьте частью платформы</span><span data-lang="en">Become part of the platform</span><span data-lang="ro">Devino parte a platformei</span></h2>
            <p><span data-lang="ru">Создайте профиль, находите нужные связи и открывайте возможности для своего бизнеса.</span><span data-lang="en">Create your profile, find the right connections and discover opportunities for your business.</span><span data-lang="ro">Creează-ți profilul, găsește conexiunile potrivite și descoperă oportunități pentru afacerea ta.</span></p>
            <div class="miro-about-cta__actions"><a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary"><span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span></a><a href="{{ route('members') }}" class="miro-button miro-button--secondary"><span data-lang="ru">Посмотреть участников</span><span data-lang="en">Meet the members</span><span data-lang="ro">Vezi membrele</span></a></div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')

    <script src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/js/about.js') }}"></script>
</body>
</html>


