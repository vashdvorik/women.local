<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\OnboardingRequest;
use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Шаг после подтверждения почты: анкета (имя, телефон, о себе, чего ждёте). После неё участнице предлагаются тарифы.
 * Анкета уходит на проверку администратору (статус pending), но тариф можно купить, не дожидаясь одобрения.
 */
class OnboardingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        /** @var BotUser $user */
        $user = Auth::guard('participant')->user();

        if ($user->isProfileComplete()) {
            return redirect()->route('account.subscription');
        }

        return view('account.auth.onboarding', ['user' => $user]);
    }

    public function store(OnboardingRequest $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = Auth::guard('participant')->user();

        $user->update($request->validated());
        $user->forceFill(['profile_completed_at' => $user->profile_completed_at ?? now()])->save();

        ComputeUserEmbedding::dispatch($user);

        return redirect()->route('account.subscription');
    }
}
