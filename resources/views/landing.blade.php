<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INSPIRE Community — Платформа партнёрства Молдовы</title>
    <meta name="description" content="INSPIRE Community — платформа ИИ-матчинга для молодых лидеров и предпринимателей Молдовы. Найдите партнёра, ментора или соучредителя через Telegram.">
    <meta property="og:title" content="INSPIRE Community">
    <meta property="og:description" content="Платформа партнёрства и роста для лидеров Молдовы">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#7c3aed">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                        sans:  ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                        },
                        ink: { DEFAULT: '#1a1225' }
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
        body { font-family: 'Inter', sans-serif; background: #faf9fc; color: #1a1225; overflow-x: hidden; }
        h1,h2,h3,.serif { font-family: 'Cormorant Garamond', Georgia, serif; }

        .grad-text {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 60%, #818cf8 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .grad-bg   { background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%); }
        .grad-dark { background: linear-gradient(160deg, #1a0533 0%, #0f0f2e 100%); }

        .glass {
            background: rgba(255,255,255,0.72);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(124,58,237,0.12);
            box-shadow: 0 2px 32px rgba(124,58,237,0.06), 0 1px 2px rgba(0,0,0,0.04);
        }
        .glass-dark {
            background: rgba(26,18,37,0.55);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(167,139,250,0.18);
        }
        .glow-border { position: relative; }
        .glow-border::before {
            content: ''; position: absolute; inset: 0; border-radius: inherit; padding: 1px;
            background: linear-gradient(135deg, rgba(124,58,237,.4), rgba(79,70,229,.2), rgba(124,58,237,.4));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
        }
        .orb { border-radius: 50%; filter: blur(80px); opacity: 0.35; pointer-events: none; }

        .reveal       { opacity: 0; transform: translateY(36px);  transition: opacity .7s cubic-bezier(.22,1,.36,1), transform .7s cubic-bezier(.22,1,.36,1); }
        .reveal-left  { opacity: 0; transform: translateX(-40px); transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); }
        .reveal-right { opacity: 0; transform: translateX(40px);  transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); }
        .reveal.visible, .reveal-left.visible, .reveal-right.visible { opacity: 1; transform: none; }
        .delay-1 { transition-delay: .1s; } .delay-2 { transition-delay: .2s; }
        .delay-3 { transition-delay: .3s; } .delay-4 { transition-delay: .4s; }

        #navbar { background: rgba(250,249,252,0.65); backdrop-filter: blur(10px); transition: background .3s, box-shadow .3s, backdrop-filter .3s; }
        #navbar.scrolled { background: rgba(250,249,252,0.96); box-shadow: 0 1px 0 rgba(124,58,237,0.1); backdrop-filter: blur(24px); }

        details summary::-webkit-details-marker { display: none; }
        details[open] .chevron { transform: rotate(180deg); }
        .chevron { transition: transform .3s ease; }
        ::selection { background: rgba(124,58,237,.18); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f5f3ff; }
        ::-webkit-scrollbar-thumb { background: rgba(124,58,237,.3); border-radius: 3px; }
        :focus-visible { outline: 2px solid #7c3aed; outline-offset: 3px; border-radius: 4px; }

        /* Hero CSS animations — above-fold content, no JS dependency */
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

        /* Stats counter */
        .stat-num { font-variant-numeric: tabular-nums; }
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
<body class="antialiased">

{{-- NAVBAR --}}
<nav id="navbar" class="fixed top-0 inset-x-0 z-50 px-4">
    <div class="max-w-6xl mx-auto flex items-center justify-between h-16">
        <a href="#" class="flex items-center gap-2.5 group">
            <span class="w-7 h-7 rounded-lg grad-bg flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <span class="font-semibold text-ink tracking-tight text-sm">INSPIRE Community</span>
        </a>
        <div class="flex items-center gap-6">
            <div class="hidden sm:flex items-center gap-5 text-sm text-gray-500">
                <a href="#about" class="hover:text-brand-600 transition">
                    <span data-lang="ru">О платформе</span>
                    <span data-lang="en">About</span>
                    <span data-lang="ro">Despre</span>
                </a>
                <a href="#values" class="hover:text-brand-600 transition">
                    <span data-lang="ru">Ценности</span>
                    <span data-lang="en">Values</span>
                    <span data-lang="ro">Valori</span>
                </a>
                <a href="#faq" class="hover:text-brand-600 transition">FAQ</a>
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
               class="hidden sm:inline-flex items-center gap-1.5 grad-bg text-white text-xs font-semibold px-4 py-2 rounded-full shadow-md hover:shadow-lg transition-shadow">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Войти в кабинет
            </a>
            <button id="mobile-menu-btn"
                class="sm:hidden p-2 -mr-1 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition"
                aria-label="Открыть меню" aria-expanded="false" aria-controls="mobile-menu">
                <svg id="hamburger-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="hidden sm:hidden absolute top-full inset-x-0 border-b border-brand-100 px-4 py-5 shadow-xl" style="background:rgba(255,255,255,0.98);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);">
        <div class="flex flex-col gap-1">
            <a href="#about" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
                <span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span>
            </a>
            <a href="#values" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
                <span data-lang="ru">Ценности</span><span data-lang="en">Values</span><span data-lang="ro">Valori</span>
            </a>
            <a href="#network" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">INSPIRE Network</a>
            <a href="#faq" class="mobile-nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">FAQ</a>
            <div class="border-t border-gray-100 mt-3 pt-4">
                <a href="{{ route('account.login') }}" target="_blank"
                   class="flex items-center justify-center gap-2.5 grad-bg text-white font-semibold text-sm px-5 py-3.5 rounded-full shadow-md w-full">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    Войти в кабинет
                </a>
            </div>
        </div>
    </div>
</nav>

{{-- HERO --}}
<section class="relative min-h-screen flex items-center overflow-hidden pt-16">
    <div class="absolute inset-0 -z-10" style="background-image:radial-gradient(rgba(124,58,237,.06) 1.5px,transparent 1.5px);background-size:32px 32px;"></div>
    <div class="orb absolute -top-32 -right-32 w-[600px] h-[600px]" style="background:radial-gradient(#a78bfa,#4f46e5);"></div>
    <div class="orb absolute bottom-0 left-1/4 w-[400px] h-[400px]" style="background:radial-gradient(#7c3aed,transparent);opacity:.15;"></div>

    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-24 grid lg:grid-cols-2 gap-16 items-center">
        <div>
            <div class="hero-in hero-in-1 inline-flex items-center gap-2 bg-brand-50 border border-brand-200 text-brand-600 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-8 tracking-wide uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                INSPIRE Network &middot; Moldova
            </div>

            <h1 class="serif text-5xl sm:text-6xl lg:text-7xl font-semibold leading-[1.1] tracking-tight mb-6 hero-in hero-in-2">
                <span data-lang="ru">Платформа<br><em class="grad-text not-italic">партнёрства</em><br>и роста</span>
                <span data-lang="en">Platform of<br><em class="grad-text not-italic">partnership</em><br>and growth</span>
                <span data-lang="ro">Platformă de<br><em class="grad-text not-italic">parteneriat</em><br>și creștere</span>
            </h1>

            <p class="text-lg text-gray-500 leading-relaxed max-w-md mb-10 hero-in hero-in-3" data-lang="ru">Объединяем молодых лидеров и предпринимателей Молдовы через технологии ИИ-матчинга.</p>
            <p class="text-lg text-gray-500 leading-relaxed max-w-md mb-10 hero-in hero-in-3" data-lang="en">Connecting young leaders and entrepreneurs of Moldova through AI matching technology.</p>
            <p class="text-lg text-gray-500 leading-relaxed max-w-md mb-10 hero-in hero-in-3" data-lang="ro">Unim tineri lideri și antreprenori ai Moldovei prin tehnologia de matching AI.</p>

            <div class="flex flex-wrap items-center gap-4 hero-in hero-in-4">
                <a href="https://t.me/WomenComBot" target="_blank"
                   class="inline-flex items-center gap-2 grad-bg text-white font-semibold text-sm px-7 py-3.5 rounded-full shadow-lg shadow-brand-600/25 hover:shadow-brand-600/45 hover:-translate-y-0.5 transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    <span data-lang="ru">Присоединиться</span>
                    <span data-lang="en">Join now</span>
                    <span data-lang="ro">Alătură-te</span>
                </a>
                <a href="#about" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-brand-600 transition">
                    <span data-lang="ru">Узнать больше</span>
                    <span data-lang="en">Learn more</span>
                    <span data-lang="ro">Află mai mult</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>

        <div class="hidden lg:flex items-center justify-center hero-in-right">
            <div class="relative w-[440px] h-[440px]">
                <div class="absolute inset-0 rounded-full border border-brand-200/60 animate-[spin_40s_linear_infinite]"></div>
                <div class="absolute inset-8 rounded-full border border-brand-300/40 animate-[spin_25s_linear_infinite_reverse]"></div>
                <div class="absolute inset-16 rounded-full border border-brand-400/25 animate-[spin_18s_linear_infinite]"></div>
                <div class="absolute inset-24 rounded-3xl glass glow-border flex flex-col items-center justify-center gap-4 shadow-xl">
                    <div class="w-14 h-14 rounded-2xl grad-bg flex items-center justify-center shadow-lg shadow-brand-600/30">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="serif text-2xl font-semibold text-ink">INSPIRE</span>
                    <div class="flex gap-1.5">
                        <span class="w-2 h-2 rounded-full" style="background:#7c3aed"></span>
                        <span class="w-2 h-2 rounded-full" style="background:#4f46e5"></span>
                        <span class="w-2 h-2 rounded-full" style="background:#818cf8"></span>
                    </div>
                </div>
                <div class="absolute top-6 left-1/2 -translate-x-1/2 w-11 h-11 rounded-xl glass glow-border flex items-center justify-center text-lg shadow-sm">🤝</div>
                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 w-11 h-11 rounded-xl glass glow-border flex items-center justify-center text-lg shadow-sm">🌱</div>
                <div class="absolute top-1/2 left-4 -translate-y-1/2 w-11 h-11 rounded-xl glass glow-border flex items-center justify-center text-lg shadow-sm">⚡</div>
                <div class="absolute top-1/2 right-4 -translate-y-1/2 w-11 h-11 rounded-xl glass glow-border flex items-center justify-center text-lg shadow-sm">🌍</div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-gray-400">
        <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>

{{-- STATS --}}
<section class="py-12 bg-white border-b border-gray-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
            <div class="reveal">
                <div class="serif text-4xl font-bold grad-text stat-num leading-none">150+</div>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">
                    <span data-lang="ru">участников</span><span data-lang="en">members</span><span data-lang="ro">participanți</span>
                </p>
            </div>
            <div class="reveal delay-1">
                <div class="serif text-4xl font-bold grad-text stat-num leading-none">5+</div>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">
                    <span data-lang="ru">университетов</span><span data-lang="en">universities</span><span data-lang="ro">universități</span>
                </p>
            </div>
            <div class="reveal delay-2">
                <div class="serif text-4xl font-bold grad-text stat-num leading-none">3</div>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">
                    <span data-lang="ru">языка</span><span data-lang="en">languages</span><span data-lang="ro">limbi</span>
                </p>
            </div>
            <div class="reveal delay-3">
                <div class="text-3xl leading-none">🇳🇱</div>
                <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">
                    <span data-lang="ru">при поддержке NL</span><span data-lang="en">NL backed</span><span data-lang="ro">sprijin NL</span>
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ABOUT --}}
<section class="py-28 px-4 sm:px-6 bg-white scroll-mt-20" id="about">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-600 mb-3">
                <span data-lang="ru">О платформе</span>
                <span data-lang="en">About the platform</span>
                <span data-lang="ro">Despre platformă</span>
            </p>
            <h2 class="serif text-4xl sm:text-5xl font-semibold text-ink">
                <span data-lang="ru">Технологии в&nbsp;пользу<br><em class="not-italic grad-text">человека</em></span>
                <span data-lang="en">Technology for the<br><em class="not-italic grad-text">benefit of people</em></span>
                <span data-lang="ro">Tehnologie în beneficiul<br><em class="not-italic grad-text">oamenilor</em></span>
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="glass glow-border rounded-3xl p-8 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-1">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">Интеллектуальный матчинг</span>
                    <span data-lang="en">Intelligent matching</span>
                    <span data-lang="ro">Matching inteligent</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Заполните профиль — ИИ найдёт тех, кто может стать вашим партнёром, клиентом или ментором.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Fill in your profile — AI will find those who can become your partner, client or mentor.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Completează profilul — AI va găsi cei care pot deveni partenerul, clientul sau mentorul tău.</p>
            </div>
            <div class="glass glow-border rounded-3xl p-8 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-2">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">Сообщество лидеров</span>
                    <span data-lang="en">Community of leaders</span>
                    <span data-lang="ro">Comunitate de lideri</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Выпускники Академии INSPIRE, социальные предприниматели, исследователи и активисты, меняющие Молдову.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">INSPIRE Academy graduates, social entrepreneurs, researchers and activists changing Moldova.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Absolvenți ai Academiei INSPIRE, antreprenori sociali, cercetători și activiști care schimbă Moldova.</p>
            </div>
            <div class="glass glow-border rounded-3xl p-8 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-3">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">От знакомства к результату</span>
                    <span data-lang="en">From meeting to outcome</span>
                    <span data-lang="ro">De la cunoaștere la rezultat</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Ищете соучредителя для стартапа или эксперта для проекта? Платформа создаёт значимые связи.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Looking for a startup co-founder or a project expert? The platform creates meaningful connections.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Cauți co-fondator pentru startup sau expert pentru proiect? Platforma creează conexiuni semnificative.</p>
            </div>
        </div>
    </div>
