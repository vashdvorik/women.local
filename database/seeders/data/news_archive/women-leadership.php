<?php

// Новость: бизнес-форум «Женское лидерство» (6 ноября 2026, Тирасполь). Тексты на трёх языках; фото — public/themes/public/miro/images/news/archive/women-leadership/.
// Загружает NewsArchiveSeeder. Абзацы на всех языках идут в одном порядке и в одном числе: структура страницы общая.
// Абзацы с дефисом — пункты списка (программа, спикеры), на всех языках они стоят на одних и тех же местах.
// 01.webp — обложка (кадр афиши 16:9: заголовок форума и героиня), 02.webp — афиша целиком (картинка на странице).
// Ссылки на регистрацию в тексте пока нет: полный адрес формы нужно добавить абзацем «Регистрация: …» после абзаца «Участие …».
// Дата в будущем: до дня форума новость показывается у участниц в «Ближайших событиях».

return [
    'date' => '2026-11-06',
    'type' => 'forum',
    'merge' => null,
    'title' => [
        'ru' => 'Бизнес-форум «Женское лидерство»',
        'ro' => 'Forumul de afaceri „Leadership feminin”',
        'en' => 'Business forum “Women’s Leadership”',
    ],
    'description' => [
        'ru' => 'От идеи к реализации: возможности для роста женского бизнеса. 6 ноября 2026 года, 15:00–17:30, Park Hotel, Тирасполь.',
        'ro' => 'De la idee la realizare: oportunități pentru dezvoltarea afacerilor conduse de femei. 6 noiembrie 2026, 15:00–17:30, Park Hotel, Tiraspol.',
        'en' => 'From idea to implementation: opportunities for growing women’s business. 6 November 2026, 15:00–17:30, Park Hotel, Tiraspol.',
    ],
    'body' => [
        'ru' => [
            'Как превратить бизнес-идею в успешный проект, использовать современные технологии для развития бизнеса и находить новые возможности для роста?',
            'Приглашаем женщин-предпринимателей на бизнес-форум «Женское лидерство» — площадку для обмена опытом, профессионального общения и новых деловых знакомств.',
            'В программе форума:',
            '- Панельная дискуссия с женщинами-предпринимательницами о пути от бизнес-идеи к её реализации.',
            '- Практические выступления о возможностях искусственного интеллекта в бизнесе, устойчивом лидерстве и перспективах женского предпринимательства.',
            '- Нетворкинг, развитие деловых связей и презентация продукции участниц проекта.',
            'Спикеры форума:',
            '- Инна Швец — эксперт в сфере здоровья и развития, автор образовательных и социальных проектов.',
            '- Ирина Плешкова — консультант по искусственному интеллекту, специалист по оптимизации труда с использованием AI-инструментов.',
            '- Валерия Зелинская — предприниматель, fashion-эксперт, основательница магазина Lemone.md.',
            '- Модератор — Влада Лысенко, доктор юридических наук, профессор, эксперт по международному сотрудничеству и развитию.',
            'Дата: 6 ноября 2026 года.',
            'Время: 15:00–17:30.',
            'Место: Park Hotel, Тирасполь, пер. Набережный, 3.',
            'Участие — возможность получить новые знания, обменяться практическим опытом и стать частью сообщества женщин, которые развивают бизнес и создают новые возможности.',
            'Мероприятие реализуется Агентством инноваций и развития при поддержке Структуры «ООН-женщины» UN Women Moldova и финансируется Женским фондом мира и гуманитарной помощи (Women’s Peace & Humanitarian Fund).',
        ],
        'ro' => [
            'Cum să transformi o idee de afaceri într-un proiect de succes, să folosești tehnologiile moderne pentru dezvoltarea afacerii și să găsești noi oportunități de creștere?',
            'Invităm femeile antreprenoare la forumul de afaceri „Leadership feminin” — un spațiu pentru schimb de experiență, comunicare profesională și noi cunoștințe de afaceri.',
            'În programul forumului:',
            '- Discuție în panel cu femei antreprenoare despre drumul de la ideea de afaceri la realizarea ei.',
            '- Prezentări practice despre oportunitățile inteligenței artificiale în afaceri, leadershipul sustenabil și perspectivele antreprenoriatului feminin.',
            '- Networking, dezvoltarea relațiilor de afaceri și prezentarea produselor participantelor la proiect.',
            'Vorbitorii forumului:',
            '- Inna Șvet — expertă în domeniul sănătății și dezvoltării, autoare de proiecte educaționale și sociale.',
            '- Irina Pleșcova — consultantă în inteligență artificială, specialistă în optimizarea muncii cu instrumente AI.',
            '- Valeria Zelinskaia — antreprenoare, expertă în modă, fondatoarea magazinului Lemone.md.',
            '- Moderatoare — Vlada Lîsenco, doctor în drept, profesoară, expertă în cooperare și dezvoltare internațională.',
            'Data: 6 noiembrie 2026.',
            'Ora: 15:00–17:30.',
            'Locul: Park Hotel, Tiraspol, str. Naberejnîi, 3.',
            'Participarea este o ocazie de a obține cunoștințe noi, de a face schimb de experiență practică și de a deveni parte a comunității femeilor care își dezvoltă afacerea și creează noi oportunități.',
            'Evenimentul este organizat de Agenția pentru Inovare și Dezvoltare cu sprijinul Entității ONU Femei UN Women Moldova și este finanțat de Fondul pentru Pace și Ajutor Umanitar al Femeilor (Women’s Peace & Humanitarian Fund).',
        ],
        'en' => [
            'How do you turn a business idea into a successful project, use modern technology to grow your business and find new opportunities for growth?',
            'We invite women entrepreneurs to the business forum “Women’s Leadership” — a place to share experience, network professionally and make new business connections.',
            'The forum programme:',
            '- A panel discussion with women entrepreneurs on the path from a business idea to its implementation.',
            '- Practical talks on the opportunities of artificial intelligence in business, sustainable leadership and the prospects of women’s entrepreneurship.',
            '- Networking, building business connections and a presentation of products by the project participants.',
            'Forum speakers:',
            '- Inna Shvets — an expert in health and development, author of educational and social projects.',
            '- Irina Pleshkova — an artificial intelligence consultant and specialist in optimising work with AI tools.',
            '- Valeria Zelinskaya — an entrepreneur, fashion expert and founder of the Lemone.md store.',
            '- Moderator — Vlada Lysenko, Doctor of Law, professor, expert in international cooperation and development.',
            'Date: 6 November 2026.',
            'Time: 15:00–17:30.',
            'Venue: Park Hotel, Tiraspol, 3 Naberezhny Lane.',
            'Taking part is a chance to gain new knowledge, share practical experience and become part of a community of women who grow their businesses and create new opportunities.',
            'The event is implemented by the Agency for Innovation and Development with the support of UN Women Moldova and is funded by the Women’s Peace & Humanitarian Fund.',
        ],
    ],
];
