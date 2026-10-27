<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\BotUser;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Письмо о решении по анкете участнице, которая не подключила Telegram: одобрена, отклонена, доступ закрыт. */
class ModerationNoticeMail extends Mailable
{
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const REVOKED = 'revoked';

    public function __construct(public readonly BotUser $user, public readonly string $kind) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __("participant.mail.moderation.{$this->kind}.subject", [], $this->user->messageLocale()));
    }

    public function content(): Content
    {
        $locale = $this->user->messageLocale();

        return new Content(
            view: 'mail.participant.action',
            with: [
                'locale' => $locale,
                'line' => __("participant.mail.moderation.{$this->kind}.line", [], $locale),
                'button' => __('participant.mail.moderation.button', [], $locale),
                'note' => __('participant.mail.moderation.note', [], $locale),
                'url' => route('account.index'),
            ],
        );
    }
}