</section>

{{-- STEPS (dark) --}}
<section class="grad-dark py-28 px-4 sm:px-6 relative overflow-hidden scroll-mt-20" id="join">
    <div class="orb absolute -top-24 right-0 w-[500px] h-[500px]" style="background:radial-gradient(#7c3aed,transparent);opacity:.18;"></div>
    <div class="orb absolute -bottom-32 left-0 w-[400px] h-[400px]" style="background:radial-gradient(#4f46e5,transparent);opacity:.15;"></div>
    <div class="max-w-5xl mx-auto relative">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-400 mb-3">
                <span data-lang="ru">Как присоединиться</span>
                <span data-lang="en">How to join</span>
                <span data-lang="ro">Cum să te alături</span>
            </p>
            <h2 class="serif text-4xl sm:text-5xl font-semibold text-white">
                <span data-lang="ru">Три простых шага</span>
                <span data-lang="en">Three simple steps</span>
                <span data-lang="ro">Trei pași simpli</span>
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="glass-dark rounded-3xl p-8 reveal delay-1">
                <div class="serif text-6xl font-bold grad-text mb-6 leading-none">01</div>
                <h3 class="serif text-xl font-semibold text-white mb-3">
                    <span data-lang="ru">Регистрация</span>
                    <span data-lang="en">Registration</span>
                    <span data-lang="ro">Înregistrare</span>
                </h3>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="ru">Перейдите в Telegram-бот и заполните профиль: компетенции, интересы и цели.</p>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="en">Go to the Telegram bot and fill in your profile: competencies, interests and goals.</p>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="ro">Mergi la bot-ul Telegram și completează profilul: competențe, interese și obiective.</p>
            </div>
            <div class="glass-dark rounded-3xl p-8 reveal delay-2">
                <div class="serif text-6xl font-bold grad-text mb-6 leading-none">02</div>
                <h3 class="serif text-xl font-semibold text-white mb-3">
                    <span data-lang="ru">ИИ-анализ</span>
                    <span data-lang="en">AI analysis</span>
                    <span data-lang="ro">Analiză AI</span>
                </h3>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="ru">Алгоритм проанализирует профиль и найдёт участников с наибольшим потенциалом для партнёрства.</p>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="en">The algorithm will analyze your profile and find participants with the greatest partnership potential.</p>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="ro">Algoritmul va analiza profilul și va găsi participanți cu cel mai mare potențial de parteneriat.</p>
            </div>
            <div class="glass-dark rounded-3xl p-8 reveal delay-3">
                <div class="serif text-6xl font-bold grad-text mb-6 leading-none">03</div>
                <h3 class="serif text-xl font-semibold text-white mb-3">
                    <span data-lang="ru">Сотрудничество</span>
                    <span data-lang="en">Collaboration</span>
                    <span data-lang="ro">Colaborare</span>
                </h3>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="ru">Получите персонализированные рекомендации и начните диалог с вашим будущим партнёром.</p>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="en">Get personalized recommendations and start a dialogue with your future partner.</p>
                <p class="text-sm text-gray-400 leading-relaxed" data-lang="ro">Obțineți recomandări personalizate și inițiați un dialog cu viitorul partener.</p>
            </div>
        </div>
        <div class="text-center mt-14 reveal delay-4">
            <a href="https://t.me/WomenComBot" target="_blank"
               class="inline-flex items-center gap-2.5 grad-bg text-white font-semibold px-8 py-4 rounded-full shadow-xl shadow-brand-600/30 hover:shadow-brand-600/50 hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                <span data-lang="ru">Начать сейчас</span>
                <span data-lang="en">Start now</span>
                <span data-lang="ro">Începe acum</span>
            </a>
        </div>
    </div>
