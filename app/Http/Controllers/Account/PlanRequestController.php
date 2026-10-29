<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Enums\Plan;
use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Services\Subscriptions\PlanPurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** «Оставить заявку» на закрытый тариф: менеджер позвонит по телефону из анкеты и откроет покупку. */
class PlanRequestController extends Controller
{
    public function store(Request $request, PlanPurchase $purchase): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        $data = $request->validate([
            'plan' => ['required', Rule::in(array_map(fn (Plan $plan): string => $plan->value, Plan::paid()))],
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'plan.required' => __('subscription.errors.invalid_plan'),
            'plan.in' => __('subscription.errors.invalid_plan'),
        ]);

        $plan = Plan::from($data['plan']);

        if (! $purchase->isClosed($plan) || $purchase->canBuy($user, $plan)) {
            return redirect()->route('account.subscription')->with('error', __('participant.plans.not_closed'));
        }

        if (blank($user->phone)) {
            return redirect()->route('account.profile.edit')->with('error', __('participant.plans.phone_missing'));
        }

        $purchase->request($user, $plan, $data['comment'] ?? null);

        return redirect()->route('account.subscription')->with('success', __('participant.plans.requested_flash'));
    }
}
