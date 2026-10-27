<?php

namespace Tests\Feature;

use App\Enums\DebateStatus;
use App\Events\Debate\MessageSent;
use App\Models\Debate;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_messages_publicly(): void
    {
        $debate = Debate::factory()->create();
        Message::factory()->for($debate)->create([
            'content' => 'viejo',
            'created_at' => now()->subMinute(),
        ]);
        Message::factory()->for($debate)->create([
            'content' => 'nuevo',
            'created_at' => now(),
        ]);

        $this->getJson("/api/v1/debates/{$debate->id}/messages")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.content', 'nuevo')
            ->assertJsonPath('data.1.content', 'viejo');
    }

    public function test_authenticated_user_can_post_message(): void
    {
        Event::fake([MessageSent::class]);

        $debate = Debate::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/debates/{$debate->id}/messages", [
            'content' => 'Estoy de acuerdo',
        ])->assertCreated()
            ->assertJsonPath('data.content', 'Estoy de acuerdo')
            ->assertJsonPath('data.user.id', $user->id);

        Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($debate) {
            return $event->message->debate_id === $debate->id
                && $event->broadcastOn()[0]->name === 'debate.'.$debate->id;
        });
    }

    public function test_authenticated_user_can_reply_to_message(): void
    {
        $debate = Debate::factory()->create();
        $parent = Message::factory()->for($debate)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/debates/{$debate->id}/messages", [
            'content' => 'Respuesta al hilo',
            'parent_id' => $parent->id,
        ])->assertCreated()
            ->assertJsonPath('data.content', 'Respuesta al hilo')
            ->assertJsonPath('data.parent_id', $parent->id);
    }

    public function test_cannot_post_to_closed_debate(): void
    {
        $debate = Debate::factory()->create(['status' => DebateStatus::Closed]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/v1/debates/{$debate->id}/messages", [
            'content' => 'Tarde',
        ])->assertForbidden();
    }
}
