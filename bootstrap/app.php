<?php

use App\Http\Middleware\AdminLocale;
use App\Http\Middleware\EnsureAdminEmail;
use App\Http\Middleware\FlushPublicCacheAfterAdminChange;
use App\Http\Middleware\KeepNamesTogether;
use App\Http\Middleware\RequirePlan;
use App\Http\Middleware\SetLocale;
use App\Support\ErrorPage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
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
            KeepNamesTogether::class,
        ]);

        $middleware->alias([
            'admin.email'  => EnsureAdminEmail::class,
            'admin.locale' => AdminLocale::class,
            'admin.flush-public-cache' => FlushPublicCacheAfterAdminChange::class,
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

        // Слишком большой запрос (PDF каталога больше post_max_size): понятная страница вместо «Oops! An Error Occurred».
        // Сессия здесь ещё не запущена (проверка размера идёт раньше), поэтому это страница, а не возврат с сообщением.
        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if ($e->getStatusCode() === 413 && $request->is('admin', 'admin/*')) {
                return response()->view('errors.413', [], 413);
            }
        });

        // Ошибки форм, после которых человека лучше вернуть на ту же страницу с понятным сообщением, чем показать страницу
        // ошибки: форма простояла дольше сессии (419), слишком частые отправки (429), просроченная ссылка из письма (403).
        // Запросы AJAX/JSON получают обычный ответ с кодом.
        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $status = $e->getStatusCode();

            // Ссылка подтверждения почты просрочена (живёт сутки) или повреждена: на страницу входа, где можно запросить новую.
            if ($e instanceof InvalidSignatureException && $request->routeIs('account.verification.verify')) {
                app()->setLocale(ErrorPage::locale($request));

                return redirect()->route('account.login')->with('error', __('participant.verification.invalid'));
            }

            // Только кабинет и админка: их страницы показывают сообщение «error». У публичных форм (подписка, контакты) свои
            // ключи сообщений, там остаётся страница ошибки с понятным текстом.
            if (in_array($status, [419, 429], true) && ! $request->isMethod('GET') && ! $request->isMethod('HEAD')
                && $request->is('app/account', 'app/account/*', 'admin', 'admin/*')) {
                app()->setLocale(ErrorPage::locale($request));

                $message = $status === 419
                    ? __('errors.session_expired')
                    : __('errors.too_many', ['seconds' => max(1, (int) ($e->getHeaders()['Retry-After'] ?? 60))]);

                // Введённое сохраняем, но пароли и служебное не возвращаем в форму.
                return redirect()->back(fallback: url('/'))
                    ->withInput($request->except(['_token', 'password', 'password_confirmation', 'current_password', 'gemini_api_key', 'openrouter_api_key', 'deepseek_api_key']))
                    ->with('error', $message);
            }

            return null;
        });

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
