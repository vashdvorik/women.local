# Карта проекта Women Entrepreneurs Platform

> Рабочая карта для дальнейшей разработки. Составлена по исходному коду проекта и проверена 25.07.2026;
> раздел про админку и публичные материалы обновлён 20.09.2026 после переноса админки из education3 (см. docs/ADMIN_MIGRATION.md).
> Это карта фактически подключённых механизмов, а не список планируемых разделов из маркетингового лендинга.

## 1. Коротко о проекте

Проект — Laravel-приложение с тремя пользовательскими контурами:

1. публичный лендинг;
2. кабинет одобренной участницы, связанный с Telegram;
3. закрытая админ-панель (Blade, Alpine.js, Tailwind; перенесена из education3).

Telegram одновременно используется как канал регистрации и уведомлений, источник идентичности участницы и точка запуска AI-поиска. Кабинет работает через обычную web-сессию, которую можно создать magic-link из Telegram или через Telegram Mini App.

Стек:

- PHP 8.2+, Laravel 12;
- собственная админка `/admin` (Blade, Alpine.js, Tailwind 3.4, Vite); собранные ассеты коммитятся в `public/build`; Intervention Image (WebP) и Purifier (очистка HTML);
- Nutgram Laravel для Telegram webhook и conversations;
- Gemini API для text embeddings;
- MySQL/SQLite через Laravel migrations;
- database session, cache и queue в рекомендуемой конфигурации;
- Blade, Tailwind CDN и Alpine.js для интерфейса кабинета; Vite подключён для базовых ресурсов приложения.

## 2. Дерево пользовательских контуров

```text
/
├── публичный лендинг
│   ├── classic / warm / dark — одна Blade-страница с разными темами
│   ├── platform — отдельный вариант landing-platform.blade.php
│   └── miro — отдельный Miro-inspired вариант landing-miro.blade.php
│
├── /members
│   └── публичный каталог из 12 демонстрационных профилей в стиле Miro
│
├── /events
│   └── публичная лента событий, новостей и возможностей в стиле Miro
│
├── /about
│   └── отдельная страница «О платформе» в стиле Miro: миссия, аудитория, возможности и путь участницы
│
├── /partners
│   └── публичный каталог 10 координаторов и партнёров платформы в стиле Miro
│
├── личный кабинет участницы
│   └── classic / warm / dark / miro — общий layout с независимыми темами
│
├── /app/account
│   ├── /login              вход и запуск Telegram Mini App
│   ├── /auth?token=...     создание сессии по полному magic-link
│   ├── /                   главная кабинета
│   ├── /profile            профиль участницы
│   ├── /profile/edit       форма редактирования профиля
│   ├── /matches            AI-рекомендации контактов
│   ├── /search?q=...       AI-поиск по профилям
│   ├── /people             каталог одобренных участниц
│   ├── /people/{botUser}   карточка участницы
│   ├── /knowledge          статическая страница раздела знаний
│   ├── /opportunities      лента возможностей/публикаций
│   ├── /opportunities/create
│   ├── POST /opportunities
│   ├── DELETE /opportunities/{opportunity}
│   ├── POST /profile       сохранение профиля
│   ├── DELETE /profile     удаление профиля и сессии
│   └── POST /logout        выход
│
├── /go/{8 hex chars}       короткая ссылка из Telegram
├── /language/{ru|en|ro}    переключение языка в session + cookie
├── POST /telegram/webhook  входящие обновления Telegram
├── /experts, /events       карточки из БД (админка: «Эксперты», «Новости»)
├── /media/publications[/{slug}], /media/photos[/{slug}], /media/videos, /projects, /opportunities[/{slug}]
│                           материалы из админки, тема miro
├── POST /subscribe         подписка на новости (форма в подвале)
├── /login                  вход администратора
└── /admin                  админка в двух лагерях: «Внешний сайт» (/admin) и «Кабинеты участниц» (/admin/cabinets)
```

Все маршруты кабинета, кроме `/login`, `/auth` и `/tma-auth`, защищены `RequireAccountAuth`.

## 3. Маршруты и точки входа

### Публичная часть

Маршруты описаны в [routes/web.php](../routes/web.php).

