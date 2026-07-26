@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $partnerGroups = [
        [
            'tone' => 'yellow',
            'title' => ['ru' => 'Координаторы платформы', 'en' => 'Platform coordinators', 'ro' => 'Coordonatorii platformei'],
            'description' => ['ru' => 'Организации, которые помогают развивать платформу и объединять женское предпринимательское сообщество.', 'en' => 'Organisations helping to develop the platform and bring the women’s business community together.', 'ro' => 'Organizații care contribuie la dezvoltarea platformei și la reunirea comunității femeilor antreprenoare.'],
            'partners' => [
                ['image' => 'coordinator-ida.png', 'url' => 'https://innovation.md/', 'name' => ['ru' => 'Агентство инноваций и развития', 'en' => 'Agency for Innovation and Development', 'ro' => 'Agenția pentru Inovare și Dezvoltare']],
                ['image' => 'coordinator-creative.png', 'url' => 'https://creativity.md/', 'name' => ['ru' => 'Ассоциация креативных индустрий Приднестровья', 'en' => 'Association of Creative Industries of Transnistria', 'ro' => 'Asociația Industriilor Creative din Transnistria']],
                ['image' => 'coordinator-platform.png', 'url' => 'https://social.innovation.md/', 'name' => ['ru' => 'Платформа социального предпринимательства', 'en' => 'Social Entrepreneurship Platform', 'ro' => 'Platforma antreprenoriatului social']],
            ],
        ],
        [
            'tone' => 'teal',
            'title' => ['ru' => 'Местные партнёры', 'en' => 'Local partners', 'ro' => 'Parteneri locali'],
            'description' => ['ru' => 'Профессиональные сообщества и центры, с которыми мы создаём новые возможности для участниц.', 'en' => 'Professional communities and hubs creating new opportunities for members.', 'ro' => 'Comunități profesionale și huburi care creează oportunități noi pentru membre.'],
            'partners' => [
                ['image' => 'local-eba.png', 'url' => 'https://eba.md/', 'name' => ['ru' => 'European Business Association Moldova', 'en' => 'European Business Association Moldova', 'ro' => 'European Business Association Moldova']],
                ['image' => 'local-afam.png', 'url' => 'https://afam.md/', 'name' => ['ru' => 'AFAM', 'en' => 'AFAM', 'ro' => 'AFAM']],
                ['image' => 'local-glia.png', 'url' => 'https://glia.md/', 'name' => ['ru' => 'Glia Impact Hub', 'en' => 'Glia Impact Hub', 'ro' => 'Glia Impact Hub']],
                ['image' => 'local-progen.png', 'url' => 'https://progen.md/', 'name' => ['ru' => 'Centrul Parteneriat pentru Dezvoltare', 'en' => 'Centrul Parteneriat pentru Dezvoltare', 'ro' => 'Centrul Parteneriat pentru Dezvoltare']],
            ],
        ],
        [
            'tone' => 'rose',
            'title' => ['ru' => 'Международные партнёры', 'en' => 'International partners', 'ro' => 'Parteneri internaționali'],
            'description' => ['ru' => 'Международные организации, поддерживающие развитие, устойчивость и расширение возможностей сообщества.', 'en' => 'International organisations supporting the community’s development, resilience and opportunities.', 'ro' => 'Organizații internaționale care susțin dezvoltarea, reziliența și oportunitățile comunității.'],
            'partners' => [
                ['image' => 'intl-netherlands.png', 'url' => 'https://www.netherlandsandyou.nl/web/moldova', 'name' => ['ru' => 'Королевство Нидерландов', 'en' => 'Kingdom of the Netherlands', 'ro' => 'Regatul Țărilor de Jos']],
                ['image' => 'intl-nrc.png', 'url' => 'https://www.nrc.no/moldova', 'name' => ['ru' => 'Норвежский совет по делам беженцев', 'en' => 'Norwegian Refugee Council', 'ro' => 'Consiliul Norvegian pentru Refugiați']],
                ['image' => 'intl-unwomen.png', 'url' => 'https://moldova.unwomen.org/', 'name' => ['ru' => 'ООН-женщины', 'en' => 'UN Women', 'ro' => 'ONU Femei']],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform — Partners</title>
    <meta name="description" content="Партнёры Women Entrepreneurs Platform — организации и сообщества, которые поддерживают развитие женского предпринимательства.">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --miro-primary:#1c1c1e; --miro-yellow:#ffd02f; --miro-yellow-light:#fff4c4; --miro-blue:#4262ff; --miro-coral:#ffc6c6; --miro-rose:#ffd8f4; --miro-teal:#c3faf5; --miro-orange:#ffe6cd; --miro-surface:#f7f8fa; --miro-surface-soft:#fafbfc; --miro-hairline:#e0e2e8; --miro-hairline-soft:#eef0f3; --miro-hairline-strong:#c7cad5; --miro-ink-deep:#050038; --miro-ink:#1c1c1e; --miro-charcoal:#2c2c34; --miro-slate:#555a6a; --miro-steel:#6b6f7e; --miro-muted:#a5a8b5; --miro-shadow:rgba(5,0,56,.08) 0 12px 32px -4px; --miro-font:"Roobert PRO","Noto Sans",-apple-system,BlinkMacSystemFont,sans-serif; }
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
        .miro-brand__mark { width:28px; height:28px; display:grid; place-items:center; border-radius:6px; background:var(--miro-yellow); color:var(--miro-primary); font-size:13px; font-weight:600; }
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
        .miro-button--small { min-height:40px; padding:10px 18px; }
        .miro-partners-page { background:linear-gradient(180deg,#fff 0%,#fafbfc 50%,#fff 100%); }
        .miro-partners-hero { position:relative; overflow:hidden; padding:96px 0 84px; background:var(--miro-yellow-light); }
        .miro-partners-hero::before, .miro-partners-hero::after { content:""; position:absolute; border:1px solid rgba(66,98,255,.24); border-radius:50%; }
        .miro-partners-hero::before { width:360px; height:360px; top:-190px; right:10%; }
        .miro-partners-hero::after { width:190px; height:190px; bottom:-120px; left:5%; border-color:rgba(28,28,30,.16); }
        .miro-partners-hero__inner { position:relative; z-index:1; display:grid; grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr); align-items:end; gap:56px; }
        .miro-eyebrow { margin:0 0 18px; color:var(--miro-blue); font-size:12px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
        .miro-partners-hero h1 { max-width:800px; margin:0; color:var(--miro-ink-deep); font-size:clamp(48px,7vw,84px); font-weight:500; line-height:1.02; letter-spacing:-.06em; }
        .miro-partners-hero__copy { max-width:630px; margin:24px 0 0; color:var(--miro-slate); font-size:18px; }
        .miro-partners-hero__note { padding:24px; border:1px solid rgba(5,0,56,.12); border-radius:24px; background:rgba(255,255,255,.62); box-shadow:var(--miro-shadow); }
        .miro-partners-hero__note strong { display:block; color:var(--miro-ink-deep); font-size:24px; font-weight:500; line-height:1.15; }
        .miro-partners-hero__note span { display:block; margin-top:10px; color:var(--miro-slate); font-size:14px; }
        .miro-partners-section { padding:88px 0 24px; }
        .miro-partners-group { padding:0 0 72px; }
        .miro-partners-group__head { display:flex; align-items:end; justify-content:space-between; gap:28px; margin-bottom:28px; }
        .miro-partners-group__heading { max-width:700px; }
        .miro-partners-group__heading h2 { margin:0; color:var(--miro-ink-deep); font-size:clamp(32px,4vw,52px); font-weight:500; line-height:1.08; letter-spacing:-.05em; }
        .miro-partners-group__heading p { max-width:680px; margin:14px 0 0; color:var(--miro-slate); font-size:16px; }
        .miro-partners-group__count { flex:0 0 auto; padding:10px 14px; border-radius:9999px; background:var(--miro-surface); color:var(--miro-steel); font-size:13px; }
        .miro-partners-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
        .miro-partner-card { min-height:250px; display:flex; flex-direction:column; justify-content:space-between; padding:22px; border-radius:24px; background:var(--miro-yellow); transition:transform .18s ease,box-shadow .18s ease; }
        .miro-partner-card:hover { transform:translateY(-3px); box-shadow:var(--miro-shadow); }
        .miro-partner-card--teal { background:var(--miro-teal); }
        .miro-partner-card--rose { background:var(--miro-rose); }
        .miro-partner-card__logo { min-height:145px; display:grid; place-items:center; padding:20px; border-radius:16px; background:rgba(255,255,255,.68); }
        .miro-partner-card__logo img { width:min(100%,220px); height:100px; object-fit:contain; }
        .miro-partner-card__bottom { display:flex; align-items:end; justify-content:space-between; gap:16px; margin-top:20px; }
        .miro-partner-card h3 { margin:0; color:var(--miro-ink-deep); font-size:17px; font-weight:500; line-height:1.3; }
        .miro-partner-link { flex:0 0 auto; display:inline-flex; align-items:center; gap:6px; padding:9px 12px; border:1px solid rgba(5,0,56,.18); border-radius:9999px; color:var(--miro-ink-deep); font-size:12px; font-weight:600; white-space:nowrap; transition:background .18s ease,color .18s ease,border-color .18s ease; }
        .miro-partner-link:hover { border-color:var(--miro-primary); background:var(--miro-primary); color:#fff; }
        .miro-partners-cta { margin:8px auto 88px; padding:56px 32px; border-radius:30px; background:var(--miro-primary); color:#fff; text-align:center; }
        .miro-partners-cta h2 { max-width:680px; margin:0 auto; color:#fff; font-size:clamp(34px,5vw,56px); font-weight:500; line-height:1.08; letter-spacing:-.05em; }
        .miro-partners-cta p { max-width:590px; margin:18px auto 0; color:var(--miro-muted); font-size:17px; }
        .miro-partners-cta__actions { display:flex; justify-content:center; flex-wrap:wrap; gap:12px; margin-top:28px; }
        .miro-footer { padding:64px 0 28px; background:var(--miro-primary); color:#fff; }
        .miro-footer__top { display:grid; grid-template-columns:1.4fr repeat(4,1fr); gap:32px; padding-bottom:56px; }
        .miro-footer__brand p { max-width:250px; margin:18px 0 0; color:var(--miro-muted); font-size:14px; }
        .miro-footer .miro-brand__logo { width:214px; height:62px; padding:7px 10px; border-radius:12px; background:#fff; }
        .miro-footer h4 { margin:0 0 14px; font-size:16px; font-weight:500; }
        .miro-footer ul { display:grid; gap:8px; margin:0; padding:0; list-style:none; color:var(--miro-muted); font-size:14px; }
        .miro-footer li a:hover { color:#fff; }
        .miro-footer__bottom { display:flex; justify-content:space-between; gap:20px; padding-top:22px; border-top:1px solid rgba(255,255,255,.12); color:var(--miro-muted); font-size:12px; }
        @media (max-width:1023px) { .miro-container{width:min(100% - 40px,760px)} .miro-nav__links,.miro-nav__actions>.miro-languages,.miro-nav__actions>.miro-button{display:none} .miro-mobile-toggle{display:grid;place-items:center} .miro-nav.is-open .miro-nav__links{position:absolute;top:68px;left:0;right:0;display:grid;gap:0;padding:12px 20px 20px;border-bottom:1px solid var(--miro-hairline);background:#fff} .miro-nav.is-open .miro-nav__links a{padding:14px 0;border-bottom:1px solid var(--miro-hairline-soft)} .miro-nav.is-open .miro-nav__mobile-menu{display:grid;gap:10px;padding-top:14px} .miro-nav.is-open .miro-nav__mobile-menu .miro-languages{width:max-content;margin:0 0 4px} .miro-nav.is-open .miro-nav__mobile-menu .miro-button{width:100%} .miro-partners-hero__inner{grid-template-columns:1fr;gap:34px} .miro-partners-hero__note{max-width:560px} .miro-partners-grid{grid-template-columns:repeat(2,minmax(0,1fr))} .miro-footer__top{grid-template-columns:repeat(3,1fr)} .miro-footer__brand{grid-column:1/-1} }
        @media (max-width:767px) { .miro-container{width:min(100% - 32px,540px)} .miro-brand{font-size:14px} .miro-partners-hero{padding:66px 0 58px} .miro-partners-hero h1{font-size:48px} .miro-partners-hero__copy{font-size:16px} .miro-partners-section{padding-top:64px} .miro-partners-group{padding-bottom:56px} .miro-partners-group__head{display:block} .miro-partners-group__count{display:inline-block; margin-top:18px} .miro-partners-grid{grid-template-columns:1fr} .miro-partners-cta{padding:46px 22px; margin-bottom:64px; border-radius:24px} .miro-footer__top{grid-template-columns:repeat(2,1fr)} .miro-footer__bottom{flex-direction:column} }
        @media (max-width:479px) { .miro-footer__top{gap:28px 16px} }
    </style>
</head>
<body>
    @include('partials.miro-header', ['miroCurrentPage' => 'partners'])

    <main class="miro-partners-page">

        <section class="miro-partners-section">
            <div class="miro-container">
                @foreach ($partnerGroups as $group)
                    <section class="miro-partners-group">
                        <div class="miro-partners-group__head">
                            <div class="miro-partners-group__heading">
                                <h2><span data-lang="ru">{{ $group['title']['ru'] }}</span><span data-lang="en">{{ $group['title']['en'] }}</span><span data-lang="ro">{{ $group['title']['ro'] }}</span></h2>
                                <p><span data-lang="ru">{{ $group['description']['ru'] }}</span><span data-lang="en">{{ $group['description']['en'] }}</span><span data-lang="ro">{{ $group['description']['ro'] }}</span></p>
                            </div>
                            <span class="miro-partners-group__count">{{ count($group['partners']) }} <span data-lang="ru">партнёра</span><span data-lang="en">partners</span><span data-lang="ro">parteneri</span></span>
                        </div>
                        <div class="miro-partners-grid">
                            @foreach ($group['partners'] as $partner)
                                <article class="miro-partner-card miro-partner-card--{{ $group['tone'] }}">
                                    <div class="miro-partner-card__logo"><img src="{{ asset('images/partners/' . $partner['image']) }}" alt="{{ $partner['name']['en'] }}" loading="lazy"></div>
                                    <div class="miro-partner-card__bottom">
                                        <h3><span data-lang="ru">{{ $partner['name']['ru'] }}</span><span data-lang="en">{{ $partner['name']['en'] }}</span><span data-lang="ro">{{ $partner['name']['ro'] }}</span></h3>
                                        <a href="{{ $partner['url'] }}" target="_blank" rel="noopener noreferrer" class="miro-partner-link"><span data-lang="ru">Сайт</span><span data-lang="en">Website</span><span data-lang="ro">Site</span><span aria-hidden="true">↗</span></a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="miro-partners-cta">
                    <h2><span data-lang="ru">Создавать возможности вместе</span><span data-lang="en">Create opportunities together</span><span data-lang="ro">Creăm oportunități împreună</span></h2>
                    <p><span data-lang="ru">Если вы хотите поддержать женское предпринимательство или предложить участницам новую возможность, давайте познакомимся.</span><span data-lang="en">If you want to support women’s entrepreneurship or offer members a new opportunity, let’s connect.</span><span data-lang="ro">Dacă vrei să susții antreprenoriatul feminin sau să oferi membrelor o oportunitate nouă, hai să ne cunoaștem.</span></p>
                    <div class="miro-partners-cta__actions"><a href="{{ $managerUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary" style="background:var(--miro-yellow);color:var(--miro-primary)"><span data-lang="ru">Стать партнёром</span><span data-lang="en">Become a partner</span><span data-lang="ro">Devino partener</span></a><a href="{{ route('about') }}" class="miro-button miro-button--secondary" style="border-color:rgba(255,255,255,.35);color:#fff"><span data-lang="ru">О платформе</span><span data-lang="en">About the platform</span><span data-lang="ro">Despre platformă</span></a></div>
                </section>
            </div>
        </section>
    </main>

    @include('partials.miro-footer')

    <script>
        (() => {
            const root = document.documentElement;
            const nav = document.getElementById('miro-nav');
            const toggle = document.getElementById('miro-mobile-toggle');
            const localeButtons = document.querySelectorAll('[data-locale]');
            const getLocale = () => localStorage.getItem('miro-locale') || 'ru';
            const setLocale = (locale) => {
                root.lang = locale;
                localStorage.setItem('miro-locale', locale);
                localeButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.locale === locale));
            };
            setLocale(getLocale());
            localeButtons.forEach((button) => button.addEventListener('click', () => setLocale(button.dataset.locale)));
            toggle?.addEventListener('click', () => {
                const open = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        })();
    </script>
</body>
</html>
