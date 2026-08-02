<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        $user = BotUser::approved()->findOrFail((int) $request->input('user_id'));

        $request->session()->regenerate();
        $request->session()->put([
            'account_telegram_id' => $user->telegram_id,
            '_account_expires' => now()->addDays(7)->timestamp,
        ]);

        return redirect()->route('account.index');
    }

    private function ensureLocalEnvironment(): void
    {
        abort_unless(app()->environment('local'), 404);
    }

    private function loginUsers()
    {
        $users = BotUser::approved()
            ->orderBy('full_name')
            ->get(['id', 'telegram_id', 'full_name', 'telegram_username']);

        if ($users->isNotEmpty()) {
            return $users;
        }

        // Keep the local project immediately usable even when its database is empty.
        $demoUser = BotUser::updateOrCreate(
            ['telegram_id' => 9876543210],
            [
                'telegram_username' => 'local_demo',
                'first_name' => 'Демо',
                'full_name' => 'Демо участница',
                'description' => 'Локальный профиль для разработки интерфейса кабинета.',
                'expectation' => 'Тестирование возможностей платформы.',
                'status' => BotUser::STATUS_APPROVED,
                'approved_at' => now(),
            ],
        );

        return collect([$demoUser]);
    }
}
