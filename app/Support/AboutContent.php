<?php

namespace App\Support;

/**
 * Тексты двух страниц раздела «О нас» и «Люди» на трёх языках: «Приоритеты» (/about/priorities) и «Как стать участницей»
 * (/members/join). Оба написаны заново на основании Положения о платформе Woman’s Hub (App\Support\Regulations): цели,
 * принципы, партнёры, членство, Комиссия, права и обязанности, пакеты, Gala. Остальное взято из того, как платформа
 * работает на самом деле: регистрация по почте, оплата в кабинете картой Агропромбанка на 12 месяцев, что открыто
 * на тарифе Open (600) и что входит в Community и Private (docs/SUBSCRIPTIONS.md, docs/PROJECT_MAP.md).
 *
 * Тексты пакетов (перечни возможностей) здесь не дублируются: страница берёт их из lang/{ru,en,ro}/subscription.php, как
 * и страница «Подписка» в кабинете, а цены из App\Enums\Plan (админка «Подписки → Цена подписки»).
 *
 * Английский и румынский — переводы; их стоит вычитать носителям, прежде чем считать окончательными.
 */
final class AboutContent
{
    /** @return array<string, mixed> */
    public static function priorities(): array
    {
        return [
            'eyebrow' => self::t('О нас', 'About us', 'Despre noi'),
            'title' => self::t('Приоритеты', 'Priorities', 'Priorități'),
            'intro' => self::t(
                'Woman’s Hub создан, чтобы женщины-предприниматели росли вместе: развивали своё дело, находили партнёров и получали поддержку сообщества.',
                'Woman’s Hub exists so that women entrepreneurs can grow together: develop their business, find partners and receive the support of a community.',
                'Woman’s Hub există pentru ca femeile antreprenoare să crească împreună: să-și dezvolte afacerea, să găsească parteneri și să primească sprijinul unei comunități.',
            ),

            'mission' => [
                'eyebrow' => self::t('Миссия', 'Mission', 'Misiune'),
                'title' => self::t('Платформа, где женщины растут вместе', 'A platform where women grow together', 'O platformă în care femeile cresc împreună'),
                'text' => [
                    self::t(
                        'Woman’s Hub — платформа для женщин-предпринимателей и специализированная площадка для объединения женщин, заинтересованных в предпринимательстве, профессиональном развитии, деловом взаимодействии и реализации совместных инициатив.',
                        'Woman’s Hub is a platform for women entrepreneurs and a specialized venue that brings together women interested in entrepreneurship, professional development, business cooperation and joint initiatives.',
                        'Woman’s Hub este o platformă pentru femeile antreprenoare și un spațiu specializat care reunește femeile interesate de antreprenoriat, dezvoltare profesională, colaborare în afaceri și realizarea unor inițiative comune.',
                    ),
                    self::t(
                        'Платформа создаётся и развивается на базе Агентства Инноваций и Развития — её институциональной и организационной основы.',
                        'The platform is created and developed on the basis of the Agency for Innovation and Development, its institutional and organizational foundation.',
                        'Platforma este creată și dezvoltată în cadrul Agenției pentru Inovare și Dezvoltare, baza sa instituțională și organizatorică.',
                    ),
                ],
                'link' => self::t('Читать Положение', 'Read the Regulations', 'Citește Regulamentul'),
            ],

            'goals' => [
                'eyebrow' => self::t('Цели', 'Goals', 'Scopuri'),
                'title' => self::t('Шесть приоритетов платформы', 'Six priorities of the platform', 'Șase priorități ale platformei'),
                'intro' => self::t(
                    'Они записаны в Положении о платформе и определяют всё, что мы делаем: от мероприятий до личного кабинета.',
                    'They are set out in the Regulations of the platform and define everything we do, from events to the personal cabinet.',
                    'Ele sunt consemnate în Regulamentul platformei și definesc tot ce facem, de la evenimente până la cabinetul personal.',
                ),
                'items' => [
                    self::card('sprout',
                        'Развитие женского предпринимательства', 'Developing women’s entrepreneurship', 'Dezvoltarea antreprenoriatului feminin',
                        'Помогаем женщинам развивать своё дело и раскрывать профессиональный потенциал: знаниями, контактами и поддержкой сообщества.',
                        'We help women grow their business and unlock their professional potential through knowledge, contacts and the support of the community.',
                        'Ajutăm femeile să-și dezvolte afacerea și să-și valorifice potențialul profesional prin cunoștințe, contacte și sprijinul comunității.'),
                    self::card('users',
                        'Устойчивое сообщество', 'A lasting community', 'O comunitate stabilă',
                        'Объединяем активных женщин в платформу, на которую можно опереться: закрытое сообщество, где ценят поддержку и обмен опытом.',
                        'We bring active women together in a platform they can rely on: a closed community that values support and the sharing of experience.',
                        'Reunim femeile active într-o platformă pe care se pot baza: o comunitate închisă, în care sunt apreciate sprijinul și schimbul de experiență.'),
                    self::card('link',
                        'Контакты и партнёрство', 'Contacts and partnership', 'Contacte și parteneriat',
                        'Создаём условия для деловых знакомств, партнёрства и совместных проектов: нетворкинг, встречи, подбор полезных контактов.',
                        'We create conditions for business connections, partnership and joint projects: networking, meetings, a selection of useful contacts.',
                        'Creăm condiții pentru cunoștințe de afaceri, parteneriate și proiecte comune: networking, întâlniri, selecția unor contacte utile.'),
                    self::card('graduation',
                        'Знания и консультации', 'Knowledge and consulting', 'Cunoștințe și consultanță',
                        'Даём доступ к информационным, образовательным, экспертным и консультационным возможностям: лекциям, программам, встречам со специалистами.',
                        'We give access to informational, educational, expert and consulting opportunities: lectures, programs, meetings with specialists.',
                        'Oferim acces la oportunități informaționale, educaționale, de expertiză și de consultanță: prelegeri, programe, întâlniri cu specialiști.'),
                    self::card('megaphone',
                        'Продвижение бизнеса и экспертизы', 'Promoting business and expertise', 'Promovarea afacerii și a expertizei',
                        'Помогаем участницам заявить о своём бизнесе, проектах и профессиональной экспертизе: внутри сообщества и в коммуникациях платформы.',
                        'We help members present their businesses, projects and professional expertise within the community and in the platform’s communications.',
                        'Ajutăm participantele să-și facă cunoscute afacerea, proiectele și expertiza profesională, în cadrul comunității și în comunicările platformei.'),
                    self::card('sparkles',
                        'Участие в инициативах', 'Taking part in initiatives', 'Participarea la inițiative',
                        'Вовлекаем женщин в предпринимательские, социальные, креативные и общественные инициативы.',
                        'We involve women in entrepreneurial, social, creative and public initiatives.',
                        'Implicăm femeile în inițiative antreprenoriale, sociale, creative și civice.'),
                ],
            ],

            'principles' => [
                'title' => self::t('На чём держится платформа', 'What the platform is built on', 'Pe ce se sprijină platforma'),
                'items' => [
                    self::card('heart',
                        'Взаимная поддержка', 'Mutual support', 'Sprijin reciproc',
                        'Участницы помогают друг другу советом, контактом и делом.',
                        'Members help each other with advice, introductions and action.',
                        'Participantele se ajută reciproc cu sfaturi, contacte și fapte.'),
                    self::card('shield',
                        'Профессиональная этика', 'Professional ethics', 'Etică profesională',
                        'Честность и ответственность в делах и в общении.',
                        'Honesty and responsibility in business and in communication.',
                        'Onestitate și responsabilitate în afaceri și în comunicare.'),
                    self::card('smile',
                        'Уважение', 'Respect', 'Respect',
                        'К каждой участнице, к партнёрам и к их труду.',
                        'For every member, for partners and for their work.',
                        'Față de fiecare participantă, față de parteneri și față de munca lor.'),
                    self::card('handshake',
                        'Сотрудничество', 'Cooperation', 'Cooperare',
                        'Вместе можно больше, чем в одиночку.',
                        'Together we can do more than alone.',
                        'Împreună putem mai mult decât singure.'),
                    self::card('bulb',
                        'Предпринимательская инициатива', 'Entrepreneurial initiative', 'Inițiativă antreprenorială',
                        'Поддерживаем тех, кто начинает и развивает своё дело.',
                        'We support those who start and grow a business.',
                        'Sprijinim pe cele care încep și dezvoltă o afacere.'),
                ],
            ],

            'people' => [
                'title' => self::t('Кого объединяет Woman’s Hub', 'Who Woman’s Hub brings together', 'Pe cine reunește Woman’s Hub'),
                'intro' => self::t(
                    'Участницей может стать женщина, которая разделяет цели и ценности платформы.',
                    'A woman who shares the goals and values of the platform can become a member.',
                    'Poate deveni participantă orice femeie care împărtășește scopurile și valorile platformei.',
                ),
                'items' => [
                    self::t('женщины-предприниматели', 'women entrepreneurs', 'femei antreprenoare'),
                    self::t('руководители', 'executives', 'manageri'),
                    self::t('специалисты', 'specialists', 'specialiste'),
                    self::t('эксперты', 'experts', 'experte'),
                    self::t('представительницы креативных и социальных направлений', 'representatives of creative and social fields', 'reprezentante ale domeniilor creative și sociale'),
                    self::t('другие активные участницы деловой и общественной жизни', 'other active participants in business and public life', 'alte participante active la viața de afaceri și cea publică'),
                ],
                'link' => self::t('Как стать участницей', 'How to become a member', 'Cum să devii membră'),
            ],

            'foundation' => [
                'title' => self::t('Основатели и партнёры платформы', 'Founders and partners of the platform', 'Fondatorii și partenerii platformei'),
                'intro' => self::t(
                    'Women’s Hub создана по инициативе партнёров, объединённых общей миссией — поддерживать женщин, развивать предпринимательство и создавать новые возможности для сотрудничества.',
                    'Women’s Hub was created on the initiative of partners united by a shared mission: to support women, develop entrepreneurship and create new opportunities for cooperation.',
                    'Women’s Hub a fost creată din inițiativa unor parteneri uniți de o misiune comună: să sprijine femeile, să dezvolte antreprenoriatul și să creeze noi oportunități de colaborare.',
                ),
                'items' => [
                    self::card('building',
                        'Агентство Инноваций и Развития', 'Agency for Innovation and Development', 'Agenția pentru Inovare și Dezvoltare',
                        'Институциональная и организационная основа проекта.',
                        'The institutional and organizational foundation of the project.',
                        'Baza instituțională și organizatorică a proiectului.'),
                    self::card('users',
                        'Ассоциация социальных предпринимателей', 'Association of Social Entrepreneurs', 'Asociația Antreprenorilor Sociali',
                        'Партнёр Woman’s Hub.', 'A partner of Woman’s Hub.', 'Partener al Woman’s Hub.'),
                    self::card('sparkles',
                        'Ассоциация креативных индустрий', 'Association of Creative Industries', 'Asociația Industriilor Creative',
                        'Партнёр Woman’s Hub.', 'A partner of Woman’s Hub.', 'Partener al Woman’s Hub.'),
                    self::card('link',
                        'Дополнительные партнёры', 'Additional partners', 'Parteneri suplimentari',
                        'Партнёры — в соответствии с отдельными соглашениями.',
                        'Partners, under separate agreements.',
                        'Parteneri, în baza unor acorduri separate.'),
                ],
                'includes_title' => self::t('Партнёрское взаимодействие может включать', 'Partnership cooperation may include', 'Colaborarea cu partenerii poate include'),
                'includes' => [
                    self::t('образовательные программы', 'educational programs', 'programe educaționale'),
                    self::t('консультационную и экспертную поддержку', 'consulting and expert support', 'sprijin consultativ și de expertiză'),
                    self::t('развитие предпринимательских инициатив', 'development of entrepreneurial initiatives', 'dezvoltarea inițiativelor antreprenoriale'),
                    self::t('networking', 'networking', 'networking'),
                    self::t('мероприятия', 'events', 'evenimente'),
                    self::t('совместные проекты', 'joint projects', 'proiecte comune'),
                ],
                'link' => self::t('Все партнёры', 'All partners', 'Toți partenerii'),
            ],

            'gala' => [
                'eyebrow' => self::t('Признание', 'Recognition', 'Recunoaștere'),
                'title' => self::t('Гала Премия «Женщина Года»', 'Gala “Woman of the Year”', 'Gala „Woman of the Year”'),
                'text' => [
                    self::t(
                        'Ежегодная Гала Премия «Женщина Года» признаёт женщин, достигших значимых результатов в профессиональной, предпринимательской, общественной, социальной, культурной и других сферах.',
                        'The annual gala award recognizes women who have achieved significant results in professional, entrepreneurial, public, social, cultural and other fields.',
                        'Gala-premiul anual recunoaște femeile care au obținut rezultate semnificative în domeniul profesional, antreprenorial, civic, social, cultural și în alte domenii.',
                    ),
                    self::t(
                        'Участие в Гала Премии «Женщина Года» входит в оба пакета членства, а право номинировать на премию есть у резидентов Woman’s Hub.',
                        'Participation in the Gala is included in both membership packages, and residents of Woman’s Hub have the right to nominate for the award.',
                        'Participarea la Gală este inclusă în ambele pachete de membru, iar rezidentele Woman’s Hub au dreptul să nominalizeze la premiu.',
                    ),
                ],
                'links' => [
                    'regulations' => self::t('Положение о Гала Премии', 'Regulations of the Gala Award', 'Regulamentul Gala-premiului'),
                    'photos' => self::t('Фотогалерея', 'Photo gallery', 'Galerie foto'),
                ],
            ],

            'cta' => [
                'title' => self::t('Присоединяйтесь к Woman’s Hub', 'Join Woman’s Hub', 'Alătură-te Woman’s Hub'),
                'text' => self::t(
                    'Зарегистрируйтесь, выберите тариф и откройте личный кабинет.',
                    'Sign up, choose a plan and open your personal cabinet.',
                    'Înregistrează-te, alege un tarif și deschide cabinetul personal.',
                ),
                'join' => self::t('Как стать участницей', 'How to become a member', 'Cum să devii membră'),
                'regulations' => self::t('Положение', 'Regulations', 'Regulament'),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function join(): array
    {
        return [
            'eyebrow' => self::t('Люди', 'People', 'Oameni'),
            'title' => self::t('Как стать участницей', 'How to become a member', 'Cum să devii membră'),
            'intro' => self::t(
                'Участницей может стать женщина, которая разделяет цели и ценности платформы. Зарегистрируйтесь по почте, заполните анкету, выберите тариф и откройте личный кабинет.',
                'A woman who shares the goals and values of the platform can become a member. Sign up by email, fill in your profile, choose a plan and open your personal cabinet.',
                'Poate deveni participantă orice femeie care împărtășește scopurile și valorile platformei. Înregistrează-te prin e-mail, completează profilul, alege un tarif și deschide cabinetul personal.',
            ),
            'apply' => self::t('Зарегистрироваться', 'Sign up', 'Înregistrează-te'),
            'regulations' => self::t('Читать Положение', 'Read the Regulations', 'Citește Regulamentul'),

            'steps' => [
                'eyebrow' => self::t('Четыре шага', 'Four steps', 'Patru pași'),
                'title' => self::t('Ваш путь в сообщество Women’s Hub', 'Your path to the Women’s Hub community', 'Drumul tău în comunitatea Women’s Hub'),
                'items' => [
                    self::card('telegram',
                        'Зарегистрируйтесь', 'Sign up', 'Înregistrează-te',
                        'Зарегистрируйтесь по почте (или в одно нажатие через Google), подтвердите адрес и заполните анкету: имя, телефон, чем вы занимаетесь и чего ждёте от сообщества.',
                        'Sign up by email (or in one tap with Google), confirm your address and fill in your profile: name, phone, what you do and what you expect from the community.',
                        'Înregistrează-te prin e-mail (sau dintr-o atingere cu Google), confirmă adresa și completează profilul: nume, telefon, cu ce te ocupi și ce aștepți de la comunitate.'),
                    self::card('users',
                        'Заявку рассматривает Комиссия', 'The Committee reviews your application', 'Cererea este examinată de Comisie',
                        'Комиссия по отбору участниц из шести человек (трёх представителей Агентства и трёх представителей партнёров) решает, соответствует ли заявка целям и ценностям платформы. Ответ приходит на почту, а если вы подключили Telegram, то и туда.',
                        'The Member Selection Committee of six people (three representatives of the Agency and three of the partners) decides whether the application matches the goals and values of the platform. The answer comes by email, and in Telegram too if you have connected it.',
                        'Comisia de selecție a participantelor, formată din șase persoane (trei reprezentanți ai Agenției și trei ai partenerilor), decide dacă cererea corespunde scopurilor și valorilor platformei. Răspunsul vine prin e-mail, iar dacă ai conectat Telegram, și acolo.'),
                    self::card('card',
                        'Выберите тариф и оплатите', 'Choose a plan and pay', 'Alege tariful și plătește',
                        'Сразу после анкеты вам предложат тарифы Open, Community и Private на 12 месяцев: оплатите картой на защищённой странице банка. Некоторые тарифы могут открываться по заявке: менеджер позвонит и откроет покупку. Данные карты платформа не получает.',
                        'Right after the profile you will be offered the Open, Community and Private plans for 12 months: pay by card on the bank’s secure page. Some plans may open by request: a manager will call you and open the purchase. The platform never receives your card details.',
                        'Imediat după profil ți se propun tarifele Open, Community și Private pe 12 luni: plătește cu cardul pe pagina securizată a băncii. Unele tarife se pot deschide la cerere: un manager te va suna și va deschide achiziția. Platforma nu primește datele cardului.'),
                    self::card('key',
                        'Пользуйтесь возможностями', 'Use the opportunities', 'Folosește oportunitățile',
                        'Закрытое сообщество, каталог участниц, подбор контактов, мероприятия и обучение открываются сразу после оплаты и работают, пока действует членство.',
                        'The closed community, the members’ directory, contact matching, events and learning open right after payment and work for as long as your membership is valid.',
                        'Comunitatea închisă, catalogul participantelor, selecția de contacte, evenimentele și învățarea se deschid imediat după plată și funcționează cât timp este valabilă calitatea de membră.'),
                ],
            ],

            'plans' => [
                'eyebrow' => self::t('Пакеты участия', 'Participation packages', 'Pachete de participare'),
                'title' => self::t('Выберите свой пакет', 'Choose your package', 'Alege-ți pachetul'),
                'intro' => self::t(
                    'Членство индивидуальное и оформляется на 12 месяцев. Статус резидента действует в течение оплаченного периода.',
                    'Membership is individual and is issued for 12 months. Resident status is valid for the paid period.',
                    'Calitatea de membră este individuală și se acordă pentru 12 luni. Statutul de rezidentă este valabil pe durata perioadei achitate.',
                ),
                'open_note' => self::t(
                    'Тариф Open открывает кабинет с материалами и возможностями, но не включает поиск участниц и подбор контактов.',
                    'The Open plan opens the cabinet with materials and opportunities but does not include member search and contact matching.',
                    'Tariful Open deschide cabinetul cu materiale și oportunități, dar nu include căutarea participantelor și selecția de contacte.',
                ),
                'note' => self::t(
                    'Полный перечень возможностей пакетов — в разделе 6 Положения.',
                    'The full list of benefits of the packages is in section 6 of the Regulations.',
                    'Lista completă a avantajelor pachetelor se află în secțiunea 6 a Regulamentului.',
                ),
            ],

            'terms' => [
                'eyebrow' => self::t('Условия', 'Terms', 'Condiții'),
                'title' => self::t('Условия участия', 'Terms of participation', 'Condiții de participare'),
                'intro' => self::t(
                    'Коротко о том, что важно знать до заявки. Полный текст — в Положении о платформе.',
                    'In short, what is worth knowing before you apply. The full text is in the Regulations of the platform.',
                    'Pe scurt, ce este bine să știi înainte de a depune cererea. Textul complet se află în Regulamentul platformei.',
                ),
                'items' => [
                    self::card('idcard',
                        'Индивидуальное членство', 'Individual membership', 'Calitate de membră individuală',
                        'Членство оформляется на женщину лично и предоставляется на условиях выбранного пакета: Woman’s Hub Community или Woman’s Hub Private.',
                        'Membership is issued to the woman personally and is provided under the terms of the chosen package: Woman’s Hub Community or Woman’s Hub Private.',
                        'Calitatea de membră se acordă femeii personal și în condițiile pachetului ales: Woman’s Hub Community sau Woman’s Hub Private.'),
                    self::card('card',
                        'Заявка и взнос', 'Application and fee', 'Cerere și cotizație',
                        'Для вступления нужно подать заявку и оплатить членский взнос за выбранный пакет.',
                        'To join, you submit an application and pay the membership fee for the chosen package.',
                        'Pentru aderare se depune o cerere și se achită cotizația de membru pentru pachetul ales.'),
                    self::card('calendar',
                        'Срок действия', 'Term of validity', 'Termen de valabilitate',
                        'Статус резидента действует в течение оплаченного периода. Продление добавляет 12 месяцев к концу текущего срока.',
                        'Resident status is valid for the paid period. Renewal adds 12 months to the end of the current term.',
                        'Statutul de rezidentă este valabil pe durata perioadei achitate. Prelungirea adaugă 12 luni la sfârșitul termenului curent.'),
                    self::card('users',
                        'Комиссия по отбору', 'Selection Committee', 'Comisia de selecție',
                        'Заявки рассматривает Комиссия из шести человек: три представителя Агентства Инноваций и Развития и три представителя партнёров платформы.',
                        'Applications are reviewed by a Committee of six people: three representatives of the Agency for Innovation and Development and three representatives of the platform’s partners.',
                        'Cererile sunt examinate de o Comisie din șase persoane: trei reprezentanți ai Agenției pentru Inovare și Dezvoltare și trei reprezentanți ai partenerilor platformei.'),
                    self::card('star',
                        'Права резидентов', 'Rights of residents', 'Drepturile rezidentelor',
                        'Получать информацию о деятельности платформы, участвовать в мероприятиях и программах по своему пакету, пользоваться преимуществами членства, предлагать инициативы и участвовать в совместных проектах.',
                        'To receive information about the platform’s activities, to take part in events and programs under their package, to use the benefits of membership, to propose initiatives and to take part in joint projects.',
                        'Să primească informații despre activitatea platformei, să participe la evenimente și programe conform pachetului, să beneficieze de avantajele calității de membră, să propună inițiative și să participe la proiecte comune.'),
                    self::card('shield',
                        'Обязанности резидентов', 'Obligations of residents', 'Obligațiile rezidentelor',
                        'Соблюдать Положение и внутренние правила, уважительно относиться к другим участницам и партнёрам, соблюдать деловую этику и своевременно оплачивать членский взнос.',
                        'To comply with the Regulations and the internal rules, to treat other members and partners with respect, to observe business ethics and to pay the membership fee on time.',
                        'Să respecte Regulamentul și regulile interne, să se raporteze cu respect față de celelalte participante și parteneri, să respecte etica în afaceri și să achite la timp cotizația de membru.'),
                ],
                'link' => self::t('Читать Положение', 'Read the Regulations', 'Citește Regulamentul'),
            ],

            'cabinet' => [
                'eyebrow' => self::t('Личный кабинет', 'Personal cabinet', 'Cabinet personal'),
                'title' => self::t('Что вы получаете в кабинете', 'What you get in the cabinet', 'Ce primești în cabinet'),
                'intro' => self::t(
                    'Кабинет открывается после регистрации по почте и выбора тарифа. Open даёт возможности и обучающие материалы; поиск участниц, подбор контактов и ИИ-помощник входят в Community и Private.',
                    'The cabinet opens after you sign up by email and choose a plan. Open gives opportunities and learning materials; member search, contact matching and the AI assistant are part of Community and Private.',
                    'Cabinetul se deschide după înregistrarea prin e-mail și alegerea unui tarif. Open oferă oportunități și materiale de instruire; căutarea participantelor, selecția de contacte și asistentul AI fac parte din Community și Private.',
                ),
                'login' => self::t('Войти в кабинет', 'Sign in to the cabinet', 'Intră în cabinet'),
                'groups' => [
                    [
                        'badge' => self::t('После одобрения заявки', 'After your application is approved', 'După aprobarea cererii'),
                        'items' => [
                            self::card('idcard',
                                'Мой профиль', 'My profile', 'Profilul meu',
                                'Расскажите о своём деле и о том, что ищете: по профилю вас смогут найти другие участницы.',
                                'Tell us about your business and what you are looking for: other members will be able to find you through your profile.',
                                'Spune-ne despre afacerea ta și ce cauți: celelalte participante te vor putea găsi după profil.'),
                            self::card('book',
                                'Материалы платформы', 'Platform materials', 'Materialele platformei',
                                'Новости, анонсы и публикации сайта на главной странице кабинета.',
                                'News, announcements and publications of the website on the home page of the cabinet.',
                                'Știri, anunțuri și publicații ale site-ului pe pagina principală a cabinetului.'),
                            self::card('card',
                                'Подписка и оплаты', 'Subscription and payments', 'Abonament și plăți',
                                'Выбор пакета, оплата, продление и история платежей в одном месте.',
                                'Choosing a package, payment, renewal and payment history in one place.',
                                'Alegerea pachetului, plata, prelungirea și istoricul plăților într-un singur loc.'),
                        ],
                    ],
                    [
                        'badge' => self::t('С пакетом Community и Private', 'With the Community and Private packages', 'Cu pachetele Community și Private'),
                        'items' => [
                            self::card('users',
                                'Каталог участниц', 'Members’ directory', 'Catalogul participantelor',
                                'Профили других участниц сообщества: найдите коллег, экспертов и клиентов.',
                                'Profiles of other community members: find colleagues, experts and clients.',
                                'Profilurile celorlalte participante ale comunității: găsește colege, experte și clienți.'),
                            self::card('sparkles',
                                'Подбор контактов', 'Contact matching', 'Selecția de contacte',
                                'Рекомендации участниц, которые лучше всего подходят вашему профилю.',
                                'Recommendations of the members who best match your profile.',
                                'Recomandări ale participantelor care se potrivesc cel mai bine profilului tău.'),
                            self::card('search',
                                'Поиск по запросу', 'Search by request', 'Căutare după cerere',
                                'Опишите, что вам нужно, обычными словами и получите подходящих участниц.',
                                'Describe what you need in plain words and get the matching members.',
                                'Descrie ce ai nevoie în cuvinte simple și primește participantele potrivite.'),
                            self::card('chat',
                                'ИИ-помощник', 'AI assistant', 'Asistent AI',
                                'Подскажет, как лучше рассказать о себе, и поможет обновить профиль.',
                                'Suggests how to present yourself better and helps you update your profile.',
                                'Îți sugerează cum să te prezinți mai bine și te ajută să-ți actualizezi profilul.'),
                            self::card('flag',
                                'Возможности', 'Opportunities', 'Oportunități',
                                'Публикуйте проекты, встречи и события и находите предложения других участниц.',
                                'Publish projects, meetings and events and find offers from other members.',
                                'Publică proiecte, întâlniri și evenimente și găsește ofertele celorlalte participante.'),
                            self::card('graduation',
                                'Обучение', 'Learning', 'Învățare',
                                'Образовательные материалы и встречи для участниц.',
                                'Educational materials and meetings for members.',
                                'Materiale și întâlniri educaționale pentru participante.'),
                            self::card('telegram',
                                'Уведомления в Telegram', 'Telegram notifications', 'Notificări în Telegram',
                                'Подключаются в профиле: сообщения об оплате и сроке тарифа, подходящие знакомства и рассылки платформы.',
                                'Connected in your profile: messages about payments and your plan term, matching contacts and platform newsletters.',
                                'Se conectează în profil: mesaje despre plată și termenul tarifului, contacte potrivite și newslettere ale platformei.'),
                        ],
                    ],
                ],
            ],

            'photos' => [
                'title' => self::t('Сообщество вживую', 'The community in person', 'Comunitatea în persoană'),
                'text' => self::t(
                    'Встречи, панельные дискуссии, нетворкинг и Гала Премия «Женщина Года»: фото с наших мероприятий.',
                    'Meetings, panel discussions, networking and the gala award: photos from our events.',
                    'Întâlniri, dezbateri în panel, networking și gala premiilor: fotografii de la evenimentele noastre.',
                ),
                'link' => self::t('Все фотоальбомы', 'All photo albums', 'Toate albumele foto'),
            ],

            'faq' => [
                'title' => self::t('Частые вопросы', 'Frequently asked questions', 'Întrebări frecvente'),
                'items' => [
                    [
                        'q' => self::t('Сколько действует членство?', 'How long does membership last?', 'Cât timp este valabilă calitatea de membră?'),
                        'a' => self::t(
                            '12 месяцев. Продление добавляет ещё 12 месяцев к концу текущего срока, ничего не сгорает.',
                            '12 months. Renewal adds another 12 months to the end of your current term, so nothing is lost.',
                            '12 luni. Prelungirea adaugă încă 12 luni la sfârșitul termenului curent, nimic nu se pierde.',
                        ),
                    ],
                    [
                        'q' => self::t('Что будет, когда срок закончится?', 'What happens when the term ends?', 'Ce se întâmplă când se încheie termenul?'),
                        'a' => self::t(
                            'Кабинет закроется, а профиль сохранится. За 14 и за 3 дня до окончания мы напомним в Telegram (если вы его подключили) или по почте. Автоплатежей нет: продлить можно по ссылке из сообщения.',
                            'The cabinet will close, while your profile remains. We will remind you 14 and 3 days before the end, in Telegram (if you have connected it) or by email. There are no automatic payments: you can renew using the link in the message.',
                            'Cabinetul se va închide, iar profilul rămâne. Te vom anunța cu 14 și cu 3 zile înainte de sfârșit, în Telegram (dacă l-ai conectat) sau prin e-mail. Nu există plăți automate: poți prelungi prin linkul din mesaj.',
                        ),
                    ],
                    [
                        'q' => self::t('Как оплатить?', 'How do I pay?', 'Cum plătesc?'),
                        'a' => self::t(
                            'Банковской картой на защищённой странице Агропромбанка. Данные карты вводятся только на стороне банка и платформе не передаются.',
                            'By bank card on the secure page of Agroprombank. Card details are entered only on the bank’s side and are never passed to the platform.',
                            'Cu cardul bancar, pe pagina securizată a Agroprombank. Datele cardului se introduc doar la bancă și nu sunt transmise platformei.',
                        ),
                    ],
                    [
                        'q' => self::t('Можно ли перейти с Community на Private?', 'Can I switch from Community to Private?', 'Pot trece de la Community la Private?'),
                        'a' => self::t(
                            'Да. Private начинается сразу, а остаток Community продолжится после окончания Private.',
                            'Yes. Private starts right away, and the remainder of Community continues after Private ends.',
                            'Da. Private începe imediat, iar restul perioadei Community continuă după încheierea Private.',
                        ),
                    ],
                    [
                        'q' => self::t('Входит ли в пакет участие в Гала Премии «Женщина Года»?', 'Is participation in the Gala included?', 'Participarea la Gală este inclusă?'),
                        'a' => self::t(
                            'Да, участие в Гала Премии «Женщина Года» входит в оба пакета. Номинировать на премию могут резиденты по отдельному Положению о премии.',
                            'Yes, participation in the Gala “Woman of the Year” is included in both packages. Residents may nominate for the award under the separate Regulations on the Award.',
                            'Da, participarea la Gala „Woman of the Year” este inclusă în ambele pachete. Rezidentele pot nominaliza la premiu conform Regulamentului separat privind premiul.',
                        ),
                    ],
                    [
                        'q' => self::t('Кто рассматривает заявки?', 'Who reviews the applications?', 'Cine examinează cererile?'),
                        'a' => self::t(
                            'Комиссия по отбору участниц: три представителя Агентства Инноваций и Развития и три представителя партнёров платформы.',
                            'The Member Selection Committee: three representatives of the Agency for Innovation and Development and three representatives of the platform’s partners.',
                            'Comisia de selecție a participantelor: trei reprezentanți ai Agenției pentru Inovare și Dezvoltare și trei reprezentanți ai partenerilor platformei.',
                        ),
                    ],
                ],
            ],

            'cta' => [
                'title' => self::t('Готовы присоединиться?', 'Ready to join?', 'Ești gata să te alături?'),
                'text' => self::t(
                    'Зарегистрируйтесь по почте, заполните анкету и выберите тариф.',
                    'Sign up by email, fill in your profile and choose a plan.',
                    'Înregistrează-te prin e-mail, completează profilul și alege un tarif.',
                ),
            ],
        ];
    }

    /** @return array{ru: string, en: string, ro: string} */
    private static function t(string $ru, string $en, string $ro): array
    {
        return ['ru' => $ru, 'en' => $en, 'ro' => $ro];
    }

    /**
     * Карточка: значок, название и текст на трёх языках.
     *
     * @return array{icon: string, title: array<string, string>, text: array<string, string>}
     */
    private static function card(string $icon, string $titleRu, string $titleEn, string $titleRo, string $textRu, string $textEn, string $textRo): array
    {
        return ['icon' => $icon, 'title' => self::t($titleRu, $titleEn, $titleRo), 'text' => self::t($textRu, $textEn, $textRo)];
    }
}
