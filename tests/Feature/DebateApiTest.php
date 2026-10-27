<?php

namespace Tests\Feature;

use App\Enums\DebateStatus;
use App\Models\Debate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DebateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_debates_without_authentication(): void
    {
        Debate::factory()->count(2)->create();

        $this->getJson('/api/v1/debates')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_create_debate_with_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/debates', [
            'title' => 'Reforma electoral',
            'description' => 'Punto de debate',
            'category' => 'politica',
            'image' => UploadedFile::fake()->image('portada.jpg'),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Reforma electoral')
            ->assertJsonPath('data.author.nick', $user->nick)
            ->assertJsonPath('data.video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertDatabaseHas('debates', [
            'title' => 'Reforma electoral',
            'author_id' => $user->id,
        ]);

        $path = Debate::query()->first()->image_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_guest_cannot_create_debate(): void
    {
        $this->postJson('/api/v1/debates', [
            'title' => 'Sin sesión',
        ])->assertUnauthorized();
    }

    public function test_admin_can_update_and_regular_user_cannot(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();
        $debate = Debate::factory()->for($admin, 'author')->create();

        Sanctum::actingAs($other);
        $this->putJson("/api/v1/debates/{$debate->id}", [
            'title' => 'Hack',
        ])->assertForbidden();

        Sanctum::actingAs($admin);
        $this->putJson("/api/v1/debates/{$debate->id}", [
            'title' => 'Título editado',
            'status' => DebateStatus::Closed->value,
        ])->assertOk()
            ->assertJsonPath('data.title', 'Título editado')
            ->assertJsonPath('data.status', 'closed');
    }
}
