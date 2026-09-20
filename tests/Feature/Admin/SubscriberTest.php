<?php

namespace Tests\Feature\Admin;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_the_list_and_can_search(): void
    {
        Subscriber::create(['name' => 'Анна', 'email' => 'anna@example.test']);
        Subscriber::create(['name' => 'Борис', 'email' => 'boris@example.test']);

        $this->actingAsAdmin()->get(route('admin.subscribers.index'))
            ->assertOk()
            ->assertSee('anna@example.test')
            ->assertSee('boris@example.test');

        $this->actingAsAdmin()->get(route('admin.subscribers.index', ['q' => 'boris']))
            ->assertOk()
            ->assertSee('boris@example.test')
            ->assertDontSee('anna@example.test');
    }

    public function test_subscriber_can_be_deleted(): void
    {
        $subscriber = Subscriber::create(['name' => 'Анна', 'email' => 'anna@example.test']);

        $this->actingAsAdmin()->delete(route('admin.subscribers.destroy', $subscriber))
            ->assertRedirect(route('admin.subscribers.index'));

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_csv_export_contains_subscribers(): void
    {
        Subscriber::create(['name' => 'Анна', 'email' => 'anna@example.test']);

        $response = $this->actingAsAdmin()->get(route('admin.subscribers.export.csv'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('anna@example.test', $response->streamedContent());
    }

    public function test_txt_export_is_a_plain_email_list(): void
    {
        Subscriber::create(['name' => 'Анна', 'email' => 'anna@example.test']);

        $response = $this->actingAsAdmin()->get(route('admin.subscribers.export.txt'));

        $response->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('content-type'));
        $this->assertSame("anna@example.test\n", $response->streamedContent());
    }
}