| URL | Имя | Обработчик | Назначение |
|---|---|---|---|
| `GET /` | — | closure | Загружает активную тему через `SiteSetting::landingTheme()` и рендерит `landing` |
| `GET /about` | `about` | closure | Отдельная публичная страница «О платформе» в теме Miro; контент пока статичный |
| `GET /members` | `members` | closure | Публичный визуальный каталог из 12 статичных демонстрационных профилей; реальный backend пока не подключён |
| `GET /events` | `events` | closure | Публичная статичная лента из 9 новостей исходного проекта в стиле Miro |
| `GET /partners` | `partners` | closure | Публичный каталог из 10 партнёров, перенесённых со страницы `women.creativity.md/partners/`; логотипы хранятся локально |
| `GET /language/{locale}` | `language.switch` | closure | Разрешены только `ru`, `en`, `ro`; возвращает на предыдущую страницу |
| `POST /telegram/webhook` | `telegram.webhook` | Nutgram closure | Передаёт обновление Telegram в `$bot->run()` |
| `GET /go/{code}` | `account.go` | closure | Ищет префикс token, проверяет срок, перенаправляет на полный auth-link |

Web middleware подключает `SetLocale` глобально для web-группы и исключает `telegram/webhook` из CSRF-проверки.

### Аутентификация кабинета

| URL | Имя | Назначение |
|---|---|---|
| `GET /app/account/login` | `account.login` | Экран входа; внутри Telegram Mini App автоматически отправляет `initData` |
| `GET /app/account/auth?token=...` | `account.auth` | Проверяет `LoginToken`, approved-статус и создаёт сессию на 7 дней |
| `POST /app/account/tma-auth` | `account.tma-auth` | Проверяет подпись Telegram Mini App HMAC-SHA256 и создаёт сессию |

Оба auth-маршрута ограничены throttle `20,1`. Session keys, которые нельзя менять без обновления middleware и тестов:

- `account_telegram_id` — Telegram ID текущей участницы;
- `_account_expires` — Unix timestamp окончания 7-дневной сессии.

### Защищённый кабинет

Маршруты описаны в `routes/web.php`, логика — в `AccountController` и `OpportunityController`.

| Раздел | View | Что делает |
|---|---|---|
| Главная | `account/index.blade.php` | Навигационная панель кабинета и быстрые действия |
| Профиль | `account/profile.blade.php` | Показывает данные, аватар и действие редактирования/удаления |
| Редактирование | `account/profile-edit.blade.php` | Валидирует и сохраняет `full_name`, `description`, `expectation`; ставит job эмбеддинга |
| Матчи | `account/matches.blade.php` | Показывает top-N по cosine similarity или пустое состояние |
| Поиск | `account/search.blade.php` | Берёт `q`, запрашивает Gemini embedding и ищет по approved-профилям |
| Участницы | `account/people.blade.php` | Все approved-профили, кроме текущей участницы |
| Участница | `account/person.blade.php` | Профиль approved-пользовательницы; не-approved возвращает 404 |
| Возможности | `account/opportunities/index.blade.php` | Пагинация по 15 публикаций; удалять можно только свои |
| Новая возможность | `account/opportunities/create.blade.php` | Создание `project`, `meeting` или `event` |
| Знания | `account/knowledge.blade.php` | Текущий статический экран/заготовка раздела |

Общий layout кабинета — `resources/views/account/layout.blade.php`. Он содержит sidebar, языковой переключатель, logout, Telegram WebApp initialization и скрывает logout внутри Telegram Mini App.

## 4. Лендинг

Корневой маршрут всегда рендерит `resources/views/landing.blade.php`.

В начале этого файла есть переключатель:

```blade
@if(($landingTheme ?? 'classic') === 'platform')
    @include('landing-platform')
@elseif(($landingTheme ?? 'classic') === 'miro')
    @include('landing-miro')
@else
    ... classic landing ...
@endif
```

### Classic / warm / dark

Одна большая Blade-страница с клиентским переключением `ru/en/ro` через `data-lang`, `localStorage` и `document.documentElement.lang`. Основные якоря страницы:

- `#who-for` — для кого платформа;
- `#how-works` — как работает;
- `#tools` — инструменты;
- `#learning` — обучение и менторство;
- `#events` — события;
- `#stories` — истории;
- `#contact` — контакты.

Ключевые внешние переходы — `https://t.me/WomenComBot`. В лендинге есть ссылки-заготовки `href="#"`; это не отдельные серверные страницы.

### Platform

`resources/views/landing-platform.blade.php` — альтернативный дизайн из старого docs-прототипа. Содержит якоря `#about`, `#learning`, `#members`, `#events`, `#contact`, ссылки в кабинет и ссылки на Telegram-бота/сообщество.

