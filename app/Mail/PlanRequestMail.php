<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PlanRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Письмо менеджеру о новой заявке на закрытый тариф. Менеджер читает по-русски. «Ответить» пишет участнице (Reply-To),
 * но главное в письме — телефон: менеджер звонит и открывает покупку в админке («Кабинеты участниц → Заявки на тариф»).
 */
class PlanRequestMail extends Mailable
{
    public function __construct(public readonly PlanRequest $planRequest) {}

    public function envelope(): Envelope
    {
        $user = $this->planRequest->botUser;
        $name = trim((string) preg_replace('/\s+/u', ' ', (string) $user?->full_name));

        return new Envelope(
            subject: "Заявка на тариф {$this->planRequest->planEnum()->shortTitle()}: {$name}",
            replyTo: $user?->email ? [new Address($user->email, $name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.participant.plan-request', with: ['planRequest' => $this->planRequest]);
    }
}
