<?php

namespace Tests\Feature\Admin;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nutgram\Laravel\Facades\Telegram;
use Tests\TestCase;

class ProfileModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Уведомления уходят в бота: подменяем Telegram, реальных запросов быть не должно.
        Telegram::fake();
    }

    public function test_list_shows_profiles_with_status_counters(): void
    {
        BotUser::factory()->pending()->create(['full_name' => 'Анна Ожидающая']);
        BotUser::factory()->approved()->create(['full_name' => 'Мария Одобренная']);

        $this->actingAsAdmin()
            ->get(route('admin.profiles.index'))
            ->assertOk()
            ->assertSee('Анна Ожидающая')
            ->assertSee('Мария Одобренная')
            ->assertSee('Ожидают');
    }

    public function test_list_filters_by_status_and_search(): void
    {
        BotUser::factory()->pending()->create(['full_name' => 'Анна Ожидающая', 'telegram_username' => 'anna_w']);
        BotUser::factory()->approved()->create(['full_name' => 'Мария Одобренная', 'telegram_username' => 'maria_ok']);

        $this->actingAsAdmin()
            ->get(route('admin.profiles.index', ['status' => 'pending']))
            ->assertSee('Анна Ожидающая')
            ->assertDontSee('Мария Одобренная');

        $this->actingAsAdmin()
            ->get(route('admin.profiles.index', ['q' => 'maria_ok']))
            ->assertSee('Мария Одобренная')
            ->assertDontSee('Анна Ожидающая');
    }

    public function test_unknown_status_and_sort_values_are_ignored(): void
    {
        BotUser::factory()->pending()->create(['full_name' => 'Анна Ожидающая']);

        $this->actingAsAdmin()
            ->get(route('admin.profiles.index', ['status' => 'hacked', 'sort' => 'telegram_id; drop table']))
            ->assertOk()
            ->assertSee('Анна Ожидающая');
    }

    public function test_approve_sets_status_and_notifies_participant(): void
    {
        $profile = BotUser::factory()->pending()->create(['full_name' => 'Анна Ожидающая']);

        $this->actingAsAdmin()
            ->post(route('admin.profiles.approve', $profile))
            ->assertRedirect()
            ->assertSessionHas('success');

        $profile->refresh();
        $this->assertSame(BotUser::STATUS_APPROVED, $profile->status);
        $this->assertNotNull($profile->approved_at);
    }

    public function test_reject_pending_profile_sets_rejected_status(): void
    {
        $profile = BotUser::factory()->pending()->create();

        $this->actingAsAdmin()->post(route('admin.profiles.reject', $profile))->assertRedirect();

        $this->assertSame(BotUser::STATUS_REJECTED, $profile->fresh()->status);
    }

    public function test_reject_approved_profile_revokes_access(): void
    {
        $profile = BotUser::factory()->approved()->create();

        $this->actingAsAdmin()->post(route('admin.profiles.reject', $profile))->assertRedirect();

        $this->assertSame(BotUser::STATUS_REJECTED, $profile->fresh()->status);
    }

    public function test_edit_updates_texts_but_never_the_status(): void
    {
        $profile = BotUser::factory()->pending()->create(['full_name' => 'Старое имя']);

        $this->actingAsAdmin()
            ->put(route('admin.profiles.update', $profile), [
                'full_name' => 'Новое имя',
                'description' => 'Описание',
                'expectation' => 'Запрос',
                'status' => BotUser::STATUS_APPROVED,
            ])
            ->assertRedirect(route('admin.profiles.show', $profile));

        $profile->refresh();
        $this->assertSame('Новое имя', $profile->full_name);
        $this->assertSame('Описание', $profile->description);
        $this->assertSame(BotUser::STATUS_PENDING, $profile->status);
    }

    public function test_edit_requires_a_name(): void
    {
        $profile = BotUser::factory()->pending()->create();

        $this->actingAsAdmin()
            ->put(route('admin.profiles.update', $profile), ['full_name' => ''])
            ->assertSessionHasErrors('full_name');
    }

    public function test_show_page_renders_profile_details(): void
    {
        $profile = BotUser::factory()->approved()->create([
            'full_name' => 'Мария Одобренная',
            'description' => 'Швейное производство',
            'telegram_username' => 'maria_ok',
        ]);

        $this->actingAsAdmin()
            ->get(route('admin.profiles.show', $profile))
            ->assertOk()
            ->assertSee('Швейное производство')
            ->assertSee('@maria_ok');
    }

    public function test_delete_and_bulk_delete_remove_profiles(): void
    {
        [$a, $b, $keep] = BotUser::factory()->count(3)->create();

        $this->actingAsAdmin()->delete(route('admin.profiles.destroy', $a))->assertRedirect(route('admin.profiles.index'));
        $this->assertModelMissing($a);

        $this->actingAsAdmin()
            ->delete(route('admin.profiles.bulk-destroy'), ['ids' => [$b->id]])
            ->assertRedirect(route('admin.profiles.index'));

        $this->assertModelMissing($b);
        $this->assertModelExists($keep);
    }

    public function test_bulk_delete_requires_a_selection(): void
    {
        $this->actingAsAdmin()
            ->delete(route('admin.profiles.bulk-destroy'), ['ids' => []])
            ->assertSessionHasErrors('ids');
    }
}
