<?php

/*
|--------------------------------------------------------------------------
| Сообщения Telegram-бота
|--------------------------------------------------------------------------
|
| ВСЕ тексты, которые бот и сайт отправляют участницам в Telegram, лежат здесь и больше нигде:
| диалоги, статусы заявки, уведомления, рассылка, подписи кнопок и описания команд.
| Код берёт их через App\Support\BotMessages; захардкоженных текстов в коде нет (это проверяет тест
| BotMessagesSourceTest).
|
| Этот файл — эталон. Администратор правит тексты в админке («Кабинеты участниц → Сообщения бота»);
| правки хранятся в базе (site_settings.bot_messages) и накладываются поверх эталона, поэтому не
| затираются при `git pull`. Кнопка «Вернуть исходный» в админке возвращает текст из этого файла.
| Новое сообщение добавляется здесь: оно сразу появится в админке.
|
| Формат записи:
|
|   group  — раздел в админке (см. 'groups');
|   title  — название в админке; when — когда бот отправляет это сообщение;
|   kind   — text (Telegram-HTML: <b>, <i>, <u>, <s>, <code>, <a href="…">), button (подпись кнопки,
|            одна строка без HTML) или command (описание команды в меню Telegram);
|   vars   — переменные, доступные в тексте как {имя}; значение — подсказка для администратора;
|   raw    — какие из них код уже собрал как безопасный HTML (остальные экранируются автоматически);
|   required — переменные, без которых сообщение теряет смысл (например, {url} у ссылки для входа);
|   trigger — подпись кнопки меню, по которой бот узнаёт нажатие: у всех языков подписи должны различаться;
|   inactive — причина, по которой сообщение сейчас не отправляется (в админке помечается «Не используется»);
|   text   — сам текст на ru / en / ro. Язык выбирается по языку Telegram участницы, по умолчанию ru.
|
| Переносы строк — обычные (\n). Символы < > & вне тегов пишутся как &lt; &gt; &amp;.
|
*/

