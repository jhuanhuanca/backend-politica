<?php

namespace Tests\Feature;

use App\Enums\SuggestionStatus;
use App\Enums\SuggestionType;
use App\Models\Debate;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\VoteTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuggestionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_suggest(): void
    {
        $this->postJson('/api/v1/suggestions', [
            'type' => 'debate',
            'title' => 'Reforma electoral',
            'description' => 'Abramos el padrón y los plazos.',
        ])->assertUnauthorized();
    }

    public function test_user_can_suggest_debate_and_list_own(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Suggestion::factory()->for($other, 'user')->create(['title' => 'Ajena']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/suggestions', [
            'type' => SuggestionType::Debate->value,
            'title' => 'Recambio generacional',
            'description' => 'Un debate sobre límites de mandato.',
            'category' => 'politica',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Recambio generacional')
            ->assertJsonPath('data.type', 'debate')
            ->assertJsonPath('data.status', 'pending');

        $this->getJson('/api/v1/suggestions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Recambio generacional');
    }

    public function test_user_can_suggest_vote(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/suggestions', [
            'type' => SuggestionType::Vote->value,
            'title' => 'Firma para recambio sindical',
            'description' => 'Tope de dos gestiones en federaciones.',
            'target_signatures' => 5000,
        ])->assertCreated()
            ->assertJsonPath('data.type', 'vote')
            ->assertJsonPath('data.target_signatures', 5000);
    }

    public function test_regular_user_cannot_list_all_suggestions(): void
    {
        $suggestion = Suggestion::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/admin/suggestions')->assertForbidden();
        $this->postJson("/api/v1/admin/suggestions/{$suggestion->id}/publish")->assertForbidden();
        $this->postJson("/api/v1/admin/suggestions/{$suggestion->id}/reject")->assertForbidden();
    }

    public function test_admin_can_publish_debate_suggestion(): void
    {
        $admin = User::factory()->admin()->create();
        $suggestion = Suggestion::factory()->create([
            'title' => 'Agua y litio',
            'description' => 'Quién decide la renta.',
            'category' => 'economia',
        ]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/suggestions/{$suggestion->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.debate_id', Debate::query()->first()->id);

        $this->assertDatabaseHas('debates', [
            'title' => 'Agua y litio',
            'author_id' => $suggestion->user_id,
        ]);
    }

    public function test_admin_can_publish_vote_suggestion(): void
    {
        $admin = User::factory()->admin()->create();
        $suggestion = Suggestion::factory()->vote()->create([
            'title' => 'Tope de edad en candidaturas',
            'target_signatures' => 2500,
        ]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/suggestions/{$suggestion->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.vote_topic_id', VoteTopic::query()->first()->id);

        $this->assertDatabaseHas('vote_topics', [
            'title' => 'Tope de edad en candidaturas',
            'target_signatures' => 2500,
        ]);
    }

    public function test_admin_can_reject_suggestion(): void
    {
        $admin = User::factory()->admin()->create();
        $suggestion = Suggestion::factory()->create();
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/suggestions/{$suggestion->id}/reject", [
            'admin_note' => 'Fuera de agenda.',
        ])->assertOk()
            ->assertJsonPath('data.status', SuggestionStatus::Rejected->value)
            ->assertJsonPath('data.admin_note', 'Fuera de agenda.');
    }

    public function test_admin_lists_pending_suggestions(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Suggestion::factory()->create(['title' => 'Pendiente']);
        Suggestion::factory()->rejected()->create(['title' => 'Rechazada']);

        $this->getJson('/api/v1/admin/suggestions?status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Pendiente');
    }
}
