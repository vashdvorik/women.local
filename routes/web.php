<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\DevAccountLoginController;
use App\Http\Controllers\Account\OpportunityController;
use App\Http\Controllers\Account\TmaAuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\PublicSite;
use App\Http\Middleware\RequireAccountAuth;
use App\Models\LoginToken;
use App\Services\PublicThemeView;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

Route::get('/', PublicSite\LandingController::class);

Route::get('/members', function () {
    return PublicThemeView::render('members');
})->name('members');

Route::get('/experts', PublicSite\ExpertsController::class)->name('experts');

Route::get('/events', PublicSite\EventsController::class)->name('events');

Route::get('/about', function () {
    return PublicThemeView::render('about');
})->name('about');

Route::get('/partners', function () {
    return PublicThemeView::render('partners');
})->name('partners');

Route::get('/contact', function () {
    return PublicThemeView::render('contact');
})->name('contact');

$publicSection = static fn (array $data) => PublicThemeView::render('public-section', $data);

Route::get('/about/priorities', function () {
    return PublicThemeView::render('priorities');
})->name('about.priorities');

Route::get('/about/leadership', fn () => $publicSection([
    'pageKey' => 'leadership',
    'eyebrow' => ['ru' => 'О нас', 'en' => 'About us', 'ro' => 'Despre noi'],
    'title' => ['ru' => 'Руководство', 'en' => 'Leadership', 'ro' => 'Conducere'],
    'intro' => ['ru' => 'Страница о команде и людях, которые развивают платформу и поддерживают сообщество женщин-предпринимательниц.', 'en' => 'A page about the team and people developing the platform and supporting the community of women entrepreneurs.', 'ro' => 'O pagină despre echipa și oamenii care dezvoltă platforma și susțin comunitatea femeilor antreprenoare.'],
    'placeholder' => true,
]))->name('about.leadership');

Route::get('/about/regulations', fn () => $publicSection([
    'pageKey' => 'regulations',
    'eyebrow' => ['ru' => 'О нас', 'en' => 'About us', 'ro' => 'Despre noi'],
    'title' => ['ru' => 'Положение', 'en' => 'Regulations', 'ro' => 'Regulament'],
    'intro' => ['ru' => 'Здесь будет опубликовано положение о работе платформы, правилах участия и взаимодействии внутри сообщества.', 'en' => 'This page will contain the platform regulations, participation rules and community guidelines.', 'ro' => 'Aici va fi publicat regulamentul platformei, regulile de participare și principiile comunității.'],
    'placeholder' => true,
]))->name('about.regulations');

Route::get('/about/reports', fn () => $publicSection([
    'pageKey' => 'reports',
    'eyebrow' => ['ru' => 'О нас', 'en' => 'About us', 'ro' => 'Despre noi'],
    'title' => ['ru' => 'Отчёты', 'en' => 'Reports', 'ro' => 'Rapoarte'],
    'intro' => ['ru' => 'Раздел с отчётами, результатами программ и материалами о развитии сообщества.', 'en' => 'A section for reports, programme results and materials about the community’s development.', 'ro' => 'O secțiune pentru rapoarte, rezultatele programelor și materiale despre dezvoltarea comunității.'],
    'placeholder' => true,
]))->name('about.reports');

Route::get('/members/participants', fn () => redirect()->route('members'))->name('members.participants');
Route::get('/members/experts', fn () => redirect()->route('experts'))->name('members.experts');

Route::get('/members/honorary', fn () => $publicSection([
    'pageKey' => 'honorary',
    'eyebrow' => ['ru' => 'Люди', 'en' => 'People', 'ro' => 'Oameni'],
    'title' => ['ru' => 'Почётные члены', 'en' => 'Honorary members', 'ro' => 'Membre onorifice'],
    'intro' => ['ru' => 'В этом разделе будут представлены женщины, внесшие особый вклад в развитие сообщества и женского предпринимательства.', 'en' => 'This section will introduce women who have made a special contribution to the community and women’s entrepreneurship.', 'ro' => 'Această secțiune va prezenta femeile care au contribuit în mod special la comunitate și la antreprenoriatul feminin.'],
    'placeholder' => true,
]))->name('members.honorary');

