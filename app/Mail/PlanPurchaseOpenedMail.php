<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\Plan;
use App\Models\BotUser;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Письмо участнице: менеджер открыл ей покупку закрытого тарифа. */
class PlanPurchaseOpenedMail extends Mailable
{
    public function __construct(public readonly BotUser $user, public readonly Plan $plan) {}

    public function envelope(): Envelope
    {
        $locale = $this->user->messageLocale();

        return new Envelope(subject: __('participant.mail.opened.subject', ['plan' => $this->plan->title($locale)], $locale));
    }

    public function content(): Content
    {
        $locale = $this->user->messageLocale();

        return new Content(
            view: 'mail.participant.action',
            with: [
                'locale' => $locale,
                'line' => __('participant.mail.opened.line', ['plan' => $this->plan->title($locale)], $locale),
                'button' => __('participant.mail.opened.button', [], $locale),
                'note' => __('participant.mail.opened.note', [], $locale),
                'url' => route('account.subscription'),
            ],
        );
    }
}