return [

    'groups' => [
        'registration' => ['title' => 'Заявка на участие', 'hint' => 'Диалог, который бот ведёт с новой участницей: от приветствия до «заявка отправлена».'],
        'status' => ['title' => 'Статус заявки', 'hint' => 'Ответы бота тем, чья заявка ещё рассматривается или отклонена.'],
        'moderation' => ['title' => 'Решение по заявке', 'hint' => 'Сообщения, которые уходят участнице, когда администратор одобряет или отклоняет заявку либо закрывает доступ.'],
        'login' => ['title' => 'Вход в кабинет', 'hint' => 'Ссылки для входа в личный кабинет: из бота и с сайта.'],
        'guide' => ['title' => 'С чего начать', 'hint' => 'Памятка после одобрения заявки.'],
        'search' => ['title' => 'Поиск контактов', 'hint' => 'Диалог поиска участниц с помощью ИИ и карточки результатов.'],
        'broadcast' => ['title' => 'Рассылка о новых публикациях', 'hint' => 'Уведомление всем одобренным участницам, когда в кабинете появляется новая публикация.'],
        'site' => ['title' => 'После действий на сайте', 'hint' => 'Сообщения в бот, которые отправляет сайт.'],
        'menu' => ['title' => 'Меню и команды', 'hint' => 'Кнопки главного меню и описания команд в меню Telegram.'],
        'subscription' => ['title' => 'Подписка', 'hint' => 'Сообщения о тарифе: подписка включена, напоминания об окончании, закрытые функции. Тарифы: Open (бесплатно), Community и Private (платные, на год).'],
    ],

    'messages' => [

        // ---------------------------------------------------------------- Заявка на участие
        'registration_welcome' => [
            'group' => 'registration',
            'title' => 'Приветствие',
            'when' => 'Человек впервые пишет боту или отправляет /start.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "Здравствуйте! 👋\n\nЭто заявка на участие в Women Entrepreneurs Platform of the Two Banks — цифровом пространстве для женщин-предпринимательниц, где можно представить бизнес, учиться, находить контакты и узнавать о возможностях.\n\nЯ задам несколько коротких вопросов. Это займёт 2-3 минуты.\n\nГотовы начать?",
                'en' => "Hello! 👋\n\nThis is an application to join Women Entrepreneurs Platform of the Two Banks — a digital space for women entrepreneurs where you can present your business, learn, find contacts and discover opportunities.\n\nI will ask a few short questions. It takes 2-3 minutes.\n\nReady to start?",
                'ro' => "Bună ziua! 👋\n\nAceasta este cererea de aderare la Women Entrepreneurs Platform of the Two Banks — un spațiu digital pentru femeile antreprenor, unde puteți să vă prezentați afacerea, să învățați, să găsiți contacte și să aflați despre oportunități.\n\nVoi pune câteva întrebări scurte. Va dura 2-3 minute.\n\nSunteți gata să începeți?",
            ],
        ],
        'registration_start_button' => [
            'group' => 'registration',
            'title' => 'Кнопка «Начать» под приветствием',
            'when' => 'Показывается под приветствием.',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => 'Да, начать', 'en' => "Yes, let's start", 'ro' => 'Da, să începem'],
        ],
        'registration_ask_name' => [
            'group' => 'registration',
            'title' => 'Вопрос: как вас зовут',
            'when' => 'После нажатия «Начать».',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Как вас зовут? Укажите имя и фамилию.',
                'en' => 'What is your name? Please enter your first and last name.',
                'ro' => 'Cum vă numiți? Indicați numele și prenumele.',
            ],
        ],
        'registration_name_required' => [
            'group' => 'registration',
            'title' => 'Напоминание: имя нужно текстом',
            'when' => 'Вместо имени пришло не текстовое сообщение (фото, стикер).',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Пожалуйста, отправьте имя и фамилию текстом.',
                'en' => 'Please send your first and last name as text.',
                'ro' => 'Vă rugăm să trimiteți numele și prenumele sub formă de text.',
            ],
        ],
        'registration_ask_description' => [
            'group' => 'registration',
            'title' => 'Вопрос: что вы представляете',
            'when' => 'После имени.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "Что вы представляете?\n\nРасскажите о бизнесе, сфере, продуктах, услугах, опыте или идее. Можно добавить ссылку.",
                'en' => "What do you represent?\n\nTell us about your business, field, products, services, experience or idea. You can add a link.",
                'ro' => "Pe cine reprezentați?\n\nPovestiți despre afacere, domeniu, produse, servicii, experiență sau idee. Puteți adăuga un link.",
            ],
        ],
        'registration_description_required' => [
            'group' => 'registration',
            'title' => 'Напоминание: описание нужно текстом',
            'when' => 'Вместо описания пришло не текстовое сообщение.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Пожалуйста, опишите ваш бизнес, опыт или идею текстом.',
                'en' => 'Please describe your business, experience or idea in text.',
                'ro' => 'Vă rugăm să descrieți afacerea, experiența sau ideea sub formă de text.',
            ],
        ],
        'registration_ask_expectation' => [
            'group' => 'registration',
            'title' => 'Вопрос: что вы ищете',
            'when' => 'После описания; под сообщением кнопка «Пропустить».',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "Что вы ищете на платформе и чем можете быть полезны другим участницам?\n\nНапример: партнёры, клиенты, поставщики, знания, менторство, новые рынки, услуги или опыт.",
                'en' => "What are you looking for on the platform, and how can you be useful to other members?\n\nFor example: partners, clients, suppliers, knowledge, mentoring, new markets, services or experience.",
                'ro' => "Ce căutați pe platformă și cu ce puteți fi utilă altor membre?\n\nDe exemplu: parteneri, clienți, furnizori, cunoștințe, mentorat, piețe noi, servicii sau experiență.",
            ],
        ],
        'registration_skip_button' => [
            'group' => 'registration',
            'title' => 'Кнопка «Пропустить»',
            'when' => 'Под вопросом «что вы ищете».',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => 'Пропустить', 'en' => 'Skip', 'ro' => 'Omiteți'],
        ],
        'registration_done' => [
            'group' => 'registration',
            'title' => 'Заявка отправлена',
            'when' => 'Последнее сообщение анкеты: заявка сохранена и ждёт решения.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы', 'site_url' => 'Адрес сайта платформы'],
            'text' => [
                'ru' => "Спасибо, {name}!\n\nЗаявка отправлена на рассмотрение. После одобрения вы получите доступ к личному кабинету. Каталог участниц, поиск контактов и другие возможности сообщества входят в платную подписку.\n\nЕсли есть вопрос, напишите команде проекта: @lesnichenkoP\n\nСайт платформы:\n{site_url}",
                'en' => "Thank you, {name}!\n\nYour application has been submitted for review. Once approved, you will get access to your account. The members directory, contact search and other community opportunities are part of the paid subscription.\n\nIf you have a question, write to the project team: @lesnichenkoP\n\nPlatform website:\n{site_url}",
                'ro' => "Vă mulțumim, {name}!\n\nCererea a fost trimisă spre examinare. După aprobare, veți primi acces la cabinetul personal. Catalogul membrelor, căutarea de contacte și celelalte oportunități ale comunității fac parte din abonamentul plătit.\n\nDacă aveți o întrebare, scrieți echipei proiectului: @lesnichenkoP\n\nSite-ul platformei:\n{site_url}",
            ],
        ],

        // ---------------------------------------------------------------- Статус заявки
        'status_pending' => [
            'group' => 'status',
            'title' => 'Заявка на рассмотрении',
            'when' => 'Участница с ещё не рассмотренной заявкой пишет боту.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы'],
            'text' => [
                'ru' => "{name}, ваша заявка уже на рассмотрении.\n\nКоманда проекта проверит профиль и откроет доступ после одобрения. Если есть вопрос, напишите: @lesnichenkoP",
                'en' => "{name}, your application is already under review.\n\nThe project team will check your profile and open access after approval. If you have a question, write to: @lesnichenkoP",
                'ro' => "{name}, cererea dvs. este deja în curs de examinare.\n\nEchipa proiectului va verifica profilul și va deschide accesul după aprobare. Dacă aveți o întrebare, scrieți la: @lesnichenkoP",
            ],
        ],
        'status_rejected' => [
            'group' => 'status',
            'title' => 'Доступ закрыт',
            'when' => 'Пишет участница, заявка которой отклонена или доступ закрыт.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "Доступ к платформе закрыт.\n\nЕсли у вас есть вопросы по заявке или участию, напишите команде проекта: @lesnichenkoP",
                'en' => "Access to the platform is closed.\n\nIf you have questions about your application or participation, write to the project team: @lesnichenkoP",
                'ro' => "Accesul la platformă este închis.\n\nDacă aveți întrebări despre cerere sau participare, scrieți echipei proiectului: @lesnichenkoP",
            ],
        ],

        // ---------------------------------------------------------------- Решение по заявке
        'moderation_approved' => [
            'group' => 'moderation',
            'title' => 'Заявка одобрена',
            'when' => 'Администратор одобрил профиль участницы. Вместе с сообщением появляется главное меню.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы'],
            'text' => [
                'ru' => "🎉 {name}, ваша заявка одобрена.\n\nДобро пожаловать в Women Entrepreneurs Platform of the Two Banks. Вам открыт личный кабинет с уровнем доступа Open: материалы и информация с сайта платформы.\n\nКаталог участниц, поиск контактов, ИИ-помощник и публикации возможностей входят в подписку WOMEN’S HUB COMMUNITY. Оформить её можно в разделе «Подписка» личного кабинета.\n\nЗаполните профиль подробнее, чтобы вас лучше понимали другие участницы.",
                'en' => "🎉 {name}, your application has been approved.\n\nWelcome to Women Entrepreneurs Platform of the Two Banks. Your account is open at the Open access level: materials and information from the platform website.\n\nThe members directory, contact search, the AI assistant and publishing opportunities are part of the WOMEN’S HUB COMMUNITY subscription. You can subscribe in the “Subscription” section of your account.\n\nFill in your profile in more detail so that other members understand you better.",
                'ro' => "🎉 {name}, cererea dvs. a fost aprobată.\n\nBine ați venit la Women Entrepreneurs Platform of the Two Banks. Cabinetul dvs. este deschis la nivelul de acces Open: materiale și informații de pe site-ul platformei.\n\nCatalogul membrelor, căutarea de contacte, asistentul AI și publicarea de oportunități fac parte din abonamentul WOMEN’S HUB COMMUNITY. Vă puteți abona în secțiunea „Abonament” din cabinetul personal.\n\nCompletați profilul mai detaliat, pentru ca celelalte membre să vă înțeleagă mai bine.",
            ],
        ],
        'moderation_approved_guide_prompt' => [
            'group' => 'moderation',
            'title' => 'Вопрос «С чего начать?»',
            'when' => 'Второе сообщение сразу после «Заявка одобрена», с кнопкой ниже.',
            'kind' => 'text',
            'vars' => [],
            'text' => ['ru' => 'С чего начать?', 'en' => 'Where to start?', 'ro' => 'De unde să începeți?'],
        ],
        'moderation_approved_guide_button' => [
            'group' => 'moderation',
            'title' => 'Кнопка «С чего начать?»',
            'when' => 'Под вопросом «С чего начать?»; открывает памятку.',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => 'С чего начать? →', 'en' => 'Where to start? →', 'ro' => 'De unde să începeți? →'],
        ],
        'moderation_rejected' => [
            'group' => 'moderation',
            'title' => 'Заявка отклонена',
            'when' => 'Администратор отклонил заявку.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы'],
            'text' => [
                'ru' => "{name}, спасибо за интерес к Women Entrepreneurs Platform of the Two Banks.\n\nСейчас ваша заявка не была одобрена. Если хотите уточнить детали или задать вопрос команде проекта, напишите: @lesnichenkoP",
                'en' => "{name}, thank you for your interest in Women Entrepreneurs Platform of the Two Banks.\n\nAt the moment your application has not been approved. If you would like to clarify the details or ask the project team a question, write to: @lesnichenkoP",
                'ro' => "{name}, vă mulțumim pentru interesul față de Women Entrepreneurs Platform of the Two Banks.\n\nÎn acest moment cererea dvs. nu a fost aprobată. Dacă doriți să aflați detalii sau să puneți o întrebare echipei proiectului, scrieți la: @lesnichenkoP",
            ],
        ],
        'moderation_access_revoked' => [
            'group' => 'moderation',
            'title' => 'Доступ закрыт',
            'when' => 'Администратор закрыл доступ уже одобренной участнице. Главное меню убирается.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "Доступ к Women Entrepreneurs Platform of the Two Banks закрыт.\n\nЕсли у вас есть вопросы по участию, напишите команде проекта: @lesnichenkoP",
                'en' => "Access to Women Entrepreneurs Platform of the Two Banks has been closed.\n\nIf you have questions about participation, write to the project team: @lesnichenkoP",
                'ro' => "Accesul la Women Entrepreneurs Platform of the Two Banks a fost închis.\n\nDacă aveți întrebări despre participare, scrieți echipei proiectului: @lesnichenkoP",
            ],
        ],

        // ---------------------------------------------------------------- Вход в кабинет
        'login_link' => [
            'group' => 'login',
            'title' => 'Ссылка для входа (из бота)',
            'when' => 'Одобренная участница отправляет /start или /login: ссылка идёт прямо в тексте.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы', 'url' => 'Ссылка для входа (действует 24 часа)'],
            'required' => ['url'],
            'text' => [
                'ru' => "Здравствуйте, {name}! Перейдите по ссылке ниже, чтобы открыть личный кабинет Women Entrepreneurs Platform of the Two Banks.\n\n🔐 {url}\n\n⏱ Ссылка действует 24 часа.",
                'en' => "Hello, {name}! Follow the link below to open your Women Entrepreneurs Platform of the Two Banks account.\n\n🔐 {url}\n\n⏱ The link is valid for 24 hours.",
                'ro' => "Bună ziua, {name}! Accesați linkul de mai jos pentru a intra în cabinetul Women Entrepreneurs Platform of the Two Banks.\n\n🔐 {url}\n\n⏱ Linkul este valabil 24 de ore.",
            ],
        ],
        'login_not_approved' => [
            'group' => 'login',
            'title' => 'Вход недоступен',
            'when' => '/login от человека, чья заявка не одобрена или не подана.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Вход доступен только одобренным участницам платформы. Чтобы подать заявку, отправьте /start.',
                'en' => 'Login is available only to approved members of the platform. To apply, send /start.',
                'ro' => 'Accesul este disponibil doar membrelor aprobate ale platformei. Pentru a depune o cerere, trimiteți /start.',
            ],
        ],
        'login_site_message' => [
            'group' => 'login',
            'title' => 'Вход с сайта: сообщение',
            'when' => 'Участница ввела свой @username на странице входа сайта; ссылка приходит кнопкой под сообщением.',
            'inactive' => 'На странице входа сейчас нет формы с @username, поэтому это сообщение не отправляется. Оно заработает, если такой вход вернётся.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы'],
            'text' => [
                'ru' => "Здравствуйте, {name}! Нажмите кнопку ниже, чтобы войти в личный кабинет Women Entrepreneurs Platform of the Two Banks.\n\n⏱ Ссылка действует 24 часа.",
                'en' => "Hello, {name}! Press the button below to open your Women Entrepreneurs Platform of the Two Banks account.\n\n⏱ The link is valid for 24 hours.",
                'ro' => "Bună ziua, {name}! Apăsați butonul de mai jos pentru a intra în cabinetul Women Entrepreneurs Platform of the Two Banks.\n\n⏱ Linkul este valabil 24 de ore.",
            ],
        ],
        'login_site_button' => [
            'group' => 'login',
            'title' => 'Вход с сайта: кнопка',
            'when' => 'Кнопка со ссылкой под сообщением «Вход с сайта».',
            'inactive' => 'На странице входа сейчас нет формы с @username, поэтому эта кнопка не отправляется. Она заработает, если такой вход вернётся.',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => '🔐 Войти в кабинет →', 'en' => '🔐 Open account →', 'ro' => '🔐 Intră în cabinet →'],
        ],

        // ---------------------------------------------------------------- С чего начать
        'guide_start' => [
            'group' => 'guide',
            'title' => 'Памятка «С чего начать»',
            'when' => 'Участница нажала «С чего начать? →» после одобрения заявки.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "Как начать работу с Women Entrepreneurs Platform of the Two Banks?\n\n1. Откройте личный кабинет\nИспользуйте кнопку входа или ссылку из этого бота.\n\n2. Заполните профиль\nРасскажите, что вы представляете, что ищете и чем можете быть полезны другим участницам.\n\n3. Ищите контакты и возможности\nС подпиской WOMEN’S HUB COMMUNITY платформа помогает находить предпринимательниц, запросы, предложения, события и полезные материалы.\n\n4. Оставайтесь на связи\nTelegram будет присылать важные обновления, приглашения и публикации сообщества.",
                'en' => "How to get started with Women Entrepreneurs Platform of the Two Banks?\n\n1. Open your account\nUse the login button or the link from this bot.\n\n2. Fill in your profile\nTell us what you represent, what you are looking for and how you can be useful to other members.\n\n3. Look for contacts and opportunities\nWith a WOMEN’S HUB COMMUNITY subscription, the platform helps you find entrepreneurs, requests, offers, events and useful materials.\n\n4. Stay in touch\nTelegram will send you important updates, invitations and community publications.",
                'ro' => "Cum începeți să lucrați cu Women Entrepreneurs Platform of the Two Banks?\n\n1. Deschideți cabinetul personal\nFolosiți butonul de autentificare sau linkul din acest bot.\n\n2. Completați profilul\nPovestiți pe cine reprezentați, ce căutați și cu ce puteți fi utilă altor membre.\n\n3. Căutați contacte și oportunități\nCu abonamentul WOMEN’S HUB COMMUNITY, platforma vă ajută să găsiți antreprenoare, solicitări, oferte, evenimente și materiale utile.\n\n4. Rămâneți în legătură\nTelegram vă va trimite actualizări importante, invitații și publicații ale comunității.",
            ],
        ],

        // ---------------------------------------------------------------- Поиск контактов
        'search_intro' => [
            'group' => 'search',
            'title' => 'Подсказка поиска',
            'when' => 'Участница нажала «Найти контакты».',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "🔍 <b>Поиск контактов</b>\n\nОпишите, кого или какую экспертизу вы ищете. AI поможет сориентироваться в профилях участниц и предложит близкие варианты.\n\n<i>Например:\n· ищу партнёрку для экспорта\n· нужен эксперт по маркетингу\n· хочу найти поставщиков упаковки\n· ищу ментора по финансам</i>",
                'en' => "🔍 <b>Contact search</b>\n\nDescribe who or what expertise you are looking for. AI will help you navigate the members' profiles and suggest close matches.\n\n<i>For example:\n· looking for an export partner\n· need a marketing expert\n· want to find packaging suppliers\n· looking for a finance mentor</i>",
                'ro' => "🔍 <b>Căutare de contacte</b>\n\nDescrieți pe cine sau ce expertiză căutați. AI vă va ajuta să vă orientați în profilurile membrelor și va propune variante apropiate.\n\n<i>De exemplu:\n· caut un partener pentru export\n· am nevoie de un expert în marketing\n· vreau să găsesc furnizori de ambalaje\n· caut un mentor în finanțe</i>",
            ],
        ],
        'search_query_required' => [
            'group' => 'search',
            'title' => 'Напоминание: запрос нужен текстом',
            'when' => 'Вместо запроса пришло не текстовое сообщение.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Пожалуйста, напишите текстом, кого или какую поддержку вы ищете.',
                'en' => 'Please write in text who or what kind of support you are looking for.',
                'ro' => 'Vă rugăm să scrieți, sub formă de text, pe cine sau ce fel de sprijin căutați.',
            ],
        ],
        'search_in_progress' => [
            'group' => 'search',
            'title' => 'Идёт поиск',
            'when' => 'Сразу после запроса, пока ИИ ищет.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => '⏳ Ищу подходящие профили...',
                'en' => '⏳ Looking for suitable profiles...',
                'ro' => '⏳ Caut profiluri potrivite...',
            ],
        ],
        'search_profile_missing' => [
            'group' => 'search',
            'title' => 'Профиль не найден',
            'when' => 'Поиск запросил человек, которого нет среди участниц.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => '⚠️ Профиль не найден. Откройте @WomenComBot и отправьте /start, чтобы подать заявку.',
                'en' => '⚠️ Profile not found. Open @WomenComBot and send /start to apply.',
                'ro' => '⚠️ Profilul nu a fost găsit. Deschideți @WomenComBot și trimiteți /start pentru a depune o cerere.',
            ],
        ],
        'search_no_results' => [
            'group' => 'search',
            'title' => 'Ничего не найдено',
            'when' => 'По запросу нет подходящих профилей.',
            'kind' => 'text',
            'vars' => ['query' => 'Запрос участницы'],
            'text' => [
                'ru' => "По запросу «{query}» пока нет близких результатов.\n\nПопробуйте переформулировать: укажите сферу, задачу, тип контакта или формат сотрудничества.",
                'en' => "No close matches for “{query}” yet.\n\nTry rephrasing: specify the field, the task, the type of contact or the cooperation format.",
                'ro' => "Pentru solicitarea «{query}» nu există încă rezultate apropiate.\n\nÎncercați să reformulați: indicați domeniul, sarcina, tipul de contact sau formatul de colaborare.",
            ],
        ],
        'search_unavailable' => [
            'group' => 'search',
            'title' => 'Поиск недоступен',
            'when' => 'ИИ-провайдер не ответил или вернул ошибку.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => '⚠️ Поиск сейчас недоступен. Попробуйте ещё раз позже.',
                'en' => '⚠️ Search is unavailable right now. Please try again later.',
                'ro' => '⚠️ Căutarea nu este disponibilă acum. Încercați din nou mai târziu.',
            ],
        ],
        'search_result' => [
            'group' => 'search',
            'title' => 'Карточка результата: заголовок',
            'when' => 'Верх карточки найденной участницы. Ниже бот сам добавляет её описание.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя найденной участницы', 'percent' => 'Процент совпадения (число)'],
            'text' => [
                'ru' => "👤 <b>{name}</b>\nСовпадение по профилю: <b>{percent}%</b>",
                'en' => "👤 <b>{name}</b>\nProfile match: <b>{percent}%</b>",
                'ro' => "👤 <b>{name}</b>\nPotrivire cu profilul: <b>{percent}%</b>",
            ],
        ],
        'search_result_expectation' => [
            'group' => 'search',
            'title' => 'Карточка результата: «что ищет»',
            'when' => 'Добавляется в конец карточки, если найденная участница указала, что ищет или предлагает.',
            'kind' => 'text',
            'vars' => ['text' => 'Что ищет или предлагает найденная участница'],
            'required' => ['text'],
            'text' => [
                'ru' => '🔎 <b>Ищет или предлагает:</b> {text}',
                'en' => '🔎 <b>Looking for or offering:</b> {text}',
                'ro' => '🔎 <b>Caută sau oferă:</b> {text}',
            ],
        ],
        'search_write_button' => [
            'group' => 'search',
            'title' => 'Кнопка «Написать»',
            'when' => 'Под карточкой, если у найденной участницы есть @username.',
            'kind' => 'button',
            'vars' => ['username' => 'Telegram-имя без @'],
            'required' => ['username'],
            'text' => ['ru' => 'Написать @{username}', 'en' => 'Message @{username}', 'ro' => 'Scrie @{username}'],
        ],
        'search_more_button' => [
            'group' => 'search',
            'title' => 'Кнопка «Показать ещё»',
            'when' => 'Под первой карточкой, если найдено больше одного профиля.',
            'kind' => 'button',
            'vars' => ['count' => 'Сколько ещё профилей (число)'],
            'text' => ['ru' => 'Показать ещё {count} →', 'en' => 'Show {count} more →', 'ro' => 'Arată încă {count} →'],
        ],

        // ---------------------------------------------------------------- Рассылка о новых публикациях
        'broadcast_opportunity' => [
            'group' => 'broadcast',
            'title' => 'Новая публикация',
            'when' => 'Публикация участницы одобрена и появилась в кабинете: рассылка всем одобренным участницам, кроме автора.',
            'kind' => 'text',
            'vars' => [
                'emoji' => 'Значок типа публикации',
                'type' => 'Тип публикации (например, «Проект»)',
                'title' => 'Заголовок публикации',
                'body' => 'Текст публикации (до 300 символов)',
                'details' => 'Дата и место, если указаны (с переносами строк; может быть пустой)',
                'author' => 'Имя автора',
            ],
            'raw' => ['details'],
            'required' => ['title'],
            'text' => [
                'ru' => "🔔 <b>Новая публикация на платформе</b>\n\n{emoji} <b>{type}:</b> {title}\n\n{body}{details}\n\n👤 Опубликовала: {author}",
                'en' => "🔔 <b>New post on the platform</b>\n\n{emoji} <b>{type}:</b> {title}\n\n{body}{details}\n\n👤 Published by: {author}",
                'ro' => "🔔 <b>Publicație nouă pe platformă</b>\n\n{emoji} <b>{type}:</b> {title}\n\n{body}{details}\n\n👤 Publicat de: {author}",
            ],
        ],
        'broadcast_opportunity_author' => [
            'group' => 'broadcast',
            'title' => 'Автор без имени',
            'when' => 'Подставляется вместо имени автора, если оно неизвестно.',
            'kind' => 'text',
            'vars' => [],
            'text' => ['ru' => 'Участница', 'en' => 'A member', 'ro' => 'O membră'],
        ],
        'broadcast_opportunity_button' => [
            'group' => 'broadcast',
            'title' => 'Кнопка «Посмотреть»',
            'when' => 'Под уведомлением; ведёт к публикациям в кабинете.',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => 'Посмотреть в кабинете', 'en' => 'View in your account', 'ro' => 'Vezi în cabinet'],
        ],

        // ---------------------------------------------------------------- После действий на сайте
        'site_profile_deleted' => [
            'group' => 'site',
            'title' => 'Профиль удалён',
            'when' => 'Участница удалила свой профиль в кабинете на сайте.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "✅ Ваш профиль удалён.\n\nЕсли вы захотите снова присоединиться к Women Entrepreneurs Platform of the Two Banks, откройте @WomenComBot и отправьте /start.",
                'en' => "✅ Your profile has been deleted.\n\nIf you want to join Women Entrepreneurs Platform of the Two Banks again, open @WomenComBot and send /start.",
                'ro' => "✅ Profilul dvs. a fost șters.\n\nDacă doriți să vă alăturați din nou Women Entrepreneurs Platform of the Two Banks, deschideți @WomenComBot și trimiteți /start.",
            ],
        ],
        'site_profile_deleted_button' => [
            'group' => 'site',
            'title' => 'Профиль удалён: кнопка',
            'when' => 'Под сообщением «Профиль удалён»; запускает заявку заново.',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => '↩️ Подать заявку снова', 'en' => '↩️ Apply again', 'ro' => '↩️ Aplică din nou'],
        ],

        // ---------------------------------------------------------------- Меню и команды
        'menu_matches_button' => [
            'group' => 'menu',
            'title' => 'Кнопка меню «Найти контакты»',
            'when' => 'Главное меню одобренной участницы. По этой подписи бот узнаёт нажатие, поэтому у кнопок она должна быть разной.',
            'kind' => 'button',
            'trigger' => true,
            'vars' => [],
            'text' => ['ru' => '🔎 Найти контакты', 'en' => '🔎 Find contacts', 'ro' => '🔎 Găsește contacte'],
        ],
        'menu_chat_button' => [
            'group' => 'menu',
            'title' => 'Кнопка меню «Чат сообщества»',
            'when' => 'Главное меню одобренной участницы. По этой подписи бот узнаёт нажатие, поэтому у кнопок она должна быть разной.',
            'kind' => 'button',
            'trigger' => true,
            'vars' => [],
            'text' => ['ru' => '💬 Чат сообщества', 'en' => '💬 Community chat', 'ro' => '💬 Chatul comunității'],
        ],
        'menu_chat_stub' => [
            'group' => 'menu',
            'title' => 'Ответ на «Чат сообщества»',
            'when' => 'Участница нажала «Чат сообщества».',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Чат сообщества будет доступен после подключения команды проекта.',
                'en' => 'The community chat will be available once the project team connects it.',
                'ro' => 'Chatul comunității va fi disponibil după ce echipa proiectului îl va conecta.',
            ],
        ],
        'menu_command_start' => [
            'group' => 'menu',
            'title' => 'Описание команды /start',
            'when' => 'Подпись в меню команд Telegram (кнопка «Меню» у поля ввода). После сохранения обновляется в Telegram автоматически.',
            'kind' => 'command',
            'vars' => [],
            'text' => ['ru' => 'Подать заявку или войти', 'en' => 'Apply or log in', 'ro' => 'Depune o cerere sau autentifică-te'],
        ],
        'menu_command_login' => [
            'group' => 'menu',
            'title' => 'Описание команды /login',
            'when' => 'Подпись в меню команд Telegram. После сохранения обновляется в Telegram автоматически.',
            'kind' => 'command',
            'vars' => [],
            'text' => ['ru' => 'Войти в личный кабинет', 'en' => 'Open your account', 'ro' => 'Intră în cabinetul personal'],
        ],

        // ---------------------------------------------------------------- Подписка
        'plan_required' => [
            'group' => 'subscription',
            'title' => 'Функция только для подписчиц',
            'when' => 'Участница с тарифом Open (бесплатным) нажала «Найти контакты» в боте.',
            'kind' => 'text',
            'vars' => ['url' => 'Ссылка на страницу «Подписка» в кабинете'],
            'text' => [
                'ru' => "🔒 Поиск контактов доступен в подписке <b>WOMEN’S HUB COMMUNITY</b> и выше.\n\nСейчас у вас открытый доступ (Open). Оформить подписку можно в личном кабинете:\n{url}",
                'en' => "🔒 Contact search is available with a <b>WOMEN’S HUB COMMUNITY</b> subscription or higher.\n\nYou currently have open access (Open). You can subscribe in your account:\n{url}",
                'ro' => "🔒 Căutarea de contacte este disponibilă cu abonamentul <b>WOMEN’S HUB COMMUNITY</b> sau superior.\n\nAcum aveți acces deschis (Open). Vă puteți abona în cabinetul personal:\n{url}",
            ],
        ],
        'subscription_activated' => [
            'group' => 'subscription',
            'title' => 'Подписка включена',
            'when' => 'Оплата подтверждена банком или администратор выдал тариф вручную.',
            'kind' => 'text',
            'vars' => [
                'plan' => 'Название тарифа',
                'until' => 'Дата окончания (дд.мм.гггг)',
                'url' => 'Ссылка на страницу «Подписка» в кабинете',
            ],
            'text' => [
                'ru' => "🎉 Подписка <b>{plan}</b> включена до {until}.\n\nТеперь вам доступно всё, что входит в тариф: каталог участниц, поиск контактов, ИИ-помощник и публикации возможностей.\n\nУправлять подпиской можно здесь:\n{url}",
                'en' => "🎉 Your <b>{plan}</b> subscription is active until {until}.\n\nYou now have everything included in the plan: the members directory, contact search, the AI assistant and publishing opportunities.\n\nYou can manage your subscription here:\n{url}",
                'ro' => "🎉 Abonamentul <b>{plan}</b> este activ până la {until}.\n\nAcum aveți tot ce include pachetul: catalogul membrelor, căutarea de contacte, asistentul AI și publicarea de oportunități.\n\nÎți poți gestiona abonamentul aici:\n{url}",
            ],
        ],
        'subscription_reminder' => [
            'group' => 'subscription',
            'title' => 'Подписка скоро закончится',
            'when' => 'За 14 и за 3 дня до окончания подписки (раз в сутки проверяет команда subscriptions:notify).',
            'kind' => 'text',
            'vars' => [
                'plan' => 'Название тарифа',
                'until' => 'Дата окончания (дд.мм.гггг)',
                'days' => 'Сколько дней осталось (число)',
                'url' => 'Ссылка на страницу «Подписка» в кабинете',
            ],
            'text' => [
                'ru' => "⏳ Подписка <b>{plan}</b> заканчивается {until} (осталось дней: {days}).\n\nЧтобы не потерять доступ к сообществу, продлите её:\n{url}",
                'en' => "⏳ Your <b>{plan}</b> subscription ends on {until} ({days} days left).\n\nRenew it to keep your access to the community:\n{url}",
                'ro' => "⏳ Abonamentul <b>{plan}</b> se încheie la {until} (mai sunt {days} zile).\n\nReînnoiți-l pentru a păstra accesul la comunitate:\n{url}",
            ],
        ],
        'subscription_expired' => [
            'group' => 'subscription',
            'title' => 'Подписка закончилась',
            'when' => 'Срок подписки истёк: доступ вернулся на уровень Open.',
            'kind' => 'text',
            'vars' => [
                'plan' => 'Название тарифа, который закончился',
                'url' => 'Ссылка на страницу «Подписка» в кабинете',
            ],
            'text' => [
                'ru' => "Подписка <b>{plan}</b> закончилась. Доступ вернулся на открытый уровень Open.\n\nВаш профиль сохранён. Продлить подписку можно в любой момент:\n{url}",
                'en' => "Your <b>{plan}</b> subscription has ended. Your access is back to the open level (Open).\n\nYour profile is saved. You can renew at any time:\n{url}",
                'ro' => "Abonamentul <b>{plan}</b> s-a încheiat. Accesul a revenit la nivelul deschis (Open).\n\nProfilul dvs. este păstrat. Puteți reînnoi oricând:\n{url}",
            ],
        ],

    ],
];
