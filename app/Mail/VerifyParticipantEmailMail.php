<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\BotUser;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Письмо со ссылкой подтверждения почты при регистрации участницы. */
class VerifyParticipantEmailMail extends Mailable
{
    public function __construct(
        public readonly BotUser $user,
        public readonly string $url,
        public readonly string $language = 'ru',
        public readonly ?string $code = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('participant.mail.verify.subject', [], $this->language));
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.participant.action',
            text: 'mail.participant.action-text',
            with: [
                'locale' => $this->language,
                'line' => __('participant.mail.verify.line', [], $this->language),
                'button' => __('participant.mail.verify.button', [], $this->language),
                'note' => __('participant.mail.verify.note', [], $this->language),
                'url' => $this->url,
                'code' => $this->code,
                'codeLine' => __('participant.mail.verify.code_line', [], $this->language),
                'codeNote' => __('participant.mail.verify.code_note', ['minutes' => \App\Models\BotUser::EMAIL_CODE_MINUTES], $this->language),
            ],
        );
    }
}
