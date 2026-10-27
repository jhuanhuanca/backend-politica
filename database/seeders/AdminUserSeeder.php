<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->where('is_admin', true)->exists()) {
            return;
        }

        $this->command?->warn('El administrador no se crea desde .env. Usá: php artisan admin:create');
    }
}
