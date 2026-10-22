<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Раздел «Обращения»: список, поиск, пометка «прочитано» при открытии, удаление, счётчик в меню. */
class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    private function enquiry(array $attributes = []): ContactMessage
    {
        return ContactMessage::create($attributes + [
            'name' => 'Анна', 'email' => 'anna@example.test', 'message' => 'Хотим предложить партнёрство.', 'locale' => 'ru',
        ]);
    }

    public function test_admin_sees_the_list_and_can_search(): void
    {
        $this->enquiry();
        $this->enquiry(['name' => 'Борис', 'email' => 'boris@example.test', 'message' => 'Вопрос про мероприятие.']);

        $this->actingAsAdmin()->get(route('admin.contact-messages.index'))
            ->assertOk()->assertSee('anna@example.test')->assertSee('boris@example.test');

        $this->actingAsAdmin()->get(route('admin.contact-messages.index', ['q' => 'мероприятие']))
            ->assertOk()->assertSee('boris@example.test')->assertDontSee('anna@example.test');
    }

    public function test_opening_a_message_marks_it_as_read(): void
    {
        $contact = $this->enquiry();

        $this->actingAsAdmin()->get(route('admin.contact-messages.index', ['unread' => 1]))->assertSee('anna@example.test');

        $this->actingAsAdmin()->get(route('admin.contact-messages.show', $contact))
            ->assertOk()->assertSee('Хотим предложить партнёрство.');

        $this->assertNotNull($contact->fresh()->read_at);
        $this->actingAsAdmin()->get(route('admin.contact-messages.index', ['unread' => 1]))->assertDontSee('anna@example.test');
    }

    public function test_sidebar_counts_unread_messages(): void
    {
        $this->enquiry();
        $this->enquiry(['email' => 'second@example.test']);
        $this->enquiry(['email' => 'read@example.test', 'read_at' => now()]);

        $this->actingAsAdmin()->get(route('admin.contact-messages.index'))
            ->assertSee('Непрочитанные обращения')
            ->assertSeeInOrder(['Обращения', 'nav-counter', '2']);
    }

    public function test_message_that_was_not_mailed_is_flagged(): void
    {
        $contact = $this->enquiry(['mail_error' => 'Connection refused']);

        $this->actingAsAdmin()->get(route('admin.contact-messages.show', $contact))
            ->assertSee('Не ушло')->assertSee('Connection refused');
    }

    public function test_message_can_be_deleted(): void
    {
        $contact = $this->enquiry();

        $this->actingAsAdmin()->delete(route('admin.contact-messages.destroy', $contact))
            ->assertRedirect(route('admin.contact-messages.index'));

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_guests_cannot_open_the_section(): void
    {
        $contact = $this->enquiry();

        $this->get(route('admin.contact-messages.index'))->assertRedirect();
        $this->get(route('admin.contact-messages.show', $contact))->assertRedirect();
        $this->delete(route('admin.contact-messages.destroy', $contact))->assertRedirect();
    }
}
