<?php

namespace App\Services\Broadcasts;

use App\Models\Broadcast;
use App\Support\BroadcastText;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Отправка одного сообщения рассылки в Telegram (Bot API напрямую): текст или фото с подписью,
 * кнопки-ссылки на языке получательницы. Фото загружается один раз, дальше уходит по file_id, который вернул Telegram.
 */
class TelegramBroadcaster
{
    public function send(Broadcast $broadcast, int|string $chatId, string $locale): DeliveryOutcome
    {
        $text = $broadcast->text($locale);
        $markup = $this->markup($broadcast->buttonsFor($locale));

        $response = $this->call($broadcast, $chatId, BroadcastText::html($text), 'HTML', $markup);

        // Telegram не принял разметку (редкий случай): то же сообщение чистым текстом, чем потерять его.
        if ($this->description($response) !== null && str_contains(mb_strtolower($this->description($response)), "can't parse entities")) {
            $response = $this->call($broadcast, $chatId, BroadcastText::plain($text), null, $markup);
        }

        return $this->outcome($response);
    }

    /** @return array<string, mixed> ответ Bot API (ok / result / error_code / description) */
    private function call(Broadcast $broadcast, int|string $chatId, string $text, ?string $parseMode, ?string $markup, bool $retried = false): array
    {
        try {
            if ($broadcast->hasPhoto()) {
                $response = $this->sendPhoto($broadcast, $chatId, $text, $parseMode, $markup);

                // Сохранённый file_id перестал приниматься: один раз загружаем фото заново.
                if (! $retried && filled($broadcast->photo_file_id) && str_contains(mb_strtolower((string) $this->description($response)), 'wrong file')) {
                    $broadcast->forceFill(['photo_file_id' => null])->save();

                    return $this->call($broadcast, $chatId, $text, $parseMode, $markup, true);
                }

                return $response;
            }

            return $this->request()->post($this->url('sendMessage'), array_filter([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode,
                'reply_markup' => $markup,
            ], fn ($value) => $value !== null))->json() ?? [];
        } catch (Throwable $e) {
            return ['ok' => false, 'error_code' => 0, 'description' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> */
    private function sendPhoto(Broadcast $broadcast, int|string $chatId, string $caption, ?string $parseMode, ?string $markup): array
    {
        $fields = array_filter([
            'chat_id' => $chatId,
            'caption' => $caption,
            'parse_mode' => $parseMode,
            'reply_markup' => $markup,
        ], fn ($value) => $value !== null);

        if (filled($broadcast->photo_file_id)) {
            return $this->request()->post($this->url('sendPhoto'), $fields + ['photo' => $broadcast->photo_file_id])->json() ?? [];
        }

        $binary = Storage::disk('originals')->get($broadcast->photo_path);
        $json = $this->request()->attach('photo', $binary, 'photo.jpg')->post($this->url('sendPhoto'), $fields)->json() ?? [];

        // Самый крупный размер последним: его file_id подходит для всех следующих отправок.
        $fileId = collect($json['result']['photo'] ?? [])->last()['file_id'] ?? null;
        if (($json['ok'] ?? false) && $fileId) {
            $broadcast->forceFill(['photo_file_id' => $fileId])->save();
        }

        return $json;
    }

    /** @param  array<string, mixed>  $response */
    private function outcome(array $response): DeliveryOutcome
    {
        if ($response['ok'] ?? false) {
            return DeliveryOutcome::sent();
        }

        $code = (int) ($response['error_code'] ?? 0);
        $description = (string) ($response['description'] ?? 'Telegram не ответил');

        if ($code === 429) {
            return DeliveryOutcome::retry((int) ($response['parameters']['retry_after'] ?? 5));
        }

        if ($code === 403 || str_contains(mb_strtolower($description), 'chat not found')) {
            return DeliveryOutcome::blocked($description);
        }

        return DeliveryOutcome::failed($description);
    }

    /** @param  array<string, mixed>  $response */
    private function description(array $response): ?string
    {
        return isset($response['description']) ? (string) $response['description'] : null;
    }

    /** @param  list<array{text: string, url: string}>  $buttons */
    private function markup(array $buttons): ?string
    {
        if ($buttons === []) {
            return null;
        }

        // Каждая кнопка в своём ряду: длинные подписи на телефоне не обрезаются.
        return json_encode(['inline_keyboard' => array_map(fn (array $button) => [$button], $buttons)], JSON_UNESCAPED_UNICODE);
    }

    private function request(): PendingRequest
    {
        return Http::timeout(20)->acceptJson();
    }

    private function url(string $method): string
    {
        return 'https://api.telegram.org/bot'.config('nutgram.token').'/'.$method;
    }
}