Route::get('/members/join', fn () => $publicSection([
    'pageKey' => 'join',
    'eyebrow' => ['ru' => 'Люди', 'en' => 'People', 'ro' => 'Oameni'],
    'title' => ['ru' => 'Как стать участницей', 'en' => 'How to become a member', 'ro' => 'Cum să devii membră'],
    'intro' => ['ru' => 'Присоединяйтесь к сообществу, чтобы находить знания, контакты, партнёров и возможности для развития.', 'en' => 'Join the community to discover knowledge, connections, partners and opportunities for growth.', 'ro' => 'Alătură-te comunității pentru a descoperi cunoștințe, conexiuni, parteneri și oportunități de dezvoltare.'],
    'steps' => [
        ['title' => ['ru' => 'Зарегистрируйтесь', 'en' => 'Register', 'ro' => 'Înregistrează-te'], 'text' => ['ru' => 'Откройте доступ к платформе через Telegram и создайте свой профиль.', 'en' => 'Access the platform through Telegram and create your profile.', 'ro' => 'Accesează platforma prin Telegram și creează-ți profilul.']],
        ['title' => ['ru' => 'Расскажите о себе', 'en' => 'Tell us about yourself', 'ro' => 'Spune-ne despre tine'], 'text' => ['ru' => 'Укажите свою специализацию, цели, интересы и то, что можете предложить сообществу.', 'en' => 'Add your specialization, goals, interests and what you can offer the community.', 'ro' => 'Adaugă specializarea, obiectivele, interesele și ceea ce poți oferi comunității.']],
        ['title' => ['ru' => 'Находите свои возможности', 'en' => 'Find your opportunities', 'ro' => 'Găsește oportunitățile potrivite'], 'text' => ['ru' => 'Общайтесь с участницами, экспертами и партнёрами, которые близки вашим задачам.', 'en' => 'Connect with members, experts and partners who match your goals.', 'ro' => 'Conectează-te cu membre, experți și parteneri potriviți obiectivelor tale.']],
    ],
    'cta' => true,
]))->name('members.join');

Route::get('/gala', fn () => $publicSection([
    'pageKey' => 'gala',
    'eyebrow' => ['ru' => 'События', 'en' => 'Events', 'ro' => 'Evenimente'],
    'title' => ['ru' => 'Gala', 'en' => 'Gala', 'ro' => 'Gala'],
    'intro' => ['ru' => 'Страница ежегодной гала-премии и историй женщин, которые создают изменения.', 'en' => 'A page for the annual gala award and stories of women creating change.', 'ro' => 'O pagină dedicată galei anuale și poveștilor femeilor care creează schimbare.'],
    'placeholder' => true,
]))->name('gala');

// Материалы из админки (тема miro): проекты, возможности, медиатека.
Route::get('/projects', PublicSite\ProjectsController::class)->name('projects');
Route::get('/opportunities', [PublicSite\OpportunitiesController::class, 'index'])->name('opportunities');
Route::get('/opportunities/{opportunity}', [PublicSite\OpportunitiesController::class, 'show'])->name('opportunities.show');
Route::get('/media/photos', [PublicSite\AlbumsController::class, 'index'])->name('media.photos');
Route::get('/media/photos/{album}', [PublicSite\AlbumsController::class, 'show'])->name('media.photos.show');
Route::get('/media/videos', PublicSite\VideosController::class)->name('media.videos');
Route::get('/media/publications', [PublicSite\PublicationsController::class, 'index'])->name('media.publications');
Route::get('/media/publications/{post}', [PublicSite\PublicationsController::class, 'show'])->name('media.publications.show');

// Подписка на новости (форма в подвале сайта).
Route::post('/subscribe', [PublicSite\SubscribeController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('subscribe');

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['ru', 'en', 'ro'], true), 404);

    session(['locale' => $locale]);
    cookie()->queue(cookie('locale', $locale, 60 * 24 * 365));

    return back();
})->name('language.switch');

// Telegram Webhook — POST запрос от серверов Telegram
Route::post('/telegram/webhook', function (Nutgram $bot) {
    $bot->run();
})->name('telegram.webhook');

// Account: magic-link auth (no middleware)
Route::get('/app/account/auth', [AccountController::class, 'auth'])->middleware('throttle:20,1')->name('account.auth');
Route::get('/app/account/login', [AccountController::class, 'login'])->name('account.login');
Route::post('/app/account/tma-auth', [TmaAuthController::class, 'auth'])->middleware('throttle:20,1')->name('account.tma-auth');

// Local-only account shortcut for visual development. It is unavailable outside APP_ENV=local.
Route::get('/dev/account-login', [DevAccountLoginController::class, 'index'])->name('dev.account.login');
Route::post('/dev/account-login', [DevAccountLoginController::class, 'login'])->name('dev.account.login.submit');

// Short-link redirect: /go/{code} — hides the full token from Telegram dialog
Route::get('/go/{code}', function (string $code) {
    $token = LoginToken::where('token', 'like', $code . '%')->first();

    if (! $token || ! $token->isValid()) {
        return redirect()->route('account.login')->with('error', __('account.messages.short_link_invalid'));
    }

    return redirect()->route('account.auth', ['token' => $token->token]);
})->middleware('throttle:20,1')->where('code', '[0-9a-f]{8}')->name('account.go');

