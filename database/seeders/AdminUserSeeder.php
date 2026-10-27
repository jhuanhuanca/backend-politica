<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\IdentityHasher;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = (string) config('admin.email');

        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrador',
                'nick' => (string) config('admin.nick'),
                'department' => 'La Paz',
                'email_hash' => app(IdentityHasher::class)->hashEmail($adminEmail),
                'password' => (string) config('admin.password'),
                'is_admin' => true,
            ]
        );
    }
}
