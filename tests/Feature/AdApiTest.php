<?php

namespace Tests\Feature;

use App\Enums\AdSlot;
use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_lists_only_active_ads(): void
    {
        Ad::factory()->create(['slot' => AdSlot::Left, 'is_active' => true, 'title' => 'Activo']);
        Ad::factory()->create(['slot' => AdSlot::Left, 'is_active' => false, 'title' => 'Oculto']);

        $this->getJson('/api/v1/ads')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Activo')
            ->assertJsonPath('data.0.slot', 'left');
    }

    public function test_guest_cannot_manage_ads(): void
    {
        $this->post('/api/v1/admin/ads', [
            'slot' => 'left',
            'image' => UploadedFile::fake()->image('aviso.jpg'),
        ], ['Accept' => 'application/json'])->assertUnauthorized();
    }

    public function test_admin_can_create_update_and_delete_ad(): void
    {
        Storage::fake('uploads');
        Sanctum::actingAs(User::factory()->admin()->create());

        $created = $this->post('/api/v1/admin/ads', [
            'slot' => 'right',
            'title' => 'Campaña',
            'link_url' => 'https://ejemplo.bo',
            'image' => UploadedFile::fake()->image('aviso.jpg'),
        ], ['Accept' => 'application/json']);

        $created->assertCreated()
            ->assertJsonPath('data.slot', 'right')
            ->assertJsonPath('data.title', 'Campaña');

        $ad = Ad::query()->first();
        $this->assertNotNull($ad);
        Storage::disk('uploads')->assertExists($ad->image_path);

        $this->patchJson("/api/v1/admin/ads/{$ad->id}", [
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/v1/admin/ads/{$ad->id}")->assertNoContent();
        $this->assertDatabaseCount('ads', 0);
    }
}