`resources/views/landing-miro.blade.php` — маркетинговая тема по `dizayn/miro/DESIGN.md`: sticky navigation, hero с фото и градиентной композицией, pastel feature cards, benefits/how-it-works/platform-offers, AI/workspace blocks, members, events, stories, dark CTA и multi-column footer. Тема использует существующие изображения из `public/images`; их можно заменить позже без изменения маршрутов.

`resources/views/members-miro.blade.php` — публичный Miro-каталог `/members`. Сейчас содержит 12 экспертных профилей, перенесённых из публичной секции `women.creativity.md`; фотографии сохранены локально в `public/images/experts`. Дополнительные поля профиля пока статичны и не подключены к backend.

`resources/views/about-miro.blade.php` — публичная страница `/about` в теме Miro. Содержит миссию платформы, аудиторию, экосистему возможностей, пользовательский путь, фокус на сотрудничестве между обоими берегами и CTA. Тексты подготовлены по ТЗ и материалам исходных сайтов; контент пока статичный.

`resources/views/partners-miro.blade.php` — публичный каталог `/partners` в теме Miro. Включает 3 координатора платформы, 4 местных и 3 международных партнёра; логотипы загружены в `public/images/partners` с исходной страницы.

Общий header и мобильное меню Miro вынесены в `resources/views/partials/miro-header.blade.php`, общий footer — в `resources/views/partials/miro-footer.blade.php`. Оба partial подключаются на лендинге, `/about`, `/members`, `/events` и `/partners`; активный раздел передаётся параметром `miroCurrentPage`.

Брендовые изображения подготовлены из `dizayn/logo.png`: оптимизированный горизонтальный логотип — `public/images/brand/logo.webp` (около 38 KB), PNG-версия — `public/images/brand/logo.png`, компактная иконка для favicon — `public/images/brand/favicon.png` (около 10 KB). Логотип используется в Miro header/footer и в компактных шапках альтернативного лендинга и кабинета; favicon подключён к публичным страницам, login и кабинету.

Активная тема хранится в `site_settings` под ключом `landing_theme` и изменяется админской страницей `LandingThemeSettings`. Настройка лендинга не влияет на кабинет участницы.

## 5. Telegram-контур

Подключение Nutgram и обработчики обновлений:

- `routes/web.php` — webhook endpoint;
- `routes/telegram.php` — команды, callback-и и fallback;
- `app/Telegram/TelegramKeyboards.php` — основная reply keyboard;
- `app/Telegram/Conversations/RegistrationConversation.php` — регистрационный диалог;
- `app/Telegram/Conversations/SearchConversation.php` — AI-поиск в чате.

### Команды и состояния

```text
/start или /start login
├── нет BotUser       → RegistrationConversation
├── pending            → сообщение «заявка на рассмотрении»
├── approved           → magic-link вход (для /start login)
└── rejected           → сообщение о закрытом доступе

/login
└── только approved → новый magic-link

approved keyboard
├── «Найти контакты» → SearchConversation
└── «Чат сообщества» → пока ответ-заглушка
```

Callback-и:

- `restart` — начать регистрацию заново;
- `start_guide` — отправить инструкцию после одобрения;
- `reg:yes`, `reg:skip` — шаги регистрации;
- `search:more` — показать остальные результаты поиска.

Регистрация собирает имя, описание бизнеса и ожидания. Затем создаёт `BotUser` со статусом `pending`, пытается скачать аватар из Telegram и ставит `ComputeUserEmbedding` в очередь. Одобрение/отклонение выполняется администратором в админке (раздел «Профили участниц»).

## 6. Админка

Собственная админка на `/admin`, перенесённая из проекта education3 (`D:\OSPanel\home\education3`, его `AGENTS.md` и `DESIGN.md` — контракт и дизайн-система). Blade + Alpine.js + Tailwind 3.4, сборка Vite; готовые ассеты лежат в `public/build` и коммитятся (на хостинге нет Node). Filament удалён.

**Доступ.** Ролей нет: пускается ровно одна почта из `ADMIN_EMAIL` (`config/admin.php`), проверку делает `EnsureAdminEmail` на всей группе роутов, одного `auth` мало. Вход — `/login`, перебор пароля режется в трёх слоях (`LoginRequest`: почта+IP 5/мин, почта 20/15 мин; `throttle:login` 30/мин по IP). Админка всегда по-русски (`AdminLocale`), независимо от языка сайта.

