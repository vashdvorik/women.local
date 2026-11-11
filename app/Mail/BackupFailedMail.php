<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Письмо команде: ночная резервная копия сайта не удалась (команда backup:run). */
class BackupFailedMail extends Mailable
{
    public function __construct(public readonly string $reason) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Резервная копия сайта не удалась');
    }

    public function content(): Content
    {
        return new Content(text: 'mail.backup-failed');
    }
}
