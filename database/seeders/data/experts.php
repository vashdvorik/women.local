<?php

/**
 * Эксперты, перенесённые из массива `$profiles` страницы experts.blade.php (тема miro), и добавленные позже.
 * Источник для ExpertSeeder (пустая база) и ExpertRosterSeeder (база, где эксперты уже есть); фото — относительно
 * public/themes/public/miro/images.
 *
 * Необязательные ключи записи:
 *  - 'crop'          => ['x', 'y', 'width', 'height'] — квадрат кадра в пикселях исходного фото (иначе центр кадра);
 *  - 'photo_rev'     => N — версия фото: ExpertRosterSeeder один раз заменит фото уже заведённого эксперта этим файлом.
 *                       Файл в теме перерисовали (или заменили) — поднимите номер, и на сервере фото заменится ещё раз.
 *                       Фото, которое потом поменяли в админке, до следующего подъёма номера не трогается. Без ключа
 *                       фото существующего эксперта не меняется никогда: на сайт идёт картинка из public/uploads, и
 *                       правка файла в теме её не обновит.
 *  - 'move_to_end'   => N — ExpertRosterSeeder один раз поставит уже заведённого эксперта на последнее место списка
 *                       (N — номер версии: подняли — переставит ещё раз; порядок, который потом поменяли в админке,
 *                       до этого не трогается). На пустой базе порядок и так задаёт порядок записей в этом файле,
 *                       поэтому такую запись держите в нём последней.
 * Порядок на сайте — порядок записей; новые эксперты ставятся в конец.
 */
