<?php

namespace Tests\Feature;

use App\Enums\VoteTopicStatus;
use App\Models\User;
use App\Models\VoteTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SignatureApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_sign_once(): void
    {
        $topic = VoteTopic::factory()->create(['target_signatures' => 2]);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/vote-topics/{$topic->id}/sign")
            ->assertCreated();

        $this->assertDatabaseCount('signatures', 1);
        $this->assertSame(1, $topic->fresh()->signatures_count);

        $this->postJson("/api/v1/vote-topics/{$topic->id}/sign")
            ->assertStatus(422);
    }

    public function test_cannot_sign_closed_topic(): void
    {
        $topic = VoteTopic::factory()->create(['status' => VoteTopicStatus::Closed]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/v1/vote-topics/{$topic->id}/sign")
            ->assertForbidden();
    }

    public function test_reaches_target_and_marks_achieved(): void
    {
        $topic = VoteTopic::factory()->create(['target_signatures' => 1]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/v1/vote-topics/{$topic->id}/sign")
            ->assertCreated();

        $this->assertSame(VoteTopicStatus::Achieved, $topic->fresh()->status);
    }

    public function test_admin_can_create_topic(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/vote-topics', [
            'title' => 'Ley de transparencia',
            'target_signatures' => 500,
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Ley de transparencia')
            ->assertJsonPath('data.signatures_count', 0);
    }
}
