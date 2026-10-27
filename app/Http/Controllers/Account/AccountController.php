<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ProfileUpdateRequest;
use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use App\Services\AvatarStore;
use App\Services\EmbeddingService;
use App\Services\AiAssistantService;
use App\Services\MatchingService;
use App\Support\CabinetHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        // Главная — навигационная для всех тарифов: список «С чего начать», плитки разделов (закрытые для Open с замком),
        // «Что нового». У Open вместо «Что нового» — материалы публичного сайта и предложение перейти на Community.
        return view('account.home', ['user' => $user] + CabinetHome::build($user, app()->getLocale()));
    }

    /** Чат с ИИ-помощником кабинета (подбор контактов и возможностей); открыт с тарифа Community. */
    public function assistant(): View
    {
        return $this->themedView('assistant');
    }

    public function profile(): View
    {
        return $this->themedView('profile');
    }

    public function profileEdit(): View
    {
        return $this->themedView('profile-edit');
    }

    public function updateProfile(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');
        $user->update($request->safe()->except(['photo', 'remove_photo']));

        // Фото: новое заменяет прежнее (и фото из Telegram), галочка «Убрать фото» возвращает букву-заглушку.
        if ($request->hasFile('photo')) {
            app(AvatarStore::class)->store($user, $request->file('photo'));
        } elseif ($request->boolean('remove_photo')) {
            app(AvatarStore::class)->remove($user);
        }

        ComputeUserEmbedding::dispatch($user);

        return redirect()->route('account.profile')
            ->with('success', __('account.messages.profile_updated'));
    }

    public function matches(MatchingService $matcher): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $accountUser->markSeen('matches');
        $matches = $matcher->topMatches($accountUser);

        return $this->themedView('matches', compact('matches'));
    }

    public function people(): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $people = BotUser::discoverable()
            ->whereKeyNot($accountUser->getKey())
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone', 'telegram_username', 'description', 'expectation', 'avatar_path']);

        return $this->themedView('people', compact('people'));
    }

    public function showPerson(BotUser $botUser): View
    {
        // В каталоге видны только одобренные участницы с действующим платным тарифом, не скрытые администратором:
        // остальных открыть нельзя (скрытый открывает только свою страницу). Раздел открыт с тарифа Community.
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        abort_unless(
            $botUser->is($accountUser) || BotUser::discoverable()->whereKey($botUser->getKey())->exists(),
            404,
        );

        return $this->themedView('person', ['person' => $botUser]);
    }

    public function search(Request $request, EmbeddingService $embedder, MatchingService $matcher): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $accountUser->markSeen('search');

        $query   = trim((string) $request->query('q', ''));
        $results = null;

        if ($query !== '') {
            $request->validate(['q' => ['string', 'max:500']]);

            try {
                $vector  = $embedder->embedQuery($query);
                $results = $matcher->searchByQuery($vector, $accountUser);
            } catch (\Throwable $e) {
                logger()->warning('AI search failed', ['error' => $e->getMessage()]);
                $results = collect();
            }
        }

        return $this->themedView('search', compact('query', 'results'));
    }

    public function knowledge(): View
    {
        return $this->themedView('knowledge');
    }

    public function assistantMessage(Request $request, AiAssistantService $assistant): JsonResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.role' => ['required', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:1600'],
        ]);

        try {
            $reply = $assistant->respond(
                $user,
                trim($data['message']),
                $data['history'] ?? [],
                app()->getLocale(),
            );

            // Шаг «Спросите ИИ-помощника» на главной отмечается, когда она задала настоящий вопрос и получила ответ.
            $user->markSeen('assistant');

            return response()->json($reply);
        } catch (\Throwable $e) {
            logger()->warning('AI assistant failed', ['message' => $e->getMessage()]);

            return response()->json([
                'message' => __('account.assistant.unavailable'),
            ], 503);
        }
    }

    public function assistantProfileUpdate(Request $request): JsonResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');
        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:1000'],
            'expectation' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! array_filter($data, fn ($value): bool => is_string($value) && trim($value) !== '')) {
            return response()->json(['message' => __('account.assistant.nothing_to_save')], 422);
        }

        $user->update($data);
        ComputeUserEmbedding::dispatch($user);

        return response()->json(['message' => __('account.assistant.profile_saved')]);
    }

    public function deleteProfile(Request $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        Auth::guard('participant')->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login')
            ->with('success', __('account.messages.profile_deleted'));
    }

    /**
     * Render an account page from the active cabinet theme directory.
     *
     * @param array<string, mixed> $data
     */
    private function themedView(string $page, array $data = []): View
    {
        $theme = view()->shared('accountTheme');
        $theme = is_string($theme) && array_key_exists($theme, \App\Models\SiteSetting::ACCOUNT_THEMES)
            ? $theme
            : \App\Models\SiteSetting::DEFAULT_ACCOUNT_THEME;

        return view("themes.account.{$theme}.pages.{$page}", $data);
    }
}
