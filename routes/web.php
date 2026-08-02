<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\DevAccountLoginController;
use App\Http\Controllers\Account\OpportunityController;
use App\Http\Controllers\Account\TmaAuthController;
use App\Http\Middleware\RequireAccountAuth;
use App\Models\LoginToken;
use App\Services\PublicThemeView;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

Route::get('/', function () {
    return PublicThemeView::render('landing');
});

Route::get('/members', function () {
    return PublicThemeView::render('members');
})->name('members');

Route::get('/events', function () {
    return PublicThemeView::render('events');
})->name('events');

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

Route::get('/about/priorities', fn () => $publicSection([
    'pageKey' => 'priorities',
    'eyebrow' => ['ru' => 'О нас', 'en' => 'About us', 'ro' => 'Despre noi'],
    'title' => ['ru' => 'Приоритеты платформы', 'en' => 'Our priorities', 'ro' => 'Prioritățile platformei'],
    'intro' => ['ru' => 'Мы создаём практическую среду, в которой женщины могут развивать бизнес, находить поддержку и открывать новые возможности.', 'en' => 'We create a practical environment where women can grow their businesses, find support and discover new opportunities.', 'ro' => 'Creăm un mediu practic în care femeile își pot dezvolta afacerile, găsi sprijin și descoperi oportunități noi.'],
    'items' => [
        ['ru' => 'Доступ к знаниям и развитию', 'en' => 'Access to knowledge and growth', 'ro' => 'Acces la cunoștințe și dezvoltare'],
        ['ru' => 'Связи между предпринимательницами', 'en' => 'Connections between women entrepreneurs', 'ro' => 'Conexiuni între femeile antreprenoare'],
        ['ru' => 'Поддержка новых проектов и партнёрств', 'en' => 'Support for new projects and partnerships', 'ro' => 'Sprijin pentru proiecte și parteneriate noi'],
    ],
]))->name('about.priorities');

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
Route::get('/members/experts', fn () => redirect()->route('members'))->name('members.experts');

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

Route::get('/projects', fn () => $publicSection([
    'pageKey' => 'projects',
    'eyebrow' => ['ru' => 'Платформа', 'en' => 'Platform', 'ro' => 'Platformă'],
    'title' => ['ru' => 'Проекты', 'en' => 'Projects', 'ro' => 'Proiecte'],
    'intro' => ['ru' => 'Здесь будут собраны проекты, инициативы и совместные программы сообщества.', 'en' => 'This page will collect community projects, initiatives and joint programmes.', 'ro' => 'Aici vor fi prezentate proiectele, inițiativele și programele comune ale comunității.'],
    'placeholder' => true,
]))->name('projects');

Route::get('/opportunities', fn () => $publicSection([
    'pageKey' => 'opportunities',
    'eyebrow' => ['ru' => 'Платформа', 'en' => 'Platform', 'ro' => 'Platformă'],
    'title' => ['ru' => 'Возможности', 'en' => 'Opportunities', 'ro' => 'Oportunități'],
    'intro' => ['ru' => 'Гранты, программы, события и партнёрские предложения для развития бизнеса.', 'en' => 'Grants, programmes, events and partnership offers for business growth.', 'ro' => 'Granturi, programe, evenimente și oferte de parteneriat pentru dezvoltarea afacerii.'],
    'placeholder' => true,
]))->name('opportunities');

Route::get('/media/photos', fn () => $publicSection([
    'pageKey' => 'photos',
    'eyebrow' => ['ru' => 'Медиатека', 'en' => 'Media library', 'ro' => 'Mediatecă'],
    'title' => ['ru' => 'Фото', 'en' => 'Photos', 'ro' => 'Fotografii'],
    'intro' => ['ru' => 'Фотографии с мероприятий, встреч и важных моментов сообщества.', 'en' => 'Photos from events, meetings and important community moments.', 'ro' => 'Fotografii de la evenimente, întâlniri și momente importante ale comunității.'],
    'placeholder' => true,
]))->name('media.photos');

Route::get('/media/videos', fn () => $publicSection([
    'pageKey' => 'videos',
    'eyebrow' => ['ru' => 'Медиатека', 'en' => 'Media library', 'ro' => 'Mediatecă'],
    'title' => ['ru' => 'Видео', 'en' => 'Videos', 'ro' => 'Video'],
    'intro' => ['ru' => 'Видео с мероприятий, интервью и образовательных программ платформы.', 'en' => 'Videos from events, interviews and platform learning programmes.', 'ro' => 'Videoclipuri de la evenimente, interviuri și programele educaționale ale platformei.'],
    'placeholder' => true,
]))->name('media.videos');

Route::get('/media/publications', fn () => $publicSection([
    'pageKey' => 'publications',
    'eyebrow' => ['ru' => 'Медиатека', 'en' => 'Media library', 'ro' => 'Mediatecă'],
    'title' => ['ru' => 'Публикации', 'en' => 'Publications', 'ro' => 'Publicații'],
    'intro' => ['ru' => 'Публикации, статьи и материалы платформы будут собраны в отдельном разделе медиатеки.', 'en' => 'Platform publications, articles and materials will be collected in a dedicated media section.', 'ro' => 'Publicațiile, articolele și materialele platformei vor fi reunite într-o secțiune dedicată.'],
    'placeholder' => true,
]))->name('media.publications');

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
        Route::resource('opportunities', OpportunityController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
    });
