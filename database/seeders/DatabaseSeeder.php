<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\IdentityHasher;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'demo@viejocracia.test';

        User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Redacción',
                'nick' => 'redaccion',
                'department' => 'La Paz',
                'email_hash' => app(IdentityHasher::class)->hashEmail($email),
                'password' => 'password',
                'is_admin' => false,
            ]
        );

        $this->call(CurrentNewsSeeder::class);
    }
}
