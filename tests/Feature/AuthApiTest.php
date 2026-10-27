<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/register', [
            'nick' => 'militante1',
            'email' => 'militante1@example.com',
            'password' => 'password123',
            'department' => 'La Paz',
            'privacy_accepted' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.nick', 'militante1')
            ->assertJsonPath('user.department', 'La Paz')
            ->assertJsonPath('user.is_admin', false)
            ->assertJsonStructure(['token', 'user' => ['id', 'nick', 'email', 'department', 'is_admin']]);

        $this->assertDatabaseHas('users', [
            'nick' => 'militante1',
            'email' => 'militante1@example.com',
            'department' => 'La Paz',
        ]);

        Mail::assertQueued(WelcomeUserMail::class, function (WelcomeUserMail $mail) {
            return $mail->hasTo('militante1@example.com')
                && $mail->user->nick === 'militante1';
        });
    }

    public function test_register_requires_department(): void
    {
        $this->postJson('/api/v1/register', [
            'nick' => 'sindepto',
            'email' => 'sindepto@example.com',
            'password' => 'password123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['department', 'privacy_accepted']);

        $this->postJson('/api/v1/register', [
            'nick' => 'sinprivacidad',
            'email' => 'sinprivacidad@example.com',
            'password' => 'password123',
            'department' => 'La Paz',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['privacy_accepted']);
    }

    public function test_user_can_update_department(): void
    {
        $user = User::factory()->create(['department' => null]);
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/user', [
            'department' => 'Santa Cruz',
        ])->assertOk()
            ->assertJsonPath('department', 'Santa Cruz');
    }

    public function test_user_can_login_with_nick_or_email(): void
    {
        $user = User::factory()->create([
            'nick' => 'redaccion',
            'email' => 'demo@viejocracia.test',
        ]);

        $this->postJson('/api/v1/login', [
            'username' => 'redaccion',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['token']);

        $this->postJson('/api/v1/login', [
            'username' => 'demo@viejocracia.test',
            'password' => 'password',
        ])->assertOk();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create(['nick' => 'redaccion']);

        $this->postJson('/api/v1/login', [
            'username' => 'redaccion',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['username']);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Sesión cerrada.');
    }
}
