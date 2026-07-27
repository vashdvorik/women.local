@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
@endphp
<!doctype html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Свяжитесь с командой Women Entrepreneurs Platform через Telegram и присоединяйтесь к сообществу.">
    <title>Контакты — Women Entrepreneurs Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --miro-black: #1c1c1e;
            --miro-primary: #1c1c1e;
            --miro-on-primary: #fff;
            --miro-pink: #ffd8f4;
            --miro-coral: #ffc6c6;
            --miro-coral-strong: #ff9999;
            --miro-rose-light: #fde0f0;
            --miro-blue: #4262ff;
            --miro-white: #fff;
            --miro-muted: #6f6f76;
            --miro-line: #e8e8eb;
            --miro-hairline: #e0e2e8;
            --miro-hairline-soft: #eef0f3;
            --miro-hairline-strong: #c7cad5;
            --miro-surface: #f7f8fa;
            --miro-charcoal: #2c2c34;
            --miro-ink: #1c1c1e;
            --miro-slate: #555a6a;
            --miro-steel: #6b6f7e;
            --miro-font: 'Noto Sans', sans-serif;
            --miro-shadow: 0 18px 48px rgba(28, 28, 30, .08);
        }

        * { box-sizing: border-box; }
        body { margin: 0; color: var(--miro-black); background: #fff; font-family: 'Noto Sans', sans-serif; }
        a { color: inherit; }
        html:not([lang="ru"]) [data-lang="ru"],
        html:not([lang="en"]) [data-lang="en"],
        html:not([lang="ro"]) [data-lang="ro"] { display: none !important; }
        .miro-container { width: min(100% - 48px, 1180px); margin: 0 auto; }

        /* Shared Miro navigation and footer */
        .miro-nav { position: sticky; top: 0; z-index: 30; min-height: 68px; border-bottom: 1px solid var(--miro-hairline-soft); background: rgba(255,255,255,.94); backdrop-filter: blur(16px); }
        .miro-nav__inner { min-height: 68px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .miro-brand { display: inline-flex; align-items: center; gap: 10px; white-space: nowrap; font-size: 16px; font-weight: 600; letter-spacing: -.02em; text-decoration: none; }
        .miro-brand__logo { width: 176px; height: 52px; object-fit: contain; }
        .miro-nav__links { display: flex; align-items: center; gap: 28px; color: var(--miro-slate); font-size: 14px; }
        .miro-nav__links a { transition: color .18s ease; }
        .miro-nav__links a:hover, .miro-nav__links a.is-active { color: var(--miro-primary); }
        .miro-nav__links a.is-active { font-weight: 600; }
        .miro-nav__actions { display: flex; align-items: center; gap: 10px; }
        .miro-languages { display: inline-flex; align-items: center; gap: 2px; margin-right: 8px; padding: 3px; border: 1px solid var(--miro-hairline); border-radius: 9999px; background: var(--miro-surface); }
        .miro-languages button { border: 0; border-radius: 9999px; padding: 5px 7px; background: transparent; color: var(--miro-steel); cursor: pointer; font: 500 11px/1 var(--miro-font); }
        .miro-languages button.is-active { background: var(--miro-primary); color: #fff; }
        .miro-mobile-toggle { display: none; width: 40px; height: 40px; flex: 0 0 40px; border: 1px solid var(--miro-hairline); border-radius: 9999px; background: #fff; color: var(--miro-primary); cursor: pointer; }
        .miro-nav__mobile-menu { display: none; }
        .miro-button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 12px 24px; border-radius: 9999px; font-size: 14px; font-weight: 500; line-height: 1.3; text-decoration: none; transition: transform .18s ease, background .18s ease, border-color .18s ease; }
        .miro-button:hover { transform: translateY(-1px); }
        .miro-button--primary { background: var(--miro-primary); color: #fff; }
        .miro-button--primary:hover { background: var(--miro-charcoal); }
        .miro-button--secondary { border: 1px solid var(--miro-hairline-strong); background: transparent; color: var(--miro-ink); }
        .miro-button--secondary:hover { border-color: var(--miro-primary); }
        .miro-button--small { min-height: 40px; padding: 10px 18px; }
        .miro-footer { padding: 64px 0 28px; background: var(--miro-primary); color: #fff; }
        .miro-footer__top { display: grid; grid-template-columns: 1.4fr repeat(4, 1fr); gap: 32px; padding-bottom: 56px; }
        .miro-footer__brand p { max-width: 250px; margin: 18px 0 0; color: var(--miro-muted); font-size: 14px; }
        .miro-footer .miro-brand__logo { width: 214px; height: 62px; padding: 7px 10px; border-radius: 12px; background: #fff; }
        .miro-footer h4 { margin: 0 0 14px; font-size: 16px; font-weight: 500; }
        .miro-footer ul { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; color: var(--miro-muted); font-size: 14px; }
        .miro-footer li a:hover { color: #fff; }
        .miro-footer__bottom { display: flex; justify-content: space-between; gap: 20px; padding-top: 22px; border-top: 1px solid rgba(255,255,255,.12); color: var(--miro-muted); font-size: 12px; }

        .miro-contact-page { overflow: hidden; }
        .miro-contact-hero { position: relative; padding: 92px 0 82px; background: var(--miro-pink); }
        .miro-contact-hero::after { content: ''; position: absolute; width: 260px; height: 260px; right: 7%; bottom: -100px; border-radius: 50%; background: var(--miro-coral); opacity: .65; }
        .miro-contact-hero__inner { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr); gap: 72px; align-items: end; }
        .miro-contact-eyebrow { margin: 0 0 18px; color: var(--miro-blue); font-size: 12px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
        .miro-contact-hero h1 { max-width: 720px; margin: 0; font-size: clamp(46px, 7vw, 86px); line-height: .98; letter-spacing: -.065em; }
        .miro-contact-hero__copy { max-width: 480px; margin: 26px 0 0; color: var(--miro-slate); font-size: 18px; line-height: 1.65; }
        .miro-contact-hero__note { padding: 26px; border: 1px solid rgba(28, 28, 30, .12); border-radius: 28px; background: rgba(255, 255, 255, .7); box-shadow: var(--miro-shadow); }
        .miro-contact-hero__note strong { display: block; margin-bottom: 10px; font-size: 21px; line-height: 1.2; }
        .miro-contact-hero__note p { margin: 0; color: var(--miro-muted); line-height: 1.55; }
        .miro-contact-section { padding: 92px 0 110px; }
        .miro-contact-layout { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(280px, .9fr); gap: 72px; align-items: start; }
        .miro-contact-section h2 { max-width: 560px; margin: 0 0 16px; font-size: clamp(34px, 4vw, 52px); line-height: 1.04; letter-spacing: -.045em; }
        .miro-contact-lead { max-width: 570px; margin: 0 0 38px; color: var(--miro-muted); font-size: 17px; line-height: 1.65; }
        .miro-contact-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .miro-contact-card { min-height: 214px; padding: 24px; border: 1px solid var(--miro-line); border-radius: 24px; background: var(--miro-white); box-shadow: 0 10px 30px rgba(28, 28, 30, .04); }
        .miro-contact-card:nth-child(1) { background: var(--miro-rose-light); }
        .miro-contact-card:nth-child(2) { background: var(--miro-coral); }
        .miro-contact-card:nth-child(3) { background: rgba(66, 98, 255, .08); }
        .miro-contact-card__icon { display: grid; width: 42px; height: 42px; margin-bottom: 28px; place-items: center; border-radius: 13px; background: var(--miro-black); color: #fff; font-size: 18px; font-weight: 800; }
        .miro-contact-card h3 { margin: 0 0 8px; font-size: 19px; letter-spacing: -.02em; }
        .miro-contact-card p { margin: 0 0 18px; color: var(--miro-slate); font-size: 14px; line-height: 1.55; }
        .miro-contact-card__links { display: grid; gap: 8px; }
        .miro-contact-card a { display: inline-flex; color: var(--miro-black); font-size: 14px; font-weight: 800; text-decoration: none; }
        .miro-contact-card a:hover { color: var(--miro-blue); }
        .miro-contact-topics { padding: 30px; border-radius: 28px; background: var(--miro-black); color: #fff; box-shadow: var(--miro-shadow); }
        .miro-contact-topics h3 { margin: 0 0 24px; font-size: 24px; letter-spacing: -.03em; }
        .miro-contact-topics ul { display: grid; gap: 14px; margin: 0; padding: 0; list-style: none; }
        .miro-contact-topics li { display: flex; gap: 12px; align-items: baseline; padding-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,.16); color: #e7e7ea; line-height: 1.45; }
        .miro-contact-topics li::before { content: '↗'; color: var(--miro-pink); font-weight: 800; }
        .miro-contact-cta { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-top: 28px; }
        .miro-contact-button { display: inline-flex; min-height: 48px; align-items: center; justify-content: center; padding: 0 20px; border-radius: 999px; font-size: 14px; font-weight: 800; text-decoration: none; }
        .miro-contact-button--primary { background: var(--miro-pink); color: var(--miro-black); }
        .miro-contact-button--secondary { border: 1px solid rgba(255,255,255,.35); color: #fff; }

        @media (max-width: 1023px) {
            .miro-container { width: min(100% - 40px, 760px); }
            .miro-nav__links, .miro-nav__actions > .miro-languages, .miro-nav__actions > .miro-button { display: none; }
            .miro-mobile-toggle { display: grid; place-items: center; }
            .miro-nav.is-open .miro-nav__links { position: absolute; top: 68px; left: 0; right: 0; display: grid; gap: 0; padding: 12px 20px 20px; border-bottom: 1px solid var(--miro-hairline); background: #fff; }
            .miro-nav.is-open .miro-nav__links a { padding: 14px 0; border-bottom: 1px solid var(--miro-hairline-soft); }
            .miro-nav.is-open .miro-nav__mobile-menu { display: grid; gap: 10px; padding-top: 14px; }
            .miro-nav.is-open .miro-nav__mobile-menu .miro-languages { width: max-content; margin: 0 0 4px; }
            .miro-nav.is-open .miro-nav__mobile-menu .miro-button { width: 100%; }
            .miro-footer__top { grid-template-columns: repeat(3, 1fr); }
            .miro-footer__brand { grid-column: 1 / -1; }
            .miro-contact-hero__inner, .miro-contact-layout { grid-template-columns: 1fr; gap: 38px; }
            .miro-contact-hero__note { max-width: 520px; }
        }
        @media (max-width: 767px) {
            .miro-container { width: min(100% - 32px, 540px); }
            .miro-contact-hero { padding: 64px 0 62px; }
            .miro-contact-hero h1 { font-size: 54px; }
            .miro-contact-hero__copy { font-size: 16px; }
            .miro-contact-section { padding: 64px 0 78px; }
            .miro-contact-cards { grid-template-columns: 1fr; }
            .miro-contact-card { min-height: auto; }
            .miro-contact-topics { padding: 24px; }
            .miro-footer__top { grid-template-columns: repeat(2, 1fr); gap: 28px 16px; }
            .miro-footer__bottom { flex-direction: column; }
        }
        @media (max-width: 479px) {
            .miro-contact-hero h1 { font-size: 46px; }
        }
    </style>
    @include('partials.miro-media-styles')
</head>
<body>
    @include('partials.miro-header', ['miroCurrentPage' => 'contact'])

    <main class="miro-contact-page">


        <section class="miro-contact-section">
            <div class="miro-container miro-contact-layout">
                <div>
                    <h2><span data-lang="ru">Напишите туда, где вам удобно</span><span data-lang="en">Reach us where it feels easiest</span><span data-lang="ro">Scrie-ne acolo unde îți este mai ușor</span></h2>
                    <p class="miro-contact-lead"><span data-lang="ru">Мы собрали основные точки входа: бот для быстрых действий, команда проекта для сотрудничества и сообщество для новостей и общения.</span><span data-lang="en">We’ve gathered the main ways to connect: a bot for quick actions, the project team for collaboration, and the community for news and conversation.</span><span data-lang="ro">Am adunat principalele modalități de contact: un bot pentru acțiuni rapide, echipa proiectului pentru colaborare și comunitatea pentru noutăți și conversații.</span></p>
                    <div class="miro-contact-cards">
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">@</div>
                            <h3><span data-lang="ru">Telegram-бот</span><span data-lang="en">Telegram bot</span><span data-lang="ro">Bot Telegram</span></h3>
                            <p><span data-lang="ru">Регистрация, вход и уведомления платформы.</span><span data-lang="en">Registration, access and platform notifications.</span><span data-lang="ro">Înregistrare, acces și notificări ale platformei.</span></p>
                            <a href="{{ $botUrl }}" target="_blank" rel="noopener">@WomenComBot&nbsp;→</a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">✦</div>
                            <h3><span data-lang="ru">Команда проекта</span><span data-lang="en">Project team</span><span data-lang="ro">Echipa proiectului</span></h3>
                            <p><span data-lang="ru">Вопросы о платформе, партнёрстве и сотрудничестве.</span><span data-lang="en">Questions about the platform, partnerships and collaboration.</span><span data-lang="ro">Întrebări despre platformă, parteneriate și colaborare.</span></p>
                            <a href="{{ $managerUrl }}" target="_blank" rel="noopener">@lesnichenkoP&nbsp;→</a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">↗</div>
                            <h3><span data-lang="ru">Сообщество</span><span data-lang="en">Community</span><span data-lang="ro">Comunitate</span></h3>
                            <p><span data-lang="ru">Новости, приглашения и новые знакомства.</span><span data-lang="en">News, invitations and new connections.</span><span data-lang="ro">Noutăți, invitații și conexiuni noi.</span></p>
                            <a href="{{ $communityUrl }}" target="_blank" rel="noopener"><span data-lang="ru">Открыть сообщество&nbsp;→</span><span data-lang="en">Open the community&nbsp;→</span><span data-lang="ro">Deschide comunitatea&nbsp;→</span></a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">⌂</div>
                            <h3><span data-lang="ru">Офис и телефон</span><span data-lang="en">Office and phone</span><span data-lang="ro">Oficiu și telefon</span></h3>
                            <p><span data-lang="ru">г. Тирасполь, ул. Свердлова, 57<br>Приднестровье, MD-3300</span><span data-lang="en">57 Sverdlova Street, Tiraspol<br>Transnistria, MD-3300</span><span data-lang="ro">str. Sverdlov 57, Tiraspol<br>Transnistria, MD-3300</span></p>
                            <a href="tel:+37377798317">+373 777 983 17&nbsp;→</a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">@</div>
                            <h3><span data-lang="ru">Электронная почта</span><span data-lang="en">Email</span><span data-lang="ro">Email</span></h3>
                            <p><span data-lang="ru">Напишите нам по общим и проектным вопросам.</span><span data-lang="en">Write to us about general and project questions.</span><span data-lang="ro">Scrie-ne pentru întrebări generale și despre proiect.</span></p>
                            <div class="miro-contact-card__links">
                                <a href="mailto:women.tiras.hub@gmail.com">women.tiras.hub@gmail.com</a>
                                <a href="mailto:elena.sinika@innovation.md">elena.sinika@innovation.md</a>
                            </div>
                        </article>
                    </div>
                </div>

                <aside class="miro-contact-topics">
                    <h3><span data-lang="ru">Что можно обсудить?</span><span data-lang="en">What can we discuss?</span><span data-lang="ro">Ce putem discuta?</span></h3>
                    <ul>
                        <li><span data-lang="ru">Участие и профиль на платформе</span><span data-lang="en">Participation and your platform profile</span><span data-lang="ro">Participarea și profilul pe platformă</span></li>
                        <li><span data-lang="ru">Партнёрство и поддержка проекта</span><span data-lang="en">Partnerships and project support</span><span data-lang="ro">Parteneriate și susținerea proiectului</span></li>
                        <li><span data-lang="ru">Событие, новость или возможность</span><span data-lang="en">An event, news item or opportunity</span><span data-lang="ro">Un eveniment, o știre sau o oportunitate</span></li>
                        <li><span data-lang="ru">Обучение, наставничество и связи</span><span data-lang="en">Learning, mentoring and connections</span><span data-lang="ro">Învățare, mentorat și conexiuni</span></li>
                    </ul>
                    <div class="miro-contact-cta">
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-contact-button miro-contact-button--primary"><span data-lang="ru">Написать в Telegram&nbsp;→</span><span data-lang="en">Message us on Telegram&nbsp;→</span><span data-lang="ro">Scrie-ne pe Telegram&nbsp;→</span></a>
                        <a href="{{ route('about') }}" class="miro-contact-button miro-contact-button--secondary"><span data-lang="ru">О платформе</span><span data-lang="en">About the platform</span><span data-lang="ro">Despre platformă</span></a>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    @include('partials.miro-footer')

    <script>
        (function () {
            var supported = ['ru', 'en', 'ro'];
            var saved = localStorage.getItem('miro_lang');
            var browser = (navigator.language || '').slice(0, 2);
            var locale = supported.indexOf(saved) >= 0 ? saved : supported.indexOf(browser) >= 0 ? browser : 'ru';
            var root = document.documentElement;
            var nav = document.getElementById('miro-nav');
            var toggle = document.getElementById('miro-mobile-toggle');
            function setLocale(next) {
                if (supported.indexOf(next) < 0) next = 'ru';
                locale = next;
                root.lang = locale;
                localStorage.setItem('miro_lang', locale);
                document.querySelectorAll('[data-locale]').forEach(function (button) { button.classList.toggle('is-active', button.getAttribute('data-locale') === locale); });
            }
            document.querySelectorAll('[data-locale]').forEach(function (button) { button.addEventListener('click', function () { setLocale(button.getAttribute('data-locale')); }); });
            if (toggle) { toggle.addEventListener('click', function () { var isOpen = nav.classList.toggle('is-open'); toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false'); }); }
            document.querySelectorAll('.miro-nav__links a').forEach(function (link) { link.addEventListener('click', function () { nav.classList.remove('is-open'); if (toggle) toggle.setAttribute('aria-expanded', 'false'); }); });
            setLocale(locale);
        })();
    </script>
</body>
</html>
