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
        'help' => ['title' => 'AI помощник', 'hint' => 'Помощник в главном меню: как пользоваться ботом и кабинетом. Знания помощника правятся отдельно: «Настройки кабинетов → Помощник бота».'],
        'onboarding' => ['title' => 'Первые шаги', 'hint' => 'Сообщения, которые ведут участницу от регистрации к кабинету: что дальше после заявки, напоминание тем, кто так и не зашёл в кабинет, обновление меню.'],
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
                'ru' => "Здравствуйте! 👋\n\nЭто заявка на участие в Women's hub — цифровом пространстве для женщин-предпринимательниц, где можно представить бизнес, учиться, находить контакты и узнавать о возможностях.\n\nЯ задам несколько коротких вопросов. Это займёт 2-3 минуты.\n\nГотовы начать?",
                'en' => "Hello! 👋\n\nThis is an application to join Women's hub — a digital space for women entrepreneurs where you can present your business, learn, find contacts and discover opportunities.\n\nI will ask a few short questions. It takes 2-3 minutes.\n\nReady to start?",
                'ro' => "Bună ziua! 👋\n\nAceasta este cererea de aderare la Women's hub — un spațiu digital pentru femeile antreprenor, unde puteți să vă prezentați afacerea, să învățați, să găsiți contacte și să aflați despre oportunități.\n\nVoi pune câteva întrebări scurte. Va dura 2-3 minute.\n\nSunteți gata să începeți?",
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
            'when' => 'Последнее сообщение анкеты: заявка сохранена и ждёт решения. Под ним кнопка «Сайт платформы».',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы', 'contact' => 'Telegram команды проекта'],
            'text' => [
                'ru' => "Спасибо, {name}! ✅\n\nЗаявка отправлена. Вот что будет дальше:\n\n1️⃣ Команда проекта проверит профиль вручную.\n2️⃣ Как только заявку одобрят, я сам напишу вам сюда: придёт сообщение с кнопками, а внизу появится меню «Кабинет» и «AI помощник».\n3️⃣ В кабинете вы заполните профиль, увидите материалы платформы и сможете оформить подписку.\n\nПока ждёте, загляните на сайт платформы (кнопка ниже). Писать боту ничего не нужно: уведомление придёт само.\n\nВопрос к команде проекта: {contact}",
                'en' => "Thank you, {name}! ✅\n\nYour application has been submitted. Here is what happens next:\n\n1️⃣ The project team will check your profile by hand.\n2️⃣ As soon as the application is approved, I will write to you here myself: you will get a message with buttons, and the menu with “Cabinet” and “AI assistant” will appear at the bottom.\n3️⃣ In your account you will fill in your profile, see the platform's materials and be able to subscribe.\n\nWhile you wait, have a look at the platform website (the button below). You do not need to write to the bot: the notification will come by itself.\n\nA question for the project team: {contact}",
                'ro' => "Vă mulțumim, {name}! ✅\n\nCererea a fost trimisă. Iată ce urmează:\n\n1️⃣ Echipa proiectului va verifica profilul manual.\n2️⃣ De îndată ce cererea este aprobată, vă scriu eu singur aici: veți primi un mesaj cu butoane, iar jos va apărea meniul „Cabinet” și „Asistent AI”.\n3️⃣ În cabinet veți completa profilul, veți vedea materialele platformei și vă veți putea abona.\n\nCât așteptați, aruncați o privire pe site-ul platformei (butonul de mai jos). Nu trebuie să scrieți botului: notificarea vine singură.\n\nO întrebare pentru echipa proiectului: {contact}",
            ],
        ],
        'registration_done_site_button' => [
            'group' => 'registration',
            'title' => 'Кнопка «Сайт платформы»',
            'when' => 'Под сообщением «Заявка отправлена»: открывает сайт платформы.',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => '🌐 Сайт платформы', 'en' => '🌐 Platform website', 'ro' => '🌐 Site-ul platformei'],
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
                'ru' => "🎉 {name}, ваша заявка одобрена.\n\nДобро пожаловать в Women's hub. Вам открыт личный кабинет с уровнем доступа Open: материалы и информация с сайта платформы.\n\nКаталог участниц, поиск контактов, ИИ-помощник и публикации возможностей входят в подписку WOMEN’S HUB COMMUNITY. Оформить её можно в разделе «Подписка» личного кабинета.\n\nЗаполните профиль подробнее, чтобы вас лучше понимали другие участницы.",
                'en' => "🎉 {name}, your application has been approved.\n\nWelcome to Women's hub. Your account is open at the Open access level: materials and information from the platform website.\n\nThe members directory, contact search, the AI assistant and publishing opportunities are part of the WOMEN’S HUB COMMUNITY subscription. You can subscribe in the “Subscription” section of your account.\n\nFill in your profile in more detail so that other members understand you better.",
                'ro' => "🎉 {name}, cererea dvs. a fost aprobată.\n\nBine ați venit la Women's hub. Cabinetul dvs. este deschis la nivelul de acces Open: materiale și informații de pe site-ul platformei.\n\nCatalogul membrelor, căutarea de contacte, asistentul AI și publicarea de oportunități fac parte din abonamentul WOMEN’S HUB COMMUNITY. Vă puteți abona în secțiunea „Abonament” din cabinetul personal.\n\nCompletați profilul mai detaliat, pentru ca celelalte membre să vă înțeleagă mai bine.",
            ],
        ],
        'moderation_approved_guide_prompt' => [
            'group' => 'moderation',
            'title' => 'Приглашение войти в кабинет',
            'when' => 'Второе сообщение сразу после «Заявка одобрена»; под ним кнопки «Открыть кабинет» и «С чего начать?».',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Чтобы войти в личный кабинет, нажмите кнопку «Открыть кабинет» ниже. Если не знаете, с чего начать, нажмите «С чего начать?».',
                'en' => 'To enter your account, tap “Open my account” below. Not sure where to start? Tap “Where to start?”.',
                'ro' => 'Pentru a intra în cabinetul personal, apăsați „Deschide cabinetul” mai jos. Nu știți de unde să începeți? Apăsați „De unde să începeți?”.',
            ],
        ],
        'cabinet_open_button' => [
            'group' => 'moderation',
            'title' => 'Кнопка «Открыть кабинет»',
            'when' => 'Под сообщением «Приглашение войти в кабинет» и под памяткой «С чего начать»: открывает кабинет прямо в Telegram, без ссылки и пароля.',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => 'Открыть кабинет', 'en' => 'Open my account', 'ro' => 'Deschide cabinetul'],
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
                'ru' => "{name}, спасибо за интерес к Women's hub.\n\nСейчас ваша заявка не была одобрена. Если хотите уточнить детали или задать вопрос команде проекта, напишите: @lesnichenkoP",
                'en' => "{name}, thank you for your interest in Women's hub.\n\nAt the moment your application has not been approved. If you would like to clarify the details or ask the project team a question, write to: @lesnichenkoP",
                'ro' => "{name}, vă mulțumim pentru interesul față de Women's hub.\n\nÎn acest moment cererea dvs. nu a fost aprobată. Dacă doriți să aflați detalii sau să puneți o întrebare echipei proiectului, scrieți la: @lesnichenkoP",
            ],
        ],
        'moderation_access_revoked' => [
            'group' => 'moderation',
            'title' => 'Доступ закрыт',
            'when' => 'Администратор закрыл доступ уже одобренной участнице. Главное меню убирается.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "Доступ к Women's hub закрыт.\n\nЕсли у вас есть вопросы по участию, напишите команде проекта: @lesnichenkoP",
                'en' => "Access to Women's hub has been closed.\n\nIf you have questions about participation, write to the project team: @lesnichenkoP",
                'ro' => "Accesul la Women's hub a fost închis.\n\nDacă aveți întrebări despre participare, scrieți echipei proiectului: @lesnichenkoP",
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
                'ru' => "Здравствуйте, {name}! Перейдите по ссылке ниже, чтобы открыть личный кабинет Women's hub.\n\n🔐 {url}\n\n⏱ Ссылка действует 24 часа.",
                'en' => "Hello, {name}! Follow the link below to open your Women's hub account.\n\n🔐 {url}\n\n⏱ The link is valid for 24 hours.",
                'ro' => "Bună ziua, {name}! Accesați linkul de mai jos pentru a intra în cabinetul Women's hub.\n\n🔐 {url}\n\n⏱ Linkul este valabil 24 de ore.",
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
                'ru' => "Здравствуйте, {name}! Нажмите кнопку ниже, чтобы войти в личный кабинет Women's hub.\n\n⏱ Ссылка действует 24 часа.",
                'en' => "Hello, {name}! Press the button below to open your Women's hub account.\n\n⏱ The link is valid for 24 hours.",
                'ro' => "Bună ziua, {name}! Apăsați butonul de mai jos pentru a intra în cabinetul Women's hub.\n\n⏱ Linkul este valabil 24 de ore.",
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
                'ru' => "Как начать работу с Women's hub?\n\n1. Откройте личный кабинет\nНажмите кнопку «Открыть кабинет» под этим сообщением. Если кнопки нет, отправьте команду /login: бот пришлёт ссылку для входа.\n\n2. Заполните профиль\nРасскажите, что вы представляете, что ищете и чем можете быть полезны другим участницам.\n\n3. Ищите контакты и возможности\nС подпиской WOMEN’S HUB COMMUNITY платформа помогает находить предпринимательниц, запросы, предложения, события и полезные материалы.\n\n4. Оставайтесь на связи\nTelegram будет присылать важные обновления, приглашения и публикации сообщества.",
                'en' => "How to get started with Women's hub?\n\n1. Open your account\nTap “Open my account” under this message. If there is no button, send /login and the bot will send you a sign-in link.\n\n2. Fill in your profile\nTell us what you represent, what you are looking for and how you can be useful to other members.\n\n3. Look for contacts and opportunities\nWith a WOMEN’S HUB COMMUNITY subscription, the platform helps you find entrepreneurs, requests, offers, events and useful materials.\n\n4. Stay in touch\nTelegram will send you important updates, invitations and community publications.",
                'ro' => "Cum începeți să lucrați cu Women's hub?\n\n1. Deschideți cabinetul personal\nApăsați „Deschide cabinetul” sub acest mesaj. Dacă nu există butonul, trimiteți comanda /login: botul vă va trimite linkul de autentificare.\n\n2. Completați profilul\nPovestiți pe cine reprezentați, ce căutați și cu ce puteți fi utilă altor membre.\n\n3. Căutați contacte și oportunități\nCu abonamentul WOMEN’S HUB COMMUNITY, platforma vă ajută să găsiți antreprenoare, solicitări, oferte, evenimente și materiale utile.\n\n4. Rămâneți în legătură\nTelegram vă va trimite actualizări importante, invitații și publicații ale comunității.",
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
                'ru' => "✅ Ваш профиль удалён.\n\nЕсли вы захотите снова присоединиться к Women's hub, откройте @WomenComBot и отправьте /start.",
                'en' => "✅ Your profile has been deleted.\n\nIf you want to join Women's hub again, open @WomenComBot and send /start.",
                'ro' => "✅ Profilul dvs. a fost șters.\n\nDacă doriți să vă alăturați din nou Women's hub, deschideți @WomenComBot și trimiteți /start.",
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
            'inactive' => 'Убрана из меню: теперь в меню «Кабинет» и «AI помощник». Подпись оставлена, чтобы старая клавиатура на телефоне продолжала работать, пока не обновится.',
            'trigger' => true,
            'vars' => [],
            'text' => ['ru' => '🔎 Найти контакты', 'en' => '🔎 Find contacts', 'ro' => '🔎 Găsește contacte'],
        ],
        'menu_chat_button' => [
            'group' => 'menu',
            'title' => 'Кнопка меню «Чат сообщества»',
            'when' => 'Главное меню одобренной участницы. По этой подписи бот узнаёт нажатие, поэтому у кнопок она должна быть разной.',
            'kind' => 'button',
            'inactive' => 'Убрана из меню: теперь в меню «Кабинет» и «AI помощник». Подпись оставлена, чтобы старая клавиатура на телефоне получила новое меню при нажатии.',
            'trigger' => true,
            'vars' => [],
            'text' => ['ru' => '💬 Чат сообщества', 'en' => '💬 Community chat', 'ro' => '💬 Chatul comunității'],
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

        // ---------------------------------------------------------------- AI помощник
        'menu_cabinet_button' => [
            'group' => 'menu',
            'title' => 'Кнопка меню «Кабинет»',
            'when' => 'Главное меню одобренной участницы. На сайте с https открывает кабинет прямо в Telegram, где такой кнопки нет — бот присылает ссылку для входа. По этой подписи бот узнаёт нажатие, поэтому у кнопок она должна различаться.',
            'kind' => 'button',
            'trigger' => true,
            'vars' => [],
            'text' => ['ru' => '🏠 Кабинет', 'en' => '🏠 Cabinet', 'ro' => '🏠 Cabinet'],
        ],
        'menu_help_button' => [
            'group' => 'menu',
            'title' => 'Кнопка меню «AI помощник»',
            'when' => 'Главное меню одобренной участницы: начинает разговор с помощником, который объясняет, как пользоваться ботом и кабинетом. По этой подписи бот узнаёт нажатие, поэтому у кнопок она должна различаться.',
            'kind' => 'button',
            'trigger' => true,
            'vars' => [],
            'text' => ['ru' => '🤖 AI помощник', 'en' => '🤖 AI assistant', 'ro' => '🤖 Asistent AI'],
        ],
        'help_exit_button' => [
            'group' => 'help',
            'title' => 'Кнопка «Закрыть помощника»',
            'when' => 'Клавиатура внизу, пока идёт разговор с помощником: возвращает главное меню.',
            'kind' => 'button',
            'trigger' => true,
            'vars' => [],
            'text' => ['ru' => '✖️ Закрыть помощника', 'en' => '✖️ Close assistant', 'ro' => '✖️ Închide asistentul'],
        ],
        'help_intro' => [
            'group' => 'help',
            'title' => 'Приветствие помощника',
            'when' => 'Участница нажала «AI помощник» (или «Спросить помощника» под сообщением бота).',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы', 'contact' => 'Telegram команды проекта'],
            'text' => [
                'ru' => "🤖 {name}, я помощник Women's hub.\n\nСпросите меня, как пользоваться ботом и личным кабинетом: как войти, что где находится, чем отличаются тарифы, как оформить подписку.\n\nНапример:\n• Как войти в кабинет?\n• Что даёт подписка Community?\n• Как изменить свой профиль?\n\nПросто напишите вопрос своими словами. Когда закончите, нажмите «Закрыть помощника» внизу.\n\nЕсли я не смогу помочь, напишите команде проекта: {contact}",
                'en' => "🤖 {name}, I am the Women's hub assistant.\n\nAsk me how to use the bot and your personal account: how to sign in, what is where, how the plans differ, how to subscribe.\n\nFor example:\n• How do I enter my account?\n• What does the Community subscription give?\n• How do I change my profile?\n\nJust write your question in your own words. When you are done, tap “Close assistant” at the bottom.\n\nIf I cannot help, write to the project team: {contact}",
                'ro' => "🤖 {name}, sunt asistentul Women's hub.\n\nÎntrebați-mă cum se folosește botul și cabinetul personal: cum intrați, ce unde se află, prin ce diferă tarifele, cum vă abonați.\n\nDe exemplu:\n• Cum intru în cabinet?\n• Ce oferă abonamentul Community?\n• Cum îmi schimb profilul?\n\nScrieți întrebarea cu cuvintele dvs. Când terminați, apăsați „Închide asistentul” din partea de jos.\n\nDacă nu pot ajuta, scrieți echipei proiectului: {contact}",
            ],
        ],
        'help_exit' => [
            'group' => 'help',
            'title' => 'Помощник закрыт',
            'when' => 'Участница закрыла помощника кнопкой «Закрыть помощника»: возвращается главное меню.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Помощник закрыт. Кнопки «Кабинет» и «AI помощник» всегда внизу: если понадобится подсказка, нажмите «AI помощник».',
                'en' => 'The assistant is closed. The “Cabinet” and “AI assistant” buttons are always at the bottom: if you need a hint, tap “AI assistant”.',
                'ro' => 'Asistentul este închis. Butoanele „Cabinet” și „Asistent AI” sunt mereu jos: dacă aveți nevoie de o indicație, apăsați „Asistent AI”.',
            ],
        ],
        'help_unavailable' => [
            'group' => 'help',
            'title' => 'Помощник недоступен',
            'when' => 'Провайдер ИИ не отвечает или не настроен.',
            'kind' => 'text',
            'vars' => ['contact' => 'Telegram команды проекта'],
            'text' => [
                'ru' => 'Помощник сейчас не отвечает. Попробуйте через несколько минут или напишите команде проекта: {contact}',
                'en' => 'The assistant is not responding right now. Try again in a few minutes or write to the project team: {contact}',
                'ro' => 'Asistentul nu răspunde acum. Încercați peste câteva minute sau scrieți echipei proiectului: {contact}',
            ],
        ],
        'help_limit' => [
            'group' => 'help',
            'title' => 'Лимит вопросов помощнику',
            'when' => 'Участница задала помощнику больше вопросов за сутки, чем разрешено.',
            'kind' => 'text',
            'vars' => ['limit' => 'Сколько вопросов в сутки', 'contact' => 'Telegram команды проекта'],
            'text' => [
                'ru' => 'На сегодня вопросов помощнику достаточно ({limit} в сутки). Завтра он снова ответит. Если вопрос срочный, напишите команде проекта: {contact}',
                'en' => 'That is enough questions for the assistant today ({limit} per day). It will answer again tomorrow. If it is urgent, write to the project team: {contact}',
                'ro' => 'Pentru azi sunt destule întrebări către asistent ({limit} pe zi). Mâine va răspunde din nou. Dacă este urgent, scrieți echipei proiectului: {contact}',
            ],
        ],
        'help_text_only' => [
            'group' => 'help',
            'title' => 'Помощник понимает только текст',
            'when' => 'В разговоре с помощником участница прислала не текст (фото, голосовое, стикер).',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Я понимаю только текстовые вопросы. Напишите словами, что вас интересует.',
                'en' => 'I only understand text questions. Please write in words what you are interested in.',
                'ro' => 'Înțeleg doar întrebări în text. Scrieți în cuvinte ce vă interesează.',
            ],
        ],
        'help_ask_button' => [
            'group' => 'help',
            'title' => 'Кнопка «Спросить помощника»',
            'when' => 'Под сообщениями бота об одобрении заявки и напоминанием о кабинете: начинает разговор с помощником.',
            'kind' => 'button',
            'vars' => [],
            'text' => ['ru' => '🤖 Спросить помощника', 'en' => '🤖 Ask the assistant', 'ro' => '🤖 Întreabă asistentul'],
        ],

        // ---------------------------------------------------------------- Первые шаги
        'menu_updated' => [
            'group' => 'onboarding',
            'title' => 'Меню обновилось',
            'when' => 'Участница нажала кнопку прежнего меню («Найти контакты», «Чат сообщества»), а также команда `php artisan bot:refresh-menu` присылает это сообщение всем одобренным, чтобы у них появилось новое меню.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы'],
            'text' => [
                'ru' => "✨ {name}, меню бота обновилось.\n\nТеперь внизу две кнопки:\n🏠 «Кабинет» — открывает ваш личный кабинет прямо в Telegram, без паролей.\n🤖 «AI помощник» — подскажет, как всем пользоваться: вход, профиль, тарифы.\n\nНе знаете, с чего начать? Откройте кабинет и заполните профиль.",
                'en' => "✨ {name}, the bot menu has been updated.\n\nNow there are two buttons at the bottom:\n🏠 “Cabinet” opens your personal account right in Telegram, no passwords.\n🤖 “AI assistant” tells you how to use everything: signing in, profile, plans.\n\nNot sure where to start? Open the account and fill in your profile.",
                'ro' => "✨ {name}, meniul botului s-a actualizat.\n\nAcum sunt două butoane jos:\n🏠 „Cabinet” deschide cabinetul personal chiar în Telegram, fără parole.\n🤖 „Asistent AI” vă spune cum se folosește totul: intrarea, profilul, tarifele.\n\nNu știți de unde să începeți? Deschideți cabinetul și completați profilul.",
            ],
        ],
        'cabinet_reminder' => [
            'group' => 'onboarding',
            'title' => 'Напоминание о кабинете',
            'when' => 'Заявку одобрили два дня назад и больше, а участница так и не открыла кабинет: сообщение уходит один раз (`php artisan bot:remind-cabinet`, раз в сутки).',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы'],
            'text' => [
                'ru' => "{name}, ваш кабинет Women's hub ждёт вас 🌷\n\nВы уже участница, но ещё не заходили в кабинет. Это займёт минуту: нажмите «Открыть кабинет», заполните профиль, и платформа сможет подбирать для вас контакты и возможности.\n\nНе получается войти или не знаете, с чего начать? Спросите помощника: он объяснит по шагам.",
                'en' => "{name}, your Women's hub account is waiting for you 🌷\n\nYou are already a member, but you have not opened your account yet. It takes a minute: tap “Open my account”, fill in your profile, and the platform will be able to match contacts and opportunities for you.\n\nCannot sign in or not sure where to start? Ask the assistant: it will explain step by step.",
                'ro' => "{name}, cabinetul dvs. Women's hub vă așteaptă 🌷\n\nSunteți deja membră, dar nu ați deschis încă cabinetul. Durează un minut: apăsați „Deschide cabinetul”, completați profilul, iar platforma va putea selecta contacte și oportunități pentru dvs.\n\nNu reușiți să intrați sau nu știți de unde să începeți? Întrebați asistentul: vă explică pas cu pas.",
            ],
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
