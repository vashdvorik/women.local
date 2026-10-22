<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use App\Support\ContactForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Форма «Связаться с нами» на странице «Контакты»: сохранение, письмо команде, защита от ботов. */
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /** Данные формы так, как их отправляет человек: метка времени выдана давно. */
    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Анна Петрова',
            'email' => 'Anna@Example.test',
            'phone' => '+373 777 12 345',
            'organization' => 'Ассоциация «Весна», директор',
            'message' => 'Здравствуйте! Хотим предложить совместный проект.',
            'form_token' => Crypt::encryptString((string) (time() - 30)),
            'website' => '',
        ], $override);
    }

    public function test_contact_page_shows_the_form_instead_of_the_old_topics_block(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('id="contact-form"', false)
            ->assertSee('action="'.route('contact.send').'"', false)
            ->assertSee('name="form_token"', false)
            ->assertSee('Связаться с нами')
            ->assertDontSee('Что можно обсудить?')
            ->assertDontSee('miro-contact-topics');
    }

    public function test_message_is_saved_and_mailed_to_the_team(): void
    {
        Mail::fake();

        $this->post(route('contact.send'), $this->payload())
            ->assertRedirect(route('contact').'#contact-form')
            ->assertSessionHas('contact_sent', true);

        $contact = ContactMessage::sole();
        $this->assertSame('Анна Петрова', $contact->name);
        $this->assertSame('anna@example.test', $contact->email);
        $this->assertSame('+373 777 12 345', $contact->phone);
        $this->assertSame('Ассоциация «Весна», директор', $contact->organization);
        $this->assertNotNull($contact->mail_sent_at);
        $this->assertNull($contact->mail_error);
        $this->assertNull($contact->read_at);

        Mail::assertSent(ContactMessageMail::class, fn (ContactMessageMail $mail) => $mail->hasTo('womenplatform@gmail.com')
            && $mail->hasReplyTo('anna@example.test')
            && str_contains($mail->render(), 'Хотим предложить совместный проект')
            && str_contains($mail->render(), 'Ассоциация «Весна», директор'));
    }

    public function test_phone_and_organization_are_optional(): void
    {
        Mail::fake();

        $this->post(route('contact.send'), $this->payload(['phone' => '', 'organization' => '']))->assertSessionHas('contact_sent');

        $contact = ContactMessage::sole();
        $this->assertNull($contact->phone);
        $this->assertNull($contact->organization);
    }

    public function test_contact_page_has_the_organization_field(): void
    {
        $this->get(route('contact'))->assertOk()->assertSee('name="organization"', false)->assertSee('Организация и должность (по желанию)');
    }

    public function test_message_is_kept_even_when_the_mail_server_is_down(): void
    {
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('Connection refused'));

        $this->post(route('contact.send'), $this->payload())->assertSessionHas('contact_sent', true);

        $contact = ContactMessage::sole();
        $this->assertNull($contact->mail_sent_at);
        $this->assertSame('Connection refused', $contact->mail_error);
    }

    public function test_invalid_data_is_rejected_and_input_kept(): void
    {
        Mail::fake();

        $this->from(route('contact'))
            ->post(route('contact.send'), $this->payload(['email' => 'not-an-email', 'message' => 'hi', 'phone' => 'abc']))
            ->assertRedirect(route('contact').'#contact-form')
            ->assertSessionHas('contact_error', 'invalid')
            ->assertSessionHasErrors(['email', 'message', 'phone'])
            ->assertSessionHasInput('name', 'Анна Петрова');

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_hidden_trap_field_silently_drops_bots(): void
    {
        Mail::fake();

        $this->post(route('contact.send'), $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHas('contact_sent', true);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_instant_or_forged_submissions_are_refused(): void
    {
        Mail::fake();

        foreach ([ContactForm::token(), 'forged', '', Crypt::encryptString((string) (time() - 8 * 86400))] as $token) {
            $this->post(route('contact.send'), $this->payload(['form_token' => $token]))
                ->assertSessionHas('contact_error', 'token');
        }

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_no_more_than_three_messages_per_hour_from_one_address(): void
    {
        Mail::fake();

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('contact.send'), $this->payload())->assertSessionHas('contact_sent');
        }

        $this->post(route('contact.send'), $this->payload())->assertSessionHas('contact_error', 'throttled');

        $this->assertDatabaseCount('contact_messages', 3);
    }

    public function test_line_breaks_in_the_name_cannot_inject_mail_headers(): void
    {
        Mail::fake();

        $this->post(route('contact.send'), $this->payload(['name' => "Анна\r\nBcc: victim@example.test"]))
            ->assertSessionHas('contact_sent');

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            $subject = $mail->envelope()->subject;

            return ! str_contains($subject, "\n") && ! str_contains($subject, "\r") && $mail->hasTo('womenplatform@gmail.com');
        });
    }
}
