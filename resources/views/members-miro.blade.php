@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);

    // Публичные экспертные профили перенесены с women.creativity.md; дополнительные поля пока статичны.
    $profiles = [
        [
            'photo' => 'experts/expert-carolina.png',
            'name' => ['ru' => 'Каролина Бугаян', 'en' => 'Carolina Bugaiyan', 'ro' => 'Carolina Bugaiyan'],
            'role' => ['ru' => 'Президент Ассоциации деловых женщин Молдовы (AFAM)', 'en' => 'President of the Association of Women Entrepreneurs in Moldova (AFAM)', 'ro' => 'Președinta Asociației Femeilor de Afaceri din Moldova (AFAM)'],
            'specialization' => ['ru' => 'Женское предпринимательство и развитие делового сообщества', 'en' => 'Women’s entrepreneurship & business community development', 'ro' => 'Antreprenoriat feminin și dezvoltarea comunității de business'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'AFAM', 'en' => 'AFAM', 'ro' => 'AFAM'],
                ['ru' => 'Партнёрства', 'en' => 'Partnerships', 'ro' => 'Parteneriate'],
            ],
            'description' => ['ru' => 'Развивает женское предпринимательство и деловые связи в Молдове.', 'en' => 'Develops women’s entrepreneurship and business connections in Moldova.', 'ro' => 'Dezvoltă antreprenoriatul feminin și conexiunile de business în Moldova.'],
            'looking_for' => ['ru' => 'Новые профессиональные связи', 'en' => 'New professional connections', 'ro' => 'Conexiuni profesionale noi'],
            'can_offer' => ['ru' => 'Экспертизу и связи в деловом сообществе', 'en' => 'Expertise and connections in the business community', 'ro' => 'Expertiză și conexiuni în comunitatea de business'],
            'tone' => 'pink',
        ],
        [
            'photo' => 'experts/expert-aurelia.png',
            'name' => ['ru' => 'Аурелия Саликов', 'en' => 'Aurelia Salicov', 'ro' => 'Aurelia Salicov'],
            'role' => ['ru' => 'Вице-президент Международного бизнес-сообщества в Молдове', 'en' => 'Vice-President of the International Business Society in Moldova', 'ro' => 'Vicepreședinta International Business Society din Moldova'],
            'specialization' => ['ru' => 'Международное деловое сотрудничество', 'en' => 'International business cooperation', 'ro' => 'Cooperare internațională de business'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'Международные связи', 'en' => 'International relations', 'ro' => 'Relații internaționale'],
                ['ru' => 'Партнёрства', 'en' => 'Partnerships', 'ro' => 'Parteneriate'],
            ],
            'description' => ['ru' => 'Развивает международное деловое сотрудничество и новые партнёрства.', 'en' => 'Builds international business cooperation and new partnerships.', 'ro' => 'Dezvoltă cooperarea internațională de business și parteneriate noi.'],
            'looking_for' => ['ru' => 'Международные профессиональные контакты', 'en' => 'International professional connections', 'ro' => 'Conexiuni profesionale internaționale'],
            'can_offer' => ['ru' => 'Знание деловой среды и партнёрские связи', 'en' => 'Business environment knowledge and partner connections', 'ro' => 'Cunoașterea mediului de business și conexiuni cu parteneri'],
            'tone' => 'teal',
        ],
        [
            'photo' => 'experts/expert-vlada.png',
            'name' => ['ru' => 'Влада Лысенко', 'en' => 'Vlada Lysenko', 'ro' => 'Vlada Lysenko'],
            'role' => ['ru' => 'Доктор наук, профессор, международный консультант', 'en' => 'Doctor of Sciences, Professor & International Consultant', 'ro' => 'Doctor în științe, profesor și consultant internațional'],
            'specialization' => ['ru' => 'Наука, образование и международный консалтинг', 'en' => 'Research, education & international consulting', 'ro' => 'Cercetare, educație și consultanță internațională'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'Образование', 'en' => 'Education', 'ro' => 'Educație'],
                ['ru' => 'Консалтинг', 'en' => 'Consulting', 'ro' => 'Consultanță'],
            ],
            'description' => ['ru' => 'Объединяет академический опыт, образование и международный консалтинг.', 'en' => 'Combines academic experience, education and international consulting.', 'ro' => 'Combină experiența academică, educația și consultanța internațională.'],
            'looking_for' => ['ru' => 'Профессиональные и образовательные проекты', 'en' => 'Professional and education projects', 'ro' => 'Proiecte profesionale și educaționale'],
            'can_offer' => ['ru' => 'Консультации и экспертный взгляд', 'en' => 'Consulting and an expert perspective', 'ro' => 'Consultanță și perspectivă de expert'],
            'tone' => 'orange',
        ],
        [
            'photo' => 'experts/expert-zinaida.png',
            'name' => ['ru' => 'Зинаида Емельянова', 'en' => 'Zinaida Emelyanova', 'ro' => 'Zinaida Emelyanova'],
            'role' => ['ru' => 'Директор Агентства инноваций и развития', 'en' => 'Director of the Agency for Innovation and Development', 'ro' => 'Directoarea Agenției pentru Inovații și Dezvoltare'],
            'specialization' => ['ru' => 'Инновации и развитие проектов', 'en' => 'Innovation & project development', 'ro' => 'Inovații și dezvoltarea proiectelor'],
            'tags' => [
                ['ru' => 'Инновации', 'en' => 'Innovation', 'ro' => 'Inovație'],
                ['ru' => 'Развитие', 'en' => 'Development', 'ro' => 'Dezvoltare'],
                ['ru' => 'Возможности', 'en' => 'Opportunities', 'ro' => 'Oportunități'],
            ],
            'description' => ['ru' => 'Развивает инновационные проекты и поддерживает предпринимательские инициативы.', 'en' => 'Develops innovation projects and supports entrepreneurial initiatives.', 'ro' => 'Dezvoltă proiecte inovatoare și susține inițiative antreprenoriale.'],
            'looking_for' => ['ru' => 'Инновационные проекты и новые связи', 'en' => 'Innovative projects and new connections', 'ro' => 'Proiecte inovatoare și conexiuni noi'],
            'can_offer' => ['ru' => 'Ориентир в экосистеме развития и инноваций', 'en' => 'Guidance in the innovation and development ecosystem', 'ro' => 'Orientare în ecosistemul de inovații și dezvoltare'],
            'tone' => 'rose',
        ],
        [
            'photo' => 'experts/expert-mariana.png',
            'name' => ['ru' => 'Мариана Руфа', 'en' => 'Mariana Rufa', 'ro' => 'Mariana Rufa'],
            'role' => ['ru' => 'Исполнительный директор Европейской бизнес-ассоциации в Молдове (EBA)', 'en' => 'Executive Director of the European Business Association of Moldova (EBA)', 'ro' => 'Directoarea executivă a European Business Association of Moldova (EBA)'],
            'specialization' => ['ru' => 'Европейский бизнес и деловые ассоциации', 'en' => 'European business & business associations', 'ro' => 'Business european și asociații de business'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'Европейский бизнес', 'en' => 'European business', 'ro' => 'Business european'],
                ['ru' => 'B2B', 'en' => 'B2B', 'ro' => 'B2B'],
            ],
            'description' => ['ru' => 'Представляет европейскую бизнес-среду и развивает деловые связи в Молдове.', 'en' => 'Represents the European business environment and builds connections in Moldova.', 'ro' => 'Reprezintă mediul european de business și dezvoltă conexiuni în Moldova.'],
            'looking_for' => ['ru' => 'Деловые контакты и совместные инициативы', 'en' => 'Business connections and joint initiatives', 'ro' => 'Conexiuni de business și inițiative comune'],
            'can_offer' => ['ru' => 'Связи в европейской бизнес-среде', 'en' => 'Connections in the European business environment', 'ro' => 'Conexiuni în mediul european de business'],
            'tone' => 'coral',
        ],
        [
            'photo' => 'experts/expert-valeria.png',
            'name' => ['ru' => 'Валерия Зелинская', 'en' => 'Valeria Zelinskaya', 'ro' => 'Valeria Zelinskaya'],
            'role' => ['ru' => 'Стилистка, основательница бренда L’emone и собственного ателье', 'en' => 'Stylist, founder of L’emone and her own atelier', 'ro' => 'Stilistă, fondatoarea brandului L’emone și a propriului atelier'],
            'specialization' => ['ru' => 'Стиль, мода и собственный бренд', 'en' => 'Styling, fashion & brand building', 'ro' => 'Stil, modă și dezvoltarea unui brand'],
            'tags' => [
                ['ru' => 'Основательница', 'en' => 'Founder', 'ro' => 'Fondatoare'],
                ['ru' => 'Мода', 'en' => 'Fashion', 'ro' => 'Modă'],
                ['ru' => 'Креатив', 'en' => 'Creative', 'ro' => 'Creativ'],
            ],
            'description' => ['ru' => 'Развивает собственный бренд и ателье на стыке стиля, моды и предпринимательства.', 'en' => 'Builds her own brand and atelier at the intersection of styling, fashion and entrepreneurship.', 'ro' => 'Își dezvoltă brandul și atelierul la intersecția dintre stil, modă și antreprenoriat.'],
            'looking_for' => ['ru' => 'Творческие коллаборации и новые контакты', 'en' => 'Creative collaborations and new connections', 'ro' => 'Colaborări creative și conexiuni noi'],
            'can_offer' => ['ru' => 'Опыт в стиле, дизайне и развитии бренда', 'en' => 'Experience in styling, design and brand building', 'ro' => 'Experiență în stil, design și dezvoltarea brandului'],
            'tone' => 'blue',
        ],
        [
            'photo' => 'experts/expert-olga-levitskaya.png',
            'name' => ['ru' => 'Ольга Левицкая', 'en' => 'Olga Levitskaya', 'ro' => 'Olga Levitskaya'],
            'role' => ['ru' => 'Президент NGO Scenario', 'en' => 'President of NGO Scenario', 'ro' => 'Președinta ONG Scenario'],
            'specialization' => ['ru' => 'НКО и социальные инициативы', 'en' => 'NGOs & social initiatives', 'ro' => 'ONG-uri și inițiative sociale'],
            'tags' => [
                ['ru' => 'Лидерка НКО', 'en' => 'NGO leader', 'ro' => 'Lideră ONG'],
                ['ru' => 'Социальное влияние', 'en' => 'Social impact', 'ro' => 'Impact social'],
                ['ru' => 'Сообщества', 'en' => 'Community', 'ro' => 'Comunitate'],
            ],
            'description' => ['ru' => 'Развивает социальные инициативы и проекты с общественным влиянием.', 'en' => 'Develops social initiatives and projects with community impact.', 'ro' => 'Dezvoltă inițiative sociale și proiecte cu impact în comunitate.'],
            'looking_for' => ['ru' => 'Партнёрства вокруг проектов с влиянием', 'en' => 'Partnerships around impact projects', 'ro' => 'Parteneriate pentru proiecte cu impact'],
            'can_offer' => ['ru' => 'Опыт развития социальных инициатив', 'en' => 'Experience developing social initiatives', 'ro' => 'Experiență în dezvoltarea inițiativelor sociale'],
            'tone' => 'pink',
        ],
        [
            'photo' => 'experts/expert-irena.png',
            'name' => ['ru' => 'Ирена Покладова', 'en' => 'Irena Pokladova', 'ro' => 'Irena Pokladova'],
            'role' => ['ru' => 'Соосновательница Forge Academy, CEO Moldova Business Club', 'en' => 'Co-founder of Forge Academy, CEO of Moldova Business Club', 'ro' => 'Co-fondatoarea Forge Academy, CEO Moldova Business Club'],
            'specialization' => ['ru' => 'Образование, бизнес-сообщества и лидерство', 'en' => 'Education, business communities & leadership', 'ro' => 'Educație, comunități de business și leadership'],
            'tags' => [
                ['ru' => 'CEO', 'en' => 'CEO', 'ro' => 'CEO'],
                ['ru' => 'Образование', 'en' => 'Education', 'ro' => 'Educație'],
                ['ru' => 'Сообщества', 'en' => 'Community', 'ro' => 'Comunitate'],
            ],
            'description' => ['ru' => 'Создаёт образовательные проекты и развивает бизнес-сообщества.', 'en' => 'Builds education projects and grows business communities.', 'ro' => 'Creează proiecte educaționale și dezvoltă comunități de business.'],
            'looking_for' => ['ru' => 'Сильные образовательные и деловые коллаборации', 'en' => 'Strong education and business collaborations', 'ro' => 'Colaborări puternice în educație și business'],
            'can_offer' => ['ru' => 'Опыт создания сообществ и образовательных проектов', 'en' => 'Experience building communities and education projects', 'ro' => 'Experiență în crearea comunităților și proiectelor educaționale'],
            'tone' => 'teal',
        ],
        [
            'photo' => 'experts/expert-sabina.png',
            'name' => ['ru' => 'Сабина Криган', 'en' => 'Sabina Crigan', 'ro' => 'Sabina Crigan'],
            'role' => ['ru' => 'Член совета директоров AFAM, владелица и партнёр Gateway & Partners', 'en' => 'AFAM board member, owner and partner at Gateway & Partners', 'ro' => 'Membră a consiliului AFAM, proprietară și parteneră la Gateway & Partners'],
            'specialization' => ['ru' => 'Бизнес-консалтинг и предпринимательство', 'en' => 'Business consulting & entrepreneurship', 'ro' => 'Consultanță de business și antreprenoriat'],
            'tags' => [
                ['ru' => 'AFAM', 'en' => 'AFAM', 'ro' => 'AFAM'],
                ['ru' => 'Партнёр', 'en' => 'Partner', 'ro' => 'Parteneră'],
                ['ru' => 'Консалтинг', 'en' => 'Consulting', 'ro' => 'Consultanță'],
            ],
            'description' => ['ru' => 'Совмещает предпринимательство, консалтинг и участие в развитии делового сообщества.', 'en' => 'Combines entrepreneurship, consulting and business community development.', 'ro' => 'Combină antreprenoriatul, consultanța și dezvoltarea comunității de business.'],
            'looking_for' => ['ru' => 'Профессиональные партнёрства и новые проекты', 'en' => 'Professional partnerships and new projects', 'ro' => 'Parteneriate profesionale și proiecte noi'],
            'can_offer' => ['ru' => 'Бизнес-опыт и консалтинговую экспертизу', 'en' => 'Business experience and consulting expertise', 'ro' => 'Experiență de business și expertiză în consultanță'],
            'tone' => 'rose',
        ],
        [
            'photo' => 'experts/expert-diana.png',
            'name' => ['ru' => 'Диана Сакирчук', 'en' => 'Diana Sakirchuk', 'ro' => 'Diana Sakirchuk'],
            'role' => ['ru' => 'Основательница PureCup', 'en' => 'Founder of PureCup', 'ro' => 'Fondatoarea PureCup'],
            'specialization' => ['ru' => 'Предпринимательство и развитие продукта', 'en' => 'Entrepreneurship & product development', 'ro' => 'Antreprenoriat și dezvoltarea produsului'],
            'tags' => [
                ['ru' => 'Основательница', 'en' => 'Founder', 'ro' => 'Fondatoare'],
                ['ru' => 'PureCup', 'en' => 'PureCup', 'ro' => 'PureCup'],
                ['ru' => 'Продукт', 'en' => 'Product', 'ro' => 'Produs'],
            ],
            'description' => ['ru' => 'Развивает PureCup и собственный предпринимательский проект.', 'en' => 'Builds PureCup and her own entrepreneurial project.', 'ro' => 'Dezvoltă PureCup și propriul proiect antreprenorial.'],
            'looking_for' => ['ru' => 'Новые деловые контакты и партнёрства', 'en' => 'New business connections and partnerships', 'ro' => 'Conexiuni de business și parteneriate noi'],
            'can_offer' => ['ru' => 'Опыт создания и развития продукта', 'en' => 'Experience building and developing a product', 'ro' => 'Experiență în crearea și dezvoltarea unui produs'],
            'tone' => 'orange',
        ],
        [
            'photo' => 'experts/expert-olga-melnichuk.png',
            'name' => ['ru' => 'Ольга Мельничук', 'en' => 'Olga Melnichuk', 'ro' => 'Olga Melnichuk'],
            'role' => ['ru' => 'Соучредительница Business Angels Moldova, исполнительный директор Startup Moldova', 'en' => 'Co-founder of Business Angels Moldova, Executive Director of Startup Moldova', 'ro' => 'Co-fondatoarea Business Angels Moldova, directoarea executivă Startup Moldova'],
            'specialization' => ['ru' => 'Стартапы, инвестиции и предпринимательство', 'en' => 'Startups, investment & entrepreneurship', 'ro' => 'Startupuri, investiții și antreprenoriat'],
            'tags' => [
                ['ru' => 'Стартапы', 'en' => 'Startups', 'ro' => 'Startupuri'],
                ['ru' => 'Инвестиции', 'en' => 'Investment', 'ro' => 'Investiții'],
                ['ru' => 'Сообщества', 'en' => 'Community', 'ro' => 'Comunitate'],
            ],
            'description' => ['ru' => 'Развивает стартап- и инвестиционную экосистему Молдовы.', 'en' => 'Develops Moldova’s startup and investment ecosystem.', 'ro' => 'Dezvoltă ecosistemul de startupuri și investiții din Moldova.'],
            'looking_for' => ['ru' => 'Стартапы, инвесторов и экосистемные партнёрства', 'en' => 'Startups, investors and ecosystem partnerships', 'ro' => 'Startupuri, investitori și parteneriate în ecosistem'],
            'can_offer' => ['ru' => 'Связи в стартапах и инвестиционном сообществе', 'en' => 'Connections in the startup and investment community', 'ro' => 'Conexiuni în comunitatea de startupuri și investiții'],
            'tone' => 'coral',
        ],
        [
            'photo' => 'experts/expert-irina.png',
            'name' => ['ru' => 'Ирина Плешкова', 'en' => 'Irina Pleshkova', 'ro' => 'Irina Pleshkova'],
            'role' => ['ru' => 'Эксперт по внедрению AI и цифровой эффективности', 'en' => 'Expert in AI adoption and digital efficiency', 'ro' => 'Expertă în implementarea AI și eficiență digitală'],
            'specialization' => ['ru' => 'AI, цифровая трансформация и эффективность', 'en' => 'AI, digital transformation & efficiency', 'ro' => 'AI, transformare digitală și eficiență'],
            'tags' => [
                ['ru' => 'AI', 'en' => 'AI', 'ro' => 'AI'],
                ['ru' => 'Цифровизация', 'en' => 'Digital', 'ro' => 'Digital'],
                ['ru' => 'Эффективность', 'en' => 'Efficiency', 'ro' => 'Eficiență'],
            ],
            'description' => ['ru' => 'Помогает предпринимателям внедрять AI и цифровые инструменты для роста эффективности.', 'en' => 'Helps entrepreneurs adopt AI and digital tools to improve efficiency.', 'ro' => 'Ajută antreprenorii să adopte AI și instrumente digitale pentru eficiență.'],
            'looking_for' => ['ru' => 'Бизнесы, заинтересованные в цифровой эффективности', 'en' => 'Businesses interested in digital efficiency', 'ro' => 'Afaceri interesate de eficiență digitală'],
            'can_offer' => ['ru' => 'Экспертизу в AI и цифровой трансформации', 'en' => 'Expertise in AI and digital transformation', 'ro' => 'Expertiză în AI și transformare digitală'],
            'tone' => 'blue',
        ],
    ];

    $locales = ['ru', 'en', 'ro'];
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform — Members</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <meta name="description" content="Public catalogue of experts, founders and participants of Women Entrepreneurs Platform.">
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
        .miro-members-page { background:linear-gradient(180deg,#fff 0%,#fafbfc 42%,#fff 100%); }
        .miro-members-hero { position:relative; overflow:hidden; padding:88px 0 76px; background:var(--miro-pink); }
        .miro-members-hero::before { content:""; position:absolute; top:-130px; right:8%; width:290px; height:290px; border:1px solid rgba(66,98,255,.3); border-radius:50%; }
        .miro-members-hero::after { content:""; position:absolute; right:12%; bottom:0; width:180px; height:130px; border-radius:100% 0 0; background:var(--miro-teal); opacity:.75; }
        .miro-members-hero__inner { position:relative; z-index:1; display:grid; grid-template-columns:minmax(0,1fr) 360px; align-items:end; gap:64px; }
        .miro-eyebrow { margin:0 0 18px; color:var(--miro-blue); font-size:12px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
        .miro-members-hero h1 { max-width:780px; margin:0; color:var(--miro-ink-deep); font-size:clamp(46px,7vw,82px); font-weight:500; line-height:1.02; letter-spacing:-.06em; }
        .miro-members-hero__copy { max-width:630px; margin:24px 0 0; color:var(--miro-slate); font-size:18px; }
        .miro-members-hero__note { justify-self:end; max-width:300px; padding:22px; border:1px solid rgba(5,0,56,.1); border-radius:24px; background:rgba(255,255,255,.78); box-shadow:0 8px 24px rgba(5,0,56,.05); color:var(--miro-charcoal); }
        .miro-members-hero__note strong { display:block; margin-bottom:4px; color:var(--miro-ink-deep); font-size:28px; font-weight:500; letter-spacing:-.04em; }
        .miro-members-hero__note span { color:var(--miro-slate); font-size:14px; }
        .miro-members-section { padding:88px 0 104px; }
        .miro-section__head { max-width:760px; margin-bottom:40px; }
        .miro-section__head h2 { margin:0; color:var(--miro-ink-deep); font-size:clamp(34px,5vw,58px); font-weight:500; line-height:1.08; letter-spacing:-.05em; }
        .miro-section__head p { margin:16px 0 0; color:var(--miro-slate); font-size:18px; }
        .miro-members-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:20px; }
        .miro-public-member { display:flex; min-width:0; flex-direction:column; overflow:hidden; border:1px solid var(--miro-hairline-soft); border-radius:26px; background:#fff; box-shadow:0 8px 24px rgba(5,0,56,.045); transition:transform .2s ease,box-shadow .2s ease; }
        .miro-public-member:hover { transform:translateY(-3px); box-shadow:var(--miro-shadow); }
        .miro-public-member__visual { position:relative; height:270px; background:var(--miro-teal); }
        .miro-public-member__visual::after { content:""; position:absolute; right:-20px; bottom:-28px; width:120px; height:120px; border:14px solid rgba(255,255,255,.5); border-radius:50%; }
        .miro-public-member__visual img { position:relative; z-index:1; width:100%; height:100%; object-fit:cover; object-position:center; }
        .miro-public-member__body { display:flex; flex:1; flex-direction:column; padding:24px; }
        .miro-public-member h3 { margin:0; color:var(--miro-ink-deep); font-size:24px; font-weight:500; line-height:1.15; letter-spacing:-.03em; }
        .miro-public-member__specialization { margin:8px 0 0; color:var(--miro-blue); font-size:14px; font-weight:600; }
        .miro-public-member__tags { display:flex; flex-wrap:wrap; gap:6px; margin-top:0; margin-bottom:18px; }
        .miro-public-member__tag { display:inline-flex; align-items:center; padding:5px 9px; border-radius:9999px; color:var(--miro-primary); font-size:11px; font-weight:500; line-height:1.2; }
        .miro-public-member__tag--pink, .miro-public-member__tag--yellow { background:var(--miro-cream); }
        .miro-public-member__tag--teal { background:var(--miro-teal); color:#187574; }
        .miro-public-member__tag--rose { background:var(--miro-rose); }
        .miro-public-member__tag--coral { background:var(--miro-coral); }
        .miro-public-member__tag--blue { background:#eef1ff; color:var(--miro-blue); }
        .miro-public-member__tag--orange { background:var(--miro-orange); }
        .miro-public-member__description { margin:18px 0 0; color:var(--miro-slate); font-size:14px; line-height:1.5; }
        .miro-public-member__details { display:grid; gap:12px; margin-top:auto; padding-top:22px; }
        .miro-public-member__detail { padding-top:12px; border-top:1px solid var(--miro-hairline-soft); }
        .miro-public-member__detail strong { display:block; margin-bottom:4px; color:var(--miro-ink-deep); font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
        .miro-public-member__detail span { display:block; color:var(--miro-charcoal); font-size:13px; line-height:1.4; }
        .miro-members-cta { margin-top:72px; padding:64px 32px; border-radius:32px; background:var(--miro-primary); color:#fff; text-align:center; }
        .miro-members-cta h2 { max-width:700px; margin:0 auto; color:#fff; font-size:clamp(34px,5vw,58px); font-weight:500; line-height:1.08; letter-spacing:-.05em; }
        .miro-members-cta p { max-width:580px; margin:18px auto 0; color:var(--miro-muted); font-size:18px; }
        .miro-members-cta__actions { display:flex; justify-content:center; flex-wrap:wrap; gap:12px; margin-top:28px; }
        .miro-footer { padding:64px 0 28px; background:var(--miro-primary); color:#fff; }
        .miro-footer__top { display:grid; grid-template-columns:1.4fr repeat(4,1fr); gap:32px; padding-bottom:56px; }
        .miro-footer__brand p { max-width:250px; margin:18px 0 0; color:var(--miro-muted); font-size:14px; }
        .miro-footer .miro-brand__logo { width:214px; height:62px; padding:7px 10px; border-radius:12px; background:#fff; }
        .miro-footer h4 { margin:0 0 14px; font-size:16px; font-weight:500; }
        .miro-footer ul { display:grid; gap:8px; margin:0; padding:0; list-style:none; color:var(--miro-muted); font-size:14px; }
        .miro-footer li a:hover { color:#fff; }
        .miro-footer__bottom { display:flex; justify-content:space-between; gap:20px; padding-top:22px; border-top:1px solid rgba(255,255,255,.12); color:var(--miro-muted); font-size:12px; }
        @media (max-width:1023px) { .miro-container{width:min(100% - 40px,760px)} .miro-nav__links,.miro-nav__actions>.miro-languages,.miro-nav__actions>.miro-button{display:none} .miro-mobile-toggle{display:grid;place-items:center} .miro-nav.is-open .miro-nav__links{position:absolute;top:68px;left:0;right:0;display:grid;gap:0;padding:12px 20px 20px;border-bottom:1px solid var(--miro-hairline);background:#fff} .miro-nav.is-open .miro-nav__links a{padding:14px 0;border-bottom:1px solid var(--miro-hairline-soft)} .miro-nav.is-open .miro-nav__mobile-menu{display:grid;gap:10px;padding-top:14px} .miro-nav.is-open .miro-nav__mobile-menu .miro-languages{width:max-content;margin:0 0 4px} .miro-nav.is-open .miro-nav__mobile-menu .miro-button{width:100%} .miro-members-hero__inner{grid-template-columns:1fr;gap:32px} .miro-members-hero__note{justify-self:start} .miro-members-grid{grid-template-columns:repeat(2,minmax(0,1fr))} .miro-footer__top{grid-template-columns:repeat(3,1fr)} .miro-footer__brand{grid-column:1/-1} }
        @media (max-width:767px) { .miro-container{width:min(100% - 32px,540px)} .miro-brand{font-size:14px} .miro-members-hero{padding:64px 0 56px} .miro-members-hero h1{font-size:48px} .miro-members-hero__copy,.miro-section__head p,.miro-members-cta p{font-size:16px} .miro-members-section{padding:64px 0 76px} .miro-section__head h2,.miro-members-cta h2{font-size:38px} .miro-members-grid{grid-template-columns:1fr} .miro-members-cta{padding:48px 22px;border-radius:24px} .miro-footer__top{grid-template-columns:repeat(2,1fr)} .miro-footer__bottom{flex-direction:column} }
        @media (max-width:479px) { .miro-footer__top{gap:28px 16px} }
    </style>
</head>
<body>
    @include('partials.miro-header', ['miroCurrentPage' => 'members'])
    @if(false)
    <nav class="miro-nav" id="miro-nav">
        <div class="miro-container miro-nav__inner">
            <a href="{{ url('/') }}" class="miro-brand"><span class="miro-brand__mark">W</span><span>Women</span></a>
            <div class="miro-nav__links" id="miro-nav-links">
                <a href="{{ url('/') }}"><span data-lang="ru">Главная</span><span data-lang="en">Home</span><span data-lang="ro">Acasă</span></a>
                <a href="{{ url('/') }}#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a>
                <a href="{{ url('/') }}#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a>
                <a href="{{ route('members') }}" class="is-active"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a>
                <a href="{{ route('events') }}"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a>
                <a href="{{ url('/') }}#opportunities"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></a>
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

    <main class="miro-members-page">

        <section class="miro-members-section">
            <div class="miro-container">
                <div class="miro-section__head">
                    <h2><span data-lang="ru">Наши эксперты</span><span data-lang="en">Our experts</span><span data-lang="ro">Experții noștri</span></h2>
                    <p><span data-lang="ru">Эксперты, основательницы и лидеры, которые открыты к новым связям, идеям и совместным возможностям.</span><span data-lang="en">Experts, founders and leaders who are open to new connections, ideas and shared opportunities.</span><span data-lang="ro">Experte, fondatoare și lideri deschiși către conexiuni, idei și oportunități comune.</span></p>
                </div>
                <div class="miro-members-grid">
                    @foreach($profiles as $profile)
                        <article class="miro-public-member">
                            <div class="miro-public-member__visual" style="background:var(--miro-{{ $profile['tone'] }});">
                                <img src="{{ asset('images/' . $profile['photo']) }}" alt="{{ $profile['name']['en'] }}" loading="lazy">
                            </div>
                            <div class="miro-public-member__body">
                                <div class="miro-public-member__tags">
                                    @foreach($profile['tags'] as $tag)
                                        <span class="miro-public-member__tag miro-public-member__tag--{{ $profile['tone'] }}">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $tag[$locale] }}</span>@endforeach</span>
                                    @endforeach
                                </div>
                                <h3>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $profile['name'][$locale] }}</span>@endforeach</h3>
                                <p class="miro-public-member__specialization">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $profile['role'][$locale] }}</span>@endforeach</p>
                                <p class="miro-public-member__description">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $profile['description'][$locale] }}</span>@endforeach</p>
                                <div class="miro-public-member__details">
                                    <div class="miro-public-member__detail"><strong><span data-lang="ru">Ищет</span><span data-lang="en">Looking for</span><span data-lang="ro">Caută</span></strong><span>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $profile['looking_for'][$locale] }}</span>@endforeach</span></div>
                                    <div class="miro-public-member__detail"><strong><span data-lang="ru">Может предложить</span><span data-lang="en">Can offer</span><span data-lang="ro">Poate oferi</span></strong><span>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $profile['can_offer'][$locale] }}</span>@endforeach</span></div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <section class="miro-members-cta">
                    <h2><span data-lang="ru">Зарегистрируйтесь, чтобы связаться</span><span data-lang="en">Register to make the connection</span><span data-lang="ro">Înregistrează-te pentru a lua legătura</span></h2>
                    <p><span data-lang="ru">Создайте профиль на платформе, чтобы находить нужных людей и обращаться к ним напрямую.</span><span data-lang="en">Create your platform profile to find the right people and reach out directly.</span><span data-lang="ro">Creează-ți profilul pentru a găsi oamenii potriviți și a lua legătura direct.</span></p>
                    <div class="miro-members-cta__actions">
                        <a href="{{ route('account.login') }}" class="miro-button miro-button--pink"><span data-lang="ru">Войти в кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Intră în cabinet</span></a>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button" style="border:1px solid rgba(255,255,255,.35);color:#fff"><span data-lang="ru">Присоединиться через Telegram</span><span data-lang="en">Join via Telegram</span><span data-lang="ro">Alătură-te prin Telegram</span></a>
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
                <div><h4><span data-lang="ru">Платформа</span><span data-lang="en">Platform</span><span data-lang="ro">Platformă</span></h4><ul><li><a href="{{ url('/') }}#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a></li><li><a href="{{ route('members') }}"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a></li></ul></div>
                <div><h4><span data-lang="ru">Ресурсы</span><span data-lang="en">Resources</span><span data-lang="ro">Resurse</span></h4><ul><li><a href="{{ url('/') }}#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a></li><li><a href="{{ route('events') }}"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a></li></ul></div>
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