Админка разделена на **два лагеря**, чтобы администратор не путал управление публичным сайтом и управление кабинетами. Переключатель — вверху левого меню; под ним показывается меню только текущего лагеря, а в шапке каждой страницы стоит подпись лагеря. Число на вкладке «Кабинеты участниц» — сколько профилей и постов ждут решения (видно из любого раздела сайта). Лагерь определяется по имени маршрута (`App\Support\AdminCamp`): `admin.cabinets.*`, `admin.profiles.*`, `admin.member-posts.*`, `admin.statistics.*` — «Кабинеты участниц», всё остальное — «Внешний сайт». **Новый раздел админки нужно сразу отнести к одному из двух лагерей** (имя маршрута и пункт в `components/admin/sidebar.blade.php`).

**Лагерь 1 — «Внешний сайт»** (то, что видят посетители):

| Раздел | URL | Назначение |
|---|---|---|
| Инфопанель | `/admin` | Быстрые «добавить …», счётчик черновиков, плашка о очереди модерации кабинетов, памятка |
| Публикации | `/admin/news` | Статьи со своей страницей: блоки (текст, заголовок, HTML, картинка, галереи), теги, черновик/публикация → «Медиатека → Публикации» (`/media/publications`, у каждой статьи `/media/publications/{slug}`). Не то же самое, что «Новости» |
| Возможности | `/admin/opportunities` | Гранты и программы с дедлайном (`SiteOpportunity`) → `/opportunities` |
| Новости | `/admin/events` | Карточки новостей (модель `Event`): обложка, цвет, дата или подпись, ссылка «Подробнее», порядок → страница `/events` («Новости» в меню сайта) и главная |
| Эксперты | `/admin/experts` | Карточки экспертов: портрет, цвет, порядок, тексты и теги → `/experts` и главная |
| Проекты | `/admin/projects` | Плоские карточки → `/projects` |
| Фотоальбомы, видео | `/admin/albums`, `/admin/videos` | Альбомы с галереями и кроппером; ролики YouTube с ручным порядком |
| Теги | `/admin/tags` | Названия на трёх языках и произвольный цвет |
| Подписчики | `/admin/subscribers` | Форма в подвале сайта: список, поиск, экспорт CSV/TXT |
| Настройки сайта | `/admin/settings` | Вкладки: сжатие изображений, тема публичного сайта |

**Лагерь 2 — «Кабинеты участниц»** (закрытый кабинет и Telegram-бот):

| Раздел | URL | Назначение |
|---|---|---|
| Инфопанель | `/admin/cabinets` | Очереди модерации (профили, посты) и быстрые переходы, памятка |
| Профили участниц | `/admin/profiles` | Модерация `BotUser` (бывший Filament `BotUserResource`): вкладки статусов, поиск, одобрить/отклонить с уведомлением в Telegram, правка текстов, массовое удаление |
| Посты участниц | `/admin/member-posts` | Премодерация постов из кабинета (`Opportunity`): одобренный виден всем и уходит рассылкой, ожидающий и отклонённый виден только автору |
| Статистика | `/admin/statistics` | Отчёт по заявкам, готовности профилей и публикациям (`ImpactReport`), PDF-выгрузка |
| Настройки кабинетов | `/admin/cabinets/settings` | Вкладки: тема кабинета, ИИ-провайдеры (ключи шифруются, проверка подключения), база знаний ассистента |

**Переводы** — как в education3: таблицы `*_translations`, трейт `HasTranslations`; русский обязателен, румынский и английский по желанию, при пустом поле сайт подставляет русский. Публичные страницы по-прежнему выводят все три языка сразу в `data-lang`-спанах.

**Картинки.** Реестр пропорций `App\Support\AspectRatio::SLOTS`, кадрирование в Cropper.js, сохранение WebP (`StoreUploadedImage`, качество и размер — в настройках), диск `uploads` → `public/uploads/ГГГГ/ММ` без `storage:link`; в БД хранится относительный путь.

**Что осталось массивами в Blade:** партнёры, каталог 112 участниц, служебные страницы-заглушки (руководство, положение, отчёты, почётные члены, gala, join), приоритеты и тексты лендинга, about, contact.

**Только тема miro.** Страницы, подключённые к админке, есть только в теме miro; остальные публичные темы получают её версию (`PublicThemeView`).

## 7. Данные и связи

