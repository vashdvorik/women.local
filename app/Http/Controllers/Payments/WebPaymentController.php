<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\WebPayment\ResultHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * ResultURL: сюда сам банк присылает итог оплаты (GET или POST, как выбрано при регистрации; параметров в адресе
 * быть не должно: «не допускается использование & и ?»). Без сессии и CSRF-токена, поэтому вся защита — в
 * App\Services\Payments\WebPayment\ResultHandler (подпись, сверка счёта, подтверждение через GetState).
 *
 * Ответ банку намеренно скуден: «OK» или «ERROR». Подробности — в журнале.
 */
class WebPaymentController extends Controller
{
    public function result(Request $request, ResultHandler $handler): Response
    {
        $outcome = $handler->handle($request->all());

        return response($outcome === ResultHandler::OK ? 'OK' : 'ERROR', $outcome === ResultHandler::OK ? 200 : 400)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
