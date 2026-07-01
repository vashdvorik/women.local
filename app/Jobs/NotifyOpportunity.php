<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BotUser;
use App\Models\Opportunity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyOpportunity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(private readonly Opportunity $opportunity) {}

    public function handle(): void
    {
        $opportunity = $this->opportunity->load('author');

        $emoji     = $opportunity->typeEmoji();
        $label     = $opportunity->typeLabel();
        $title     = htmlspecialchars($opportunity->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $body      = mb_strlen($opportunity->body) > 300
            ? mb_substr($opportunity->body, 0, 300) . '...'
            : $opportunity->body;
        $body      = htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $authorName = htmlspecialchars(
            $opportunity->author?->full_name ?? 'Участник',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $text = "🔔 <b>Новое в Возможностях!</b>\n\n"
            . "{$emoji} <b>{$label}:</b> {$title}\n\n"
            . "{$body}";

        if ($opportunity->event_date) {
            $text .= "\n\n📅 " . $opportunity->event_date->format('d.m.Y');
        }

        if ($opportunity->location) {
            $text .= "\n📍 " . htmlspecialchars($opportunity->location, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $text .= "\n\n👤 Опубликовал: " . $authorName;

        $pageUrl = url('/app/account/opportunities');
        $token   = config('nutgram.token');

        $recipients = BotUser::approved()
            ->where('id', '!=', $opportunity->bot_user_id)
            ->pluck('telegram_id');

        foreach ($recipients as $telegramId) {
            try {
                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id'    => $telegramId,
                    'text'       => $text,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [[
                            ['text' => '👀 Посмотреть возможность', 'url' => $pageUrl],
                        ]],
                    ]),
                ]);
            } catch (\Throwable $e) {
                Log::warning("NotifyOpportunity: failed to notify {$telegramId}", [
                    'error' => $e->getMessage(),
                ]);
            }

            // Stay within Telegram's 30 msg/sec global rate limit
            usleep(50_000);
        }
    }
}
