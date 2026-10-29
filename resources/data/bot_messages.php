<?php

/*
|--------------------------------------------------------------------------
| Сообщения Telegram-бота
|--------------------------------------------------------------------------
|
| Бот только присылает уведомления и рассылки: регистрации, меню, поиска и входа в нём нет, кабинет открывается
| на сайте по почте. ВСЕ тексты, которые бот и сайт отправляют участницам в Telegram, лежат здесь и больше нигде:
| подключение Telegram, решения по анкете, уведомления о подписке и описание команды /start.
| Код берёт их через App\Support\BotMessages; захардкоженных текстов в коде нет (это проверяет тест
| BotMessagesSourceTest). Текст рассылок администратор пишет сам при создании рассылки.
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
|   required — переменные, без которых сообщение теряет смысл;
|   trigger — подпись кнопки меню, по которой бот узнаёт нажатие: у всех языков подписи должны различаться;
|   inactive — причина, по которой сообщение сейчас не отправляется (в админке помечается «Не используется»);
|   text   — сам текст на ru / en / ro. Язык выбирается по языку участницы, по умолчанию ru.
|
| Переносы строк — обычные (\n). Символы < > & вне тегов пишутся как &lt; &gt; &amp;.
|
*/

return [

    'groups' => [
        'link' => ['title' => 'Подключение Telegram', 'hint' => 'Участница подключает Telegram по ссылке из кабинета («Мой профиль → Подключить Telegram»). Ответы бота на подключение и на любое другое сообщение.'],
        'moderation' => ['title' => 'Решение по анкете', 'hint' => 'Сообщения, которые уходят участнице с подключённым Telegram, когда администратор одобряет или отклоняет анкету либо закрывает доступ. Без Telegram то же приходит письмом.'],
        'subscription' => ['title' => 'Подписка', 'hint' => 'Сообщения о тарифе: подписка включена, напоминания об окончании. Тарифы: Community, Active и Private (платные, на год). Без Telegram то же приходит письмом.'],
        'menu' => ['title' => 'Команды', 'hint' => 'Описание команды /start в меню Telegram.'],
    ],

    'messages' => [

        // ---------------------------------------------------------------- Подключение Telegram
        'link_success' => [
            'group' => 'link',
            'title' => 'Telegram подключён',
            'when' => 'Участница перешла по ссылке из кабинета, и бот привязал её чат к кабинету.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы'],
            'text' => [
                'ru' => "✅ {name}, Telegram подключён.\n\nСюда будут приходить уведомления: оплата и окончание тарифа, подходящие знакомства и рассылки платформы. Войти в кабинет через Telegram нельзя: вход только по почте на сайте.",
                'en' => "✅ {name}, Telegram is connected.\n\nNotifications will arrive here: payments and the end of your plan, matching contacts and platform newsletters. You cannot sign in through Telegram: sign-in is by email on the website only.",
                'ro' => "✅ {name}, Telegram este conectat.\n\nAici vor veni notificările: plata și sfârșitul tarifului, contactele potrivite și newslettere ale platformei. Nu vă puteți autentifica prin Telegram: intrarea se face doar prin e-mail pe site.",
            ],
        ],
        'link_invalid' => [
            'group' => 'link',
            'title' => 'Ссылка подключения не подошла',
            'when' => 'Участница открыла ссылку подключения, которая устарела (действует час) или уже использована.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Ссылка для подключения не подошла или устарела. Откройте кабинет на сайте и нажмите «Подключить Telegram» ещё раз.',
                'en' => 'The connection link did not work or has expired. Open your account on the website and press “Connect Telegram” again.',
                'ro' => 'Linkul de conectare nu a funcționat sau a expirat. Deschideți cabinetul pe site și apăsați din nou „Conectează Telegram”.',
            ],
        ],
        'link_conflict' => [
            'group' => 'link',
            'title' => 'Telegram уже подключён к другому кабинету',
            'when' => 'Этот Telegram уже привязан к другой участнице.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => 'Этот Telegram уже подключён к другому кабинету. Отключите его там («Мой профиль → Отключить») и подключите заново.',
                'en' => 'This Telegram is already connected to another account. Disconnect it there (“My profile → Disconnect”) and connect again.',
                'ro' => 'Acest Telegram este deja conectat la un alt cabinet. Deconectați-l acolo („Profilul meu → Deconectează”) și conectați-l din nou.',
            ],
        ],
        'start_info' => [
            'group' => 'link',
            'title' => 'Ответ на любое сообщение',
            'when' => 'Кто-то пишет боту /start без ссылки подключения или любой другой текст.',
            'kind' => 'text',
            'vars' => ['url' => 'Адрес сайта платформы'],
            'text' => [
                'ru' => "Этот бот присылает уведомления и рассылки Women's hub. Меню и входа здесь нет.\n\nЧтобы подключить уведомления, откройте кабинет на сайте {url} и нажмите «Подключить Telegram» в разделе «Мой профиль».",
                'en' => "This bot sends Women's hub notifications and newsletters. There is no menu or sign-in here.\n\nTo connect notifications, open your account on the website {url} and press “Connect Telegram” in “My profile”.",
                'ro' => "Acest bot trimite notificările și newsletterele Women's hub. Aici nu există meniu și nici autentificare.\n\nPentru a conecta notificările, deschideți cabinetul pe site {url} și apăsați „Conectează Telegram” în „Profilul meu”.",
            ],
        ],

        // ---------------------------------------------------------------- Решение по анкете
        'moderation_approved' => [
            'group' => 'moderation',
            'title' => 'Анкета одобрена',
            'when' => 'Администратор одобрил анкету участницы.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы', 'url' => 'Ссылка на кабинет'],
            'text' => [
                'ru' => "🎉 {name}, ваша анкета одобрена.\n\nТеперь другие участницы платформы вас видят. Заходите в кабинет:\n{url}",
                'en' => "🎉 {name}, your profile has been approved.\n\nOther members of the platform can now see you. Open your account:\n{url}",
                'ro' => "🎉 {name}, profilul dvs. a fost aprobat.\n\nAcum celelalte membre ale platformei vă văd. Intrați în cabinet:\n{url}",
            ],
        ],
        'moderation_rejected' => [
            'group' => 'moderation',
            'title' => 'Анкета отклонена',
            'when' => 'Администратор отклонил анкету.',
            'kind' => 'text',
            'vars' => ['name' => 'Имя участницы'],
            'text' => [
                'ru' => "{name}, спасибо за интерес к Women's hub.\n\nСейчас ваша анкета не была одобрена. Если хотите уточнить детали или задать вопрос команде проекта, напишите: @lesnichenkoP",
                'en' => "{name}, thank you for your interest in Women's hub.\n\nAt the moment your profile has not been approved. If you would like to clarify the details or ask the project team a question, write to: @lesnichenkoP",
                'ro' => "{name}, vă mulțumim pentru interesul față de Women's hub.\n\nÎn acest moment profilul dvs. nu a fost aprobat. Dacă doriți să aflați detalii sau să puneți o întrebare echipei proiectului, scrieți la: @lesnichenkoP",
            ],
        ],
        'moderation_access_revoked' => [
            'group' => 'moderation',
            'title' => 'Доступ закрыт',
            'when' => 'Администратор закрыл доступ уже одобренной участнице.',
            'kind' => 'text',
            'vars' => [],
            'text' => [
                'ru' => "Доступ к Women's hub закрыт.\n\nЕсли у вас есть вопросы по участию, напишите команде проекта: @lesnichenkoP",
                'en' => "Access to Women's hub has been closed.\n\nIf you have questions about participation, write to the project team: @lesnichenkoP",
                'ro' => "Accesul la Women's hub a fost închis.\n\nDacă aveți întrebări despre participare, scrieți echipei proiectului: @lesnichenkoP",
            ],
        ],

        // ---------------------------------------------------------------- Подписка
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
                'ru' => "🎉 Подписка <b>{plan}</b> включена до {until}.\n\nКабинет открыт. Управлять подпиской можно здесь:\n{url}",
                'en' => "🎉 Your <b>{plan}</b> subscription is active until {until}.\n\nYour account is open. You can manage your subscription here:\n{url}",
                'ro' => "🎉 Abonamentul <b>{plan}</b> este activ până la {until}.\n\nCabinetul este deschis. Îți poți gestiona abonamentul aici:\n{url}",
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
                'ru' => "⏳ Подписка <b>{plan}</b> заканчивается {until} (осталось дней: {days}).\n\nЧтобы не потерять доступ, продлите её:\n{url}",
                'en' => "⏳ Your <b>{plan}</b> subscription ends on {until} ({days} days left).\n\nRenew it to keep your access:\n{url}",
                'ro' => "⏳ Abonamentul <b>{plan}</b> se încheie la {until} (mai sunt {days} zile).\n\nReînnoiți-l pentru a păstra accesul:\n{url}",
            ],
        ],
        'subscription_expired' => [
            'group' => 'subscription',
            'title' => 'Подписка закончилась',
            'when' => 'Срок подписки истёк: кабинет закрылся до нового тарифа.',
            'kind' => 'text',
            'vars' => [
                'plan' => 'Название тарифа, который закончился',
                'url' => 'Ссылка на страницу «Подписка» в кабинете',
            ],
            'text' => [
                'ru' => "Подписка <b>{plan}</b> закончилась. Кабинет закрыт до выбора тарифа.\n\nВаш профиль сохранён. Выбрать тариф можно в любой момент:\n{url}",
                'en' => "Your <b>{plan}</b> subscription has ended. Your account is closed until you choose a plan.\n\nYour profile is saved. You can choose a plan at any time:\n{url}",
                'ro' => "Abonamentul <b>{plan}</b> s-a încheiat. Cabinetul este închis până la alegerea unui tarif.\n\nProfilul dvs. este păstrat. Puteți alege un tarif oricând:\n{url}",
            ],
        ],

        // ---------------------------------------------------------------- Команды
        'menu_command_start' => [
            'group' => 'menu',
            'title' => 'Описание команды /start',
            'when' => 'Подпись в меню команд Telegram (кнопка «Меню» у поля ввода). После сохранения обновляется в Telegram автоматически.',
            'kind' => 'command',
            'vars' => [],
            'text' => ['ru' => 'Как подключить уведомления', 'en' => 'How to connect notifications', 'ro' => 'Cum conectezi notificările'],
        ],

    ],
];
