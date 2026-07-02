<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ProfileUpdateRequest;
use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use App\Models\LoginToken;
use App\Services\EmbeddingService;
use App\Services\MatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function login(): View
    {
        return view('account.login');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'regex:/^[a-zA-Z0-9_]{3,32}$/'],
        ], [
            'username.required' => __('account.validation.username_required'),
            'username.regex'    => __('account.validation.username_regex'),
        ]);

        $username = ltrim($request->input('username'), '@');

        $user = BotUser::where('telegram_username', $username)
            ->where('status', BotUser::STATUS_APPROVED)
            ->first();

        if (! $user) {
            return redirect()->route('account.login')->with('sent', true);
        }

        $token     = LoginToken::generateFor((int) $user->telegram_id);
        $url       = url('/go/' . substr($token->token, 0, 8));
        $firstName = explode(' ', (string) $user->full_name)[0];

        Http::post('https://api.telegram.org/bot' . config('nutgram.token') . '/sendMessage', [
            'chat_id'      => $user->telegram_id,
            'text'         => __('account.telegram_messages.login_link', ['name' => $firstName]),
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    ['text' => __('account.telegram_messages.login_button'), 'url' => $url],
                ]],
            ]),
        ]);

        return redirect()->route('account.login')->with('sent', true);
    }

    public function auth(Request $request): RedirectResponse
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return redirect()->route('account.login')
                ->with('error', __('account.messages.invalid_link'));
        }

        $loginToken = LoginToken::where('token', $token)->first();

        if (! $loginToken || ! $loginToken->isValid()) {
            return redirect()->route('account.login')
                ->with('error', __('account.messages.expired_link'));
        }

        $user = BotUser::where('telegram_id', $loginToken->telegram_id)
            ->where('status', BotUser::STATUS_APPROVED)
            ->first();

        if (! $user) {
            return redirect()->route('account.login')
                ->with('error', __('account.messages.access_closed'));
        }

        $request->session()->regenerate();
        session(['account_telegram_id' => $user->telegram_id]);
        session()->put('_account_expires', now()->addDays(7)->timestamp);

        return redirect()->route('account.index');
    }

    public function index(): View
    {
        return view('account.index');
    }

    public function profile(): View
    {
        return view('account.profile');
    }

    public function profileEdit(): View
    {
        return view('account.profile-edit');
    }

    public function updateProfile(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');
        $user->update($request->validated());

        ComputeUserEmbedding::dispatch($user);

        return redirect()->route('account.profile')
            ->with('success', __('account.messages.profile_updated'));
    }

    public function matches(MatchingService $matcher): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $matches = $matcher->topMatches($accountUser);

        return view('account.matches', compact('matches'));
    }

    public function people(): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $people = BotUser::approved()
            ->where('telegram_id', '!=', $accountUser->telegram_id)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'telegram_username', 'description', 'expectation', 'avatar_path']);

        return view('account.people', compact('people'));
    }

    public function showPerson(BotUser $botUser): View
    {
        abort_if($botUser->status !== BotUser::STATUS_APPROVED, 404);

        return view('account.person', ['person' => $botUser]);
    }

    public function search(Request $request, EmbeddingService $embedder, MatchingService $matcher): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $query   = trim((string) $request->query('q', ''));
        $results = null;

        if ($query !== '') {
            $request->validate(['q' => ['string', 'max:500']]);

            try {
                $vector  = $embedder->embed($query);
                $results = $matcher->searchByQuery($vector, $accountUser);
            } catch (\Throwable $e) {
                logger()->warning('AI search failed', ['error' => $e->getMessage()]);
                $results = collect();
            }
        }

        return view('account.search', compact('query', 'results'));
    }

    public function knowledge(): View
    {
        return view('account.knowledge');
    }

    public function deleteProfile(Request $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        Http::post('https://api.telegram.org/bot' . config('nutgram.token') . '/sendMessage', [
            'chat_id'      => $user->telegram_id,
            'text'         => __('account.telegram_messages.profile_deleted'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
                'inline_keyboard' => [[
                    ['text' => __('account.telegram_messages.restart_button'), 'callback_data' => 'restart'],
                ]],
            ]),
        ]);

        $user->delete();

        session()->forget('account_telegram_id');
        session()->forget('_account_expires');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login')
            ->with('success', __('account.messages.profile_deleted'));
    }

    public function logout(Request $request): RedirectResponse
    {
        session()->forget('account_telegram_id');
        session()->forget('_account_expires');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login', ['logout' => '1']);
    }
}