</section>

{{-- VALUES --}}
<section class="py-28 px-4 sm:px-6 bg-brand-50 scroll-mt-20" id="values">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-600 mb-3">
                <span data-lang="ru">Наши принципы</span>
                <span data-lang="en">Our principles</span>
                <span data-lang="ro">Principiile noastre</span>
            </p>
            <h2 class="serif text-4xl sm:text-5xl font-semibold text-ink">
                <span data-lang="ru">Ценности сообщества</span>
                <span data-lang="en">Community values</span>
                <span data-lang="ro">Valorile comunității</span>
            </h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="glass glow-border rounded-2xl p-6 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-1">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">Социальная сплочённость</span>
                    <span data-lang="en">Social cohesion</span>
                    <span data-lang="ro">Coeziune socială</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Строим мосты между людьми, организациями и регионами Молдовы.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Building bridges between people, organizations and regions of Moldova.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Construim poduri între oameni, organizații și regiunile Moldovei.</p>
            </div>
            <div class="glass glow-border rounded-2xl p-6 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-2">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">Устойчивое развитие</span>
                    <span data-lang="en">Sustainable development</span>
                    <span data-lang="ro">Dezvoltare durabilă</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Каждое партнёрство — шаг к устойчивому будущему наших сообществ.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Every partnership is a step toward a sustainable future for our communities.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Fiecare parteneriat este un pas spre un viitor durabil al comunităților noastre.</p>
            </div>
            <div class="glass glow-border rounded-2xl p-6 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-3">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">Лидерство через действие</span>
                    <span data-lang="en">Leadership through action</span>
                    <span data-lang="ro">Leadership prin acțiune</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Не просто нетворкинг — конкретные проекты и измеримые результаты.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Not just networking — concrete projects and measurable results.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Nu doar networking — proiecte concrete și rezultate măsurabile.</p>
            </div>
            <div class="glass glow-border rounded-2xl p-6 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-1">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">Глобальное гражданство</span>
                    <span data-lang="en">Global citizenship</span>
                    <span data-lang="ro">Cetățenie globală</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Мыслим локально, действуем глобально — соединяем Молдову с международным опытом.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Think locally, act globally — connecting Moldova with international experience.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Gândim local, acționăm global — conectăm Moldova cu experiența internațională.</p>
            </div>
            <div class="glass glow-border rounded-2xl p-6 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-2">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">Цифровая трансформация</span>
                    <span data-lang="en">Digital transformation</span>
                    <span data-lang="ro">Transformare digitală</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Используем ИИ, чтобы делать сотрудничество эффективнее и доступнее.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">We use AI to make collaboration more efficient and accessible.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Folosim AI pentru a face colaborarea mai eficientă și mai accesibilă.</p>
            </div>
            <div class="glass glow-border rounded-2xl p-6 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 reveal delay-3">
                <div class="w-11 h-11 rounded-2xl grad-bg flex items-center justify-center mb-6 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h3 class="serif text-2xl font-semibold text-ink mb-3">
                    <span data-lang="ru">Ориентация на результат</span>
                    <span data-lang="en">Results-oriented</span>
                    <span data-lang="ro">Orientare spre rezultat</span>
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ru">Каждая связь имеет цель. Мы создаём реальные проекты и добиваемся измеримых изменений в обществе.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="en">Every connection has a purpose. We build real projects and achieve measurable changes in society.</p>
                <p class="text-sm text-gray-500 leading-relaxed" data-lang="ro">Fiecare conexiune are un scop. Construim proiecte reale și realizăm schimbări măsurabile în societate.</p>
            </div>
        </div>
    </div>
