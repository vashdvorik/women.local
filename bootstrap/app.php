<?php

use App\Http\Middleware\AdminLocale;
use App\Http\Middleware\EnsureAdminEmail;
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
        ]);

        $middleware->validateCsrfTokens(except: [
            'telegram/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ключи ИИ-провайдеров не должны возвращаться в форму после ошибки валидации.
        $exceptions->dontFlash(['gemini_api_key', 'openrouter_api_key', 'deepseek_api_key']);

        // Собственный экран 404 внутри админки; публичные ошибки не подменяются.
        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            $isAdmin = $request->user()?->email === config('admin.email');

            if ($e->getStatusCode() === 404 && $isAdmin && $request->is('admin', 'admin/*')) {
                return response()->view('admin.errors.404', [], 404);
            }
        });
    })->create();