// Account: protected cabinet
Route::middleware(RequireAccountAuth::class)
    ->prefix('app/account')
    ->name('account.')
    ->group(function (): void {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/profile/edit', [AccountController::class, 'profileEdit'])->name('profile.edit');
        Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::delete('/profile', [AccountController::class, 'deleteProfile'])->name('profile.delete');
        Route::get('/matches', [AccountController::class, 'matches'])->name('matches');
        Route::get('/people', [AccountController::class, 'people'])->name('people');
        Route::get('/people/{botUser}', [AccountController::class, 'showPerson'])->name('people.show');
        Route::get('/search', [AccountController::class, 'search'])->name('search');
        Route::get('/knowledge', [AccountController::class, 'knowledge'])->name('knowledge');
        Route::post('/assistant/messages', [AccountController::class, 'assistantMessage'])->middleware('throttle:20,1')->name('assistant.message');
        Route::post('/assistant/profile-update', [AccountController::class, 'assistantProfileUpdate'])->middleware('throttle:10,1')->name('assistant.profile-update');
        Route::resource('opportunities', OpportunityController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
    });

/*
|--------------------------------------------------------------------------
| Админка — /admin, всегда по-русски (перенесена из education3)
|--------------------------------------------------------------------------
| Каждый роут наследует проверку почты от группы: одного «auth» мало,
| в панель пускается только ADMIN_EMAIL.
*/

Route::middleware(['admin.locale', 'auth', 'admin.email'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('news', [Admin\PostController::class, 'index'])->name('posts.index');
        Route::get('news/create', [Admin\PostController::class, 'create'])->name('posts.create');
        Route::post('news', [Admin\PostController::class, 'store'])->name('posts.store');
        Route::get('news/{post}/edit', [Admin\PostController::class, 'edit'])->name('posts.edit');
        Route::put('news/{post}', [Admin\PostController::class, 'update'])->name('posts.update');
        Route::delete('news/{post}', [Admin\PostController::class, 'destroy'])->name('posts.destroy');

        Route::get('opportunities', [Admin\SiteOpportunityController::class, 'index'])->name('opportunities.index');
        Route::get('opportunities/create', [Admin\SiteOpportunityController::class, 'create'])->name('opportunities.create');
        Route::post('opportunities', [Admin\SiteOpportunityController::class, 'store'])->name('opportunities.store');
        Route::get('opportunities/{opportunity}/edit', [Admin\SiteOpportunityController::class, 'edit'])->name('opportunities.edit');
        Route::put('opportunities/{opportunity}', [Admin\SiteOpportunityController::class, 'update'])->name('opportunities.update');
        Route::delete('opportunities/{opportunity}', [Admin\SiteOpportunityController::class, 'destroy'])->name('opportunities.destroy');

        Route::resource('tags', Admin\TagController::class)->except(['show']);

        Route::get('albums', [Admin\AlbumController::class, 'index'])->name('albums.index');
        Route::get('albums/create', [Admin\AlbumController::class, 'create'])->name('albums.create');
        Route::post('albums', [Admin\AlbumController::class, 'store'])->name('albums.store');
        Route::get('albums/{album}/edit', [Admin\AlbumController::class, 'edit'])->name('albums.edit');
        Route::put('albums/{album}', [Admin\AlbumController::class, 'update'])->name('albums.update');
        Route::delete('albums/{album}', [Admin\AlbumController::class, 'destroy'])->name('albums.destroy');

        Route::get('videos', [Admin\VideoController::class, 'index'])->name('videos.index');
        Route::get('videos/create', [Admin\VideoController::class, 'create'])->name('videos.create');
        Route::post('videos', [Admin\VideoController::class, 'store'])->name('videos.store');
        Route::get('videos/{video}/edit', [Admin\VideoController::class, 'edit'])->name('videos.edit');
        Route::put('videos/{video}', [Admin\VideoController::class, 'update'])->name('videos.update');
        Route::delete('videos/{video}', [Admin\VideoController::class, 'destroy'])->name('videos.destroy');
        Route::post('videos/{video}/move', [Admin\VideoController::class, 'move'])->name('videos.move');

        // Карточки публичного сайта: эксперты и события (плоские карточки с переводами, как проекты).
        Route::get('experts', [Admin\ExpertController::class, 'index'])->name('experts.index');
        Route::get('experts/create', [Admin\ExpertController::class, 'create'])->name('experts.create');
        Route::post('experts', [Admin\ExpertController::class, 'store'])->name('experts.store');
        Route::get('experts/{expert}/edit', [Admin\ExpertController::class, 'edit'])->name('experts.edit');
        Route::put('experts/{expert}', [Admin\ExpertController::class, 'update'])->name('experts.update');
        Route::delete('experts/{expert}', [Admin\ExpertController::class, 'destroy'])->name('experts.destroy');
        Route::post('experts/{expert}/move', [Admin\ExpertController::class, 'move'])->name('experts.move');

        Route::get('events', [Admin\EventController::class, 'index'])->name('events.index');
        Route::get('events/create', [Admin\EventController::class, 'create'])->name('events.create');
        Route::post('events', [Admin\EventController::class, 'store'])->name('events.store');
        Route::get('events/{event}/edit', [Admin\EventController::class, 'edit'])->name('events.edit');
        Route::put('events/{event}', [Admin\EventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [Admin\EventController::class, 'destroy'])->name('events.destroy');
        Route::post('events/{event}/move', [Admin\EventController::class, 'move'])->name('events.move');

        Route::get('projects', [Admin\ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/create', [Admin\ProjectController::class, 'create'])->name('projects.create');
        Route::post('projects', [Admin\ProjectController::class, 'store'])->name('projects.store');
        Route::get('projects/{project}/edit', [Admin\ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('projects/{project}', [Admin\ProjectController::class, 'update'])->name('projects.update');
        Route::delete('projects/{project}', [Admin\ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::post('projects/{project}/move', [Admin\ProjectController::class, 'move'])->name('projects.move');

        // Профили участниц из Telegram-бота: модерация (раньше — Filament BotUserResource).
        Route::get('profiles', [Admin\ProfileController::class, 'index'])->name('profiles.index');
        Route::delete('profiles', [Admin\ProfileController::class, 'bulkDestroy'])->name('profiles.bulk-destroy');
        Route::get('profiles/{profile}', [Admin\ProfileController::class, 'show'])->name('profiles.show');
        Route::get('profiles/{profile}/edit', [Admin\ProfileController::class, 'edit'])->name('profiles.edit');
        Route::put('profiles/{profile}', [Admin\ProfileController::class, 'update'])->name('profiles.update');
        Route::post('profiles/{profile}/approve', [Admin\ProfileController::class, 'approve'])->name('profiles.approve');
        Route::post('profiles/{profile}/reject', [Admin\ProfileController::class, 'reject'])->name('profiles.reject');
        Route::delete('profiles/{profile}', [Admin\ProfileController::class, 'destroy'])->name('profiles.destroy');

        // Посты участниц из кабинета: премодерация.
        Route::get('member-posts', [Admin\MemberPostController::class, 'index'])->name('member-posts.index');
        Route::get('member-posts/{post}', [Admin\MemberPostController::class, 'show'])->name('member-posts.show');
        Route::post('member-posts/{post}/approve', [Admin\MemberPostController::class, 'approve'])->name('member-posts.approve');
        Route::post('member-posts/{post}/reject', [Admin\MemberPostController::class, 'reject'])->name('member-posts.reject');
        Route::delete('member-posts/{post}', [Admin\MemberPostController::class, 'destroy'])->name('member-posts.destroy');

        Route::get('subscribers', [Admin\SubscriberController::class, 'index'])->name('subscribers.index');
        Route::get('subscribers/export.csv', [Admin\SubscriberController::class, 'exportCsv'])->name('subscribers.export.csv');
        Route::get('subscribers/export.txt', [Admin\SubscriberController::class, 'exportTxt'])->name('subscribers.export.txt');
        Route::delete('subscribers/{subscriber}', [Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');

        Route::get('statistics', [Admin\StatisticsController::class, 'index'])->name('statistics.index');
        Route::get('statistics/pdf', [Admin\StatisticsController::class, 'pdf'])->name('statistics.pdf');

        // Внешний сайт: «Настройки сайта» — сжатие изображений и тема публичного сайта.
        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::put('settings/theme', [Admin\ThemeSettingController::class, 'site'])->name('settings.theme');

        // Кабинеты участниц: инфопанель и «Настройки кабинетов» — тема кабинета, ИИ, база знаний.
        // (Профили, посты участниц и статистика — выше и ниже; лагерь определяет App\Support\AdminCamp.)
        Route::get('cabinets', [Admin\CabinetDashboardController::class, 'index'])->name('cabinets.dashboard');
        Route::get('cabinets/settings', [Admin\CabinetSettingController::class, 'edit'])->name('cabinets.settings');
        Route::put('cabinets/settings/theme', [Admin\ThemeSettingController::class, 'cabinet'])->name('cabinets.settings.theme');
        Route::put('cabinets/settings/knowledge', [Admin\AssistantKnowledgeController::class, 'update'])->name('cabinets.settings.knowledge');
        Route::put('cabinets/settings/ai', [Admin\AiSettingController::class, 'update'])->name('cabinets.settings.ai.update');
        Route::post('cabinets/settings/ai/test/{provider}', [Admin\AiSettingController::class, 'test'])->name('cabinets.settings.ai.test');

        Route::post('uploads', [Admin\UploadController::class, 'store'])->name('uploads.store');
    });

Route::middleware('admin.locale')->group(function () {
    require __DIR__.'/auth.php';
});
