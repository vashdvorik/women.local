@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $locales = ['ru', 'en', 'ro'];

    // Временные карточки событий и возможностей. Позже будут заменены данными из backend.
    $events = [
        [
            'photo' => 'event-workshop.webp',
            'type' => ['ru' => 'Обучение', 'en' => 'Learning', 'ro' => 'Învățare'],
            'date' => ['ru' => '18 июня', 'en' => 'June 18', 'ro' => '18 iunie'],
            'title' => ['ru' => 'Бизнес-лаборатория', 'en' => 'Business lab', 'ro' => 'Laborator de business'],
            'description' => ['ru' => 'Разберите бизнес-задачу на понятные шаги вместе с экспертами и другими предпринимательницами.', 'en' => 'Break a business challenge into clear steps with experts and fellow entrepreneurs.', 'ro' => 'Transformă o provocare de business în pași clari alături de experți și antreprenoare.'],
            'tone' => 'pink',
        ],
        [
            'photo' => 'event-networking.webp',
            'type' => ['ru' => 'Нетворкинг', 'en' => 'Networking', 'ro' => 'Networking'],
            'date' => ['ru' => '25 июня', 'en' => 'June 25', 'ro' => '25 iunie'],
            'title' => ['ru' => 'Встречайтесь и знакомьтесь', 'en' => 'Meet & connect', 'ro' => 'Cunoaște și conectează-te'],
            'description' => ['ru' => 'Живой разговор с предпринимательницами, экспертами и теми, с кем хочется продолжить сотрудничество.', 'en' => 'Meet entrepreneurs and experts you want to keep talking and collaborating with.', 'ro' => 'Cunoaște antreprenoare și experți cu care vrei să continui colaborarea.'],
            'tone' => 'blue',
        ],
        [
            'photo' => 'event-esg.webp',
            'type' => ['ru' => 'Возможности', 'en' => 'Opportunities', 'ro' => 'Oportunități'],
            'date' => ['ru' => '09 июля', 'en' => 'July 9', 'ro' => '9 iulie'],
            'title' => ['ru' => 'ESG для малого бизнеса', 'en' => 'ESG for small business', 'ro' => 'ESG pentru afaceri mici'],
            'description' => ['ru' => 'Практические идеи об устойчивом росте, ответственном бизнесе и новых партнёрствах.', 'en' => 'Practical ideas on sustainable growth, responsible business and new partnerships.', 'ro' => 'Idei practice despre creștere sustenabilă, business responsabil și parteneriate noi.'],
            'tone' => 'teal',
        ],
        [
            'photo' => 'story-mentor.webp',
            'type' => ['ru' => 'Наставничество', 'en' => 'Mentorship', 'ro' => 'Mentorat'],
            'date' => ['ru' => 'Открыт набор', 'en' => 'Applications open', 'ro' => 'Înscrieri deschise'],
            'title' => ['ru' => 'Найди свою экспертку', 'en' => 'Find your expert', 'ro' => 'Găsește-ți experta'],
            'description' => ['ru' => 'Возможность найти наставницу для задачи, которая сейчас важна именно твоему бизнесу.', 'en' => 'Find a mentor for the challenge that matters most to your business right now.', 'ro' => 'Găsește o mentoră pentru provocarea care contează acum pentru afacerea ta.'],
            'tone' => 'rose',
        ],
        [
            'photo' => 'story-export.webp',
            'type' => ['ru' => 'Партнёрства', 'en' => 'Partnerships', 'ro' => 'Parteneriate'],
            'date' => ['ru' => 'Новая возможность', 'en' => 'New opportunity', 'ro' => 'Oportunitate nouă'],
            'title' => ['ru' => 'Выход на новые рынки', 'en' => 'Enter new markets', 'ro' => 'Intră pe piețe noi'],
            'description' => ['ru' => 'Объявления и контакты для компаний, которые готовы развивать экспорт и международные связи.', 'en' => 'Announcements and contacts for companies ready to grow exports and international connections.', 'ro' => 'Anunțuri și contacte pentru companii gata să-și dezvolte exporturile și conexiunile internaționale.'],
            'tone' => 'orange',
        ],
        [
            'photo' => 'hero-community.webp',
            'type' => ['ru' => 'Сообщество', 'en' => 'Community', 'ro' => 'Comunitate'],
            'date' => ['ru' => 'Скоро', 'en' => 'Coming soon', 'ro' => 'În curând'],
            'title' => ['ru' => 'Региональный форум предпринимательниц', 'en' => 'Regional women founders forum', 'ro' => 'Forumul regional al femeilor antreprenoare'],
            'description' => ['ru' => 'Большая встреча о бизнесе, лидерстве и людях, которые создают возможности вместе.', 'en' => 'A gathering about business, leadership and people creating opportunities together.', 'ro' => 'O întâlnire despre business, leadership și oameni care creează oportunități împreună.'],
            'tone' => 'coral',
        ],
    ];

    $events = [
        [
            'photo' => 'news/news-white-noise.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d0%b1%d0%b5%d0%bb%d1%8b%d0%b9-%d1%88%d1%83%d0%bc-%d0%b2%d1%81%d1%82%d1%80%d0%b5%d1%87%d0%b0-%d0%ba%d1%80%d0%b5%d0%b0%d1%82%d0%b8%d0%b2%d0%b0-%d0%b8-%d0%bf%d1%80%d0%b5%d0%b4/',
            'type' => ['ru' => 'Новости', 'en' => 'News', 'ro' => 'Noutăți'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => '«Белый Шум» — встреча креатива и предпринимательства', 'en' => 'White Noise — where creativity meets entrepreneurship', 'ro' => '„White Noise” — întâlnirea dintre creativitate și antreprenoriat'],
            'description' => ['ru' => 'Арт-выставка, где встретились искусство, мода и предпринимательство.', 'en' => 'An art exhibition where art, fashion and entrepreneurship came together.', 'ro' => 'O expoziție de artă în care s-au întâlnit arta, moda și antreprenoriatul.'],
            'tone' => 'pink',
        ],
        [
            'photo' => 'news/news-conference.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d0%bc%d0%b5%d0%b6%d0%b4%d1%83%d0%bd%d0%b0%d1%80%d0%be%d0%b4%d0%bd%d0%b0%d1%8f-%d0%ba%d0%be%d0%bd%d1%84%d0%b5%d1%80%d0%b5%d0%bd%d1%86%d0%b8%d1%8f-%d0%b4%d0%bb%d1%8f-%d0%b6%d0%b5%d0%bd%d1%89%d0%b8/',
            'type' => ['ru' => 'Конференция', 'en' => 'Conference', 'ro' => 'Conferință'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => 'Международная конференция для женщин-предпринимателей', 'en' => 'International conference for women entrepreneurs', 'ro' => 'Conferință internațională pentru femei antreprenoare'],
            'description' => ['ru' => 'Конференция о лидерстве, инновациях и развитии женского предпринимательства.', 'en' => 'A conference about leadership, innovation and women’s entrepreneurship.', 'ro' => 'O conferință despre leadership, inovație și antreprenoriat feminin.'],
            'tone' => 'teal',
        ],
        [
            'photo' => 'news/news-networking.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d0%b2-glia-impact-hub-%d1%81%d0%be%d1%81%d1%82%d0%be%d1%8f%d0%bb%d0%be%d1%81%d1%8c-%d0%bd%d0%b5%d1%82%d0%b2%d0%be%d1%80%d0%ba%d0%b8%d0%bd%d0%b3-%d0%bc%d0%b5%d1%80%d0%be%d0%bf%d1%80%d0%b8%d1%8f%d1%82/',
            'type' => ['ru' => 'Нетворкинг', 'en' => 'Networking', 'ro' => 'Networking'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => 'В Glia Impact Hub состоялось нетворкинг-мероприятие «Мечта обретает крылья»', 'en' => '“Dream Takes Flight” networking event at Glia Impact Hub', 'ro' => 'Evenimentul de networking „Visul își ia zborul” la Glia Impact Hub'],
            'description' => ['ru' => 'Встреча предпринимательниц, организованная AFAM вместе с партнёрами сообщества.', 'en' => 'A gathering of women entrepreneurs organised by AFAM and community partners.', 'ro' => 'O întâlnire a femeilor antreprenoare organizată de AFAM și partenerii comunității.'],
            'tone' => 'blue',
        ],
        [
            'photo' => 'news/news-panel.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d0%bf%d0%b0%d0%bd%d0%b5%d0%bb%d1%8c%d0%bd%d0%b0%d1%8f-%d0%b4%d0%b8%d1%81%d0%ba%d1%83%d1%81%d1%81%d0%b8%d1%8f/',
            'type' => ['ru' => 'Дискуссия', 'en' => 'Panel discussion', 'ro' => 'Discuție panel'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => 'Панельная дискуссия «Производство успеха»', 'en' => 'Panel discussion: “Creating success”', 'ro' => 'Discuție panel: „Producerea succesului”'],
            'description' => ['ru' => 'Разговор о женском бизнесе — от первой идеи до её реализации.', 'en' => 'A conversation about women’s business, from the first idea to implementation.', 'ro' => 'O discuție despre businessul feminin, de la prima idee până la realizare.'],
            'tone' => 'rose',
        ],
        [
            'photo' => 'news/news-award.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d0%b3%d0%b0%d0%bb%d0%b0-%d0%bf%d1%80%d0%b5%d0%bc%d0%b8%d1%8f-%d0%b6%d0%b5%d0%bd%d1%89%d0%b8%d0%bd%d0%b0-%d0%b3%d0%be%d0%b4%d0%b0-2024/',
            'type' => ['ru' => 'Премия', 'en' => 'Awards', 'ro' => 'Premii'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => 'Гала-премия «Женщина года 2024»', 'en' => '“Woman of the Year 2024” gala awards', 'ro' => 'Gala premiilor „Femeia anului 2024”'],
            'description' => ['ru' => 'Событие, посвящённое признанию выдающихся женщин и их достижений.', 'en' => 'An event celebrating outstanding women and their achievements.', 'ro' => 'Un eveniment dedicat recunoașterii femeilor remarcabile și realizărilor lor.'],
            'tone' => 'orange',
        ],
        [
            'photo' => 'news/news-support.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d0%bf%d0%be%d0%b4%d0%b4%d0%b5%d1%80%d0%b6%d0%ba%d0%b0-%d0%b6%d0%b5%d0%bd%d1%89%d0%b8%d0%bd-%d0%bf%d1%80%d0%b5%d0%b4%d0%bf%d1%80%d0%b8%d0%bd%d0%b8%d0%bc%d0%b0%d1%82%d0%b5%d0%bb%d0%b5%d0%b9/',
            'type' => ['ru' => 'Сообщество', 'en' => 'Community', 'ro' => 'Comunitate'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => 'Поддержка женщин-предпринимателей', 'en' => 'Supporting women entrepreneurs', 'ro' => 'Sprijin pentru femeile antreprenoare'],
            'description' => ['ru' => 'О том, как женщины развивают собственные компании, находят решения и открывают новые возможности.', 'en' => 'How women build companies, find solutions and open new opportunities.', 'ro' => 'Cum femeile dezvoltă companii, găsesc soluții și deschid oportunități noi.'],
            'tone' => 'coral',
        ],
        [
            'photo' => 'news/news-stories.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d0%ba%d0%b0%d0%ba-%d0%bd%d0%b5-%d0%be%d1%81%d1%82%d0%b0%d0%bd%d0%be%d0%b2%d0%b8%d1%82%d1%8c%d1%81%d1%8f-%d1%80%d0%b5%d0%b0%d0%bb%d1%8c%d0%bd%d1%8b%d0%b5-%d0%b8%d1%81%d1%82%d0%be%d1%80%d0%b8%d0%b8/',
            'type' => ['ru' => 'Истории', 'en' => 'Stories', 'ro' => 'Istorii'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => 'Как не остановиться: реальные истории женщин в бизнесе', 'en' => 'Keep going: real stories of women in business', 'ro' => 'Cum să continui: istorii reale ale femeilor în business'],
            'description' => ['ru' => 'Истории предпринимательниц о стойкости, развитии и поддержке на пути в бизнесе.', 'en' => 'Stories of resilience, growth and support from women in business.', 'ro' => 'Istorii despre reziliență, creștere și sprijin în business.'],
            'tone' => 'pink',
        ],
        [
            'photo' => 'news/news-development.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d1%80%d0%b0%d0%b7%d0%b2%d0%b8%d1%82%d0%b8%d0%b5-%d0%b6%d0%b5%d0%bd%d1%81%d0%ba%d0%be%d0%b3%d0%be-%d0%bf%d1%80%d0%b5%d0%b4%d0%bf%d1%80%d0%b8%d0%bd%d0%b8%d0%bc%d0%b0%d1%82%d0%b5%d0%bb%d1%8c%d1%81%d1%82/',
            'type' => ['ru' => 'Развитие', 'en' => 'Development', 'ro' => 'Dezvoltare'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => 'Развитие женского предпринимательства', 'en' => 'Developing women’s entrepreneurship', 'ro' => 'Dezvoltarea antreprenoriatului feminin'],
            'description' => ['ru' => 'Встреча о развитии женщин-предпринимательниц в производственной сфере.', 'en' => 'A meeting focused on women entrepreneurs in manufacturing.', 'ro' => 'O întâlnire dedicată femeilor antreprenoare din sectorul producției.'],
            'tone' => 'teal',
        ],
        [
            'photo' => 'news/news-forum.jpg',
            'url' => 'https://women.creativity.md/2026/05/20/%d0%b6%d0%b5%d0%bd%d1%81%d0%ba%d0%b8%d0%b9-%d0%b1%d0%b8%d0%b7%d0%bd%d0%b5%d1%81-%d1%84%d0%be%d1%80%d1%83%d0%bc-%d0%b6%d0%b5%d0%bd%d1%81%d0%ba%d0%be%d0%b5-%d0%bf%d1%80%d0%b5%d0%b4%d0%bf%d1%80/',
            'type' => ['ru' => 'Форум', 'en' => 'Forum', 'ro' => 'Forum'],
            'date' => ['ru' => '20.05.2026', 'en' => 'May 20, 2026', 'ro' => '20.05.2026'],
            'title' => ['ru' => 'Женский бизнес-форум «Рост, цифровизация, устойчивость»', 'en' => 'Women’s business forum: growth, digitalisation, sustainability', 'ro' => 'Forumul de business feminin: creștere, digitalizare, sustenabilitate'],
            'description' => ['ru' => 'Форум собрал предпринимательниц, экспертов и представителей бизнес-сообщества.', 'en' => 'A forum bringing together entrepreneurs, experts and the wider business community.', 'ro' => 'Un forum care a reunit antreprenoare, experți și comunitatea de business.'],
            'tone' => 'blue',
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform — Events</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <meta name="description" content="Events, news and opportunities for women entrepreneurs.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --miro-primary:#1c1c1e; --miro-pink:#ffd8f4; --miro-cream:#fff4c4; --miro-blue:#4262ff; --miro-coral:#ffc6c6; --miro-rose:#ffd8f4; --miro-teal:#c3faf5; --miro-orange:#ffe6cd; --miro-surface:#f7f8fa; --miro-hairline:#e0e2e8; --miro-hairline-soft:#eef0f3; --miro-hairline-strong:#c7cad5; --miro-ink-deep:#050038; --miro-ink:#1c1c1e; --miro-charcoal:#2c2c34; --miro-slate:#555a6a; --miro-steel:#6b6f7e; --miro-muted:#a5a8b5; --miro-shadow:rgba(5,0,56,.08) 0 12px 32px -4px; --miro-font:"Roobert PRO","Noto Sans",-apple-system,BlinkMacSystemFont,sans-serif; }
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
        .miro-events-page { background:linear-gradient(180deg,#fff 0%,#fafbfc 45%,#fff 100%); }
        .miro-events-hero { position:relative; overflow:hidden; padding:88px 0 76px; background:var(--miro-teal); }
        .miro-events-hero::before { content:""; position:absolute; top:-150px; right:8%; width:320px; height:320px; border:1px solid rgba(5,0,56,.18); border-radius:50%; }
        .miro-events-hero::after { content:""; position:absolute; right:12%; bottom:-42px; width:210px; height:150px; border-radius:100% 100% 0 0; background:var(--miro-coral); opacity:.75; }
        .miro-events-hero__inner { position:relative; z-index:1; display:grid; grid-template-columns:minmax(0,1fr) 350px; align-items:end; gap:64px; }
        .miro-eyebrow { margin:0 0 18px; color:var(--miro-blue); font-size:12px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
        .miro-events-hero h1 { max-width:800px; margin:0; color:var(--miro-ink-deep); font-size:clamp(46px,7vw,82px); font-weight:500; line-height:1.02; letter-spacing:-.06em; }
        .miro-events-hero__copy { max-width:630px; margin:24px 0 0; color:var(--miro-slate); font-size:18px; }
        .miro-events-hero__note { justify-self:end; max-width:300px; padding:22px; border:1px solid rgba(5,0,56,.1); border-radius:24px; background:rgba(255,255,255,.78); box-shadow:0 8px 24px rgba(5,0,56,.05); color:var(--miro-charcoal); }
        .miro-events-hero__note strong { display:block; margin-bottom:4px; color:var(--miro-ink-deep); font-size:28px; font-weight:500; letter-spacing:-.04em; }
        .miro-events-hero__note span { color:var(--miro-slate); font-size:14px; }
        .miro-events-section { padding:88px 0 104px; }
        .miro-section__head { max-width:760px; margin-bottom:40px; }
        .miro-section__head h2 { margin:0; color:var(--miro-ink-deep); font-size:clamp(34px,5vw,58px); font-weight:500; line-height:1.08; letter-spacing:-.05em; }
        .miro-section__head p { margin:16px 0 0; color:var(--miro-slate); font-size:18px; }
        .miro-events-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:20px; }
        .miro-event-card { display:flex; min-width:0; flex-direction:column; overflow:hidden; border:1px solid var(--miro-hairline-soft); border-radius:26px; background:#fff; box-shadow:0 8px 24px rgba(5,0,56,.045); transition:transform .2s ease,box-shadow .2s ease; }
        .miro-event-card:hover { transform:translateY(-3px); box-shadow:var(--miro-shadow); }
        .miro-event-card__visual { position:relative; height:228px; background:var(--miro-teal); }
        .miro-event-card__visual::after { content:""; position:absolute; right:-26px; bottom:-32px; width:130px; height:130px; border:14px solid rgba(255,255,255,.5); border-radius:50%; }
        .miro-event-card__visual img { position:relative; z-index:1; width:100%; height:100%; object-fit:cover; }
        .miro-event-card__type { position:absolute; z-index:2; top:16px; left:16px; padding:7px 11px; border-radius:9999px; background:rgba(255,255,255,.92); color:var(--miro-primary); font-size:12px; font-weight:600; }
        .miro-event-card__body { display:flex; flex:1; flex-direction:column; padding:24px; }
        .miro-event-card__date { color:var(--miro-blue); font-size:12px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; }
        .miro-event-card h3 { margin:9px 0 0; color:var(--miro-ink-deep); font-size:24px; font-weight:500; line-height:1.15; letter-spacing:-.03em; }
        .miro-event-card__body p { margin:14px 0 0; color:var(--miro-slate); font-size:14px; line-height:1.5; }
        .miro-event-card__link { display:inline-flex; align-items:center; align-self:flex-start; margin-top:auto; padding-top:22px; color:var(--miro-primary); font-size:14px; font-weight:600; }
        .miro-event-card__link:hover { color:var(--miro-blue); }
        .miro-events-cta { margin-top:72px; padding:64px 32px; border-radius:32px; background:var(--miro-primary); color:#fff; text-align:center; }
        .miro-events-cta h2 { max-width:700px; margin:0 auto; color:#fff; font-size:clamp(34px,5vw,58px); font-weight:500; line-height:1.08; letter-spacing:-.05em; }
        .miro-events-cta p { max-width:580px; margin:18px auto 0; color:var(--miro-muted); font-size:18px; }
        .miro-events-cta__actions { display:flex; justify-content:center; flex-wrap:wrap; gap:12px; margin-top:28px; }
        .miro-footer { padding:64px 0 28px; background:var(--miro-primary); color:#fff; }
        .miro-footer__top { display:grid; grid-template-columns:1.4fr repeat(4,1fr); gap:32px; padding-bottom:56px; }
        .miro-footer__brand p { max-width:250px; margin:18px 0 0; color:var(--miro-muted); font-size:14px; }
        .miro-footer .miro-brand__logo { width:214px; height:62px; padding:7px 10px; border-radius:12px; background:#fff; }
        .miro-footer h4 { margin:0 0 14px; font-size:16px; font-weight:500; }
        .miro-footer ul { display:grid; gap:8px; margin:0; padding:0; list-style:none; color:var(--miro-muted); font-size:14px; }
        .miro-footer li a:hover { color:#fff; }
        .miro-footer__bottom { display:flex; justify-content:space-between; gap:20px; padding-top:22px; border-top:1px solid rgba(255,255,255,.12); color:var(--miro-muted); font-size:12px; }
        @media (max-width:1023px) { .miro-container{width:min(100% - 40px,760px)} .miro-nav__links,.miro-nav__actions>.miro-languages,.miro-nav__actions>.miro-button{display:none} .miro-mobile-toggle{display:grid;place-items:center} .miro-nav.is-open .miro-nav__links{position:absolute;top:68px;left:0;right:0;display:grid;gap:0;padding:12px 20px 20px;border-bottom:1px solid var(--miro-hairline);background:#fff} .miro-nav.is-open .miro-nav__links a{padding:14px 0;border-bottom:1px solid var(--miro-hairline-soft)} .miro-nav.is-open .miro-nav__mobile-menu{display:grid;gap:10px;padding-top:14px} .miro-nav.is-open .miro-nav__mobile-menu .miro-languages{width:max-content;margin:0 0 4px} .miro-nav.is-open .miro-nav__mobile-menu .miro-button{width:100%} .miro-events-hero__inner{grid-template-columns:1fr;gap:32px} .miro-events-hero__note{justify-self:start} .miro-events-grid{grid-template-columns:repeat(2,minmax(0,1fr))} .miro-footer__top{grid-template-columns:repeat(3,1fr)} .miro-footer__brand{grid-column:1/-1} }
        @media (max-width:767px) { .miro-container{width:min(100% - 32px,540px)} .miro-brand{font-size:14px} .miro-events-hero{padding:64px 0 56px} .miro-events-hero h1{font-size:48px} .miro-events-hero__copy,.miro-section__head p,.miro-events-cta p{font-size:16px} .miro-events-section{padding:64px 0 76px} .miro-section__head h2,.miro-events-cta h2{font-size:38px} .miro-events-grid{grid-template-columns:1fr} .miro-events-cta{padding:48px 22px;border-radius:24px} .miro-footer__top{grid-template-columns:repeat(2,1fr)} .miro-footer__bottom{flex-direction:column} }
        @media (max-width:479px) { .miro-footer__top{gap:28px 16px} }
    </style>
</head>
<body>
    @include('partials.miro-header', ['miroCurrentPage' => 'events'])
    @if(false)
    <nav class="miro-nav" id="miro-nav">
        <div class="miro-container miro-nav__inner">
            <a href="{{ url('/') }}" class="miro-brand"><span class="miro-brand__mark">W</span><span>Women</span></a>
            <div class="miro-nav__links" id="miro-nav-links">
                <a href="{{ url('/') }}"><span data-lang="ru">Главная</span><span data-lang="en">Home</span><span data-lang="ro">Acasă</span></a>
                <a href="{{ url('/') }}#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a>
                <a href="{{ url('/') }}#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a>
                <a href="{{ route('members') }}"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a>
                <a href="{{ route('events') }}" class="is-active"><span data-lang="ru">Новости</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a>
                <a href="{{ url('/') }}#contact"><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></a>
                <div class="miro-nav__mobile-menu">
                    <div class="miro-languages" aria-label="Language switcher"><button type="button" data-locale="ru">RU</button><button type="button" data-locale="en">EN</button><button type="button" data-locale="ro">RO</button></div>
                    <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary"><span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span></a>
                    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary"><span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span></a>
                </div>
            </div>
            <div class="miro-nav__actions">
                <div class="miro-languages" aria-label="Language switcher"><button type="button" data-locale="ru">RU</button><button type="button" data-locale="en">EN</button><button type="button" data-locale="ro">RO</button></div>
                <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary miro-button--small"><span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span></a>
                <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary miro-button--small"><span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span></a>
                <button type="button" class="miro-mobile-toggle" id="miro-mobile-toggle" aria-label="Menu" aria-expanded="false" aria-controls="miro-nav-links">☰</button>
            </div>
        </div>
    </nav>
    @endif

    <main class="miro-events-page">
      <section class="miro-events-section">
            <div class="miro-container">
                <div class="miro-section__head">
                    <h2><span data-lang="ru">Встречи, которые развивают</span><span data-lang="en">Stay in the loop</span><span data-lang="ro">Rămâi la curent</span></h2>
                    <p><span data-lang="ru">Воркшопы, нетворкинг, программы, объявления и партнёрские возможности для предпринимательниц.</span><span data-lang="en">Workshops, networking, programmes, announcements and partnership opportunities for women entrepreneurs.</span><span data-lang="ro">Workshopuri, networking, programe, anunțuri și oportunități de parteneriat pentru antreprenoare.</span></p>
                </div>
                <div class="miro-events-grid">
                    @foreach($events as $event)
                        <article class="miro-event-card">
                            <div class="miro-event-card__visual" style="background:var(--miro-{{ $event['tone'] }});">
                                <img src="{{ asset('images/' . $event['photo']) }}" alt="{{ $event['title']['en'] }}" loading="lazy">
                                <div class="miro-event-card__type">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event['type'][$locale] }}</span>@endforeach</div>
                            </div>
                            <div class="miro-event-card__body">
                                <div class="miro-event-card__date">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event['date'][$locale] }}</span>@endforeach</div>
                                <h3>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event['title'][$locale] }}</span>@endforeach</h3>
                                <p>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $event['description'][$locale] }}</span>@endforeach</p>
                                <a href="{{ $event['url'] }}" target="_blank" rel="noopener" class="miro-event-card__link"><span data-lang="ru">Подробнее&nbsp;→</span><span data-lang="en">Read more&nbsp;→</span><span data-lang="ro">Află mai multe&nbsp;→</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <section class="miro-events-cta">
                    <h2><span data-lang="ru">Не пропускайте новые возможности</span><span data-lang="en">Don’t miss the next opportunity</span><span data-lang="ro">Nu rata următoarea oportunitate</span></h2>
                    <p><span data-lang="ru">Присоединяйтесь к сообществу, чтобы получать новости, приглашения и контакты в Telegram.</span><span data-lang="en">Join the community to receive news, invitations and new connections in Telegram.</span><span data-lang="ro">Alătură-te comunității pentru noutăți, invitații și conexiuni noi pe Telegram.</span></p>
                    <div class="miro-events-cta__actions">
                        <a href="{{ $communityUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--pink"><span data-lang="ru">Открыть Telegram</span><span data-lang="en">Open Telegram</span><span data-lang="ro">Deschide Telegram</span></a>
                        <a href="{{ route('account.login') }}" class="miro-button" style="border:1px solid rgba(255,255,255,.35);color:#fff"><span data-lang="ru">Войти в кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Intră în cabinet</span></a>
                    </div>
                </section>
            </div>
        </section>
    </main>

    @include('partials.miro-footer')
    @if(false)
    <footer class="miro-footer" id="contact">
        <div class="miro-container">
            <div class="miro-footer__top">
                <div class="miro-footer__brand"><a href="{{ url('/') }}" class="miro-brand"><span class="miro-brand__mark">W</span><span>Women Entrepreneurs Platform</span></a><p><span data-lang="ru">Цифровое пространство для женщин-предпринимательниц из двух берегов.</span><span data-lang="en">A digital space for women entrepreneurs from both banks.</span><span data-lang="ro">Un spațiu digital pentru femeile antreprenoare de pe ambele maluri.</span></p></div>
                <div><h4><span data-lang="ru">Платформа</span><span data-lang="en">Platform</span><span data-lang="ro">Platformă</span></h4><ul><li><a href="{{ url('/') }}#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a></li><li><a href="{{ route('members') }}"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a></li><li><a href="{{ route('events') }}"><span data-lang="ru">Новости</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a></li></ul></div>
                <div><h4><span data-lang="ru">Ресурсы</span><span data-lang="en">Resources</span><span data-lang="ro">Resurse</span></h4><ul><li><a href="{{ url('/') }}#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a></li><li><a href="{{ url('/') }}#opportunities"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></a></li></ul></div>
                <div><h4><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></h4><ul><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">@WomenComBot</a></li><li><a href="{{ $managerUrl }}" target="_blank" rel="noopener">Project team</a></li><li><a href="{{ $communityUrl }}" target="_blank" rel="noopener">Community</a></li></ul></div>
                <div><h4><span data-lang="ru">Вход</span><span data-lang="en">Access</span><span data-lang="ro">Acces</span></h4><ul><li><a href="{{ route('account.login') }}"><span data-lang="ru">Кабинет участницы</span><span data-lang="en">Participant cabinet</span><span data-lang="ro">Cabinetul membrei</span></a></li><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">Telegram</a></li></ul></div>
            </div>
            <div class="miro-footer__bottom"><span>© {{ date('Y') }} Women Entrepreneurs Platform</span><span><span data-lang="ru">Сделано для роста через связи</span><span data-lang="en">Made for growth through connection</span><span data-lang="ro">Creat pentru creștere prin conexiuni</span></span></div>
        </div>
    </footer>
    @endif

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
