@php
    $assetBase = 'images/landing-docs/';
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);

    $benefits = [
        ['icon' => 'fa-book-open', 'tone' => 'purple', 'ru_t' => 'Обучение', 'en_t' => 'Learning Hub', 'ro_t' => 'Învățare', 'ru_d' => 'Практические модули, видео и материалы для развития бизнеса.', 'en_d' => 'Practical modules, videos and resources for business growth.', 'ro_d' => 'Module practice, video și resurse pentru dezvoltarea afacerii.'],
        ['icon' => 'fa-users', 'tone' => 'teal', 'ru_t' => 'Каталог участниц', 'en_t' => 'Member Directory', 'ro_t' => 'Director', 'ru_d' => 'Профили предпринимательниц, эксперток, менторов и партнёров.', 'en_d' => 'Profiles of entrepreneurs, experts, mentors and partners.', 'ro_d' => 'Profiluri de antreprenoare, experte, mentori și partenere.'],
        ['icon' => 'fa-brain', 'tone' => 'orange', 'ru_t' => 'AI-подбор', 'en_t' => 'AI Matching', 'ro_t' => 'Potrivire AI', 'ru_d' => 'Умные рекомендации контактов, партнёров и возможностей.', 'en_d' => 'Smart recommendations for contacts, partners and opportunities.', 'ro_d' => 'Recomandări inteligente de contacte, parteneri și oportunități.'],
        ['icon' => 'fa-calendar-alt', 'tone' => 'indigo', 'ru_t' => 'События', 'en_t' => 'Events', 'ro_t' => 'Evenimente', 'ru_d' => 'Тренинги, встречи, B2B-форматы и партнёрские инициативы.', 'en_d' => 'Trainings, meetings, B2B formats and partnership initiatives.', 'ro_d' => 'Traininguri, întâlniri, formate B2B și inițiative de parteneriat.'],
        ['icon' => 'fa-user-friends', 'tone' => 'teal', 'ru_t' => 'Менторство', 'en_t' => 'Mentorship', 'ro_t' => 'Mentorat', 'ru_d' => 'Доступ к опытным предпринимательницам, экспертам и поддержке.', 'en_d' => 'Access to experienced entrepreneurs, experts and support.', 'ro_d' => 'Acces la antreprenoare cu experiență, experți și sprijin.'],
        ['icon' => 'fa-paper-plane', 'tone' => 'orange', 'ru_t' => 'Уведомления', 'en_t' => 'Alerts', 'ro_t' => 'Notificări', 'ru_d' => 'Telegram помогает получать важные новости и публикации.', 'en_d' => 'Telegram helps receive important updates and publications.', 'ro_d' => 'Telegram ajută la primirea noutăților și publicațiilor importante.'],
    ];

    $steps = [
        ['icon' => 'fa-user-plus', 'ru_t' => 'Подать заявку', 'en_t' => 'Apply', 'ro_t' => 'Aplică', 'ru_d' => 'Участница открывает @WomenComBot и отправляет заявку.', 'en_d' => 'A participant opens @WomenComBot and submits an application.', 'ro_d' => 'Participanta deschide @WomenComBot și trimite cererea.'],
        ['icon' => 'fa-id-card', 'ru_t' => 'Заполнить профиль', 'en_t' => 'Create profile', 'ro_t' => 'Completează profilul', 'ru_d' => 'Профиль показывает бизнес, опыт, запросы и предложения.', 'en_d' => 'The profile shows business, experience, needs and offers.', 'ro_d' => 'Profilul arată afacerea, experiența, nevoile și ofertele.'],
        ['icon' => 'fa-comments', 'ru_t' => 'Учиться и общаться', 'en_t' => 'Learn & connect', 'ro_t' => 'Învață și conectează-te', 'ru_d' => 'Платформа открывает контакты, материалы, события и менторов.', 'en_d' => 'The platform opens contacts, materials, events and mentors.', 'ro_d' => 'Platforma deschide contacte, materiale, evenimente și mentori.'],
        ['icon' => 'fa-chart-line', 'ru_t' => 'Развивать бизнес', 'en_t' => 'Grow business', 'ro_t' => 'Dezvoltă afacerea', 'ru_d' => 'Участницы находят возможности, партнёров и новые рынки.', 'en_d' => 'Participants find opportunities, partners and new markets.', 'ro_d' => 'Participantele găsesc oportunități, parteneri și piețe noi.'],
    ];

    $tools = [
        ['icon' => 'fa-user', 'ru' => 'Профиль', 'en' => 'Profile', 'ro' => 'Profil'],
        ['icon' => 'fa-store', 'ru' => 'Витрина', 'en' => 'Showcase', 'ro' => 'Vitrină'],
        ['icon' => 'fa-graduation-cap', 'ru' => 'Обучение', 'en' => 'Training', 'ro' => 'Instruire'],
        ['icon' => 'fa-network-wired', 'ru' => 'Нетворкинг', 'en' => 'Networking', 'ro' => 'Networking'],
        ['icon' => 'fa-user-tie', 'ru' => 'Менторы', 'en' => 'Mentors', 'ro' => 'Mentori'],
        ['icon' => 'fa-handshake', 'ru' => 'Запросы', 'en' => 'Requests', 'ro' => 'Cereri'],
        ['icon' => 'fa-calendar-check', 'ru' => 'Календарь', 'en' => 'Calendar', 'ro' => 'Calendar'],
        ['icon' => 'fa-book', 'ru' => 'Библиотека', 'en' => 'Library', 'ro' => 'Bibliotecă'],
    ];