```text
User (администратор)
└── единственная почта ADMIN_EMAIL, вход /login

BotUser
├── 1:N LoginToken по telegram_id
├── 1:N Opportunity через bot_user_id
└── embedding JSON для AI-поиска/матчинга

SiteSetting
├── landing_theme — тема публичного лендинга
└── account_theme — тема кабинета участницы
```

### Таблицы

`bot_users`:

- `telegram_id` — уникальный внешний идентификатор Telegram;
- `telegram_username`, `first_name`, `full_name`;
- `description`, `expectation`;
- `status`: `pending`, `approved`, `rejected`;
- `approved_at`, `avatar_path`;
- `embedding`, `embedding_updated_at`;
- `region` присутствует в migration, но сейчас не входит в fillable/UI/основные запросы.

`login_tokens`:

- token 64 hex-символа;
- `telegram_id`, `expires_at`, `used_at`;
- `generateFor()` сначала удаляет старые токены пользователя, затем создаёт новый на 24 часа.

`opportunities`:

- автор через `bot_user_id`;
- type: `project`, `meeting`, `event`;
- title/body, optional event date/location/contact URL;
- удаление каскадное при удалении участницы.

`site_settings`:

- уникальный `key`;
- JSON `value`;
- `landing_theme` и `account_theme` кешируются forever и сбрасываются при сохранении соответствующей настройки.

## 8. AI и фоновые задачи

### Эмбеддинги

`EmbeddingService` вызывает Gemini `embedContent`. Текст участницы строится из `description + expectation`.

`ComputeUserEmbedding`:

1. получает сериализованный `BotUser`;
2. строит текст и вызывает Gemini;
3. сохраняет `embedding` и `embedding_updated_at`;
4. сбрасывает cache matches текущей участницы;
5. при ошибке пишет warning и не ломает регистрацию/сохранение профиля.

Job ставится при регистрации и после редактирования профиля. Ручной пересчёт: `php artisan ai:recompute-embeddings` (`app/Console/Commands/RecomputeEmbeddings.php`).

### Матчинг и поиск

`MatchingService` работает в памяти PHP по всем approved-профилям с embedding:

- `topMatches()` — cosine similarity, по умолчанию 5 результатов, cache TTL из `config/ai.php`;
- `searchByQuery()` — embedding пользовательского запроса, минимум score из `config/ai.php`, по умолчанию 10 результатов;
- текущая участница исключается из кандидатов;
- у кандидата без embedding нет шанса попасть в AI-выдачу.

Важно: после изменения embedding сбрасывается только cache текущего пользователя. При расширении логики профиля/матчинга нужно явно решить, как инвалидировать кэш остальных пользователей.

### Уведомления публикаций

`NotifyOpportunity` ставится после создания возможности. Job отправляет HTML-сообщение всем approved-участницам, кроме автора, с коротким текстом и ссылкой на `/app/account/opportunities`. Ошибка отправки одному получателю логируется и не останавливает рассылку.

## 9. Ключевые бизнес-потоки

### Регистрация → одобрение → вход

```text
Telegram /start
  → RegistrationConversation
  → BotUser(status=pending)
  → ComputeUserEmbedding
  → Admin approve в админке (/admin/profiles)
  → Telegram approval message + keyboard
  → /login или /start login
  → LoginToken /go/{prefix}
  → /app/account/auth
  → session(account_telegram_id, _account_expires)
  → RequireAccountAuth
```

Параллельный путь внутри Telegram Mini App:

```text
/app/account/login
  → Telegram.WebApp.initData
  → POST /app/account/tma-auth
  → HMAC-SHA256 verification + auth_date <= 24h
  → approved BotUser
  → та же 7-дневная account-сессия
```

### Редактирование профиля

```text
profile/edit
  → ProfileUpdateRequest
  → BotUser update
  → ComputeUserEmbedding queue
  → Gemini
  → embedding + updated_at
  → invalidate own matches cache
```

### Публикация возможности

```text
opportunities/create
  → OpportunityRequest
  → Opportunity::create(author=current accountUser)
  → NotifyOpportunity queue
  → Telegram notification to other approved users
```

## 10. Инварианты, которые нельзя нарушить