</section>

{{-- ABOUT INSPIRE NETWORK --}}
<section class="py-28 px-4 sm:px-6 bg-white scroll-mt-20" id="network">
    <div class="max-w-5xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="reveal-left">
                <p class="text-xs font-semibold uppercase tracking-widest text-brand-600 mb-4">INSPIRE Network</p>
                <h2 class="serif text-4xl sm:text-5xl font-semibold text-ink leading-tight mb-6">
                    <span data-lang="ru">Движение, меняющее страну</span>
                    <span data-lang="en">A movement changing the country</span>
                    <span data-lang="ro">O mișcare care schimbă țara</span>
                </h2>
                <div class="w-12 h-0.5 grad-bg mb-6 rounded-full"></div>
                <p class="text-gray-500 leading-relaxed mb-4" data-lang="ru">INSPIRE Network (Межуниверситетская сеть за устойчивый мир, инновации, исследования и образование) — совместная инициатива университетов и НПО Молдовы при поддержке <strong class="text-ink font-medium">Посольства Королевства Нидерландов</strong>.</p>
                <p class="text-gray-500 leading-relaxed mb-4" data-lang="en">INSPIRE Network (Inter-university Network for Sustainable Peace, Innovation, Research and Education) — a joint initiative of Moldovan universities and NGOs supported by the <strong class="text-ink font-medium">Embassy of the Kingdom of the Netherlands</strong>.</p>
                <p class="text-gray-500 leading-relaxed mb-4" data-lang="ro">INSPIRE Network (Rețeaua Interuniversitară pentru Pace Durabilă, Inovare, Cercetare și Educație) — o inițiativă comună a universităților și ONG-urilor din Moldova susținută de <strong class="text-ink font-medium">Ambasada Regatului Țărilor de Jos</strong>.</p>
                <p class="text-gray-500 leading-relaxed mb-8" data-lang="ru">Академия лидерства INSPIRE готовит молодых лидеров для решения глобальных вызовов. INSPIRE Community — следующий шаг: платформа, где знания превращаются в партнёрство.</p>
                <p class="text-gray-500 leading-relaxed mb-8" data-lang="en">The INSPIRE Leadership Academy trains young leaders to tackle global challenges. INSPIRE Community is the next step: a platform where knowledge turns into partnership.</p>
                <p class="text-gray-500 leading-relaxed mb-8" data-lang="ro">Academia de Leadership INSPIRE pregătește tineri lideri pentru provocările globale. INSPIRE Community este pasul următor: o platformă unde cunoașterea devine parteneriat.</p>
                <a href="https://peace.education.md/" target="_blank"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition group">
                    <span data-lang="ru">Узнать больше о проектах INSPIRE</span>
                    <span data-lang="en">Learn more about INSPIRE projects</span>
                    <span data-lang="ro">Află mai multe despre proiectele INSPIRE</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
            <div class="reveal-right">
                <div class="glass glow-border rounded-3xl p-8 space-y-6">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center flex-shrink-0">
                            <span class="serif text-2xl font-bold grad-text">100+</span>
                        </div>
                        <span class="text-sm text-gray-600">
                            <span data-lang="ru">выпускников программ INSPIRE</span>
                            <span data-lang="en">INSPIRE program graduates</span>
                            <span data-lang="ro">absolvenți ai programelor INSPIRE</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center flex-shrink-0">
                            <span class="serif text-2xl font-bold grad-text">5+</span>
                        </div>
                        <span class="text-sm text-gray-600">
                            <span data-lang="ru">университетов-партнёров</span>
                            <span data-lang="en">partner universities</span>
                            <span data-lang="ro">universități partenere</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center flex-shrink-0 text-2xl">
                            🇳🇱
                        </div>
                        <span class="text-sm text-gray-600">
                            <span data-lang="ru">поддержка Посольства Нидерландов</span>
                            <span data-lang="en">Supported by Embassy of Netherlands</span>
                            <span data-lang="ro">Susținut de Ambasada Țărilor de Jos</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-28 px-4 sm:px-6 bg-brand-50 scroll-mt-20" id="faq">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-600 mb-3">FAQ</p>
            <h2 class="serif text-4xl sm:text-5xl font-semibold text-ink">
                <span data-lang="ru">Частые вопросы</span>
                <span data-lang="en">Frequently asked questions</span>
                <span data-lang="ro">Întrebări frecvente</span>
            </h2>
        </div>
        <div class="space-y-3">
            <details class="group glass glow-border rounded-2xl overflow-hidden reveal delay-1">
                <summary class="flex items-center justify-between cursor-pointer px-7 py-5 list-none hover:bg-brand-50/50 transition">
                    <span class="serif text-base font-semibold text-ink pr-4">
                        <span data-lang="ru">Кто может присоединиться?</span>
                        <span data-lang="en">Who can join?</span>
                        <span data-lang="ro">Cine se poate alătura?</span>
                    </span>
                    <svg class="chevron w-5 h-5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-7 pb-5 text-sm text-gray-500 leading-relaxed border-t border-brand-100 pt-4">
                    <p data-lang="ru">Платформа открыта для выпускников программ INSPIRE, студентов университетов-партнёров, социальных предпринимателей и активистов, разделяющих наши ценности.</p>
                    <p data-lang="en">The platform is open to INSPIRE program graduates, students of partner universities, social entrepreneurs and activists who share our values.</p>
                    <p data-lang="ro">Platforma este deschisă absolvenților programelor INSPIRE, studenților universităților partenere, antreprenorilor sociali și activiștilor care împărtășesc valorile noastre.</p>
                </div>
            </details>
            <details class="group glass glow-border rounded-2xl overflow-hidden reveal delay-2">
                <summary class="flex items-center justify-between cursor-pointer px-7 py-5 list-none hover:bg-brand-50/50 transition">
                    <span class="serif text-base font-semibold text-ink pr-4">
                        <span data-lang="ru">Как ИИ подбирает партнёров?</span>
                        <span data-lang="en">How does AI match partners?</span>
                        <span data-lang="ro">Cum selectează AI partenerii?</span>
                    </span>
                    <svg class="chevron w-5 h-5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-7 pb-5 text-sm text-gray-500 leading-relaxed border-t border-brand-100 pt-4">
                    <p data-lang="ru">Алгоритм анализирует навыки, интересы, цели и опыт, сопоставляя их с профилями других участников для нахождения наиболее перспективных связей.</p>
                    <p data-lang="en">The algorithm analyzes skills, interests, goals and experience, matching them with other participants' profiles to find the most promising connections.</p>
                    <p data-lang="ro">Algoritmul analizează abilitățile, interesele, obiectivele și experiența, comparându-le cu profilurile altor participanți pentru a găsi conexiunile cele mai promițătoare.</p>
                </div>
            </details>
            <details class="group glass glow-border rounded-2xl overflow-hidden reveal delay-3">
                <summary class="flex items-center justify-between cursor-pointer px-7 py-5 list-none hover:bg-brand-50/50 transition">
                    <span class="serif text-base font-semibold text-ink pr-4">
                        <span data-lang="ru">Платформа бесплатна?</span>
                        <span data-lang="en">Is the platform free?</span>
                        <span data-lang="ro">Platforma este gratuită?</span>
                    </span>
                    <svg class="chevron w-5 h-5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-7 pb-5 text-sm text-gray-500 leading-relaxed border-t border-brand-100 pt-4">
                    <p data-lang="ru">Да, INSPIRE Community — некоммерческая инициатива для развития лидерского потенциала Молдовы.</p>
                    <p data-lang="en">Yes, INSPIRE Community is a non-profit initiative to develop the leadership potential of Moldova.</p>
                    <p data-lang="ro">Da, INSPIRE Community este o inițiativă non-profit pentru a dezvolta potențialul de leadership al Moldovei.</p>
                </div>
            </details>
            <details class="group glass glow-border rounded-2xl overflow-hidden reveal delay-4">
                <summary class="flex items-center justify-between cursor-pointer px-7 py-5 list-none hover:bg-brand-50/50 transition">
                    <span class="serif text-base font-semibold text-ink pr-4">
                        <span data-lang="ru">Нужно ли быть выпускником INSPIRE Academy?</span>
                        <span data-lang="en">Do I need to be an INSPIRE Academy graduate?</span>
                        <span data-lang="ro">Trebuie să fiu absolvent al INSPIRE Academy?</span>
                    </span>
                    <svg class="chevron w-5 h-5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-7 pb-5 text-sm text-gray-500 leading-relaxed border-t border-brand-100 pt-4">
                    <p data-lang="ru">Приоритет — у выпускников программ INSPIRE, но платформа открыта для всех, готовых вносить вклад в устойчивое развитие и социальные изменения.</p>
                    <p data-lang="en">Priority is given to INSPIRE program graduates, but the platform is open to anyone willing to contribute to sustainable development and social change.</p>
                    <p data-lang="ro">Prioritatea o au absolvenții programelor INSPIRE, dar platforma este deschisă tuturor celor dispuși să contribuie la dezvoltarea durabilă și schimbarea socială.</p>
                </div>
            </details>
        </div>
    </div>
