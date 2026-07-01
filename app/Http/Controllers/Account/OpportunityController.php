<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\OpportunityRequest;
use App\Jobs\NotifyOpportunity;
use App\Models\BotUser;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(): View
    {
        $opportunities = Opportunity::with('author')
            ->latest()
            ->paginate(15);

        return view('account.opportunities.index', compact('opportunities'));
    }

    public function create(): View
    {
        return view('account.opportunities.create');
    }

    public function store(OpportunityRequest $request): RedirectResponse
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $opportunity = Opportunity::create([
            'bot_user_id' => $accountUser->id,
            ...$request->validated(),
        ]);

        NotifyOpportunity::dispatch($opportunity);

        return redirect()->route('account.opportunities.index')
            ->with('success', 'Публикация добавлена. Участницы получат уведомление в Telegram.');
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        abort_if($opportunity->bot_user_id !== $accountUser->id, 403);

        $opportunity->delete();

        return redirect()->route('account.opportunities.index')
            ->with('success', 'Публикация удалена.');
    }
}
