@if(($landingTheme ?? 'classic') === 'platform')
    @include('landing-platform')
@else
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform</title>
    <meta name="description" content="A digital ecosystem for women entrepreneurs from both banks, combining learning, networking, business visibility, mentoring, opportunities, AI-supported recommendations and Telegram-based communication.">
    <meta property="og:title" content="A digital space where women's businesses connect, learn and grow">
    <meta property="og:description" content="Join a long-term platform for women entrepreneurs, women-led MSMEs, mentors, experts and partner organisations.">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#123C3A">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans:    ['Inter', 'system-ui', 'sans-serif'],
                        heading: ['Manrope', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#e8f1ef',
                            100: '#c5dbd7',
                            200: '#9dc3bc',
                            300: '#75aaa0',
                            400: '#58978b',
                            500: '#3a8476',
                            600: '#123C3A',
                            700: '#0e302e',
                            800: '#0a2423',
                            900: '#061817',
                        },
                        accent: {
                            50:  '#fdf0ed',
                            100: '#f9d8d0',
                            200: '#f4b8a9',
                            300: '#ee9882',
                            400: '#ea7f63',
                            500: '#E66B4F',
                            600: '#cc5136',
                            700: '#a83f29',
                        },
                        warm: {
                            50:  '#F6EFE3',
                            100: '#efe3cf',
                        },
                        mint: {
                            50:  '#eaf6f1',
                            100: '#DDEFE8',
                            200: '#c5e4d7',
                        },
                        ink:  '#1E2528',
                        soft: '#FAF8F3',
                    }
                }
            }
        }
    </script>
    <style>
        html:not([lang="ru"]) [data-lang="ru"],
        html:not([lang="en"]) [data-lang="en"],
        html:not([lang="ro"]) [data-lang="ro"] { display: none !important; }

        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        body { font-family: 'Inter', sans-serif; background: #FAF8F3; color: #1E2528; overflow-x: hidden; }
        h1,h2,h3,.heading { font-family: 'Manrope', 'Inter', sans-serif; }

        .grad-text {
            background: linear-gradient(135deg, #123C3A 0%, #3a8476 60%, #75aaa0 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .grad-bg   { background: linear-gradient(135deg, #123C3A 0%, #0e302e 100%); }
        .grad-dark { background: linear-gradient(160deg, #061817 0%, #0a1f1d 100%); }
        .grad-accent { background: linear-gradient(135deg, #E66B4F 0%, #cc5136 100%); }
        .grad-mint  { background: linear-gradient(160deg, #DDEFE8 0%, #eaf6f1 100%); }

        .glass {
            background: rgba(255,255,255,0.78);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(18,60,58,0.08);
            box-shadow: 0 2px 32px rgba(18,60,58,0.04), 0 1px 2px rgba(0,0,0,0.03);
        }
        .glass-dark {
            background: rgba(10,36,35,0.55);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(117,170,160,0.15);
        }
        .card-hover { transition: transform .3s ease, box-shadow .3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 16px 48px rgba(18,60,58,0.1); }

        .reveal       { opacity: 0; transform: translateY(36px);  transition: opacity .7s cubic-bezier(.22,1,.36,1), transform .7s cubic-bezier(.22,1,.36,1); }
        .reveal-left  { opacity: 0; transform: translateX(-40px); transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); }
        .reveal-right { opacity: 0; transform: translateX(40px);  transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); }
        .reveal.visible, .reveal-left.visible, .reveal-right.visible { opacity: 1; transform: none; }
        .delay-1 { transition-delay: .08s; } .delay-2 { transition-delay: .16s; }
        .delay-3 { transition-delay: .24s; } .delay-4 { transition-delay: .32s; }
        .delay-5 { transition-delay: .40s; } .delay-6 { transition-delay: .48s; }

        #navbar { background: rgba(250,248,243,0.7); backdrop-filter: blur(10px); transition: background .3s, box-shadow .3s; }
        #navbar.scrolled { background: rgba(250,248,243,0.97); box-shadow: 0 1px 0 rgba(18,60,58,0.08); backdrop-filter: blur(24px); }

        details summary::-webkit-details-marker { display: none; }
        details[open] .chevron { transform: rotate(180deg); }
        .chevron { transition: transform .3s ease; }
        ::selection { background: rgba(18,60,58,.14); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #DDEFE8; }
        ::-webkit-scrollbar-thumb { background: rgba(18,60,58,.25); border-radius: 3px; }
        :focus-visible { outline: 2px solid #123C3A; outline-offset: 3px; border-radius: 4px; }

        /* Hero animations */
        @keyframes heroFadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: none; }
        }
        @keyframes heroSlideRight {
            from { opacity: 0; transform: translateX(56px); }
            to   { opacity: 1; transform: none; }
        }
        .hero-in       { animation: heroFadeUp .9s cubic-bezier(.22,1,.36,1) both; }
        .hero-in-right { animation: heroSlideRight 1.1s cubic-bezier(.22,1,.36,1) both; animation-delay: .35s; }
        .hero-in-1 { animation-delay: .04s; }
        .hero-in-2 { animation-delay: .16s; }
        .hero-in-3 { animation-delay: .30s; }
        .hero-in-4 { animation-delay: .46s; }

        /* Wave divider */
        .wave-divider { position: relative; }
        .wave-divider::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 60px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 60' preserveAspectRatio='none'%3E%3Cpath d='M0 30 C200 0 400 60 600 30 C800 0 1000 60 1200 30 L1200 60 L0 60 Z' fill='%23FAF8F3'/%3E%3C/svg%3E") repeat-x;
            background-size: 1200px 60px;
        }

        .stat-num { font-variant-numeric: tabular-nums; }

        /* Abstract connection lines */
        .connection-lines {
            background-image:
                radial-gradient(circle at 20% 50%, rgba(18,60,58,.04) 1px, transparent 1px),
                radial-gradient(circle at 80% 50%, rgba(230,107,79,.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .dot-pattern {
            background-image: radial-gradient(rgba(18,60,58,.06) 1.5px, transparent 1.5px);
            background-size: 28px 28px;
        }
        .landing-photo {
            display: block;
            width: 100%;
            object-fit: cover;
            background: #DDEFE8;
        }
        .photo-sheen { position: relative; overflow: hidden; }
        .photo-sheen::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.18), transparent 42%, rgba(18,60,58,.18));
            pointer-events: none;
        }

        /* Admin-controlled landing themes */
        body.theme-warm {
            background: #fff7ed;
            color: #2f1f1a;
        }
        body.theme-warm .grad-text {
            background: linear-gradient(135deg, #9a3412 0%, #c2410c 52%, #f97316 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        body.theme-warm .grad-bg,
        body.theme-warm .grad-dark {
            background: linear-gradient(135deg, #7c2d12 0%, #9a3412 52%, #ea580c 100%) !important;
        }
        body.theme-warm .grad-accent {
            background: linear-gradient(135deg, #be123c 0%, #f97316 100%) !important;
        }
        body.theme-warm .grad-mint {
            background: linear-gradient(160deg, #ffedd5 0%, #fff7ed 100%) !important;
        }
        body.theme-warm #navbar {
            background: rgba(255,247,237,.76);
        }
        body.theme-warm #navbar.scrolled {
            background: rgba(255,247,237,.97);
            box-shadow: 0 1px 0 rgba(154,52,18,.12);
        }
        body.theme-warm .glass {
            background: rgba(255,255,255,.82);
            border-color: rgba(154,52,18,.12);
            box-shadow: 0 18px 52px rgba(154,52,18,.12);
        }
        body.theme-warm .text-brand-600,
        body.theme-warm .hover\:text-brand-600:hover {
            color: #9a3412 !important;
        }
        body.theme-warm .text-accent-500 {
            color: #e11d48 !important;
        }
        body.theme-warm .bg-mint-100,
        body.theme-warm .bg-mint-50,
        body.theme-warm .bg-brand-50 {
            background-color: #ffedd5 !important;
        }
        body.theme-warm .border-mint-100,
        body.theme-warm .border-brand-100,
        body.theme-warm .border-brand-200 {
            border-color: #fed7aa !important;
        }
        body.theme-warm .dot-pattern {
            background-image: radial-gradient(rgba(154,52,18,.08) 1.5px, transparent 1.5px);
        }

        body.theme-dark {
            background: #020617;
            color: #e5e7eb;
        }
        body.theme-dark .bg-soft,
        body.theme-dark .bg-white {
            background-color: #020617 !important;
        }
        body.theme-dark .text-ink,
        body.theme-dark .heading {
            color: #f8fafc !important;
        }
        body.theme-dark .text-gray-500 {
            color: #cbd5e1 !important;
        }
        body.theme-dark .text-gray-400 {
            color: #94a3b8 !important;
        }
        body.theme-dark .grad-text {
            background: linear-gradient(135deg, #f8fafc 0%, #93c5fd 48%, #f9a8d4 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        body.theme-dark .grad-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 52%, #334155 100%) !important;
        }
        body.theme-dark .grad-dark {
            background: linear-gradient(160deg, #020617 0%, #111827 100%) !important;
        }
        body.theme-dark .grad-accent {
            background: linear-gradient(135deg, #db2777 0%, #7c3aed 100%) !important;
        }
        body.theme-dark .grad-mint {
            background: linear-gradient(160deg, #111827 0%, #1e293b 100%) !important;
        }
        body.theme-dark #navbar {
            background: rgba(2,6,23,.72);
            border-bottom: 1px solid rgba(148,163,184,.12);
        }
        body.theme-dark #navbar.scrolled {
            background: rgba(2,6,23,.96);
            box-shadow: 0 1px 0 rgba(148,163,184,.12);
        }
        body.theme-dark .glass,
        body.theme-dark .glass-dark {
            background: rgba(15,23,42,.78);
            border-color: rgba(148,163,184,.16);
            box-shadow: 0 18px 52px rgba(0,0,0,.26);
        }
        body.theme-dark .border-mint-100,
        body.theme-dark .border-brand-100,
        body.theme-dark .border-brand-200,
        body.theme-dark .border-gray-100,
        body.theme-dark .border-gray-200 {
            border-color: rgba(148,163,184,.16) !important;
        }
        body.theme-dark .bg-mint-100,
        body.theme-dark .bg-mint-50,
        body.theme-dark .bg-brand-50,
        body.theme-dark .bg-accent-50,
        body.theme-dark .bg-warm-50 {
            background-color: rgba(30,41,59,.9) !important;
        }
        body.theme-dark .text-brand-600,
        body.theme-dark .hover\:text-brand-600:hover {
            color: #5eead4 !important;
        }
        body.theme-dark .text-accent-500 {
            color: #f9a8d4 !important;
        }
        body.theme-dark .dot-pattern {
            background-image: radial-gradient(rgba(148,163,184,.10) 1.5px, transparent 1.5px);
        }
    </style>
    <script>
        (function() {
            var s = localStorage.getItem('inspair_lang');
            var b = (navigator.language || '').slice(0, 2);
            var l = ['ru','en','ro'].indexOf(s) >= 0 ? s : ['ru','en','ro'].indexOf(b) >= 0 ? b : 'ru';
            document.documentElement.lang = l;
        })();
    </script>
</head>
<body class="antialiased theme-{{ $landingTheme ?? 'classic' }}">

{{-- ===== NAVBAR ===== --}}
<nav id="navbar" class="fixed top-0 inset-x-0 z-50 px-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between h-16">
        <a href="#" class="flex items-center gap-2.5 group">
            <span class="w-8 h-8 rounded-xl grad-bg flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </span>
            <span class="font-semibold text-ink tracking-tight text-sm hidden sm:inline">Women Entrepreneurs Platform</span>
        </a>
        <div class="flex items-center gap-5">
            <div class="hidden lg:flex items-center gap-5 text-sm text-gray-500">
                <a href="#who-for" class="hover:text-brand-600 transition">
                    <span data-lang="ru">Для кого</span>
                    <span data-lang="en">Who it's for</span>
                    <span data-lang="ro">Pentru cine</span>
                </a>
                <a href="#how-works" class="hover:text-brand-600 transition">
                    <span data-lang="ru">Как работает</span>
                    <span data-lang="en">How it works</span>
                    <span data-lang="ro">Cum funcționează</span>
                </a>
                <a href="#tools" class="hover:text-brand-600 transition">
                    <span data-lang="ru">Возможности</span>
                    <span data-lang="en">Tools</span>
                    <span data-lang="ro">Instrumente</span>
                </a>
                <a href="#stories" class="hover:text-brand-600 transition">
                    <span data-lang="ru">Истории</span>
                    <span data-lang="en">Stories</span>
                    <span data-lang="ro">Istorii</span>
                </a>
                <a href="#contact" class="hover:text-brand-600 transition">
                    <span data-lang="ru">Контакты</span>
                    <span data-lang="en">Contact</span>
                    <span data-lang="ro">Contact</span>
                </a>
            </div>
            <div class="flex items-center gap-0.5 bg-white/80 border border-gray-200 rounded-full p-0.5 shadow-sm">
                @foreach(['ru','en','ro'] as $l)
                <button onclick="setLang('{{ $l }}')" id="btn-{{ $l }}"
                    class="lang-btn px-2.5 py-1 text-xs font-medium rounded-full transition">
                    {{ strtoupper($l) }}
                </button>
                @endforeach
            </div>
            <a href="{{ route('account.login') }}"
               class="hidden sm:inline-flex items-center gap-1.5 text-brand-600 text-xs font-semibold px-4 py-2 rounded-full border border-brand-200 hover:bg-brand-50 transition">
                <span data-lang="ru">Войти</span>
                <span data-lang="en">Log in</span>
                <span data-lang="ro">Log in</span>
            </a>
            <a href="https://t.me/WomenComBot" target="_blank"
               class="hidden sm:inline-flex items-center gap-1.5 grad-accent text-white text-xs font-semibold px-4 py-2 rounded-full shadow-md hover:shadow-lg transition-shadow">
                <span data-lang="ru">Присоединиться к платформе</span>
                <span data-lang="en">Join the platform</span>
                <span data-lang="ro">Alătură-te</span>
            </a>
            <button id="mobile-menu-btn"
                class="lg:hidden p-2 -mr-1 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition"
                aria-label="Menu" aria-expanded="false" aria-controls="mobile-menu">
                <svg id="hamburger-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="hidden lg:hidden absolute top-full inset-x-0 border-b border-brand-100 px-4 py-5 shadow-xl" style="background:rgba(255,255,255,0.98);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);">
        <div class="flex flex-col gap-1">
            <a href="#who-for" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
                <span data-lang="ru">Для кого</span><span data-lang="en">Who it's for</span><span data-lang="ro">Pentru cine</span>
            </a>
            <a href="#how-works" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
                <span data-lang="ru">Как работает</span><span data-lang="en">How it works</span><span data-lang="ro">Cum funcționează</span>
            </a>
            <a href="#tools" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
                <span data-lang="ru">Возможности</span><span data-lang="en">Tools</span><span data-lang="ro">Instrumente</span>
            </a>
            <a href="#stories" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
                <span data-lang="ru">Истории</span><span data-lang="en">Stories</span><span data-lang="ro">Istorii</span>
            </a>
            <a href="#contact" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
                <span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span>
            </a>
            <div class="border-t border-gray-100 mt-3 pt-4 flex flex-col gap-2">
                <a href="{{ route('account.login') }}"
                   class="flex items-center justify-center gap-2.5 text-brand-600 font-semibold text-sm px-5 py-3 rounded-full border border-brand-200 w-full">
                    <span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Log in</span>
                </a>
                <a href="https://t.me/WomenComBot" target="_blank"
                   class="flex items-center justify-center gap-2.5 grad-accent text-white font-semibold text-sm px-5 py-3.5 rounded-full shadow-md w-full">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    <span data-lang="ru">Присоединиться к платформе</span>
                    <span data-lang="en">Join the platform</span>
                    <span data-lang="ro">Alătură-te</span>
                </a>
            </div>
        </div>
    </div>
</nav>

{{-- ===== HERO ===== --}}
<section class="relative min-h-[calc(100vh-72px)] flex items-center overflow-hidden pt-16">
    <div class="absolute inset-0 -z-10 dot-pattern"></div>
    {{-- Abstract orbs suggesting two banks connection --}}
    <div class="absolute top-0 left-0 w-[500px] h-[500px] rounded-full blur-[100px] opacity-20" style="background:radial-gradient(#123C3A,transparent);"></div>
    <div class="absolute bottom-0 right-0 w-[500px] h-[500px] rounded-full blur-[100px] opacity-15" style="background:radial-gradient(#E66B4F,transparent);"></div>

    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-12 pb-20 lg:pt-10 lg:pb-16 grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
        {{-- Left: text --}}
        <div>
            <div class="hero-in hero-in-1 inline-flex items-center gap-2 bg-mint-100 border border-mint-200 text-brand-600 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-6 tracking-wide">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Women Entrepreneurs Platform
            </div>

            <h1 class="heading text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.04] tracking-tight mb-5 hero-in hero-in-2">
                <span data-lang="ru">Цифровое пространство, где<br><em class="grad-text not-italic">женский бизнес</em><br>растёт и находит связи</span>
                <span data-lang="en">A digital space where<br><em class="grad-text not-italic">women's businesses</em><br>connect, learn and grow</span>
                <span data-lang="ro">Un spațiu digital unde<br><em class="grad-text not-italic">afacerile femeilor</em><br>se conectează, învață și cresc</span>
            </h1>

            <p class="text-base sm:text-lg text-gray-500 leading-relaxed max-w-lg mb-4 hero-in hero-in-3" data-lang="ru">Онлайн-платформа для женщин-предпринимательниц с обоих берегов — объединяющая обучение, нетворкинг, видимость бизнеса, менторство, возможности и практическую поддержку в одной долгосрочной цифровой экосистеме.</p>
            <p class="text-base sm:text-lg text-gray-500 leading-relaxed max-w-lg mb-4 hero-in hero-in-3" data-lang="en">An online platform for women entrepreneurs from both banks — combining learning, networking, business visibility, mentoring, opportunities and practical support in one long-term digital ecosystem.</p>
            <p class="text-base sm:text-lg text-gray-500 leading-relaxed max-w-lg mb-4 hero-in hero-in-3" data-lang="ro">O platformă online pentru femeile antreprenoare de pe ambele maluri — care combină învățarea, networkingul, vizibilitatea afacerii, mentoratul, oportunitățile și sprijinul practic într-un ecosistem digital pe termen lung.</p>

            <div class="flex flex-wrap items-center gap-4 hero-in hero-in-4">
                <a href="https://t.me/WomenComBot" target="_blank"
                   class="inline-flex items-center gap-2 grad-accent text-white font-semibold text-sm px-7 py-3.5 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all" style="box-shadow:0 8px 32px rgba(230,107,79,.3);">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    <span data-lang="ru">Присоединиться к платформе</span>
                    <span data-lang="en">Join the platform</span>
                    <span data-lang="ro">Alătură-te platformei</span>
                </a>
                <a href="#tools" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Возможности</span>
                    <span data-lang="en">Explore opportunities</span>
                    <span data-lang="ro">Explorează oportunitățile</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            <p class="text-xs text-gray-400 mt-6 hero-in hero-in-4" data-lang="ru">Для женщин-предпринимательниц, ММСП под руководством женщин, начинающих предпринимательниц, самозанятых, менторов, экспертов и партнёрских организаций.</p>
            <p class="text-xs text-gray-400 mt-6 hero-in hero-in-4" data-lang="en">For women entrepreneurs, women-led MSMEs, aspiring entrepreneurs, self-employed women, mentors, experts and partner organisations.</p>
            <p class="text-xs text-gray-400 mt-6 hero-in hero-in-4" data-lang="ro">Pentru femei antreprenoare, IMM-uri conduse de femei, aspirante la antreprenoriat, femei liber-profesioniste, mentori, experți și organizații partenere.</p>

            <div class="lg:hidden hero-in hero-in-4 mt-8 photo-sheen rounded-3xl overflow-hidden shadow-xl border border-white">
                <img
                    src="{{ asset('images/hero-community.webp') }}"
                    alt="Women entrepreneurs working together"
                    class="landing-photo h-64 sm:h-80"
                    fetchpriority="high"
                >
            </div>
        </div>

        {{-- Right: photo collage --}}
        <div class="hidden lg:flex items-center justify-center hero-in-right">
            <div class="relative w-[440px] h-[520px]">
                <div class="absolute -top-8 -right-8 w-44 h-44 rounded-full bg-mint-100 blur-3xl opacity-80"></div>
                <div class="absolute -bottom-8 -left-8 w-44 h-44 rounded-full bg-accent-100 blur-3xl opacity-70"></div>

                <div class="photo-sheen absolute inset-x-0 top-0 rounded-[2rem] overflow-hidden shadow-2xl border border-white rotate-1">
                    <img
                        src="{{ asset('images/hero-community.webp') }}"
                        alt="Women entrepreneurs working together"
                        class="landing-photo h-[340px]"
                        fetchpriority="high"
                    >
                </div>

                <div class="absolute left-2 bottom-12 w-44 photo-sheen rounded-3xl overflow-hidden shadow-xl border-4 border-white -rotate-3">
                    <img
                        src="{{ asset('images/member-digital.webp') }}"
                        alt="Digital entrepreneur profile"
                        class="landing-photo h-48"
                        loading="lazy"
                    >
                </div>

                <div class="absolute right-2 bottom-0 w-48 photo-sheen rounded-3xl overflow-hidden shadow-xl border-4 border-white rotate-3">
                    <img
                        src="{{ asset('images/member-fashion.webp') }}"
                        alt="Woman-led fashion business"
                        class="landing-photo h-52"
                        loading="lazy"
                    >
                </div>

                <div class="glass absolute left-1/2 -translate-x-1/2 bottom-5 w-[360px] rounded-3xl px-5 py-4 shadow-xl text-center">
                    <div class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold">
                        <span data-lang="ru">Два берега · одна сеть</span>
                        <span data-lang="en">Two banks · one network</span>
                        <span data-lang="ro">Două maluri · o rețea</span>
                    </div>
                    <div class="heading text-base font-bold text-ink mt-1">
                        <span data-lang="ru">Менторство, события и партнёрства для женского бизнеса</span>
                        <span data-lang="en">Mentoring, events and partnerships for women-led businesses</span>
                        <span data-lang="ro">Mentorat, evenimente și parteneriate pentru afaceri conduse de femei</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-gray-400">
        <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>

{{-- ===== STATS BAND ===== --}}
<section class="py-10 bg-white border-b border-mint-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
            <div class="reveal">
                <div class="heading text-4xl font-bold text-accent-500 stat-num leading-none">500+</div>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">
                    <span data-lang="ru">участниц</span><span data-lang="en">women registered</span><span data-lang="ro">femei înregistrate</span>
                </p>
            </div>
            <div class="reveal delay-1">
                <div class="heading text-4xl font-bold text-accent-500 stat-num leading-none">10+</div>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">
                    <span data-lang="ru">модулей обучения</span><span data-lang="en">learning modules</span><span data-lang="ro">module de învățare</span>
                </p>
            </div>
            <div class="reveal delay-2">
                <div class="heading text-4xl font-bold text-accent-500 stat-num leading-none">3</div>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">
                    <span data-lang="ru">языка</span><span data-lang="en">languages</span><span data-lang="ro">limbi</span>
                </p>
            </div>
            <div class="reveal delay-3">
                <div class="heading text-4xl font-bold text-accent-500 stat-num leading-none">30+</div>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">
                    <span data-lang="ru">менторская поддержка</span><span data-lang="en">mentoring support</span><span data-lang="ro">suport de mentorat</span>
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ===== WHO IT IS FOR ===== --}}
<section class="py-28 px-4 sm:px-6 bg-white scroll-mt-20" id="who-for">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-3">
                <span data-lang="ru">Для кого</span>
                <span data-lang="en">Who it is for</span>
                <span data-lang="ro">Pentru cine</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-ink">
                <span data-lang="ru">Для женщин на&nbsp;разных этапах предпринимательства</span>
                <span data-lang="en">Built for women at&nbsp;different stages of&nbsp;entrepreneurship</span>
                <span data-lang="ro">Creat pentru femei în&nbsp;diferite etape ale&nbsp;antreprenoriatului</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ru">Платформа поддерживает женщин, которые уже ведут бизнес, женщин, которые только готовятся его начать, самозанятых и владелиц микро-, малых и средних предприятий.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="en">The platform supports women who already run a business, women who are preparing to start one, self-employed women and owners of micro, small and medium-sized enterprises.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ro">Platforma sprijină femeile care deja conduc o afacere, femeile care se pregătesc să înceapă una, femeile liber-profesioniste și proprietarele de microîntreprinderi, întreprinderi mici și mijlocii.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
            $whoCards = [
                ['icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','ru_t' => 'Женщины-предпринимательницы','en_t' => 'Women entrepreneurs','ro_t' => 'Femei antreprenoare','ru_d' => 'Для тех, кто уже управляет и развивает свой бизнес.','en_d' => 'For women who already manage and grow their own business.','ro_d' => 'Pentru femeile care deja gestionează și își dezvoltă propria afacere.'],
                ['icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6','ru_t' => 'Начинающие основательницы','en_t' => 'Aspiring founders','ro_t' => 'Fondatoare aspirante','ru_d' => 'Для женщин, планирующих начать предпринимательскую деятельность.','en_d' => 'For women who are planning to start entrepreneurial activity.','ro_d' => 'Pentru femeile care planifică să înceapă activitatea antreprenorială.'],
                ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4','ru_t' => 'ММСП под руководством женщин','en_t' => 'Women-led MSMEs','ro_t' => 'IMM-uri conduse de femei','ru_d' => 'Для микро-, малых и средних предприятий под руководством женщин.','en_d' => 'For micro, small and medium-sized enterprises led by women.','ro_d' => 'Pentru microîntreprinderi, întreprinderi mici și mijlocii conduse de femei.'],
                ['icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','ru_t' => 'Самозанятые женщины','en_t' => 'Self-employed women','ro_t' => 'Femei liber-profesioniste','ru_d' => 'Для женщин, работающих самостоятельно в сфере услуг, ремёсел, образования, туризма, IT, сельского хозяйства и других секторов.','en_d' => 'For women working independently in services, crafts, education, tourism, IT, agriculture and other sectors.','ro_d' => 'Pentru femeile care lucrează independent în servicii, meșteșuguri, educație, turism, IT, agricultură și alte sectoare.'],
                ['icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6','ru_t' => 'Женщины из малых городов и сёл','en_t' => 'Women from small towns and rural areas','ro_t' => 'Femei din orașe mici și zone rurale','ru_d' => 'Для женщин, которым нужен лучший доступ к знаниям, контактам, рынкам и поддержке.','en_d' => 'For women who need better access to knowledge, contacts, markets and support.','ro_d' => 'Pentru femeile care au nevoie de acces mai bun la cunoștințe, contacte, piețe și sprijin.'],
                ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z','ru_t' => 'Менторы и эксперты','en_t' => 'Mentors and experts','ro_t' => 'Mentori și experți','ru_d' => 'Для профессионалов, готовых делиться знаниями, консультациями и практическим опытом.','en_d' => 'For professionals ready to share knowledge, consultations and practical business experience.','ro_d' => 'Pentru profesioniști dispuși să împărtășească cunoștințe, consultanță și experiență practică de afaceri.'],
            ];
            @endphp
            @foreach($whoCards as $i => $card)
            <div class="glass rounded-2xl p-6 card-hover reveal delay-{{ $i + 1 }}">
                <div class="w-10 h-10 rounded-xl grad-mint flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
                </div>
                <h3 class="heading text-lg font-bold text-ink mb-2">
                    <span data-lang="ru">{{ $card['ru_t'] }}</span>
                    <span data-lang="en">{{ $card['en_t'] }}</span>
                    <span data-lang="ro">{{ $card['ro_t'] }}</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">{{ $card['ru_d'] }}</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">{{ $card['en_d'] }}</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">{{ $card['ro_d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== WHAT IT HELPS WITH ===== --}}
<section class="py-28 px-4 sm:px-6 bg-soft">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-3">
                <span data-lang="ru">Преимущества</span>
                <span data-lang="en">Benefits</span>
                <span data-lang="ro">Beneficii</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-ink">
                <span data-lang="ru">Всё, что нужно для пути от&nbsp;идеи к&nbsp;росту</span>
                <span data-lang="en">Everything needed to move from idea to&nbsp;growth</span>
                <span data-lang="ro">Tot ce ai nevoie pentru a trece de la idee la creștere</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ru">Платформа объединяет практические инструменты, которые помогают женщинам учиться, презентовать свой бизнес, находить полезные контакты, получать доступ к возможностям и становиться заметными в региональном бизнес-сообществе.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="en">The platform brings together practical tools that help women learn, present their business, find useful contacts, access opportunities and become visible in a regional business community.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ro">Platforma reunește instrumente practice care ajută femeile să învețe, să își prezinte afacerea, să găsească contacte utile, să acceseze oportunități și să devină vizibile într-o comunitate regională de afaceri.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
            $benefits = [
                ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'ru_t'=>'Практическое обучение','en_t'=>'Practical learning','ro_t'=>'Învățare practică','ru_d'=>'Учитесь через практические бизнес-модули.','en_d'=>'Learn through practical business modules.','ro_d'=>'Învață prin module practice de afaceri.'],
                ['icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'ru_t'=>'Бизнес-профиль','en_t'=>'Business profile','ro_t'=>'Profil de afaceri','ru_d'=>'Создайте понятный бизнес-профиль.','en_d'=>'Create a clear business profile.','ro_d'=>'Creează un profil clar al afacerii.'],
                ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'ru_t'=>'Партнёры и контакты','en_t'=>'Partners & contacts','ro_t'=>'Parteneri & contacte','ru_d'=>'Находите партнёров, менторов, экспертов и клиентов.','en_d'=>'Find partners, mentors, experts and clients.','ro_d'=>'Găsește parteneri, mentori, experți și clienți.'],
                ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'ru_t'=>'Возможности','en_t'=>'Opportunities','ro_t'=>'Oportunități','ru_d'=>'Открывайте гранты, события, конкурсы и программы поддержки.','en_d'=>'Discover grants, events, competitions and support programmes.','ro_d'=>'Descoperă granturi, evenimente, competiții și programe de sprijin.'],
                ['icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                'ru_t'=>'Запросы и предложения','en_t'=>'Requests & offers','ro_t'=>'Cereri & oferte','ru_d'=>'Делитесь запросами и предложениями с сообществом.','en_d'=>'Share requests and offers with the community.','ro_d'=>'Împărtășește cereri și oferte cu comunitatea.'],
                ['icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                'ru_t'=>'Персональные рекомендации','en_t'=>'Personalised recommendations','ro_t'=>'Recomandări personalizate','ru_d'=>'Получайте персональные рекомендации.','en_d'=>'Receive personalised recommendations.','ro_d'=>'Primește recomandări personalizate.'],
                ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'ru_t'=>'Telegram-уведомления','en_t'=>'Telegram notifications','ro_t'=>'Notificări Telegram','ru_d'=>'Оставайтесь на связи через Telegram-уведомления.','en_d'=>'Stay connected through Telegram notifications.','ro_d'=>'Rămâi conectată prin notificări Telegram.'],
            ];
            @endphp
            @foreach($benefits as $i => $b)
            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover reveal delay-{{ $i + 1 }} border border-mint-100">
                <div class="w-10 h-10 rounded-xl bg-mint-100 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $b['icon'] }}"/></svg>
                </div>
                <h3 class="heading text-lg font-bold text-ink mb-2">
                    <span data-lang="ru">{{ $b['ru_t'] }}</span>
                    <span data-lang="en">{{ $b['en_t'] }}</span>
                    <span data-lang="ro">{{ $b['ro_t'] }}</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">{{ $b['ru_d'] }}</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">{{ $b['en_d'] }}</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">{{ $b['ro_d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== HOW IT WORKS ===== --}}
<section class="py-28 px-4 sm:px-6 bg-white scroll-mt-20" id="how-works">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-3">
                <span data-lang="ru">Процесс</span>
                <span data-lang="en">Process</span>
                <span data-lang="ro">Proces</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-ink">
                <span data-lang="ru">Как работает платформа</span>
                <span data-lang="en">How the platform works</span>
                <span data-lang="ro">Cum funcționează platforma</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ru">Каждая участница создаёт профиль, описывает свой бизнес, указывает, что она ищет и что может предложить. На основе этой информации платформа помогает связать её с обучающими материалами, событиями, менторами, экспертами, возможностями и другими предпринимательницами.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="en">Each participant creates a profile, describes her business, indicates what she is looking for and what she can offer. Based on this information, the platform helps connect her with relevant learning materials, events, mentors, experts, opportunities and other women entrepreneurs.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ro">Fiecare participantă creează un profil, descrie afacerea sa, indică ce caută și ce poate oferi. Pe baza acestor informații, platforma ajută la conectarea ei cu materiale de învățare relevante, evenimente, mentori, experți, oportunități și alte femei antreprenoare.</p>
        </div>
        {{-- Timeline on desktop, stack on mobile --}}
        <div class="relative">
            {{-- Line (desktop only) --}}
            <div class="hidden md:block absolute top-12 left-0 right-0 h-0.5 bg-mint-200"></div>
            <div class="grid md:grid-cols-4 gap-6">
                @php
                $steps = [
                    ['num'=>'1','ru_t'=>'Регистрация','en_t'=>'Register','ro_t'=>'Înregistrare','ru_d'=>'Подайте заявку через Telegram и получите доступ после одобрения команды проекта.','en_d'=>'Apply through Telegram and receive access after approval by the project team.','ro_d'=>'Trimite cererea prin Telegram și primește acces după aprobarea echipei proiectului.'],
                    ['num'=>'2','ru_t'=>'Профиль','en_t'=>'Build your profile','ro_t'=>'Construiește profilul','ru_d'=>'Добавьте визитную карточку: кто вы, что представляете, что ищете и что предлагаете.','en_d'=>'Add your business card: who you are, what you represent, what you need and what you can offer.','ro_d'=>'Adaugă cartea ta de vizită: cine ești, ce reprezinți, ce cauți și ce poți oferi.'],
                    ['num'=>'3','ru_t'=>'Ценность','en_t'=>'Receive value','ro_t'=>'Primește valoare','ru_d'=>'Получите доступ к обучающим модулям, событиям, возможностям и персональным рекомендациям.','en_d'=>'Access learning modules, events, opportunities and personalised recommendations.','ro_d'=>'Accesează module de învățare, evenimente, oportunități și recomandări personalizate.'],
                    ['num'=>'4','ru_t'=>'Рост','en_t'=>'Connect and grow','ro_t'=>'Conectează-te și crește','ru_d'=>'Находите партнёров, менторов, экспертов, клиентов и поддержку единомышленниц.','en_d'=>'Find partners, mentors, experts, clients and peer support.','ro_d'=>'Găsește partenere, mentori, experți, cliente și sprijin de la egal la egal.'],
                ];
                @endphp
                @foreach($steps as $i => $step)
                <div class="relative reveal delay-{{ $i + 1 }}">
                    <div class="w-10 h-10 rounded-full grad-bg flex items-center justify-center text-white font-bold text-sm mx-auto mb-5 relative z-10 shadow-md">0{{ $step['num'] }}</div>
                    <h3 class="heading text-lg font-bold text-ink mb-2 text-center">
                        <span data-lang="ru">{{ $step['ru_t'] }}</span>
                        <span data-lang="en">{{ $step['en_t'] }}</span>
                        <span data-lang="ro">{{ $step['ro_t'] }}</span>
                    </h3>
                    <p class="text-sm text-gray-500 leading-relaxed text-center" data-lang="ru">{{ $step['ru_d'] }}</p>
                    <p class="text-sm text-gray-500 leading-relaxed text-center" data-lang="en">{{ $step['en_d'] }}</p>
                    <p class="text-sm text-gray-500 leading-relaxed text-center" data-lang="ro">{{ $step['ro_d'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ===== MAIN TOOLS ===== --}}
<section class="py-28 px-4 sm:px-6 bg-soft scroll-mt-20" id="tools">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-3">
                <span data-lang="ru">Инструменты</span>
                <span data-lang="en">Tools</span>
                <span data-lang="ro">Instrumente</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-ink">
                <span data-lang="ru">Одна платформа. Множество инструментов.</span>
                <span data-lang="en">One platform. Many practical tools.</span>
                <span data-lang="ro">O platformă. Multe instrumente practice.</span>
            </h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @php
            $tools = [
                ['icon'=>'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z','ru_t'=>'Каталог бизнеса','en_t'=>'Business directory','ro_t'=>'Director de afaceri','ru_d'=>'Каталог предпринимательниц с фильтрами по региону, городу, сектору, продукту, услуге и интересам.','en_d'=>'A searchable directory of women entrepreneurs with filters by region, city, sector, product, service, business stage and cooperation interests.','ro_d'=>'Un director al femeilor antreprenoare cu filtre după regiune, oraș, sector, produs, serviciu, stadiul afacerii și interese de cooperare.'],
                ['icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253','ru_t'=>'Обучение','en_t'=>'Learning section','ro_t'=>'Secțiunea de învățare','ru_d'=>'Видеоуроки, руководства, чек-листы, шаблоны, практические задания, тесты и цифровые сертификаты.','en_d'=>'Video lessons, guides, checklists, templates, practical tasks, tests, webinar recordings and digital certificates.','ro_d'=>'Lecții video, ghiduri, liste de verificare, șabloane, sarcini practice, teste, înregistrări de webinare și certificate digitale.'],
                ['icon'=>'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4','ru_t'=>'Запросы и предложения','en_t'=>'Requests and offers','ro_t'=>'Cereri și oferte','ru_d'=>'Пространство для публикации бизнес-потребностей и предложений: партнёры, поставщики, услуги, помещения, выставки и менторство.','en_d'=>'A space where women can publish business needs and proposals: partners, suppliers, services, premises, packaging, exhibitions, mentoring and more.','ro_d'=>'Un spațiu unde femeile pot publica nevoi și propuneri de afaceri: parteneri, furnizori, servicii, spații, ambalaje, expoziții, mentorat și multe altele.'],
                ['icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z','ru_t'=>'Менторство','en_t'=>'Mentoring','ro_t'=>'Mentorat','ru_d'=>'Инструмент для связи предпринимательниц с менторами, экспертами и консультантами.','en_d'=>'A tool to connect less experienced entrepreneurs with mentors, experts and consultants.','ro_d'=>'Un instrument pentru conectarea antreprenoarelor mai puțin experimentate cu mentori, experți și consultanți.'],
                ['icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','ru_t'=>'Календарь событий','en_t'=>'Events calendar','ro_t'=>'Calendar evenimente','ru_d'=>'Тренинги, вебинары, B2B-встречи, бизнес-школы, консультации, форумы и церемонии награждения в одном месте.','en_d'=>'Trainings, webinars, B2B meetings, business schools, consultations, forums and award events in one place.','ro_d'=>'Traininguri, webinare, întâlniri B2B, școli de afaceri, consultații, forumuri și evenimente de premiere într-un singur loc.'],
                ['icon'=>'M13 10V3L4 14h7v7l9-11h-7z','ru_t'=>'Возможности','en_t'=>'Opportunities','ro_t'=>'Oportunități','ru_d'=>'Гранты, программы поддержки, выставки, финансовая информация, поддержка экспорта и партнёрские предложения.','en_d'=>'Grants, support programmes, exhibitions, finance-related information, export support and partner offers.','ro_d'=>'Granturi, programe de sprijin, expoziții, informații financiare, sprijin pentru export și oferte ale partenerilor.'],
                ['icon'=>'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z','ru_t'=>'Рекомендации','en_t'=>'Recommendations','ro_t'=>'Recomandări','ru_d'=>'AI-поддержка для подбора модулей, событий, менторов, грантов и полезных контактов.','en_d'=>'AI-supported suggestions for modules, events, mentors, grants or useful contacts based on profile needs.','ro_d'=>'Sugestii bazate pe AI pentru module, evenimente, mentori, granturi sau contacte utile în funcție de nevoile profilului.'],
                ['icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z','ru_t'=>'Связь с Telegram','en_t'=>'Telegram connection','ro_t'=>'Conexiune Telegram','ru_d'=>'Напоминания, анонсы, приглашения, быстрые опросы и персонализированные обновления через Telegram.','en_d'=>'Reminders, announcements, invitations, quick surveys and personalised updates through Telegram.','ro_d'=>'Mementouri, anunțuri, invitații, sondaje rapide și actualizări personalizate prin Telegram.'],
            ];
            @endphp
            @foreach($tools as $i => $t)
            <div class="glass rounded-2xl p-6 card-hover reveal delay-{{ ($i % 4) + 1 }}">
                <div class="w-10 h-10 rounded-xl bg-mint-100 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"/></svg>
                </div>
                <h3 class="heading text-lg font-bold text-ink mb-2">
                    <span data-lang="ru">{{ $t['ru_t'] }}</span>
                    <span data-lang="en">{{ $t['en_t'] }}</span>
                    <span data-lang="ro">{{ $t['ro_t'] }}</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">{{ $t['ru_d'] }}</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">{{ $t['en_d'] }}</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">{{ $t['ro_d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== PARTICIPANT PROFILE ===== --}}
<section class="py-28 px-4 sm:px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="reveal-left">
                <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-4">
                    <span data-lang="ru">Профиль участницы</span>
                    <span data-lang="en">Participant profile</span>
                    <span data-lang="ro">Profilul participantei</span>
                </p>
                <h2 class="heading text-4xl sm:text-5xl font-bold text-ink leading-tight mb-6">
                    <span data-lang="ru">Бизнес-профиль для реального сотрудничества</span>
                    <span data-lang="en">A business profile designed for real cooperation</span>
                    <span data-lang="ro">Un profil de afaceri conceput pentru o cooperare reală</span>
                </h2>
                <p class="text-gray-500 leading-relaxed mb-8" data-lang="ru">Профиль каждой участницы построен вокруг трёх практических блоков. Это делает профиль полезным не только как биография, но и как инструмент делового взаимодействия.</p>
                <p class="text-gray-500 leading-relaxed mb-8" data-lang="en">Each participant profile is structured around three practical blocks. This makes the profile useful not only as a biography, but as a tool for business interaction.</p>
                <p class="text-gray-500 leading-relaxed mb-8" data-lang="ro">Fiecare profil de participantă este structurat în jurul a trei blocuri practice. Acest lucru face profilul util nu doar ca biografie, ci și ca instrument de interacțiune în afaceri.</p>
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-mint-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-brand-600 font-bold text-sm">I</span>
                        </div>
                        <div>
                            <div class="font-semibold text-ink text-sm">
                                <span data-lang="ru">Я представляю</span>
                                <span data-lang="en">I represent</span>
                                <span data-lang="ro">Eu reprezint</span>
                            </div>
                            <p class="text-sm text-gray-500" data-lang="ru">Название бизнеса, сектор, продукты, услуги, стадия развития и местоположение.</p>
                            <p class="text-sm text-gray-500" data-lang="en">Business name, sector, products, services, development stage and location.</p>
                            <p class="text-sm text-gray-500" data-lang="ro">Denumirea afacerii, sectorul, produsele, serviciile, stadiul de dezvoltare și locația.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-accent-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-accent-500 font-bold text-sm">?</span>
                        </div>
                        <div>
                            <div class="font-semibold text-ink text-sm">
                                <span data-lang="ru">Я ищу</span>
                                <span data-lang="en">I am looking for</span>
                                <span data-lang="ro">Eu caut</span>
                            </div>
                            <p class="text-sm text-gray-500" data-lang="ru">Партнёров, клиентов, поставщиков, знания, менторство, финансирование, новые рынки или экспертную поддержку.</p>
                            <p class="text-sm text-gray-500" data-lang="en">Partners, clients, suppliers, knowledge, mentoring, finance, new markets or expert support.</p>
                            <p class="text-sm text-gray-500" data-lang="ro">Parteneri, clienți, furnizori, cunoștințe, mentorat, finanțare, piețe noi sau sprijin de specialitate.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-mint-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-brand-600 font-bold text-sm">✓</span>
                        </div>
                        <div>
                            <div class="font-semibold text-ink text-sm">
                                <span data-lang="ru">Я могу предложить</span>
                                <span data-lang="en">I can offer</span>
                                <span data-lang="ro">Eu pot oferi</span>
                            </div>
                            <p class="text-sm text-gray-500" data-lang="ru">Продукты, услуги, опыт, консультации, менторство, сотрудничество или участие в совместных инициативах.</p>
                            <p class="text-sm text-gray-500" data-lang="en">Products, services, experience, consultations, mentoring, cooperation or participation in joint initiatives.</p>
                            <p class="text-sm text-gray-500" data-lang="ro">Produse, servicii, experiență, consultanță, mentorat, cooperare sau participare la inițiative comune.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="reveal-right">
                <div class="glass rounded-3xl p-8 shadow-xl border border-mint-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl grad-bg flex items-center justify-center text-white font-bold text-xl shadow-md">EM</div>
                        <div>
                            <div class="font-bold text-ink text-lg">Elena M.</div>
                            <div class="text-sm text-gray-400">Founder, local food brand</div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-mint-50 rounded-xl p-4">
                            <div class="text-xs font-semibold text-brand-600 mb-1 uppercase tracking-wider">I represent</div>
                            <div class="text-sm text-ink">Food production · Organic snacks · Regional brand · Early growth stage · Chișinău</div>
                        </div>
                        <div class="bg-accent-50 rounded-xl p-4">
                            <div class="text-xs font-semibold text-accent-500 mb-1 uppercase tracking-wider">I am looking for</div>
                            <div class="text-sm text-ink">Export partners · Packaging suppliers · Marketing expertise · EU market access</div>
                        </div>
                        <div class="bg-mint-50 rounded-xl p-4">
                            <div class="text-xs font-semibold text-brand-600 mb-1 uppercase tracking-wider">I can offer</div>
                            <div class="text-sm text-ink">Product samples · Mentoring for food startups · Local market knowledge · Recipe development</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== LEARNING & MENTORING ===== --}}
<section class="py-28 px-4 sm:px-6 bg-soft" id="learning">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-3">
                <span data-lang="ru">Обучение</span>
                <span data-lang="en">Learning</span>
                <span data-lang="ro">Învățare</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-ink">
                <span data-lang="ru">Обучение, ведущее к&nbsp;практическим бизнес-результатам</span>
                <span data-lang="en">Learning that leads to practical business results</span>
                <span data-lang="ro">Învățare care duce la rezultate practice de afaceri</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ru">Обучающий раздел предоставит структурированные модули и практические материалы, которые помогут участницам улучшить бизнес-идею, планирование, ценообразование, маркетинг, финансовую грамотность, экспортную готовность, цифровое продвижение и лидерские навыки.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="en">The learning section will provide structured modules and practical materials that help participants improve their business idea, planning, pricing, marketing, financial literacy, export readiness, digital promotion and leadership skills.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ro">Secțiunea de învățare va oferi module structurate și materiale practice care ajută participantele să își îmbunătățească ideea de afacere, planificarea, stabilirea prețurilor, marketingul, alfabetizarea financiară, pregătirea pentru export, promovarea digitală și abilitățile de leadership.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-14">
            @php
            $modules = [
                'ru'=>['Основы предпринимательства','Бизнес-планирование','Маркетинг и продажи','Финансовая грамотность','Юридические и админ. вопросы','Доступ к новым рынкам','Экспорт и междунар. сотрудничество','ESG и устойчивый бизнес','Цифровизация бизнеса','Женское лидерство','Переговоры'],
                'en'=>['Entrepreneurship basics','Business planning','Marketing and sales','Financial literacy','Legal and admin. issues','Access to new markets','Export and intl. cooperation','ESG and sustainable business','Business digitalisation','Women\'s leadership','Negotiations'],
                'ro'=>['Bazele antreprenoriatului','Planificarea afacerii','Marketing și vânzări','Alfabetizare financiară','Aspecte juridice și admin.','Acces la piețe noi','Export și cooperare intl.','ESG și afaceri durabile','Digitalizarea afacerii','Leadership feminin','Negocieri'],
            ];
            @endphp
            @for($i = 0; $i < 11; $i++)
            <div class="bg-white rounded-xl px-4 py-3 text-sm font-medium text-ink border border-mint-100 flex items-center gap-2 reveal delay-{{ ($i % 4) + 1 }}">
                <span class="w-5 h-5 rounded-md grad-mint flex items-center justify-center text-[10px] text-brand-600 font-bold flex-shrink-0">{{ $i + 1 }}</span>
                <span data-lang="ru">{{ $modules['ru'][$i] }}</span>
                <span data-lang="en">{{ $modules['en'][$i] }}</span>
                <span data-lang="ro">{{ $modules['ro'][$i] }}</span>
            </div>
            @endfor
        </div>

        {{-- Mentoring sub-section --}}
        <div class="bg-white rounded-3xl p-10 border border-mint-100 shadow-sm reveal">
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <h3 class="heading text-2xl font-bold text-ink mb-4">
                        <span data-lang="ru">Менторство и экспертная поддержка</span>
                        <span data-lang="en">Mentoring and expert support</span>
                        <span data-lang="ro">Mentorat și sprijin de specialitate</span>
                    </h3>
                    <p class="text-gray-500 leading-relaxed" data-lang="ru">Через раздел менторства женщины могут связаться с опытными предпринимательницами, консультантами и экспертами для практического руководства в области финансов, маркетинга, продаж, экспорта, ESG, переговоров, управления командой и лидерства.</p>
                    <p class="text-gray-500 leading-relaxed" data-lang="en">Through the mentoring section, women can connect with experienced entrepreneurs, consultants and experts for practical guidance in finance, marketing, sales, export, ESG, negotiations, team management and leadership.</p>
                    <p class="text-gray-500 leading-relaxed" data-lang="ro">Prin secțiunea de mentorat, femeile se pot conecta cu antreprenoare, consultanți și experți experimentați pentru îndrumare practică în finanțe, marketing, vânzări, export, ESG, negocieri, managementul echipei și leadership.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Finance','Marketing','Sales','Export','ESG','Negotiations','Leadership','Digital'] as $tag)
                    <span class="px-4 py-2 rounded-full text-sm font-medium bg-mint-100 text-brand-600">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== EVENTS & OPPORTUNITIES ===== --}}
<section class="py-28 px-4 sm:px-6 bg-white" id="events">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-3">
                <span data-lang="ru">События и возможности</span>
                <span data-lang="en">Events & opportunities</span>
                <span data-lang="ro">Evenimente & oportunități</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-ink">
                <span data-lang="ru">События, возможности и&nbsp;поддержка в&nbsp;одном месте</span>
                <span data-lang="en">Events, opportunities and support in&nbsp;one place</span>
                <span data-lang="ro">Evenimente, oportunități și sprijin într-un singur loc</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ru">Платформа поможет женщинам находить тренинги, бизнес-школы, вебинары, B2B-встречи, форумы, консультации, менторские сессии, конкурсы, гранты, выставки и партнёрские предложения.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="en">The platform will help women discover trainings, business schools, webinars, B2B meetings, forums, consultations, mentoring sessions, competitions, grants, exhibitions and partner offers.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ro">Platforma va ajuta femeile să descopere traininguri, școli de afaceri, webinare, întâlniri B2B, forumuri, consultații, sesiuni de mentorat, competiții, granturi, expoziții și oferte ale partenerilor.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="glass rounded-3xl p-8 card-hover reveal delay-1">
                <div class="photo-sheen rounded-2xl overflow-hidden mb-6 -mx-2 -mt-2">
                    <img src="{{ asset('images/event-workshop.webp') }}" alt="Business workshop for women entrepreneurs" class="landing-photo h-44" loading="lazy">
                </div>
                <div class="w-12 h-12 rounded-2xl bg-accent-50 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="heading text-xl font-bold text-ink mb-3">
                    <span data-lang="ru">Календарь событий</span>
                    <span data-lang="en">Events calendar</span>
                    <span data-lang="ro">Calendar evenimente</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Находите предстоящие тренинги, консультации, вебинары, форумы и бизнес-встречи.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Find upcoming trainings, consultations, webinars, forums and business meetings.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Găsiți traininguri, consultații, webinare, forumuri și întâlniri de afaceri viitoare.</p>
            </div>
            <div class="glass rounded-3xl p-8 card-hover reveal delay-2">
                <div class="photo-sheen rounded-2xl overflow-hidden mb-6 -mx-2 -mt-2">
                    <img src="{{ asset('images/event-esg.webp') }}" alt="ESG and business opportunity session" class="landing-photo h-44" loading="lazy">
                </div>
                <div class="w-12 h-12 rounded-2xl bg-mint-100 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="heading text-xl font-bold text-ink mb-3">
                    <span data-lang="ru">Возможности</span>
                    <span data-lang="en">Opportunities</span>
                    <span data-lang="ro">Oportunități</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Открывайте гранты, программы поддержки, выставки, экспортную поддержку и партнёрские предложения.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Discover grants, support programmes, exhibitions, export support and partner offers.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Descoperiți granturi, programe de sprijin, expoziții, sprijin pentru export și oferte ale partenerilor.</p>
            </div>
            <div class="glass rounded-3xl p-8 card-hover reveal delay-3">
                <div class="photo-sheen rounded-2xl overflow-hidden mb-6 -mx-2 -mt-2">
                    <img src="{{ asset('images/event-networking.webp') }}" alt="Networking among women entrepreneurs" class="landing-photo h-44" loading="lazy">
                </div>
                <div class="w-12 h-12 rounded-2xl bg-warm-50 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="heading text-xl font-bold text-ink mb-3">
                    <span data-lang="ru">Обновления сообщества</span>
                    <span data-lang="en">Community updates</span>
                    <span data-lang="ro">Actualizări ale comunității</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Следите за новостями, анонсами, историями участниц и новыми инициативами.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Follow news, announcements, success stories and new initiatives.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Urmăriți știri, anunțuri, povești de succes și inițiative noi.</p>
            </div>
        </div>
    </div>
</section>

{{-- ===== AI & TELEGRAM ===== --}}
<section class="py-28 px-4 sm:px-6 bg-soft">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-3">
                <span data-lang="ru">AI и Telegram</span>
                <span data-lang="en">AI & Telegram</span>
                <span data-lang="ro">AI & Telegram</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-ink">
                <span data-lang="ru">Умная поддержка, не&nbsp;заменяющая живое общение</span>
                <span data-lang="en">Smart support without replacing human connection</span>
                <span data-lang="ro">Suport inteligent fără a înlocui conexiunea umană</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ru">Инструменты на базе ИИ помогут участницам эффективнее ориентироваться на платформе: находить релевантных предпринимательниц, улучшать описания бизнеса, готовить короткие питчи, открывать подходящие обучающие модули и получать лучшие рекомендации.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="en">AI-supported tools can help participants navigate the platform more efficiently: find relevant entrepreneurs, improve business descriptions, prepare short pitches, discover suitable learning modules and receive better recommendations.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ro">Instrumentele bazate pe AI pot ajuta participantele să navigheze mai eficient pe platformă: să găsească antreprenoare relevante, să îmbunătățească descrierile afacerii, să pregătească pitch-uri scurte, să descopere module de învățare potrivite și să primească recomandări mai bune.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl p-10 border border-mint-100 shadow-sm card-hover reveal delay-1">
                <div class="w-14 h-14 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="heading text-2xl font-bold text-ink mb-4">
                    <span data-lang="ru">AI-рекомендации</span>
                    <span data-lang="en">AI-supported recommendations</span>
                    <span data-lang="ro">Recomandări bazate pe AI</span>
                </h3>
                <p class="text-gray-500 leading-relaxed mb-6" data-lang="ru">Рекомендации модулей, событий, менторов, грантов и контактов на основе потребностей профиля. AI помогает искать и рекомендовать, но не заменяет человеческую модерацию, экспертное суждение или принятие решений по проекту.</p>
                <p class="text-gray-500 leading-relaxed mb-6" data-lang="en">Suggested modules, events, mentors, grants and contacts based on profile needs. AI must support search and recommendations, but it must not replace human moderation, expert judgement or project decision-making.</p>
                <p class="text-gray-500 leading-relaxed mb-6" data-lang="ro">Sugestii de module, evenimente, mentori, granturi și contacte pe baza nevoilor profilului. AI trebuie să sprijine căutarea și recomandările, dar nu trebuie să înlocuiască moderarea umană, judecata experților sau luarea deciziilor de proiect.</p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-mint-100 text-brand-600">Smart matching</span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-accent-50 text-accent-500">Profile tips</span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-mint-100 text-brand-600">Pitch assistant</span>
                </div>
            </div>
            <div class="bg-white rounded-3xl p-10 border border-mint-100 shadow-sm card-hover reveal delay-2">
                <div class="w-14 h-14 rounded-2xl grad-accent flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                </div>
                <h3 class="heading text-2xl font-bold text-ink mb-4">
                    <span data-lang="ru">Telegram-уведомления</span>
                    <span data-lang="en">Telegram notifications</span>
                    <span data-lang="ro">Notificări Telegram</span>
                </h3>
                <p class="text-gray-500 leading-relaxed mb-6" data-lang="ru">Быстрые напоминания, анонсы, приглашения, короткие опросы и персонализированные обновления. Интеграция с Telegram делает коммуникацию проще — без необходимости постоянно проверять сайт.</p>
                <p class="text-gray-500 leading-relaxed mb-6" data-lang="en">Fast reminders, announcements, invitations, quick surveys and personalised updates. Telegram integration will make communication easier through reminders, event invitations, announcements, personalised recommendations and quick updates.</p>
                <p class="text-gray-500 leading-relaxed mb-6" data-lang="ro">Mementouri rapide, anunțuri, invitații, sondaje rapide și actualizări personalizate. Integrarea Telegram va facilita comunicarea prin mementouri, invitații la evenimente, anunțuri, recomandări personalizate și actualizări rapide.</p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-accent-50 text-accent-500">Instant reminders</span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-mint-100 text-brand-600">Event invites</span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-accent-50 text-accent-500">Quick surveys</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== SUCCESS STORIES ===== --}}
<section class="py-28 px-4 sm:px-6 bg-white scroll-mt-20" id="stories">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent-500 mb-3">
                <span data-lang="ru">Истории</span>
                <span data-lang="en">Participant stories</span>
                <span data-lang="ro">Istorii ale participantelor</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-ink">
                <span data-lang="ru">Истории, которые делают женский бизнес заметнее</span>
                <span data-lang="en">Stories that make women-led businesses more visible</span>
                <span data-lang="ro">Povești care fac afacerile conduse de femei mai vizibile</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ru">Платформа будет делать участниц и их бизнес более заметными: через профили, примеры сотрудничества, новые инициативы и практический опыт.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="en">The platform will make participants and their businesses more visible through profiles, cooperation examples, new initiatives and practical experience.</p>
            <p class="text-gray-500 max-w-2xl mx-auto mt-4" data-lang="ro">Platforma va evidenția femeile antreprenoare, afacerile lor, realizările, creșterea și exemplele de cooperare create prin proiect.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="glass rounded-3xl p-8 card-hover reveal delay-1 text-center">
                <div class="photo-sheen rounded-2xl overflow-hidden mb-6 -mx-2 -mt-2">
                    <img src="{{ asset('images/story-export.webp') }}" alt="Woman entrepreneur preparing export growth" class="landing-photo h-48" loading="lazy">
                </div>
                <div class="w-14 h-14 rounded-2xl bg-mint-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <h3 class="heading text-lg font-bold text-ink mb-2">
                    <span data-lang="ru">От локального сервиса к&nbsp;региональной известности</span>
                    <span data-lang="en">From local service to regional visibility</span>
                    <span data-lang="ro">De la serviciu local la vizibilitate regională</span>
                </h3>
                <p class="text-xs text-gray-400 italic">
                    <span data-lang="ru">Истории участниц скоро появятся здесь</span>
                    <span data-lang="en">Participant stories coming soon</span>
                    <span data-lang="ro">Poveștile participantelor în curând</span>
                </p>
            </div>
            <div class="glass rounded-3xl p-8 card-hover reveal delay-2 text-center">
                <div class="photo-sheen rounded-2xl overflow-hidden mb-6 -mx-2 -mt-2">
                    <img src="{{ asset('images/story-mentor.webp') }}" alt="Mentoring conversation for business growth" class="landing-photo h-48" loading="lazy">
                </div>
                <div class="w-14 h-14 rounded-2xl bg-accent-50 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-6 h-6 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="heading text-lg font-bold text-ink mb-2">
                    <span data-lang="ru">Поиск партнёра через платформу</span>
                    <span data-lang="en">Finding a partner through the platform</span>
                    <span data-lang="ro">Găsirea unui partener prin platformă</span>
                </h3>
                <p class="text-xs text-gray-400 italic">
                    <span data-lang="ru">Истории участниц скоро появятся здесь</span>
                    <span data-lang="en">Participant stories coming soon</span>
                    <span data-lang="ro">Poveștile participantelor în curând</span>
                </p>
            </div>
            <div class="glass rounded-3xl p-8 card-hover reveal delay-3 text-center">
                <div class="photo-sheen rounded-2xl overflow-hidden mb-6 -mx-2 -mt-2">
                    <img src="{{ asset('images/member-agrifood.webp') }}" alt="Agri-food woman-led business profile" class="landing-photo h-48" loading="lazy">
                </div>
                <div class="w-14 h-14 rounded-2xl bg-mint-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="heading text-lg font-bold text-ink mb-2">
                    <span data-lang="ru">Обучение, менторство и&nbsp;уверенный бизнес-план</span>
                    <span data-lang="en">Learning, mentoring and a stronger business plan</span>
                    <span data-lang="ro">Învățare, mentorat și un plan de afaceri mai solid</span>
                </h3>
                <p class="text-xs text-gray-400 italic">
                    <span data-lang="ru">Истории участниц скоро появятся здесь</span>
                    <span data-lang="en">Participant stories coming soon</span>
                    <span data-lang="ro">Poveștile participantelor în curând</span>
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ===== PARTNERS & METRICS ===== --}}
<section class="py-28 px-4 sm:px-6 grad-dark relative overflow-hidden">
    <div class="absolute inset-0 dot-pattern opacity-10"></div>
    <div class="max-w-6xl mx-auto relative">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-mint-200 mb-3">
                <span data-lang="ru">Влияние</span>
                <span data-lang="en">Impact</span>
                <span data-lang="ro">Impact</span>
            </p>
            <h2 class="heading text-4xl sm:text-5xl font-bold text-white">
                <span data-lang="ru">Долгосрочная инфраструктура для&nbsp;измеримого влияния</span>
                <span data-lang="en">A long-term infrastructure for measurable impact</span>
                <span data-lang="ro">O infrastructură pe termen lung pentru un impact măsurabil</span>
            </h2>
            <p class="text-gray-400 max-w-2xl mx-auto mt-4" data-lang="ru">Платформа спроектирована как устойчивая цифровая инфраструктура, способная функционировать после завершения проектного периода. Она будет поддерживать бизнес, руководимый женщинами, повышать видимость программ поддержки, укреплять сотрудничество и создавать измеримую долгосрочную ценность.</p>
            <p class="text-gray-400 max-w-2xl mx-auto mt-4" data-lang="en">The platform is designed as a sustainable digital infrastructure that can continue functioning after the project period. It will support women-led businesses, increase the visibility of support programmes, strengthen cooperation and create measurable long-term value.</p>
            <p class="text-gray-400 max-w-2xl mx-auto mt-4" data-lang="ro">Platforma este concepută ca o infrastructură digitală durabilă care poate continua să funcționeze după perioada proiectului. Va sprijini afacerile conduse de femei, va crește vizibilitatea programelor de sprijin, va întări cooperarea și va crea valoare măsurabilă pe termen lung.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
            @php
            $metrics = [
                ['num'=>'500+','ru'=>'зарегистрированных участниц','en'=>'registered participants','ro'=>'participante înregistrate'],
                ['num'=>'300+','ru'=>'заполненных профилей','en'=>'completed profiles','ro'=>'profiluri completate'],
                ['num'=>'10+','ru'=>'обучающих модулей','en'=>'learning modules','ro'=>'module de învățare'],
                ['num'=>'50+','ru'=>'запросов и предложений','en'=>'requests and offers','ro'=>'cereri și oferte'],
            ];
            @endphp
            @foreach($metrics as $i => $m)
            <div class="glass-dark rounded-2xl p-6 reveal delay-{{ $i + 1 }}">
                <div class="heading text-4xl font-bold text-accent-500 leading-none mb-2">{{ $m['num'] }}</div>
                <div class="text-xs text-gray-400 uppercase tracking-widest">
                    <span data-lang="ru">{{ $m['ru'] }}</span>
                    <span data-lang="en">{{ $m['en'] }}</span>
                    <span data-lang="ro">{{ $m['ro'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== FINAL CTA ===== --}}
<section class="py-28 px-4 bg-soft relative overflow-hidden" id="contact">
    <div class="absolute inset-0 connection-lines"></div>
    <div class="max-w-3xl mx-auto text-center relative reveal">
        <h2 class="heading text-4xl sm:text-5xl font-bold text-ink mb-5 leading-tight">
            <span data-lang="ru">Присоединяйтесь к&nbsp;сообществу, где женский бизнес становится заметнее и&nbsp;находит связи</span>
            <span data-lang="en">Join a community where women's businesses can be seen, supported and connected</span>
            <span data-lang="ro">Alătură-te unei comunități unde afacerile femeilor pot fi văzute, sprijinite și conectate</span>
        </h2>
        <p class="text-gray-500 mb-10 max-w-xl mx-auto" data-lang="ru">Создайте профиль, откройте возможности, освойте новые навыки и свяжитесь с предпринимательницами, менторами, экспертами и партнёрами.</p>
        <p class="text-gray-500 mb-10 max-w-xl mx-auto" data-lang="en">Create your profile, discover opportunities, learn new skills and connect with women entrepreneurs, mentors, experts and partners.</p>
        <p class="text-gray-500 mb-10 max-w-xl mx-auto" data-lang="ro">Creează-ți profilul, descoperă oportunități, învață abilități noi și conectează-te cu femei antreprenoare, mentori, experți și parteneri.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="https://t.me/WomenComBot" target="_blank"
               class="inline-flex items-center gap-3 grad-accent text-white font-semibold text-lg px-10 py-4 rounded-full shadow-2xl hover:shadow-3xl hover:-translate-y-0.5 transition-all" style="box-shadow:0 12px 40px rgba(230,107,79,.35);">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                <span data-lang="ru">Присоединиться к платформе</span>
                <span data-lang="en">Join the platform</span>
                <span data-lang="ro">Alătură-te platformei</span>
            </a>
            <a href="https://t.me/WomenComBot" target="_blank"
               class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-brand-600 transition">
                <span data-lang="ru">Связаться с командой проекта</span>
                <span data-lang="en">Contact the project team</span>
                <span data-lang="ro">Contactați echipa proiectului</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ===== FOOTER ===== --}}
<footer class="bg-white border-t border-mint-100 py-16 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto grid sm:grid-cols-2 lg:grid-cols-3 gap-10 mb-12">
        {{-- Platform --}}
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-5">
                <span data-lang="ru">Платформа</span>
                <span data-lang="en">Platform</span>
                <span data-lang="ro">Platformă</span>
            </p>
            <div class="flex flex-col gap-2.5">
                <a href="#how-works" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span>
                </a>
                <a href="#learning" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span>
                </a>
                <a href="#tools" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Каталог</span><span data-lang="en">Directory</span><span data-lang="ro">Director</span>
                </a>
                <a href="#events" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span>
                </a>
                <a href="#tools" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span>
                </a>
                <a href="#stories" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Истории участниц</span><span data-lang="en">Participant stories</span><span data-lang="ro">Istorii ale participantelor</span>
                </a>
            </div>
        </div>
        {{-- Community --}}
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-5">
                <span data-lang="ru">Сообщество</span>
                <span data-lang="en">Community</span>
                <span data-lang="ro">Comunitate</span>
            </p>
            <div class="flex flex-col gap-2.5">
                <a href="#who-for" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Для предпринимательниц</span><span data-lang="en">For entrepreneurs</span><span data-lang="ro">Pentru antreprenoare</span>
                </a>
                <a href="#who-for" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Для менторов</span><span data-lang="en">For mentors</span><span data-lang="ro">Pentru mentori</span>
                </a>
                <a href="#who-for" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Для партнёров</span><span data-lang="en">For partners</span><span data-lang="ro">Pentru parteneri</span>
                </a>
                <a href="#tools" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Запросы и предложения</span><span data-lang="en">Requests and offers</span><span data-lang="ro">Cereri și oferte</span>
                </a>
                <a href="https://t.me/WomenComBot" target="_blank" class="text-sm text-gray-500 hover:text-brand-600 transition">Telegram</a>
            </div>
        </div>
        {{-- Project --}}
        <div class="sm:col-span-2 lg:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-5">
                <span data-lang="ru">Проект</span>
                <span data-lang="en">Project</span>
                <span data-lang="ro">Proiect</span>
            </p>
            <div class="flex flex-col gap-2.5">
                <a href="#" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Партнёры</span><span data-lang="en">Partners</span><span data-lang="ro">Parteneri</span>
                </a>
                <a href="#" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Поддержка доноров</span><span data-lang="en">Donor support</span><span data-lang="ro">Sprijinul donatorilor</span>
                </a>
                <a href="#contact" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span>
                </a>
                <a href="#" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Политика конфиденциальности</span><span data-lang="en">Privacy policy</span><span data-lang="ro">Politica de confidențialitate</span>
                </a>
                <a href="#" class="text-sm text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Условия использования</span><span data-lang="en">Terms of use</span><span data-lang="ro">Termeni de utilizare</span>
                </a>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto border-t border-mint-100 pt-8 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-lg grad-bg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </span>
            <span class="text-xs text-gray-400">&copy; {{ date('Y') }} Women Entrepreneurs Platform</span>
        </div>
        <p class="text-xs text-gray-400" data-lang="ru">Цифровая экосистема для обучения, нетворкинга, менторства и роста бизнеса.</p>
        <p class="text-xs text-gray-400" data-lang="en">A digital ecosystem for learning, networking, mentoring and business growth.</p>
        <p class="text-xs text-gray-400" data-lang="ro">Un ecosistem digital pentru învățare, networking, mentorat și creșterea afacerilor.</p>
    </div>
</footer>

{{-- ===== SCRIPTS ===== --}}
<script>
    const LANG_KEY = 'inspair_lang';
    function setLang(lang) {
        localStorage.setItem(LANG_KEY, lang);
        document.documentElement.lang = lang;
        document.querySelectorAll('.lang-btn').forEach(btn => {
            const active = btn.id === 'btn-' + lang;
            btn.className = 'lang-btn px-2.5 py-1 text-xs font-medium rounded-full transition '
                + (active ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:text-brand-600');
        });
    }
    setLang(document.documentElement.lang);

    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => observer.observe(el));

    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu    = document.getElementById('mobile-menu');
    const hamburgerIcon = document.getElementById('hamburger-icon');
    const closeIcon     = document.getElementById('close-icon');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isOpen = !mobileMenu.classList.contains('hidden');
            mobileMenu.classList.toggle('hidden');
            hamburgerIcon.classList.toggle('hidden', !isOpen);
            closeIcon.classList.toggle('hidden', isOpen);
            mobileMenuBtn.setAttribute('aria-expanded', String(!isOpen));
        });
        document.querySelectorAll('.mobile-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            });
        });
    }
</script>
<script>
    // Telegram In-App Browser fix
    (function () {
        if (!window.Telegram || !window.Telegram.WebApp) return;
        var tg = window.Telegram.WebApp;
        document.querySelectorAll('a[href*="t.me/WomenComBot"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                tg.openTelegramLink(link.href);
                tg.close();
            });
        });
    })();
</script>
</body>
</html>
@endif
