<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    /** Каждый запрос — с нового адреса, как при распределённой атаке. */
    private function failFrom(string $ip, string $email): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post('/login', ['email' => $email, 'password' => 'definitely-wrong']);
    }

    public function test_distributed_brute_force_is_blocked_even_with_rotating_ips(): void
    {
        $admin = $this->admin();

        // 20 неудачных попыток, каждая с уникального IP — счётчик «почта+IP»
        // так не срабатывает, но глобальный по почте копится.
        for ($i = 1; $i <= 20; $i++) {
            $this->failFrom("10.20.0.$i", $admin->email);
        }

        // Следующая попытка — с верным паролем и снова с нового адреса — отклонена.
        $this->withServerVariables(['REMOTE_ADDR' => '10.20.9.9'])
            ->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_owner_can_log_in_while_failures_are_below_the_global_limit(): void
    {
        $admin = $this->admin();

        for ($i = 1; $i <= 8; $i++) {
            $this->failFrom("10.21.0.$i", $admin->email);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.21.9.9'])
            ->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_successful_login_clears_the_global_counter(): void
    {
        $admin = $this->admin();

        for ($i = 1; $i <= 18; $i++) {
            $this->failFrom("10.22.0.$i", $admin->email);
        }

        // Верный вход сбрасывает оба счётчика.
        $this->withServerVariables(['REMOTE_ADDR' => '10.22.9.1'])
            ->post('/login', ['email' => $admin->email, 'password' => 'password']);
        $this->assertAuthenticated();
        $this->post('/logout');

        // После сброса снова копим неудачи — до порога опять далеко.
        for ($i = 1; $i <= 15; $i++) {
            $this->failFrom("10.22.1.$i", $admin->email);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.22.9.2'])
            ->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_single_address_is_stopped_after_five_attempts(): void
    {
        $admin = $this->admin();

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => $admin->email, 'password' => 'definitely-wrong']);
        }

        // Шестая попытка с того же IP — даже с верным паролем — отклонена.
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
