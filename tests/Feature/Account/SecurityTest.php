<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Безопасность кабинета: вход только участницей (guard «participant»), чужие данные недоступны, закрытый доступ
 * сразу закрывает кабинет, админ и участница не путаются.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_cabinet_needs_a_signed_in_participant(): void
    {
        $this->get(route('account.index'))->assertRedirect(route('account.login'));
        $this->get(route('account.profile'))->assertRedirect(route('account.login'));
        $this->get(route('account.subscription'))->assertRedirect(route('account.login'));
        $this->post(route('account.profile.update'), ['full_name' => 'X'])->assertRedirect(route('account.login'));
    }

    public function test_an_administrator_session_does_not_open_the_cabinet(): void
    {
        $this->actingAsAdmin()->get(route('account.index'))->assertRedirect(route('account.login'));
    }

    public function test_a_participant_session_does_not_open_the_admin(): void
    {
        $user = BotUser::factory()->active()->create();

        $this->withSession($this->participantSession($user))->get(route('admin.dashboard'))->assertRedirect();
    }

    public function test_a_participant_sees_only_her_own_data(): void
    {
        $userA = BotUser::factory()->active()->create(['full_name' => 'Анна Первая']);
        $userB = BotUser::factory()->active()->create(['full_name' => 'Борис Второй']);

        $html = $this->withSession($this->participantSession($userA))->get(route('account.profile'))->assertOk()->getContent();

        $this->assertStringContainsString('Анна Первая', $html);
        $this->assertStringNotContainsString('Борис Второй', $html);
    }

    public function test_a_rejected_participant_is_signed_out_at_the_next_request(): void
    {
        $user = BotUser::factory()->active()->create();

        $user->update(['status' => BotUser::STATUS_REJECTED]);

        $this->withSession($this->participantSession($user))->get(route('account.index'))
            ->assertRedirect(route('account.login'))
            ->assertSessionHas('error');

        $this->assertGuest('participant');
    }

    public function test_a_participant_cannot_change_her_plan_or_status_through_the_profile_form(): void
    {
        $user = BotUser::factory()->community()->create();

        $this->withSession($this->participantSession($user))->post(route('account.profile.update'), [
            'full_name' => 'Новое Имя',
            'phone' => '+373 69 123 456',
            'plan' => 'private',
            'plan_ends_at' => now()->addYears(5)->toDateTimeString(),
            'status' => 'approved',
            'email' => 'hacker@example.test',
            'telegram_id' => 1,
        ])->assertRedirect(route('account.profile'));

        $fresh = $user->fresh();
        $this->assertSame('Новое Имя', $fresh->full_name);
        $this->assertSame('open', $fresh->plan);
        $this->assertNotSame('hacker@example.test', $fresh->email);
        $this->assertNotSame(1, $fresh->telegram_id);
    }
}
