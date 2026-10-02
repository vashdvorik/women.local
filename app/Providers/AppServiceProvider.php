<?php

namespace App\Providers;

use App\Services\Payments\WebPayment\BankGateway;
use App\Services\Payments\WebPayment\BankSignature;
use App\Services\Payments\WebPayment\FakeGateway;
use App\Services\Payments\WebPayment\Gateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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

        // 20 auth attempts per minute per IP — prevents brute-force on tokens
        RateLimiter::for('20,1', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // Грубый заслон от флуда на форму входа в админку: не больше 30 запросов
        // в минуту с одного адреса. Настоящий перебор пароля (в том числе
        // распределённый) режется в App\Http\Requests\Auth\LoginRequest.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
    }
}
