<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_admin_without_reading_env_credentials(): void
    {
        $this->artisan('admin:create', [
            '--email' => 'jefe@example.com',
            '--nick' => 'jefe',
        ])
            ->expectsQuestion('Contraseña (no se guarda en .env)', 'clave-segura-9')
            ->expectsQuestion('Confirmá la contraseña', 'clave-segura-9')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'jefe@example.com',
            'nick' => 'jefe',
            'is_admin' => 1,
        ]);

        $this->assertTrue(
            password_verify('clave-segura-9', (string) User::query()->where('email', 'jefe@example.com')->value('password'))
        );
    }

    public function test_refuses_a_second_admin(): void
    {
        User::factory()->admin()->create();

        $this->artisan('admin:create', [
            '--email' => 'otro-admin@example.com',
        ])->assertFailed();

        $this->assertSame(1, User::query()->where('is_admin', true)->count());
    }
}