return [
        [
            'photo' => 'experts/expert-carolina.png',
            'photo_rev' => 2,
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
            'photo' => 'experts/expert-aurelia-portrait.webp',
            'photo_rev' => 2,
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
            'photo' => 'experts/expert-vlada.webp',
            'photo_rev' => 3,
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
            'photo' => 'experts/expert-mariana.png',
            'photo_rev' => 2,
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
            'photo' => 'experts/expert-valeria.webp',
            'photo_rev' => 3,
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
            'photo' => 'experts/expert-irena.png',
            'photo_rev' => 2,
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
            'photo_rev' => 2,
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
            'photo' => 'experts/expert-diana.webp',
            'photo_rev' => 3,
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
            'photo' => 'experts/expert-olga-melnichuk.webp',
            'photo_rev' => 3,
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
            'photo_rev' => 2,
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
        [
            'photo' => 'experts/expert-oxana.webp',
            'name' => ['ru' => 'Оксана Паладичук', 'en' => 'Oxana Paladiciuc', 'ro' => 'Oxana Paladiciuc'],
            'role' => ['ru' => 'Руководитель FinTech HUB', 'en' => 'Head of FinTech HUB', 'ro' => 'Directoarea FinTech HUB'],
            'specialization' => ['ru' => 'Финтех, стартапы и инновации', 'en' => 'Fintech, startups & innovation', 'ro' => 'Fintech, startup-uri și inovație'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'FinTech', 'en' => 'FinTech', 'ro' => 'FinTech'],
                ['ru' => 'Стартапы', 'en' => 'Startups', 'ro' => 'Startup-uri'],
            ],
            'description' => ['ru' => 'Специалист в области финтех-экосистем, развития стартапов и инноваций.', 'en' => 'Specialist in fintech ecosystems, startup development and innovation.', 'ro' => 'Specialistă în ecosisteme fintech, dezvoltarea startup-urilor și inovație.'],
            'tone' => 'teal',
        ],
        [
            'photo' => 'experts/expert-viorica.webp',
            'name' => ['ru' => 'Виорика Чербушка', 'en' => 'Viorica Cherbushka', 'ro' => 'Viorica Cerbușca'],
            'role' => ['ru' => 'Генеральный директор Artcor', 'en' => 'General Director of Artcor', 'ro' => 'Directoarea generală a Artcor'],
            'specialization' => ['ru' => 'Стартап-экосистема и креативные индустрии', 'en' => 'Startup ecosystem & creative industries', 'ro' => 'Ecosistemul startup și industriile creative'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'Стартапы', 'en' => 'Startups', 'ro' => 'Startup-uri'],
                ['ru' => 'Креативные индустрии', 'en' => 'Creative industries', 'ro' => 'Industrii creative'],
            ],
            'description' => ['ru' => 'Развивает стартап-экосистему и креативные индустрии в Республике Молдова.', 'en' => 'Develops the startup ecosystem and creative industries in the Republic of Moldova.', 'ro' => 'Dezvoltă ecosistemul de startup-uri și industriile creative din Republica Moldova.'],
            'tone' => 'rose',
        ],
        [
            'photo' => 'experts/expert-larisa.webp',
            'name' => ['ru' => 'Лариса Чепой', 'en' => 'Larisa Chepoi', 'ro' => 'Larisa Cepoi'],
            'role' => ['ru' => 'Амбассадор международной школы коучинга Noble Manhattan Coaching', 'en' => 'Ambassador of the Noble Manhattan Coaching international school of coaching', 'ro' => 'Ambasadoare a școlii internaționale de coaching Noble Manhattan Coaching'],
            'specialization' => ['ru' => 'Коучинг и бизнес-процессы', 'en' => 'Coaching & business processes', 'ro' => 'Coaching și procese de afaceri'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'Коучинг', 'en' => 'Coaching', 'ro' => 'Coaching'],
                ['ru' => 'Бизнес-процессы', 'en' => 'Business processes', 'ro' => 'Procese de afaceri'],
            ],
            'description' => ['ru' => 'Помогает предпринимателям и топ-менеджерам настраивать бизнес-процессы и достигать личных целей.', 'en' => 'Helps entrepreneurs and top managers set up business processes and reach personal goals.', 'ro' => 'Ajută antreprenorii și managerii de top să-și pună la punct procesele de afaceri și să-și atingă obiectivele personale.'],
            'tone' => 'coral',
        ],
        [
            'photo' => 'experts/expert-kristina.webp',
            'name' => ['ru' => 'Кристина Цуркан', 'en' => 'Kristina Tsurkan', 'ro' => 'Cristina Țurcan'],
            'role' => ['ru' => 'Основательница Woman Up и управляющая бизнес-сообществом Tractor', 'en' => 'Founder of Woman Up and manager of the Tractor business community', 'ro' => 'Fondatoarea Woman Up și coordonatoarea comunității de afaceri Tractor'],
            'specialization' => ['ru' => 'Женское лидерство и личностный рост', 'en' => 'Women’s leadership & personal growth', 'ro' => 'Leadership feminin și dezvoltare personală'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'Женское лидерство', 'en' => 'Women’s leadership', 'ro' => 'Leadership feminin'],
                ['ru' => 'Сообщества', 'en' => 'Communities', 'ro' => 'Comunități'],
            ],
            'description' => ['ru' => 'Продвигает идеи женского лидерства и личностного роста.', 'en' => 'Promotes the ideas of women’s leadership and personal growth.', 'ro' => 'Promovează ideile de leadership feminin și dezvoltare personală.'],
            'tone' => 'blue',
        ],
        [
            'photo' => 'experts/expert-inna.webp',
            'name' => ['ru' => 'Инна Барышникова', 'en' => 'Inna Baryshnikova', 'ro' => 'Inna Barîșnicova'],
            'role' => ['ru' => 'Финансовый эксперт, ментор и общественный деятель', 'en' => 'Financial expert, mentor and public figure', 'ro' => 'Expertă financiară, mentoră și personalitate publică'],
            'specialization' => ['ru' => 'Финансы, менторство и малый бизнес', 'en' => 'Finance, mentoring & small business', 'ro' => 'Finanțe, mentorat și afaceri mici'],
            'tags' => [
                ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['ru' => 'Финансы', 'en' => 'Finance', 'ro' => 'Finanțe'],
                ['ru' => 'Менторство', 'en' => 'Mentoring', 'ro' => 'Mentorat'],
            ],
            'description' => ['ru' => 'Помогает развивать малый бизнес и поддерживает социальные проекты.', 'en' => 'Helps small businesses grow and supports social projects.', 'ro' => 'Ajută la dezvoltarea afacerilor mici și sprijină proiecte sociale.'],
            'tone' => 'orange',
        ],
        [
            'photo' => 'experts/expert-olga-levitskaya.webp',
            'photo_rev' => 3,
            'move_to_end' => 1,
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
    ];
