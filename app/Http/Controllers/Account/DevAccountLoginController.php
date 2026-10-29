<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Enums\Plan;
use App\Models\BotUser;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Вход в кабинет одной кнопкой для разработки интерфейса. Существует только при APP_ENV=local. */
class DevAccountLoginController extends Controller
{
    public function index(): View
    {
        $this->ensureLocalEnvironment();

        return view('account.dev-login', [
            'users' => $this->loginUsers(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $this->ensureLocalEnvironment();

        $user = BotUser::query()->whereNotNull('email')->findOrFail((int) $request->input('user_id'));

        Auth::guard('participant')->login($user);
        $request->session()->regenerate();

        return redirect()->route('account.index');
    }

    private function ensureLocalEnvironment(): void
    {
        abort_unless(app()->environment('local'), 404);
    }

    private function loginUsers()
    {
        $users = BotUser::query()
            ->whereNotNull('email')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email', 'plan', 'plan_ends_at']);

        if ($users->isNotEmpty()) {
            return $users;
        }

        // Keep the local project immediately usable even when its database is empty.
        $demoUser = BotUser::updateOrCreate(
            ['email' => 'demo@women.test'],
            [
                'first_name' => 'Демо',
                'full_name' => 'Демо участница',
                'phone' => '+373 600 00 000',
                'description' => 'Локальный профиль для разработки интерфейса кабинета.',
                'expectation' => 'Тестирование возможностей платформы.',
                'status' => BotUser::STATUS_APPROVED,
                'approved_at' => now(),
            ],
        );
        $demoUser->forceFill(['email_verified_at' => now(), 'profile_completed_at' => now()])->save();

        // Локальный вход нужен для разработки интерфейса: демо-участнице сразу оплачен Active.
        if (! $demoUser->hasPlan(Plan::Active)) {
            app(SubscriptionService::class)->grant($demoUser, Plan::Active, 12, note: 'Локальная демо-участница');
        }

        return collect([$demoUser->fresh()]);
    }
}
