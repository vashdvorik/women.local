<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Письмо команде о новом обращении из формы «Связаться с нами». «Ответить» в почтовой программе пишет сразу посетительнице
 * (Reply-To), хотя само письмо уходит с адреса сайта.
 */
class ContactMessageMail extends Mailable
{
    public function __construct(public readonly ContactMessage $contact) {}

    public function envelope(): Envelope
    {
        // Имя попадает в тему письма: переводы строк убираем, чтобы в заголовок нельзя было вставить свои.
        $name = trim((string) preg_replace('/\s+/u', ' ', $this->contact->name));

        return new Envelope(
            subject: "Сообщение с сайта Women's hub: {$name}",
            replyTo: [new Address($this->contact->email, $name)],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact-message');
    }
}
