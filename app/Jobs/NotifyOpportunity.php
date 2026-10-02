<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BotUser;
use App\Models\Opportunity;
use App\Support\BotMessages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Рассылка одобренным участницам о новой публикации. Текст и подпись кнопки — из resources/data/bot_messages.php
 * (раздел «Рассылка о новых публикациях»); каждая получательница читает их на своём языке.
 */
class NotifyOpportunity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(private readonly Opportunity $opportunity) {}

    public function handle(): void
    {
        $opportunity = $this->opportunity->load('author');

        $pageUrl = url('/app/account/opportunities');
        $token   = config('nutgram.token');

        $recipients = BotUser::approved()
            ->where('id', '!=', $opportunity->bot_user_id)
            ->get(['telegram_id', 'locale']);

        // Текст собирается один раз на язык, а не на каждую получательницу.
        $texts = [];

        foreach ($recipients as $recipient) {
            $locale = $recipient->messageLocale();
            $texts[$locale] ??= $this->message($opportunity, $locale);

            try {
                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id'    => $recipient->telegram_id,
                    'text'       => $texts[$locale],
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [[
                            ['text' => BotMessages::text('broadcast_opportunity_button', $locale), 'url' => $pageUrl],
                        ]],
                    ]),
                ]);
            } catch (\Throwable $e) {
                Log::warning("NotifyOpportunity: failed to notify {$recipient->telegram_id}", [
                    'error' => $e->getMessage(),
                ]);
            }

            usleep(50_000);
        }
    }

    private function message(Opportunity $opportunity, string $locale): string
    {
        $body = mb_strlen($opportunity->body) > 300
            ? mb_substr($opportunity->body, 0, 300) . '...'
            : $opportunity->body;

        // Дата и место — строки, собранные здесь, а не введённые пользователем: в шаблоне это «raw»-переменная,
        // поэтому значение места экранируется тут.
        $details = '';

        if ($opportunity->event_date) {
            $details .= "\n\n📅 " . $opportunity->event_date->format('d.m.Y');
        }

        if ($opportunity->location) {
            $details .= "\n📍 " . BotMessages::escape($opportunity->location);
        }

        return BotMessages::text('broadcast_opportunity', $locale, [
            'emoji'   => $opportunity->typeEmoji(),
            'type'    => $opportunity->typeLabel($locale),
            'title'   => $opportunity->title,
            'body'    => $body,
            'details' => $details,
            'author'  => $opportunity->author?->full_name ?? BotMessages::text('broadcast_opportunity_author', $locale),
        ]);
    }
}