</section>

{{-- CTA BAND --}}
<section class="py-24 px-4 grad-dark relative overflow-hidden">
    <div class="orb absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-[400px]" style="background:radial-gradient(#7c3aed,transparent);opacity:.2;"></div>
    <div class="max-w-3xl mx-auto text-center relative reveal">
        <h2 class="serif text-4xl sm:text-5xl font-semibold text-white mb-5 leading-tight">
            <span data-lang="ru">Готовы стать частью сообщества?</span>
            <span data-lang="en">Ready to join the community?</span>
            <span data-lang="ro">Ești gata să te alături comunității?</span>
        </h2>
        <p class="text-gray-400 mb-10 max-w-xl mx-auto" data-lang="ru">Начните свой путь прямо сейчас — зарегистрируйтесь через Telegram-бот за одну минуту.</p>
        <p class="text-gray-400 mb-10 max-w-xl mx-auto" data-lang="en">Start your journey right now — register via Telegram bot in one minute.</p>
        <p class="text-gray-400 mb-10 max-w-xl mx-auto" data-lang="ro">Începe-ți călătoria chiar acum — înregistrează-te prin bot-ul Telegram în un minut.</p>
        <a href="https://t.me/WomenComBot" target="_blank"
           class="inline-flex items-center gap-3 grad-bg text-white font-semibold text-lg px-10 py-4 rounded-full shadow-2xl shadow-brand-600/40 hover:shadow-brand-600/60 hover:-translate-y-0.5 transition-all">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            Подать заявку
        </a>
    </div>
