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
            ->assertSee('Скрыты от других')->assertSee('Видимы другим')->assertSee('Заблокированы');
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

    public function test_approve_sets_status_and_stays_silent_for_the_participant(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        \Illuminate\Support\Facades\Http::fake();
        $profile = BotUser::factory()->pending()->create(['full_name' => 'Анна Ожидающая', 'email' => 'anna@example.test']);

        $this->actingAsAdmin()
            ->post(route('admin.profiles.approve', $profile))
            ->assertRedirect()
            ->assertSessionHas('success');

        $profile->refresh();
        $this->assertSame(BotUser::STATUS_APPROVED, $profile->status);
        $this->assertNotNull($profile->approved_at);
        \Illuminate\Support\Facades\Mail::assertNothingSent();
        \Illuminate\Support\Facades\Mail::assertNothingQueued();
        \Illuminate\Support\Facades\Http::assertNothingSent();
    }

    public function test_blocking_a_pending_profile_sets_the_blocked_status(): void
    {
        $profile = BotUser::factory()->pending()->create();

        $this->actingAsAdmin()->post(route('admin.profiles.block', $profile))->assertRedirect();

        $this->assertSame(BotUser::STATUS_REJECTED, $profile->fresh()->status);
    }

    public function test_blocking_an_approved_profile_revokes_access(): void
    {
        $profile = BotUser::factory()->approved()->create();

        $this->actingAsAdmin()->post(route('admin.profiles.block', $profile))->assertRedirect();

        $this->assertSame(BotUser::STATUS_REJECTED, $profile->fresh()->status);
    }

    public function test_hiding_from_others_returns_the_profile_to_pending_without_a_notice(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $profile = BotUser::factory()->approved()->create();

        $this->actingAsAdmin()->post(route('admin.profiles.unapprove', $profile))->assertRedirect()->assertSessionHas('success');

        $this->assertSame(BotUser::STATUS_PENDING, $profile->fresh()->status);
        \Illuminate\Support\Facades\Mail::assertNothingSent();
        \Illuminate\Support\Facades\Mail::assertNothingQueued();
    }

    public function test_unblocking_returns_the_mark_the_profile_had_before_the_block(): void
    {
        $visible = BotUser::factory()->approved()->create();
        $hidden = BotUser::factory()->pending()->create();

        foreach ([$visible, $hidden] as $profile) {
            $this->actingAsAdmin()->post(route('admin.profiles.block', $profile))->assertRedirect();
            $this->assertSame(BotUser::STATUS_REJECTED, $profile->fresh()->status);
            $this->actingAsAdmin()->post(route('admin.profiles.unblock', $profile))->assertRedirect()->assertSessionHas('success');
        }

        $this->assertSame(BotUser::STATUS_APPROVED, $visible->fresh()->status);
        $this->assertSame(BotUser::STATUS_PENDING, $hidden->fresh()->status);
        $this->assertNull($visible->fresh()->status_before_block);
    }

    public function test_a_blocked_profile_cannot_be_made_visible_before_it_is_unblocked(): void
    {
        $profile = BotUser::factory()->pending()->create();
        $this->actingAsAdmin()->post(route('admin.profiles.block', $profile));

        $this->actingAsAdmin()->post(route('admin.profiles.approve', $profile))->assertSessionHas('error');
        $this->actingAsAdmin()->post(route('admin.profiles.unapprove', $profile))->assertSessionHas('error');

        $this->assertSame(BotUser::STATUS_REJECTED, $profile->fresh()->status);
    }

    public function test_blocking_twice_does_not_lose_the_mark_from_before_the_block(): void
    {
        $profile = BotUser::factory()->approved()->create();

        $this->actingAsAdmin()->post(route('admin.profiles.block', $profile));
        $this->actingAsAdmin()->post(route('admin.profiles.block', $profile));
        $this->actingAsAdmin()->post(route('admin.profiles.unblock', $profile));

        $this->assertSame(BotUser::STATUS_APPROVED, $profile->fresh()->status);
    }

    public function test_every_button_explains_what_will_happen(): void
    {
        $visible = BotUser::factory()->approved()->create(['full_name' => 'Видимая Анна']);
        $hidden = BotUser::factory()->pending()->create(['full_name' => 'Скрытая Мария']);
        $blocked = BotUser::factory()->pending()->create(['full_name' => 'Закрытая Ирина']);
        $this->actingAsAdmin()->post(route('admin.profiles.block', $blocked));

        $html = $this->actingAsAdmin()->get(route('admin.profiles.index'))->assertOk()->getContent();

        foreach (['Скрыть от других', 'Режим невидимки', 'Заблокировать', 'Разблокировать', 'Что значат кнопки и состояния', 'class="btn-lock"', 'class="btn-unlock"'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        $this->assertStringContainsString('title="Закрывает вход в кабинет.', $html);
        $this->assertStringContainsString('Разблокировать участницу? (Закрытая Ирина)', $html);
        // У заблокированной нет кнопок видимости: сначала разблокировка
        $this->assertSame(1, substr_count($html, 'Сейчас профиль скрыт от других участниц'));
    }

    public function test_the_participant_page_shows_the_same_buttons_with_explanations(): void
    {
        $profile = BotUser::factory()->approved()->create(['full_name' => 'Видимая Анна']);

        $this->actingAsAdmin()->get(route('admin.profiles.show', $profile))
            ->assertOk()
            ->assertSee('Видимость и доступ')
            ->assertSee('Видима другим.')
            ->assertSee('Заблокировать участницу?', false);
    }

    public function test_blocked_participants_get_no_mailings(): void
    {
        $open = BotUser::factory()->approved()->create(['telegram_id' => 111]);
        $blocked = BotUser::factory()->approved()->create(['telegram_id' => 222]);
        $this->actingAsAdmin()->post(route('admin.profiles.block', $blocked));

        // Неизвестная группа берёт всех: заблокированной в ней всё равно нет
        $ids = \App\Models\Broadcast::audienceQuery('unknown-audience')->pluck('bot_users.id')->all();

        $this->assertContains($open->id, $ids);
        $this->assertNotContains($blocked->id, $ids);
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

    public function test_profiles_cannot_be_deleted_from_the_admin_only_blocked(): void
    {
        $profile = BotUser::factory()->approved()->create();

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.profiles.destroy'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.profiles.bulk-destroy'));

        $this->actingAsAdmin()->get(route('admin.profiles.index'))->assertOk()->assertDontSee('Удалить выбранные');
        $this->actingAsAdmin()->get(route('admin.profiles.show', $profile))->assertOk()->assertDontSee('Удалить');
        $this->assertModelExists($profile);
    }
}
