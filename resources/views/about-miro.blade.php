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
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --miro-primary:#1c1c1e; --miro-pink:#ffd8f4; --miro-cream:#fff4c4; --miro-blue:#4262ff; --miro-coral:#ffc6c6; --miro-rose:#ffd8f4; --miro-teal:#c3faf5; --miro-orange:#ffe6cd; --miro-surface:#f7f8fa; --miro-surface-soft:#fafbfc; --miro-hairline:#e0e2e8; --miro-hairline-soft:#eef0f3; --miro-hairline-strong:#c7cad5; --miro-ink-deep:#050038; --miro-ink:#1c1c1e; --miro-charcoal:#2c2c34; --miro-slate:#555a6a; --miro-steel:#6b6f7e; --miro-muted:#a5a8b5; --miro-shadow:rgba(5,0,56,.08) 0 12px 32px -4px; --miro-font:"Roobert PRO","Noto Sans",-apple-system,BlinkMacSystemFont,sans-serif; }
        * { box-sizing:border-box; }
        html { overflow-x:hidden; }
        body { margin:0; background:#fff; color:var(--miro-ink); font-family:var(--miro-font); font-size:16px; line-height:1.5; }
        a { color:inherit; text-decoration:none; }
        button, a { -webkit-tap-highlight-color:transparent; }
        img { display:block; max-width:100%; }
        html:not([lang="ru"]) [data-lang="ru"], html:not([lang="en"]) [data-lang="en"], html:not([lang="ro"]) [data-lang="ro"] { display:none!important; }
        :focus-visible { outline:2px solid var(--miro-blue); outline-offset:3px; border-radius:6px; }
        .miro-container { width:min(1280px,calc(100% - 64px)); margin:0 auto; }
        .miro-nav { position:sticky; top:0; z-index:30; min-height:68px; border-bottom:1px solid var(--miro-hairline-soft); background:rgba(255,255,255,.94); backdrop-filter:blur(16px); }
        .miro-nav__inner { min-height:68px; display:flex; align-items:center; justify-content:space-between; gap:24px; }
        .miro-brand { display:inline-flex; align-items:center; gap:10px; white-space:nowrap; font-size:16px; font-weight:600; letter-spacing:-.02em; }
        .miro-brand__mark { width:28px; height:28px; display:grid; place-items:center; border-radius:6px; background:var(--miro-pink); color:var(--miro-primary); font-size:13px; font-weight:600; }
        .miro-brand__logo { width:176px; height:52px; object-fit:contain; }
        .miro-nav__links { display:flex; align-items:center; gap:28px; color:var(--miro-slate); font-size:14px; }
        .miro-nav__links a { transition:color .18s ease; }
        .miro-nav__links a:hover, .miro-nav__links a.is-active { color:var(--miro-primary); }
        .miro-nav__links a.is-active { font-weight:600; }
        .miro-nav__actions { display:flex; align-items:center; gap:10px; }
        .miro-languages { display:inline-flex; align-items:center; gap:2px; margin-right:8px; padding:3px; border:1px solid var(--miro-hairline); border-radius:9999px; background:var(--miro-surface); }
        .miro-languages button { border:0; border-radius:9999px; padding:5px 7px; background:transparent; color:var(--miro-steel); cursor:pointer; font:500 11px/1 var(--miro-font); }
        .miro-languages button.is-active { background:var(--miro-primary); color:#fff; }
        .miro-mobile-toggle { display:none; width:40px; height:40px; flex:0 0 40px; border:1px solid var(--miro-hairline); border-radius:9999px; background:#fff; color:var(--miro-primary); cursor:pointer; }
        .miro-nav__mobile-menu { display:none; }
        .miro-button { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:12px 24px; border-radius:9999px; font-size:14px; font-weight:500; line-height:1.3; transition:transform .18s ease,background .18s ease,border-color .18s ease; }
        .miro-button:hover { transform:translateY(-1px); }
        .miro-button--primary { background:var(--miro-primary); color:#fff; }
        .miro-button--primary:hover { background:var(--miro-charcoal); }
        .miro-button--secondary { border:1px solid var(--miro-hairline-strong); background:transparent; color:var(--miro-ink); }
        .miro-button--secondary:hover { border-color:var(--miro-primary); }
        .miro-button--yellow { background:var(--miro-pink); color:var(--miro-primary); }
        .miro-button--small { min-height:40px; padding:10px 18px; }
        .miro-about-page { background:linear-gradient(180deg,#fff 0%,#fafbfc 45%,#fff 100%); }
        .miro-about-hero { position:relative; overflow:hidden; padding:84px 0 76px; background:#fff; }
        .miro-about-hero::before { content:""; position:absolute; top:-120px; right:8%; width:290px; height:290px; border:1px solid rgba(66,98,255,.3); border-radius:50%; }
        .miro-about-hero__inner { position:relative; z-index:1; display:grid; grid-template-columns:minmax(0,1fr) minmax(360px,.8fr); align-items:center; gap:64px; }
        .miro-eyebrow { margin:0 0 18px; color:var(--miro-blue); font-size:12px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
        .miro-about-hero h1 { max-width:760px; margin:0; color:var(--miro-ink-deep); font-size:clamp(46px,7vw,80px); font-weight:500; line-height:1.03; letter-spacing:-.06em; }
        .miro-about-hero__copy { max-width:620px; margin:24px 0 0; color:var(--miro-slate); font-size:18px; }
        .miro-about-hero__visual { position:relative; min-height:370px; }
        .miro-about-hero__visual::before { content:""; position:absolute; inset:18px 0 0 24px; border-radius:48px 0 48px 48px; background:var(--miro-teal); transform:rotate(3deg); }
        .miro-about-hero__visual img { position:relative; width:100%; height:370px; object-fit:cover; border-radius:48px 0 48px 48px; box-shadow:var(--miro-shadow); }
        .miro-about-hero__sticker { position:absolute; z-index:2; right:-12px; bottom:20px; padding:12px 16px; border-radius:10px; background:var(--miro-primary); color:#fff; font-size:13px; font-weight:500; transform:rotate(3deg); }
        .miro-about-section { padding:92px 0; }
        .miro-about-section--soft { background:var(--miro-surface-soft); }
        .miro-about-section__head { max-width:760px; margin-bottom:40px; }
        .miro-about-section__head--center { margin-right:auto; margin-left:auto; text-align:center; }
        .miro-about-section h2 { margin:0; color:var(--miro-ink-deep); font-size:clamp(34px,5vw,58px); font-weight:500; line-height:1.08; letter-spacing:-.05em; }
        .miro-about-section__head p { margin:16px 0 0; color:var(--miro-slate); font-size:18px; }
        .miro-about-split { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); align-items:center; gap:72px; }
        .miro-about-split__image { overflow:hidden; border-radius:28px; background:var(--miro-teal); box-shadow:var(--miro-shadow); }
        .miro-about-split__image img { width:100%; aspect-ratio:4 / 3; object-fit:cover; }
        .miro-about-split__copy p { margin:18px 0 0; color:var(--miro-slate); font-size:17px; }
        .miro-about-list { display:grid; gap:14px; margin:26px 0 0; padding:0; list-style:none; }
        .miro-about-list li { display:flex; gap:12px; align-items:flex-start; color:var(--miro-charcoal); }
        .miro-about-list__mark { flex:0 0 22px; width:22px; height:22px; display:grid; place-items:center; border-radius:50%; background:var(--miro-coral); color:var(--miro-primary); font-size:12px; font-weight:600; }
        .miro-about-cards { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
        .miro-about-card { min-height:210px; padding:26px; border-radius:24px; background:var(--miro-pink); }
        .miro-about-card:nth-child(2) { background:var(--miro-teal); }
        .miro-about-card:nth-child(3) { background:var(--miro-rose); }
        .miro-about-card:nth-child(4) { background:var(--miro-orange); }
        .miro-about-card:nth-child(5) { background:var(--miro-coral); }
        .miro-about-card:nth-child(6) { background:#eef1ff; }
        .miro-about-card__number { display:block; margin-bottom:38px; color:var(--miro-primary); font-size:14px; font-weight:600; }
        .miro-about-card h3 { margin:0; color:var(--miro-ink-deep); font-size:22px; font-weight:500; line-height:1.2; }
        .miro-about-card p { margin:10px 0 0; color:var(--miro-charcoal); font-size:14px; line-height:1.5; }
        .miro-about-audience { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .miro-about-audience__item { padding:22px 24px; border:1px solid var(--miro-hairline-soft); border-radius:20px; background:#fff; box-shadow:0 8px 20px rgba(5,0,56,.04); }
        .miro-about-audience__item strong { display:block; color:var(--miro-ink-deep); font-size:18px; font-weight:500; }
        .miro-about-audience__item span { display:block; margin-top:7px; color:var(--miro-slate); font-size:14px; }
        .miro-about-steps { position:relative; display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
        .miro-about-steps::before { content:""; position:absolute; top:34px; right:12%; left:12%; border-top:2px dashed var(--miro-hairline-strong); }
        .miro-about-step { position:relative; z-index:1; text-align:center; }
        .miro-about-step__number { width:68px; height:68px; display:grid; place-items:center; margin:0 auto 20px; border:6px solid #fff; border-radius:50%; background:var(--miro-coral); box-shadow:0 0 0 1px var(--miro-hairline-strong); color:var(--miro-primary); font-size:19px; font-weight:600; }
        .miro-about-step:nth-child(2) .miro-about-step__number { background:var(--miro-rose); }
        .miro-about-step:nth-child(3) .miro-about-step__number { background:var(--miro-teal); }
        .miro-about-step:nth-child(4) .miro-about-step__number { background:var(--miro-orange); }
        .miro-about-step h3 { margin:0; color:var(--miro-ink-deep); font-size:19px; font-weight:500; }
        .miro-about-step p { max-width:220px; margin:9px auto 0; color:var(--miro-slate); font-size:14px; }
        .miro-about-bridge { padding:58px 32px; border-radius:30px; background:var(--miro-primary); color:#fff; }
        .miro-about-bridge__inner { display:grid; grid-template-columns:1fr 1fr; align-items:center; gap:48px; }
        .miro-about-bridge h2 { color:#fff; }
        .miro-about-bridge p { margin:18px 0 0; color:var(--miro-muted); font-size:17px; }
        .miro-about-proof { display:flex; align-items:center; gap:16px; padding:22px; border:1px solid rgba(255,255,255,.15); border-radius:22px; background:rgba(255,255,255,.06); }
        .miro-about-proof strong { color:var(--miro-pink); font-size:46px; font-weight:500; line-height:1; letter-spacing:-.05em; }
        .miro-about-proof span { color:#fff; font-size:14px; }
        .miro-about-cta { margin-top:72px; padding:64px 32px; border-radius:32px; background:var(--miro-teal); text-align:center; }
        .miro-about-cta h2 { max-width:700px; margin:0 auto; color:var(--miro-ink-deep); font-size:clamp(34px,5vw,58px); font-weight:500; line-height:1.08; letter-spacing:-.05em; }
        .miro-about-cta p { max-width:580px; margin:18px auto 0; color:var(--miro-slate); font-size:18px; }
        .miro-about-cta__actions { display:flex; justify-content:center; flex-wrap:wrap; gap:12px; margin-top:28px; }
        .miro-footer { padding:64px 0 28px; background:var(--miro-primary); color:#fff; }
        .miro-footer__top { display:grid; grid-template-columns:1.4fr repeat(4,1fr); gap:32px; padding-bottom:56px; }
        .miro-footer__brand p { max-width:250px; margin:18px 0 0; color:var(--miro-muted); font-size:14px; }
        .miro-footer .miro-brand__logo { width:214px; height:62px; padding:7px 10px; border-radius:12px; background:#fff; }
        .miro-footer h4 { margin:0 0 14px; font-size:16px; font-weight:500; }
        .miro-footer ul { display:grid; gap:8px; margin:0; padding:0; list-style:none; color:var(--miro-muted); font-size:14px; }
        .miro-footer li a:hover { color:#fff; }
        .miro-footer__bottom { display:flex; justify-content:space-between; gap:20px; padding-top:22px; border-top:1px solid rgba(255,255,255,.12); color:var(--miro-muted); font-size:12px; }
        @media (max-width:1023px) { .miro-container{width:min(100% - 40px,760px)} .miro-nav__links,.miro-nav__actions>.miro-languages,.miro-nav__actions>.miro-button{display:none} .miro-mobile-toggle{display:grid;place-items:center} .miro-nav.is-open .miro-nav__links{position:absolute;top:68px;left:0;right:0;display:grid;gap:0;padding:12px 20px 20px;border-bottom:1px solid var(--miro-hairline);background:#fff} .miro-nav.is-open .miro-nav__links a{padding:14px 0;border-bottom:1px solid var(--miro-hairline-soft)} .miro-nav.is-open .miro-nav__mobile-menu{display:grid;gap:10px;padding-top:14px} .miro-nav.is-open .miro-nav__mobile-menu .miro-languages{width:max-content;margin:0 0 4px} .miro-nav.is-open .miro-nav__mobile-menu .miro-button{width:100%} .miro-about-hero__inner,.miro-about-bridge__inner{grid-template-columns:1fr;gap:36px} .miro-about-hero__visual{max-width:620px} .miro-about-cards{grid-template-columns:repeat(2,minmax(0,1fr))} .miro-about-steps{grid-template-columns:repeat(2,1fr)} .miro-about-steps::before{display:none} .miro-footer__top{grid-template-columns:repeat(3,1fr)} .miro-footer__brand{grid-column:1/-1} }
        @media (max-width:767px) { .miro-container{width:min(100% - 32px,540px)} .miro-brand{font-size:14px} .miro-about-hero{padding:64px 0 56px} .miro-about-hero h1{font-size:48px} .miro-about-hero__copy,.miro-about-section__head p,.miro-about-cta p{font-size:16px} .miro-about-section{padding:64px 0} .miro-about-section h2,.miro-about-cta h2{font-size:38px} .miro-about-hero__visual,.miro-about-hero__visual img{height:300px;min-height:300px} .miro-about-split{grid-template-columns:1fr;gap:36px} .miro-about-cards,.miro-about-audience,.miro-about-steps{grid-template-columns:1fr} .miro-about-steps{gap:30px} .miro-about-steps::before{display:block;top:34px;right:auto;bottom:34px;left:50%;border-top:0;border-left:2px dashed var(--miro-hairline-strong)} .miro-about-bridge{padding:42px 24px;border-radius:24px} .miro-about-cta{padding:48px 22px;border-radius:24px} .miro-footer__top{grid-template-columns:repeat(2,1fr)} .miro-footer__bottom{flex-direction:column} }
        @media (max-width:479px) { .miro-footer__top{gap:28px 16px} }
    </style>
</head>
<body>
    @include('partials.miro-header', ['miroCurrentPage' => 'about'])

    <main class="miro-about-page" id="about">
        <section class="miro-about-hero">
            <div class="miro-container miro-about-hero__inner">
                <div>
                    <p class="miro-eyebrow"><span data-lang="ru">О платформе</span><span data-lang="en">About the platform</span><span data-lang="ro">Despre platformă</span></p>
                    <h1><span data-lang="ru">Место, где женщины развивают бизнес вместе</span><span data-lang="en">A place where women grow business together</span><span data-lang="ro">Un loc în care femeile dezvoltă afaceri împreună</span></h1>
                    <p class="miro-about-hero__copy"><span data-lang="ru">Women Entrepreneurs Platform — это цифровая экосистема для обучения, деловых связей, наставничества и новых возможностей женщин-предпринимательниц с обоих берегов.</span><span data-lang="en">Women Entrepreneurs Platform is a digital ecosystem for learning, business connections, mentorship and new opportunities for women entrepreneurs from both banks.</span><span data-lang="ro">Women Entrepreneurs Platform este un ecosistem digital pentru învățare, conexiuni de business, mentorat și oportunități noi pentru femeile antreprenoare de pe ambele maluri.</span></p>
                </div>
                <div class="miro-about-hero__visual">
                    <img src="{{ asset('images/hero-community.webp') }}" alt="Women entrepreneurs collaborating">
                    <span class="miro-about-hero__sticker"><span data-lang="ru">Знания · Связи · Рост</span><span data-lang="en">Knowledge · Connection · Growth</span><span data-lang="ro">Cunoștințe · Conexiuni · Creștere</span></span>
                </div>
            </div>
        </section>

        <section class="miro-about-section">
            <div class="miro-container miro-about-split">
                <div class="miro-about-split__image"><img src="{{ asset('images/story-mentor.webp') }}" alt="Mentorship and professional growth" loading="lazy"></div>
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
