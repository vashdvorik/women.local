<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Models\ManagerMessage;
use App\Models\SiteSetting;
use App\Services\AiManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AI-менеджер кабинета: страница с чатом, история переписки и отправка вопроса. Доступен участницам с тарифом.
 * Сам менеджер и его границы описаны в App\Services\AiManagerService.
 */
class ManagerController extends Controller
{
    public function index(): View
    {
        $theme = view()->shared('accountTheme');
        $theme = is_string($theme) && array_key_exists($theme, SiteSetting::ACCOUNT_THEMES) ? $theme : SiteSetting::DEFAULT_ACCOUNT_THEME;

        return view("themes.account.{$theme}.pages.manager.index");
    }

    /** Последние реплики переписки (по порядку), чтобы чат открывался с того места, где остановились. */
    public function history(): JsonResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        $messages = ManagerMessage::where('bot_user_id', $user->id)
            ->latest('id')->limit(40)->get(['role', 'content'])
            ->reverse()
            ->map(fn (ManagerMessage $m): array => ['role' => $m->role, 'content' => $m->content])
            ->values();

        return response()->json(['messages' => $messages]);
    }

    public function message(Request $request, AiManagerService $manager): JsonResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        $data = $request->validate(['message' => ['required', 'string', 'max:1000']]);

        if ($manager->askedToday($user) >= $manager->dailyLimit()) {
            return response()->json(['message' => __('account.manager.limit', ['count' => $manager->dailyLimit()])], 429);
        }

        $answer = $manager->ask($user, trim($data['message']), app()->getLocale());

        return response()->json(['reply' => $answer['reply']]);
    }
}