- Доступ к кабинету определяется не Laravel `User`, а `BotUser` + `session('account_telegram_id')` + срок `_account_expires`.
- В кабинет допускается только `BotUser::STATUS_APPROVED`.
- `RequireAccountAuth` на каждом защищённом запросе заново проверяет наличие пользователя и его статус; отзыв доступа должен продолжать работать в уже открытой сессии.
- Не использовать `id` как идентификатор Telegram-сессии: сессия и `LoginToken` привязаны к `telegram_id`.
- Magic-link и TMA-auth должны сохранять session regeneration и CSRF/throttle-поведение.
- В каталог, AI-матчинг и Telegram-уведомления попадают только approved-участницы.
- Удалять возможность может только её автор; удаление профиля должно чистить сессию и каскадно удалять публикации.
- Любое изменение `description`/`expectation` должно учитывать очередь эмбеддинга и кэш матчей.
- Webhook Telegram должен оставаться исключённым из CSRF, а auth endpoint — защищённым throttle.
- При изменении `SiteSetting::LANDING_THEMES` нужно синхронно обновить выбор в админке и стили/ветвление лендинга.

## 11. Файловая навигация для разработки

```text
routes/
├── web.php                 HTTP routes, auth, cabinet, webhook endpoint
├── telegram.php            Nutgram handlers and helper functions
└── console.php             Artisan command declarations

app/Http/
├── Controllers/Account/    cabinet/auth/opportunities controllers
├── Controllers/Admin/      разделы админки
├── Controllers/PublicSite/ публичные страницы с материалами из админки
├── Middleware/             SetLocale, RequireAccountAuth
└── Requests/Account/       profile/opportunity validation

app/Models/                 User, BotUser, LoginToken, Opportunity (посты кабинета), SiteSetting,
                            Post, SiteOpportunity, Album, Video, Project, Tag, Expert, Event, Subscriber (+ *Translation)
app/Services/               MatchingService, EmbeddingService
app/Jobs/                   ComputeUserEmbedding, NotifyOpportunity
app/Telegram/               keyboard and conversations
app/Actions/, app/Support/    сохранение материалов, картинки, блоки, переводы (из education3)

resources/views/
├── landing*.blade.php      public landing variants, including landing-miro.blade.php
├── account/                cabinet screens and layout
├── admin/                  экраны админки
├── components/admin/       компоненты админки (sidebar, формы, редакторы)
└── themes/public/miro/     публичная тема: content-layout/list/article, partials

database/
├── migrations/              schema history
├── factories/               test data
└── seeders/                 demo/community data

tests/
├── Feature/Account/         auth, cabinet and security contracts
├── Feature/Performance/     performance checks
└── Unit/                    LoginToken and basic unit tests
```

## 12. Известные особенности перед изменениями

Это не исправления, а зафиксированные точки внимания, чтобы не принять незавершённое за готовый механизм:

1. `AccountController::sendLink()` существует, но отдельный POST route и форма для него сейчас не зарегистрированы; фактический web-вход идёт через Telegram bot link или TMA-auth.
2. Поле `login_tokens.used_at` учитывается в impact-метриках, но текущий `AccountController::auth()` его не заполняет. Поэтому метрики «использованных» токенов могут быть нулевыми, а токен остаётся действительным до `expires_at`.
3. `knowledge` и ответ «Чат сообщества» в Telegram являются статическими/временными экранами, полноценный контентный раздел и чат-интеграция не подключены.
4. `region` уже есть в схеме БД, но не проведён через модель, форму регистрации, админку или фильтры кабинета.
5. Новости (`Event`) и эксперты (`Expert`) теперь модели и редактируются в админке; разделы обучения и историй на лендинге остаются секциями одной страницы без backend.
6. Очередь важна для AI и рассылок. Для production должны работать `queue:work`/cron, иначе профиль сохранится, но embedding и уведомления останутся невыполненными.
7. Старые прототипные файлы в `docs/` могут отсутствовать в рабочем дереве и сейчас отмечены Git как удалённые. Не восстанавливать их автоматически без отдельного запроса.

## 13. Минимальная проверка после изменений

```powershell
php artisan route:list --except-vendor
php artisan test
php artisan view:cache
php artisan config:clear
```

Для изменений в конкретных контурах дополнительно проверять:

- auth/session: `tests/Feature/Account/AccountAuthTest.php`, `SecurityTest.php`;
- cabinet/profile/catalog: `AccountCabinetTest.php`;
- token semantics: `tests/Unit/LoginTokenTest.php`;
- queue: наличие worker и записей в `jobs`;
- landing: активную тему в `/admin/settings` (вкладка «Темы сайта») и публичный `/`;
- Telegram: webhook status и команды `/start`, `/login` после деплоя.
