<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Подписка на новости платформы (форма в подвале сайта). Почта уникальна: повторная
 * подписка не плодит строки. Список, поиск и выгрузка — в админке, раздел «Подписчики».
 *
 * Сообщения формы — на трёх языках в самой вёрстке (data-lang), поэтому здесь только
 * признак успеха или ошибки.
 */
class SubscribeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:191'],
            'name' => ['nullable', 'string', 'max:191'],
            'consent' => ['accepted'],
            // Скрытое поле-приманка: заполнено — значит бот.
            'website' => ['prohibited'],
        ]);

        $back = redirect()->to(url()->previous().'#subscribe');

        if ($validator->fails()) {
            return $back->with('subscribe_error', true)->withInput($request->only('email', 'name'));
        }

        $data = $validator->validated();

        Subscriber::updateOrCreate(
            ['email' => mb_strtolower($data['email'])],
            filled($data['name'] ?? null) ? ['name' => $data['name']] : [],
        );

        return $back->with('subscribed', true);
    }
}
