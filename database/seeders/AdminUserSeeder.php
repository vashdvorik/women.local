<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Пускается ровно одна почта. Сидер идемпотентен: та же почта с новым
     * паролем — пароль обновляется; ничего не менялось — запись не трогается
     * (SETUP.md §4).
     */
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');
        $name = config('admin.name');

        if (blank($email) || blank($password)) {
            throw new RuntimeException('ADMIN_EMAIL и ADMIN_PASSWORD должны быть заданы в .env.');
        }

        if (mb_strlen((string) $password) < 12) {
            throw new RuntimeException('ADMIN_PASSWORD должен быть не короче 12 символов.');
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;

        if (! $user->exists || ! Hash::check($password, $user->password)) {
            $user->password = Hash::make($password);
        }

        $user->save();
    }
}
