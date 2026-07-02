<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\OpportunityController;
use App\Http\Controllers\Account\TmaAuthController;
use App\Http\Middleware\RequireAccountAuth;
use App\Models\LoginToken;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

Route::get('/', function () {
    return view('landing', [
        'landingTheme' => SiteSetting::landingTheme(),
    ]);
});

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
