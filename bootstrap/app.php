<?php

use App\Http\Middleware\AdminLocale;
use App\Http\Middleware\EnsureAdminEmail;
use App\Http\Middleware\RequirePlan;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'admin.email'  => EnsureAdminEmail::class,
            'admin.locale' => AdminLocale::class,
            'plan'         => RequirePlan::class,
        ]);

        // Вебхук Telegram, оповещение банка (ResultURL) и возврат участницы из банка (SuccessURL / FailURL) приходят
        // с чужого сайта и без нашего CSRF-токена. Подлинность оповещения проверяет ResultHandler по подписи банка,
        // а страницы возврата ничего не меняют: статус берётся из базы и проверяется запросом GetState.
        $middleware->validateCsrfTokens(except: [
            'telegram/webhook',
            'payment/result',
            'app/account/subscription/success',
            'app/account/subscription/fail',
            'dev/fake-bank',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ключи ИИ-провайдеров не должны возвращаться в форму после ошибки валидации.
        $exceptions->dontFlash(['gemini_api_key', 'openrouter_api_key', 'deepseek_api_key']);

        // Собственный экран 404 внутри админки; публичные ошибки не подменяются.
        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            $isAdmin = $request->user()?->email === config('admin.email');

            if ($e->getStatusCode() === 404 && $isAdmin && $request->is('admin', 'admin/*')) {
                // Ошибка случилась до middleware маршрута (маршрут не найден), поэтому язык админки выставляем здесь.
                app()->setLocale(AdminLocale::resolve($request));

                return response()->view('admin.errors.404', [], 404);
            }
        });
    })->create();
