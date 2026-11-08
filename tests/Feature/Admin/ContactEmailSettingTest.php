<?php

namespace Tests\Feature\Admin;

use App\Mail\ContactMessageMail;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Почта команды задаётся в админке («Настройки сайта → Почта»): на неё уходят письма из формы «Контакты», она же показана
 * на сайте. Пока в админке пусто, берётся config site.contacts.email.
 */
class ContactEmailSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_a_saved_value_the_config_address_is_used(): void
    {
        config(['site.contacts.email' => 'config@example.test']);

        $this->assertSame('config@example.test', SiteSetting::contactEmail());
    }

    public function test_the_admin_saves_the_address_and_the_site_form_uses_it(): void
    {
        Mail::fake();

        $this->actingAsAdmin()
            ->put(route('admin.settings.contacts'), ['contact_email' => 'Team@Example.Test'])
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'contacts']));

        $this->assertSame('team@example.test', SiteSetting::contactEmail());

        $this->post(route('contact.send'), [
            'name' => 'Анна', 'email' => 'anna@example.test', 'message' => 'Здравствуйте, это проверка формы.',
            'website' => '', 'form_token' => \Illuminate\Support\Facades\Crypt::encryptString((string) (time() - 30)),
        ]);

        Mail::assertSent(ContactMessageMail::class, fn (ContactMessageMail $mail) => $mail->hasTo('team@example.test'));

        // Адрес виден и на странице «Контакты»
        $this->get(route('contact'))->assertSee('team@example.test');
    }

    public function test_an_invalid_address_is_rejected_and_the_old_one_stays(): void
    {
        SiteSetting::setContactEmail('old@example.test');

        $this->actingAsAdmin()
            ->put(route('admin.settings.contacts'), ['contact_email' => 'не почта'])
            ->assertSessionHasErrors('contact_email');

        $this->assertSame('old@example.test', SiteSetting::contactEmail());
    }

    public function test_the_mail_tab_and_the_test_email_work(): void
    {
        Mail::fake();
        SiteSetting::setContactEmail('team@example.test');

        $this->actingAsAdmin()->get(route('admin.settings.edit', ['tab' => 'contacts']))
            ->assertOk()
            ->assertSee('team@example.test')
            ->assertSee(route('admin.settings.contacts.test'));

        $this->actingAsAdmin()->post(route('admin.settings.contacts.test'))->assertSessionHas('success');
    }

    public function test_only_the_administrator_can_change_it(): void
    {
        $this->put(route('admin.settings.contacts'), ['contact_email' => 'x@example.test'])->assertRedirect();

        $this->assertSame((string) config('site.contacts.email'), SiteSetting::contactEmail());
    }
}
