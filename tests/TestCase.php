<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private ?User $admin = null;

    /** Пользователь-администратор; проверка почты настраивается на него. */
    protected function admin(): User
    {
        if ($this->admin === null) {
            $this->admin = User::factory()->create(['email' => 'editor@example.test']);
            config(['admin.email' => $this->admin->email]);
        }

        return $this->admin;
    }

    protected function actingAsAdmin(): static
    {
        return $this->actingAs($this->admin());
    }
}
