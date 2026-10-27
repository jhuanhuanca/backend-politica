<?php

namespace Tests\Feature;

use App\Enums\MilitantStatus;
use App\Enums\VoteTopicStatus;
use App\Models\Debate;
use App\Models\Message;
use App\Models\Militant;
use App\Models\User;
use App\Models\VoteTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    protected function connectionsToTransact(): array
    {
        return ['sqlite', 'militancia'];
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->getJson('/api/v1/admin/summary')->assertUnauthorized();
        $this->getJson('/api/v1/admin/users')->assertUnauthorized();
        $this->getJson('/api/v1/admin/militants')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_admin_or_create_debates(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/admin/summary')->assertForbidden();
        $this->postJson('/api/v1/debates', ['title' => 'Hack'])->assertForbidden();
        $this->postJson('/api/v1/vote-topics', ['title' => 'Hack'])->assertForbidden();
    }

    public function test_admin_can_see_summary_and_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/summary')
            ->assertOk()
            ->assertJsonPath('users', 2)
            ->assertJsonPath('debates', 0)
            ->assertJsonCount(6, 'stats.leaders')
            ->assertJsonCount(9, 'stats.departments')
            ->assertJsonPath('stats.departments.0.name', 'Chuquisaca')
            ->assertJsonPath('stats.leaders.0.department', 'La Paz')
            ->assertJsonCount(3, 'stats.spectrum')
            ->assertJsonPath('stats.spectrum.0.key', 'izquierda')
            ->assertJsonPath('stats.cleavages.0.key', 'recambio');

        $this->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_debate(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/v1/debates', [
            'title' => 'Nuevo debate',
            'category' => 'politica',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Nuevo debate');
    }

    public function test_admin_can_list_and_update_militants(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $militant = Militant::factory()->create(['status' => MilitantStatus::Pending]);

        $this->getJson('/api/v1/admin/militants')
            ->assertOk()
            ->assertJsonPath('data.0.full_name', $militant->full_name)
            ->assertJsonPath('data.0.ci', $militant->ci);

        $this->patchJson("/api/v1/admin/militants/{$militant->id}", [
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.status', 'active');
    }

    public function test_only_one_admin_can_exist(): void
    {
        User::factory()->admin()->create();

        try {
            User::factory()->admin()->create();
            $this->fail('Se permitió un segundo administrador.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('is_admin', $exception->errors());
        }

        $this->assertSame(1, User::query()->where('is_admin', true)->count());
    }

    public function test_register_never_creates_admin(): void
    {
        $this->postJson('/api/v1/register', [
            'nick' => 'otro',
            'email' => 'otro@example.com',
            'password' => 'password123',
            'department' => 'Cochabamba',
            'is_admin' => true,
        ])->assertCreated()
            ->assertJsonPath('user.is_admin', false);

        $this->assertDatabaseHas('users', [
            'email' => 'otro@example.com',
            'is_admin' => 0,
        ]);
    }

    public function test_admin_can_see_period_statistics(): void
    {
        $this->travelTo('2026-08-15 12:00:00');

        $admin = User::factory()->admin()->create(['created_at' => now()->subYear()]);
        $commenter = User::factory()->create(['created_at' => now()->subYear()]);
        $hot = Debate::factory()->for($admin, 'author')->create(['title' => 'Debate caliente']);
        $cold = Debate::factory()->for($admin, 'author')->create(['title' => 'Debate frío']);

        Message::factory()->count(4)->create([
            'debate_id' => $hot->id,
            'user_id' => $commenter->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Message::factory()->create([
            'debate_id' => $cold->id,
            'user_id' => $commenter->id,
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);

        User::factory()->count(2)->create(['created_at' => now()]);
        Militant::factory()->create(['created_at' => now()]);
        Militant::factory()->create(['created_at' => now()->subMonth()]);

        VoteTopic::factory()->for($admin, 'author')->create([
            'title' => 'Meta cumplida',
            'status' => VoteTopicStatus::Achieved,
            'target_signatures' => 10,
            'signatures_count' => 12,
        ]);
        VoteTopic::factory()->for($admin, 'author')->create([
            'status' => VoteTopicStatus::Open,
            'target_signatures' => 100,
            'signatures_count' => 3,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/summary')
            ->assertOk()
            ->assertJsonPath('stats.month', '2026-08')
            ->assertJsonPath('stats.comments_this_month', 4)
            ->assertJsonPath('stats.users_this_month', 2)
            ->assertJsonPath('stats.militants_this_month', 1)
            ->assertJsonPath('stats.votes_reached_goal', 1)
            ->assertJsonPath('stats.most_commented_debate.title', 'Debate caliente')
            ->assertJsonPath('stats.most_commented_debate.messages_count', 4)
            ->assertJsonPath('stats.votes_reached.0.title', 'Meta cumplida')
            ->assertJsonPath('stats.from', '2026-08')
            ->assertJsonPath('stats.militants_by_month.0.month', '2026-08')
            ->assertJsonPath('stats.users_by_month.0.month', '2026-08')
            ->assertJsonPath('stats.comments_by_month.0.month', '2026-08')
            ->assertJsonCount(1, 'stats.militants_by_month')
            ->assertJsonCount(9, 'stats.departments')
            ->assertJsonPath('stats.leaders.0.nick', $commenter->nick)
            ->assertJsonPath('stats.leaders.0.sample', false);
    }

    public function test_admin_can_list_signatures_of_a_topic(): void
    {
        $admin = User::factory()->admin()->create();
        $topic = VoteTopic::factory()->create();
        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/admin/vote-topics/{$topic->id}/signatures")
            ->assertOk()
            ->assertJsonPath('data', []);
    }
}
