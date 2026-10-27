<?php

namespace Tests;

use App\Models\BotUser;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    private ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Закрытый диск сжатых фото никогда не пишет в настоящую папку storage/app/private/originals, даже если тест
        // загружает картинки и забыл подменить диск сам.
        Storage::fake('originals');
    }

    /** Пользователь-администратор; проверка почты настраивается на него. */
    protected function admin(): User
    {
        if ($this->admin === null) {
            $this->admin = User::factory()->create(['email' => 'editor@example.test']);
            config(['admin.email' => $this->admin->email]);
        }

        return $this->admin;
    }

    /**
     * Сессия, в которой участница уже вошла (guard «participant»): withSession($this->participantSession($user)).
     *
     * @return array<string, int>
     */
    protected function participantSession(BotUser $user): array
    {
        return [Auth::guard('participant')->getName() => $user->getKey()];
    }

    protected function actingAsAdmin(): static
    {
        return $this->actingAs($this->admin());
    }
}
