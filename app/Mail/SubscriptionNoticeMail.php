<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\BotUser;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Письмо о подписке участнице, которая не подключила Telegram: «тариф включён», напоминание об окончании, «закончилась».
 * Тексты — lang/*\/participant.php (mail.subscription.*), язык — участницы.
 */
class SubscriptionNoticeMail extends Mailable
{
    /** @param array<string, scalar|null> $vars */
    public function __construct(
        public readonly BotUser $user,
        public readonly string $kind,
        public readonly array $vars = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __("participant.mail.subscription.{$this->kind}.subject", $this->vars, $this->user->messageLocale()));
    }

    public function content(): Content
    {
        $locale = $this->user->messageLocale();

        return new Content(
            view: 'mail.participant.action',
            with: [
                'locale' => $locale,
                'line' => __("participant.mail.subscription.{$this->kind}.line", $this->vars, $locale),
                'button' => __('participant.mail.opened.button', [], $locale),
                'note' => __('participant.mail.subscription.note', [], $locale),
                'url' => route('account.subscription'),
            ],
        );
    }
}