</section>

{{-- FOOTER --}}
<footer class="bg-white border-t border-gray-100 py-14 px-4 sm:px-6">
    <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-10 mb-10">
        <div>
            <div class="flex items-center gap-2.5 mb-3">
                <span class="w-7 h-7 rounded-lg grad-bg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <span class="font-semibold text-ink text-sm">INSPIRE Community</span>
            </div>
            <p class="text-sm text-gray-400 leading-relaxed" data-lang="ru">Платформа для партнёрства и развития будущих лидеров Молдовы.</p>
            <p class="text-sm text-gray-400 leading-relaxed" data-lang="en">Platform for partnership and development of future leaders of Moldova.</p>
            <p class="text-sm text-gray-400 leading-relaxed" data-lang="ro">Platformă pentru parteneriat și dezvoltarea viitorilor lideri ai Moldovei.</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">INSPIRE Network</p>
            <p class="text-sm text-gray-400 leading-relaxed" data-lang="ru">Реализуется при поддержке Посольства Королевства Нидерландов в Молдове.</p>
            <p class="text-sm text-gray-400 leading-relaxed" data-lang="en">Implemented with the support of the Embassy of the Kingdom of the Netherlands in Moldova.</p>
            <p class="text-sm text-gray-400 leading-relaxed" data-lang="ro">Implementat cu sprijinul Ambasadei Regatului Țărilor de Jos în Moldova.</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">
                <span data-lang="ru">Контакты</span>
                <span data-lang="en">Contacts</span>
                <span data-lang="ro">Contacte</span>
            </p>
            <div class="flex flex-col gap-2.5">
                <a href="https://t.me/WomenComBot" target="_blank"
                   class="flex items-center gap-2 text-sm text-gray-500 hover:text-brand-600 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    @WomenComBot
                </a>
                <a href="https://peace.education.md/" target="_blank"
                   class="flex items-center gap-2 text-sm text-gray-500 hover:text-brand-600 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                    peace.education.md
                </a>
            </div>
        </div>
    </div>
    <div class="max-w-6xl mx-auto border-t border-gray-100 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-400">
        <span>&copy; {{ date('Y') }} INSPIRE Community &middot; Moldova</span>
        <span>
            <span data-lang="ru">Поддержано Посольством Нидерландов</span>
            <span data-lang="en">Supported by the Embassy of the Netherlands</span>
            <span data-lang="ro">Susținut de Ambasada Țărilor de Jos</span>
        </span>
    </div>
</footer>

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
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
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
    // Telegram In-App Browser fix:
    // When the page is opened inside Telegram, links to t.me must be opened
    // via Telegram.WebApp.openTelegramLink() so the browser closes properly
    // and the bot chat opens inside the Telegram app.
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
