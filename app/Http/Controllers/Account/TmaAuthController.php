<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TmaAuthController extends Controller
{
    /**
     * Authenticate via Telegram Mini App initData.
     *
     * Telegram sends a signed `initData` string when the app opens inside
     * the Telegram client. We verify the HMAC-SHA256 signature using the
     * bot token, extract the user's telegram_id, and create a session.
     */
    public function auth(Request $request): JsonResponse
    {
        $initData = (string) $request->input('init_data', '');

        if ($initData === '') {
            return response()->json(['ok' => false, 'reason' => 'missing_data'], 400);
        }

        $telegramUser = $this->verifyInitData($initData);

        if ($telegramUser === null) {
            return response()->json(['ok' => false, 'reason' => 'invalid_signature'], 403);
        }

        $telegramId = (int) ($telegramUser['id'] ?? 0);

        if ($telegramId === 0) {
            return response()->json(['ok' => false, 'reason' => 'missing_user'], 400);
        }

        $user = BotUser::where('telegram_id', $telegramId)
            ->where('status', BotUser::STATUS_APPROVED)
            ->first();

        if (! $user) {
            return response()->json(['ok' => false, 'reason' => 'not_member'], 403);
        }

        $request->session()->regenerate();
        session(['account_telegram_id' => $user->telegram_id]);
        session()->put('_account_expires', now()->addDays(7)->timestamp);

        return response()->json([
            'ok'       => true,
            'redirect' => route('account.index'),
        ]);
    }

    /**
     * Verify Telegram WebApp initData HMAC-SHA256 signature.
     *
     * @see https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
     *
     * @return array<string,mixed>|null  Parsed `user` object, or null if invalid.
     */
    private function verifyInitData(string $initData): ?array
    {
        $params = [];
        parse_str($initData, $params);

        $receivedHash = $params['hash'] ?? null;

        if (! is_string($receivedHash) || $receivedHash === '') {
            return null;
        }

        // auth_date must be present and not older than 24 hours
        $authDate = (int) ($params['auth_date'] ?? 0);
        if ($authDate === 0 || (time() - $authDate) > 86400) {
            return null;
        }

        unset($params['hash']);
        ksort($params);

        $dataCheckString = implode(
            "\n",
            array_map(
                static fn (string $k, string $v): string => "{$k}={$v}",
                array_keys($params),
                array_values($params),
            )
        );

        // secret_key = HMAC_SHA256("WebAppData", bot_token)
        $secretKey     = hash_hmac('sha256', (string) config('nutgram.token'), 'WebAppData', true);
        $expectedHash  = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (! hash_equals($expectedHash, $receivedHash)) {
            return null;
        }

        $user = json_decode((string) ($params['user'] ?? '{}'), true);

        return is_array($user) ? $user : null;
    }
}
