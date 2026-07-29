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

        return $this->themedView('opportunities.index', compact('opportunities'));
    }

    public function create(): View
    {
        return $this->themedView('opportunities.create');
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
            ->with('success', __('account.messages.opportunity_created'));
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        abort_if($opportunity->bot_user_id !== $accountUser->id, 403);

        $opportunity->delete();

        return redirect()->route('account.opportunities.index')
            ->with('success', __('account.messages.opportunity_deleted'));
    }

    /**
     * Render an opportunity page from the active cabinet theme directory.
     *
     * @param array<string, mixed> $data
     */
    private function themedView(string $page, array $data = []): View
    {
        $theme = view()->shared('accountTheme');
        $theme = is_string($theme) && array_key_exists($theme, \App\Models\SiteSetting::ACCOUNT_THEMES)
            ? $theme
            : 'classic';

        return view("themes.account.{$theme}.pages.{$page}", $data);
    }
}
