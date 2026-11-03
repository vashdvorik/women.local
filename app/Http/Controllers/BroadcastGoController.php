<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BroadcastClick;
use App\Models\BroadcastDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Ссылка-переходник кнопки рассылки: /r/{токен получательницы}/{номер кнопки}. Записывает клик и отправляет на адрес кнопки.
 * Адрес берётся из самой рассылки по номеру кнопки, а не из запроса, поэтому подменить место назначения нельзя. Неизвестный
 * токен или кнопка — 404. Предпросмотр ссылки роботом (HEAD) кликом не считается.
 */
class BroadcastGoController extends Controller
{
    public function __invoke(Request $request, string $token, int $button): RedirectResponse
    {
        $delivery = BroadcastDelivery::query()->with('broadcast')->where('token', $token)->first();
        $buttons = array_values((array) $delivery?->broadcast?->buttons);
        $url = trim((string) ($buttons[$button]['url'] ?? ''));

        abort_if($delivery === null || $url === '' || ! preg_match('~^https?://~i', $url), 404);

        if (! $request->isMethod('HEAD')) {
            try {
                DB::transaction(function () use ($delivery, $button): void {
                    BroadcastClick::create([
                        'broadcast_id' => $delivery->broadcast_id,
                        'broadcast_delivery_id' => $delivery->id,
                        'bot_user_id' => $delivery->bot_user_id,
                        'button' => $button,
                        'clicked_at' => now(),
                    ]);

                    $delivery->newQuery()->whereKey($delivery->id)->update([
                        'clicks' => DB::raw('clicks + 1'),
                        'first_clicked_at' => $delivery->first_clicked_at ?? now(),
                    ]);
                });
            } catch (Throwable $e) {
                // Учёт кликов не должен мешать человеку дойти до ссылки
                report($e);
            }
        }

        return redirect()->away($url);
    }
}
