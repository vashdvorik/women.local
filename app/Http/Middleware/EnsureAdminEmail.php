<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminEmail
{
    /**
     * В панель пускается ровно одна почта из окружения. Ролей нет, поэтому
     * `auth` сам по себе доступ к админке не даёт.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user()?->email === config('admin.email'),
            403
        );

        return $next($request);
    }
}