@endphp

<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform</title>
    <meta name="description" content="Digital platform for women entrepreneurs: learning, networking, mentoring, events and business opportunities.">
    <meta name="theme-color" content="#4A1D96">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        html:not([lang="ru"]) [data-lang="ru"],
        html:not([lang="en"]) [data-lang="en"],
        html:not([lang="ro"]) [data-lang="ro"] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        .bg-purple-custom { background-color: #4A1D96; }
        .text-purple-custom { color: #4A1D96; }
        .border-purple-custom { border-color: #4A1D96; }
        .bg-teal-custom { background-color: #0D9488; }
        .text-teal-custom { color: #0D9488; }
        .border-teal-custom { border-color: #0D9488; }
        .hero-gradient { background: radial-gradient(circle at 82% 10%, rgba(13,148,136,.13), transparent 30%), linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%); }
        .tone-purple { background: #f3e8ff; color: #4A1D96; }
        .tone-teal { background: #ccfbf1; color: #0D9488; }
        .tone-orange { background: #ffedd5; color: #ea580c; }
        .tone-indigo { background: #e0e7ff; color: #4f46e5; }
        .lang-active { background: #4A1D96; color: white; }
        .lang-idle { color: #6b7280; }
        .hero-collage { position: relative; max-width: 560px; margin: 0 auto; padding: 0 0 108px; }
        .hero-collage::before {
            content: "";
            position: absolute;
            inset: auto -22px -4px;
            height: 58%;
            border-radius: 42px;
            background: radial-gradient(circle at 18% 30%, rgba(74,29,150,.08), transparent 30%), radial-gradient(circle at 90% 72%, rgba(13,148,136,.12), transparent 32%);
            filter: blur(4px);
            pointer-events: none;
        }
        .hero-collage__main,
        .hero-collage__portrait {
            position: relative;
            display: block;
            object-fit: cover;
            border: 6px solid #fff;
            box-shadow: 0 24px 55px rgba(17, 24, 39, .18);
        }
        .hero-collage__main {
            width: 100%;
            aspect-ratio: 1.42 / 1;
            border-radius: 34px 34px 18px 18px;
        }
        .hero-collage__portrait {
            position: absolute;
            z-index: 2;
            width: 34%;
            aspect-ratio: .86 / 1;
            border-radius: 26px;
        }
        .hero-collage__portrait.is-left { left: 4%; bottom: 58px; transform: rotate(-2deg); }
        .hero-collage__portrait.is-right { right: 4%; bottom: 34px; transform: rotate(3deg); }
        .hero-collage__label {
            position: absolute;
            left: 50%;
            bottom: 10px;
            z-index: 3;
            width: min(78%, 390px);
            transform: translateX(-50%);
            border: 1px solid rgba(74, 29, 150, .08);
            border-radius: 26px;
            background: rgba(255, 255, 255, .92);
            padding: 18px 22px;
            text-align: center;
            box-shadow: 0 20px 45px rgba(17, 24, 39, .14);
            backdrop-filter: blur(16px);
        }
        .hero-collage__label small {
            display: block;
            margin-bottom: 8px;
            color: #9ca3af;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .hero-collage__label strong {
            display: block;
            color: #1f2937;
            font-size: clamp(16px, 2.2vw, 20px);
            font-weight: 900;
            line-height: 1.35;
        }
        @media (max-width: 640px) {
            .hero-collage { padding-bottom: 116px; }
            .hero-collage__main { border-radius: 28px 28px 16px 16px; }
            .hero-collage__portrait { width: 38%; border-width: 5px; }
            .hero-collage__portrait.is-left { left: 2%; bottom: 66px; }
            .hero-collage__portrait.is-right { right: 2%; bottom: 44px; }
            .hero-collage__label { bottom: 2px; width: 86%; padding: 15px 18px; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <header class="sticky top-0 z-50 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset($assetBase . 'logo.png') }}" alt="Women Entrepreneurs Platform" class="h-12 w-12 object-contain">
                <div>
                    <p class="text-sm font-black uppercase leading-tight tracking-tighter text-purple-custom">Women<br>Entrepreneurs<br>Platform</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">of the Two Banks</p>
                </div>
            </a>

            <nav class="hidden items-center gap-6 text-sm font-bold lg:flex">
                <a href="#about" class="text-purple-custom"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a>
                <a href="#learning" class="text-gray-600 transition hover:text-purple-custom"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a>
                <a href="#members" class="text-gray-600 transition hover:text-purple-custom"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Participante</span></a>
                <a href="#events" class="text-gray-600 transition hover:text-purple-custom"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a>
                <a href="#contact" class="text-gray-600 transition hover:text-purple-custom"><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <div class="flex rounded-full border border-gray-200 bg-gray-50 p-1">
                    @foreach(['ru' => 'RU', 'en' => 'EN', 'ro' => 'RO'] as $locale => $label)
                        <button type="button" id="btn-{{ $locale }}" class="lang-btn rounded-full px-3 py-1 text-xs font-bold transition" data-locale="{{ $locale }}">{{ $label }}</button>
                    @endforeach
                </div>
                <a href="{{ route('account.login') }}" class="rounded-lg border border-purple-custom px-5 py-2 text-sm font-black text-purple-custom transition hover:bg-purple-50">
                    <span data-lang="ru">Войти</span><span data-lang="en">Login</span><span data-lang="ro">Intră</span>
                </a>
                <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="rounded-lg bg-purple-custom px-5 py-2 text-sm font-black text-white transition hover:bg-purple-800">
                    <span data-lang="ru">Присоединиться</span><span data-lang="en">Join Now</span><span data-lang="ro">Alătură-te</span>
                </a>
            </div>

            <button id="mobile-toggle" class="rounded-xl border border-gray-200 px-3 py-2 text-purple-custom md:hidden" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div id="mobile-menu" class="hidden border-t border-gray-100 bg-white px-4 py-4 md:hidden">
            <div class="mb-4 flex rounded-full border border-gray-200 bg-gray-50 p-1">
                @foreach(['ru' => 'RU', 'en' => 'EN', 'ro' => 'RO'] as $locale => $label)
                    <button type="button" id="btn-mobile-{{ $locale }}" class="lang-btn flex-1 rounded-full px-3 py-2 text-xs font-bold transition" data-locale="{{ $locale }}">{{ $label }}</button>
                @endforeach
            </div>
            <div class="grid gap-2 text-sm font-bold">
                <a href="#about" class="rounded-xl px-4 py-3 text-gray-700 hover:bg-purple-50">
                    <span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span>
                </a>
                <a href="#learning" class="rounded-xl px-4 py-3 text-gray-700 hover:bg-purple-50">
                    <span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span>
                </a>
                <a href="#members" class="rounded-xl px-4 py-3 text-gray-700 hover:bg-purple-50">
                    <span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Participante</span>
                </a>
                <a href="#events" class="rounded-xl px-4 py-3 text-gray-700 hover:bg-purple-50">
                    <span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span>
                </a>
                <a href="#contact" class="rounded-xl px-4 py-3 text-gray-700 hover:bg-purple-50">
                    <span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span>
                </a>
                <a href="{{ route('account.login') }}" class="rounded-xl px-4 py-3 text-purple-custom hover:bg-purple-50">
                    <span data-lang="ru">Войти</span><span data-lang="en">Login</span><span data-lang="ro">Intră</span>
                </a>
                <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="rounded-xl bg-purple-custom px-4 py-3 text-center text-white">
                    <span data-lang="ru">Присоединиться</span><span data-lang="en">Join Now</span><span data-lang="ro">Alătură-te</span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero-gradient overflow-hidden border-b border-gray-100">
            <div class="mx-auto flex max-w-7xl flex-col items-center px-4 py-16 lg:flex-row">
                <div class="mb-12 text-center lg:mb-0 lg:w-1/2 lg:pr-16 lg:text-left">
                    <p class="mb-4 text-sm font-black uppercase tracking-[0.28em] text-teal-custom">Women Entrepreneurs Platform</p>
                    <h1 class="mb-6 text-5xl font-black leading-[1.06] text-purple-custom lg:text-6xl">
                        <span data-lang="ru">Онлайн-платформа для женщин</span>
                        <span data-lang="en">Online platform for women entrepreneurs</span>
                        <span data-lang="ro">Platformă online pentru femei antreprenoare</span>
                    </h1>
                    <p class="mb-10 text-xl leading-relaxed text-gray-600">
                        <span data-lang="ru">Цифровое пространство для обучения, нетворкинга, менторства, видимости бизнеса и роста на обоих берегах.</span>
                        <span data-lang="en">A digital space for learning, networking, mentorship, business visibility and growth across both banks.</span>
                        <span data-lang="ro">Un spațiu digital pentru învățare, networking, mentorat, vizibilitatea afacerii și creștere pe ambele maluri.</span>
                    </p>
                    <div class="mb-10 flex flex-col justify-center gap-4 sm:flex-row lg:justify-start">
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="rounded-xl bg-purple-custom px-8 py-4 text-lg font-black text-white shadow-lg transition hover:-translate-y-1 hover:bg-purple-800">
                            <span data-lang="ru">Присоединиться к платформе</span><span data-lang="en">Join the Platform</span><span data-lang="ro">Alătură-te platformei</span>
                            <i class="fas fa-chevron-right ml-2 text-sm"></i>
                        </a>
                        <a href="#learning" class="rounded-xl border-2 border-teal-custom px-8 py-4 text-lg font-black text-teal-custom transition hover:-translate-y-1 hover:bg-teal-50">
                            <span data-lang="ru">Изучить возможности</span><span data-lang="en">Explore Learning</span><span data-lang="ro">Explorează învățarea</span>
                        </a>
                    </div>
                    <div class="inline-flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm">
                        <div class="-space-x-3">
                            <img class="inline h-10 w-10 rounded-full border-2 border-white object-cover shadow-sm" src="{{ asset($assetBase . 'member1.jpg') }}" alt="">
                            <img class="inline h-10 w-10 rounded-full border-2 border-white object-cover shadow-sm" src="{{ asset($assetBase . 'member2.jpg') }}" alt="">
                            <img class="inline h-10 w-10 rounded-full border-2 border-white object-cover shadow-sm" src="{{ asset($assetBase . 'member3.jpg') }}" alt="">
                        </div>
                        <div class="text-left">
                            <span class="text-2xl font-black text-teal-custom">500+</span>
                            <p class="text-xs font-black uppercase tracking-wider text-gray-400">
                                <span data-lang="ru">женщин для объединения</span><span data-lang="en">women to be connected</span><span data-lang="ro">femei pentru conectare</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="relative lg:w-1/2">
                    <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-purple-100 opacity-70 blur-3xl"></div>
                    <div class="absolute -bottom-10 -left-10 h-64 w-64 rounded-full bg-teal-100 opacity-70 blur-3xl"></div>
                    <div class="hero-collage">
                        <img src="{{ asset($assetBase . 'hero.jpg') }}" alt="Women working" class="hero-collage__main">
                        <img src="{{ asset($assetBase . 'member1.jpg') }}" alt="Platform member" class="hero-collage__portrait is-left">
                        <img src="{{ asset($assetBase . 'member2.jpg') }}" alt="Platform member" class="hero-collage__portrait is-right">
                        <div class="hero-collage__label">
                            <small>
                                <span data-lang="ru">Два берега · одна сеть</span>
                                <span data-lang="en">Two banks · one network</span>
                                <span data-lang="ro">Două maluri · o rețea</span>
                            </small>
                            <strong>
                                <span data-lang="ru">Менторство, события и партнёрства для женского бизнеса</span>
                                <span data-lang="en">Mentorship, events and partnerships for women-led businesses</span>
                                <span data-lang="ro">Mentorat, evenimente și parteneriate pentru afaceri conduse de femei</span>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 text-center">
                <h2 class="mb-4 text-sm font-black uppercase tracking-[0.3em] text-purple-custom">
                    <span data-lang="ru">Почему стоит присоединиться</span><span data-lang="en">Why Join? Key Benefits</span><span data-lang="ro">De ce să te alături</span>
                </h2>
                <div class="mx-auto mb-16 h-1 w-20 rounded-full bg-purple-custom"></div>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-6">
                    @foreach($benefits as $benefit)
                        <div class="group rounded-3xl border border-gray-100 p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="tone-{{ $benefit['tone'] }} mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl transition group-hover:scale-110">
                                <i class="fas {{ $benefit['icon'] }} text-2xl"></i>
                            </div>
                            <h3 class="mb-3 text-lg font-black"><span data-lang="ru">{{ $benefit['ru_t'] }}</span><span data-lang="en">{{ $benefit['en_t'] }}</span><span data-lang="ro">{{ $benefit['ro_t'] }}</span></h3>
                            <p class="text-sm leading-relaxed text-gray-500"><span data-lang="ru">{{ $benefit['ru_d'] }}</span><span data-lang="en">{{ $benefit['en_d'] }}</span><span data-lang="ro">{{ $benefit['ro_d'] }}</span></p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-gray-50 py-24">
            <div class="mx-auto max-w-7xl px-4 text-center">
                <h2 class="mb-4 text-sm font-black uppercase tracking-[0.3em] text-purple-custom">
                    <span data-lang="ru">Как это работает</span><span data-lang="en">How It Works</span><span data-lang="ro">Cum funcționează</span>
                </h2>
                <div class="mx-auto mb-20 h-1 w-20 rounded-full bg-purple-custom"></div>
                <div class="grid gap-10 lg:grid-cols-4">
                    @foreach($steps as $index => $step)
                        <div class="relative px-4">
                            <div class="mx-auto mb-8 flex h-32 w-32 items-center justify-center rounded-full border-4 {{ $index % 2 === 0 ? 'border-teal-500' : 'border-purple-custom' }} bg-white shadow-xl">
                                <i class="fas {{ $step['icon'] }} text-4xl {{ $index % 2 === 0 ? 'text-teal-500' : 'text-purple-custom' }}"></i>
                            </div>
                            <div class="absolute left-1/2 top-0 -mt-4 flex h-10 w-10 -translate-x-1/2 items-center justify-center rounded-full border-4 border-gray-50 bg-purple-custom text-xl font-black text-white shadow-lg">{{ $index + 1 }}</div>
                            <h3 class="mb-4 text-xl font-black"><span data-lang="ru">{{ $step['ru_t'] }}</span><span data-lang="en">{{ $step['en_t'] }}</span><span data-lang="ro">{{ $step['ro_t'] }}</span></h3>
                            <p class="leading-relaxed text-gray-500"><span data-lang="ru">{{ $step['ru_d'] }}</span><span data-lang="en">{{ $step['en_d'] }}</span><span data-lang="ro">{{ $step['ro_d'] }}</span></p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="learning" class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 text-center">
                <h2 class="mb-4 text-sm font-black uppercase tracking-[0.3em] text-teal-custom">
                    <span data-lang="ru">Что предлагает платформа</span><span data-lang="en">What The Platform Offers</span><span data-lang="ro">Ce oferă platforma</span>
                </h2>
                <div class="mx-auto mb-16 h-1 w-20 rounded-full bg-teal-custom"></div>
                <div class="grid grid-cols-2 gap-6 md:grid-cols-4 lg:grid-cols-8">
                    @foreach($tools as $tool)
                        <div class="group flex flex-col items-center">
                            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-3xl border-2 border-teal-100 text-teal-custom transition group-hover:border-teal-500 group-hover:bg-teal-500 group-hover:text-white">
                                <i class="fas {{ $tool['icon'] }} text-2xl"></i>
                            </div>
                            <span class="text-xs font-black uppercase tracking-widest text-gray-500"><span data-lang="ru">{{ $tool['ru'] }}</span><span data-lang="en">{{ $tool['en'] }}</span><span data-lang="ro">{{ $tool['ro'] }}</span></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="members" class="bg-gray-50 py-24">
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-16 px-4 lg:grid-cols-2">
                <div>
                    <div class="mb-12 flex items-end justify-between">
                        <div>
                            <h2 class="mb-2 text-sm font-black uppercase tracking-[0.3em] text-purple-custom">
                                <span data-lang="ru">Участницы</span><span data-lang="en">Featured Members</span><span data-lang="ro">Participante</span>
                            </h2>
                            <div class="h-1 w-16 rounded-full bg-purple-custom"></div>
                        </div>
                        <a href="{{ route('account.login') }}" class="border-b-2 border-purple-custom pb-1 text-sm font-black text-purple-custom">
                            <span data-lang="ru">Войти →</span><span data-lang="en">Login →</span><span data-lang="ro">Intră →</span>
                        </a>
                    </div>
                    <div class="grid gap-6 md:grid-cols-3">
                        @foreach([
                            ['img' => 'member1.jpg', 'name' => 'Anna Petrova', 'field' => 'Fashion & Design'],
                            ['img' => 'member2.jpg', 'name' => 'Elena Rusu', 'field' => 'Agri-food'],
                            ['img' => 'member3.jpg', 'name' => 'Marina Ionescu', 'field' => 'IT & Digital'],
                        ] as $member)
                            <div class="rounded-3xl border border-gray-100 bg-white p-6 text-center shadow-sm transition hover:shadow-xl">
                                <img src="{{ asset($assetBase . $member['img']) }}" alt="{{ $member['name'] }}" class="mx-auto mb-6 h-28 w-28 rounded-full border-4 border-purple-50 object-cover">
                                <h3 class="mb-1 text-lg font-black">{{ $member['name'] }}</h3>
                                <p class="mb-6 text-xs font-black uppercase tracking-wider text-gray-400">{{ $member['field'] }}</p>
                                <span class="block rounded-lg bg-purple-100 py-2 text-[10px] font-black uppercase text-purple-700">
                                    <span data-lang="ru">пример профиля</span><span data-lang="en">profile example</span><span data-lang="ro">exemplu profil</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="events">
                    <div class="mb-12 flex items-end justify-between">
                        <div>
                            <h2 class="mb-2 text-sm font-black uppercase tracking-[0.3em] text-teal-custom">
                                <span data-lang="ru">События</span><span data-lang="en">Upcoming Events</span><span data-lang="ro">Evenimente</span>
                            </h2>
                            <div class="h-1 w-16 rounded-full bg-teal-custom"></div>
                        </div>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="border-b-2 border-teal-custom pb-1 text-sm font-black text-teal-custom">
                            <span data-lang="ru">Присоединиться →</span><span data-lang="en">Join Now →</span><span data-lang="ro">Alătură-te →</span>
                        </a>
                    </div>
                    <div class="space-y-6">
                        @foreach([
                            ['img' => 'event1.jpg', 'title' => 'Women Business School', 'day' => '18'],
                            ['img' => 'event2.jpg', 'title' => 'ESG for SMEs Webinar', 'day' => '25'],
                        ] as $event)
                            <div class="group flex overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:shadow-xl">
                                <div class="w-2/5 overflow-hidden"><img src="{{ asset($assetBase . $event['img']) }}" alt="{{ $event['title'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-110"></div>
                                <div class="flex w-3/5 flex-col justify-between p-6">
                                    <div>
                                        <h3 class="mb-3 text-lg font-black leading-tight">{{ $event['title'] }}</h3>
                                        <div class="mb-4 flex items-center gap-4">
                                            <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-center">
                                                <span class="block text-[10px] font-black uppercase text-purple-custom">JUN</span>
                                                <span class="block text-xl font-black text-gray-800">{{ $event['day'] }}</span>
                                            </div>
                                            <p class="text-xs font-medium leading-relaxed text-gray-500">
                                                <span data-lang="ru">Онлайн и офлайн форматы для участниц платформы.</span>
                                                <span data-lang="en">Online and offline formats for platform participants.</span>
                                                <span data-lang="ro">Formate online și offline pentru participantele platformei.</span>
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="rounded-xl bg-teal-custom py-3 text-center text-sm font-black text-white shadow-md transition hover:bg-teal-700">
                                        <span data-lang="ru">Узнать через Telegram</span><span data-lang="en">Ask in Telegram</span><span data-lang="ro">Întreabă pe Telegram</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 text-center">
                <h2 class="mb-4 text-sm font-black uppercase tracking-[0.3em] text-purple-custom">
                    <span data-lang="ru">Истории роста</span><span data-lang="en">Success Stories</span><span data-lang="ro">Istorii de succes</span>
                </h2>
                <div class="mx-auto mb-20 h-1 w-20 rounded-full bg-purple-custom"></div>
                <div class="grid gap-10 text-left lg:grid-cols-2">
                    @foreach([
                        ['img' => 'success1.jpg', 'title_ru' => 'Менторство помогло усилить бизнес-модель', 'title_en' => 'How mentorship helped me expand my business', 'title_ro' => 'Cum mentoratul m-a ajutat să dezvolt afacerea'],
                        ['img' => 'success2.jpg', 'title_ru' => 'От локального бизнеса к новым рынкам', 'title_en' => 'From local to international thanks to the platform', 'title_ro' => 'De la local la piețe noi prin platformă'],
                    ] as $story)
                        <div class="flex flex-col items-center gap-6 rounded-[3rem] border border-gray-100 bg-gray-50 p-10 shadow-sm transition hover:shadow-xl md:flex-row md:items-start">
                            <img src="{{ asset($assetBase . $story['img']) }}" alt="" class="h-32 w-32 rounded-3xl border-4 border-white object-cover shadow-lg">
                            <div>
                                <i class="fas fa-quote-left mb-6 block text-4xl text-teal-custom opacity-50"></i>
                                <h3 class="mb-4 text-xl font-black leading-tight text-purple-custom"><span data-lang="ru">{{ $story['title_ru'] }}</span><span data-lang="en">{{ $story['title_en'] }}</span><span data-lang="ro">{{ $story['title_ro'] }}</span></h3>
                                <p class="mb-6 italic leading-relaxed text-gray-500">
                                    <span data-lang="ru">Истории участниц будут обновляться по мере развития сообщества и появления новых результатов.</span>
                                    <span data-lang="en">Participant stories will be updated as the community grows and new results appear.</span>
                                    <span data-lang="ro">Istoriile participantelor vor fi actualizate pe măsură ce comunitatea crește.</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <footer id="contact" class="bg-purple-custom pb-12 pt-24 text-white">
        <div class="mx-auto max-w-7xl px-4">
            <div class="mb-20 grid grid-cols-1 gap-16 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="mb-8 flex items-center gap-3">
                        <img src="{{ asset($assetBase . 'logo.png') }}" alt="" class="h-12 w-12 object-contain brightness-0 invert">
                        <p class="text-sm font-black uppercase leading-tight tracking-tighter">Women<br>Entrepreneurs<br>Platform</p>
                    </div>
                    <p class="mb-8 text-sm leading-relaxed text-purple-200">
                        <span data-lang="ru">Цифровая экосистема для обучения, нетворкинга, менторства и роста бизнеса.</span>
                        <span data-lang="en">A digital ecosystem for learning, networking, mentoring and business growth.</span>
                        <span data-lang="ro">Un ecosistem digital pentru învățare, networking, mentorat și creșterea afacerilor.</span>
                    </p>
                </div>

                <div>
                    <h4 class="mb-8 text-xs font-black uppercase tracking-[0.3em]"><span data-lang="ru">Платформа</span><span data-lang="en">Quick Links</span><span data-lang="ro">Platformă</span></h4>
                    <ul class="space-y-4 text-sm font-medium text-purple-200">
                        <li><a href="#about" class="transition hover:text-white"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a></li>
                        <li><a href="#learning" class="transition hover:text-white"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a></li>
                        <li><a href="#members" class="transition hover:text-white"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Participante</span></a></li>
                        <li><a href="#events" class="transition hover:text-white"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-8 text-xs font-black uppercase tracking-[0.3em]"><span data-lang="ru">Контакты</span><span data-lang="en">Contact Details</span><span data-lang="ro">Contact</span></h4>
                    <ul class="space-y-6 text-sm text-purple-200">
                        <li class="flex items-center"><span class="mr-4 flex h-8 w-8 items-center justify-center rounded-lg bg-purple-800"><i class="fab fa-telegram-plane"></i></span><a href="{{ $botUrl }}" target="_blank" rel="noopener" class="hover:text-white">@WomenComBot</a></li>
                        <li class="flex items-center"><span class="mr-4 flex h-8 w-8 items-center justify-center rounded-lg bg-purple-800"><i class="fas fa-user"></i></span><a href="{{ $managerUrl }}" target="_blank" rel="noopener" class="hover:text-white">@lesnichenkoP</a></li>
                        <li class="flex items-center"><span class="mr-4 flex h-8 w-8 items-center justify-center rounded-lg bg-purple-800"><i class="fas fa-users"></i></span><a href="{{ $communityUrl }}" target="_blank" rel="noopener" class="hover:text-white">TELEGRAM_COMMUNITY_URL</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-8 text-xs font-black uppercase tracking-[0.3em]"><span data-lang="ru">Присоединиться</span><span data-lang="en">Join</span><span data-lang="ro">Alătură-te</span></h4>
                    <p class="mb-6 text-sm text-purple-200">
                        <span data-lang="ru">Заявка и вход работают через Telegram.</span>
                        <span data-lang="en">Application and login work via Telegram.</span>
                        <span data-lang="ro">Aplicarea și autentificarea funcționează prin Telegram.</span>
                    </p>
                    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="block rounded-xl bg-teal-500 py-3 text-center font-black text-white transition hover:bg-teal-400">WomenComBot</a>
                </div>
            </div>

            <div class="flex flex-col items-center justify-between border-t border-purple-800 pt-12 text-[10px] font-black uppercase tracking-widest text-purple-300 md:flex-row">
                <p>© {{ date('Y') }} Women Entrepreneurs Platform</p>
                <a href="{{ route('account.login') }}" class="mt-6 hover:text-white md:mt-0">
                    <span data-lang="ru">Вход в кабинет</span><span data-lang="en">Account login</span><span data-lang="ro">Intrare în cabinet</span>
                </a>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            const LANG_KEY = 'inspair_lang';

            function setLang(lang) {
                if (!['ru', 'en', 'ro'].includes(lang)) lang = 'ru';
                localStorage.setItem(LANG_KEY, lang);
                document.documentElement.lang = lang;
                document.querySelectorAll('.lang-btn').forEach(function (button) {
                    const active = button.dataset.locale === lang;
                    button.classList.toggle('lang-active', active);
                    button.classList.toggle('lang-idle', !active);
                });
            }

            const saved = localStorage.getItem(LANG_KEY);
            const browser = (navigator.language || '').slice(0, 2);
            setLang(saved || (['ru', 'en', 'ro'].includes(browser) ? browser : 'ru'));

            document.querySelectorAll('.lang-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    setLang(button.dataset.locale);
                });
            });

            const mobileToggle = document.getElementById('mobile-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileToggle && mobileMenu) {
                mobileToggle.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                });
                mobileMenu.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        mobileMenu.classList.add('hidden');
                    });
                });
            }

            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                tg.ready();
                document.querySelectorAll('a[href*="t.me/WomenComBot"], a[href*="t.me/lesnichenkoP"]').forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        tg.openTelegramLink(link.href);
                    });
                });
            }
        })();
    </script>
</body>
</html>
