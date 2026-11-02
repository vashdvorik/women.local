<?php

namespace App\Providers;

use App\Services\Payments\WebPayment\BankGateway;
use App\Services\Payments\WebPayment\BankSignature;
use App\Services\Payments\WebPayment\FakeGateway;
use App\Services\Payments\WebPayment\Gateway;
use App\Support\PublicCache;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // asset_v(): адрес файла с меткой версии. Подключается здесь, чтобы не менять composer.json (на сервере
        // composer install всё равно идёт после каждого git pull, но так обновление не зависит от автозагрузки файлов).
        require_once app_path('Support/helpers.php');

        // Канал связи с банком выбирается настройкой webpayment.driver (docs/SUBSCRIPTIONS.md): неизвестное значение
        // означает настоящий банк, а не имитатор, чтобы опечатка в .env не превратила боевой сервер в «песочницу».
        $this->app->bind(Gateway::class, fn () => config('webpayment.driver') === 'fake'
            ? new FakeGateway()
            : new BankGateway(BankSignature::fromConfig()));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Кеш публичных страниц сбрасывается при любой правке новостей, фото и других разделов (App\Support\PublicCache)
        // и при `php artisan optimize:clear`, то есть при обычной выкладке.
        PublicCache::watch();
        $this->optimizes(clear: 'public-cache:clear');

        // 20 auth attempts per minute per IP — prevents brute-force on tokens
        RateLimiter::for('20,1', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // Формы кабинета: регистрация, вход, «Забыли пароль», новый пароль. Раньше лимит был «N в минуту с одного IP», а у
        // мобильных операторов и в офисах один IP у многих людей: часть участниц упиралась в чужие попытки. Теперь IP-лимит
        // широкий (от флуда), а узкие лимиты считаются по почте: один адрес не получит лавину писем, а разные люди не мешают друг другу.
        $email = fn (Request $request): string => Str::lower(trim((string) $request->input('email')));

        RateLimiter::for('account-register', fn (Request $request) => [
            Limit::perMinute(40)->by('ip:'.$request->ip()),
            Limit::perMinute(4)->by('ip-email:'.$request->ip().'|'.$email($request)),
            Limit::perHour(6)->by('email:'.$email($request)),
        ]);

        RateLimiter::for('account-forgot', fn (Request $request) => [
            Limit::perMinute(40)->by('ip:'.$request->ip()),
            Limit::perMinute(3)->by('ip-email:'.$request->ip().'|'.$email($request)),
            Limit::perHour(6)->by('email:'.$email($request)),
        ]);

        RateLimiter::for('account-login', fn (Request $request) => [
            Limit::perMinute(120)->by('ip:'.$request->ip()),
            Limit::perMinute(20)->by('ip-email:'.$request->ip().'|'.$email($request)),
        ]);

        RateLimiter::for('account-reset', fn (Request $request) => [
            Limit::perMinute(60)->by('ip:'.$request->ip()),
            Limit::perMinute(10)->by('ip-email:'.$request->ip().'|'.$email($request)),
        ]);

        // Грубый заслон от флуда на форму входа в админку: не больше 30 запросов
        // в минуту с одного адреса. Настоящий перебор пароля (в том числе
        // распределённый) режется в App\Http\Requests\Auth\LoginRequest.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
    }
}
