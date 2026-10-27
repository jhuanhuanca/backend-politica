<?php

namespace Tests\Feature;

use App\Models\Militant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilitantApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    protected function connectionsToTransact(): array
    {
        return ['sqlite', 'militancia'];
    }

    public function test_person_can_register_in_the_militant_padron(): void
    {
        $response = $this->postJson('/api/v1/militants', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('message', 'Gracias por su registro. Nos pondremos en contacto.')
            ->assertJsonMissingPath('ci')
            ->assertJsonMissingPath('email');

        $this->assertDatabaseHas('militants', [
            'full_name' => 'Ana María Condori',
            'ci' => '1234567LP',
            'email' => 'ana@example.com',
            'department' => 'La Paz',
            'status' => 'pending',
        ], 'militancia');
    }

    public function test_duplicate_ci_is_rejected(): void
    {
        Militant::factory()->create(['ci' => '1234567LP']);

        $this->postJson('/api/v1/militants', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ci']);
    }

    public function test_underage_birth_year_is_rejected(): void
    {
        $this->postJson('/api/v1/militants', $this->validPayload([
            'birth_year' => now()->year - 16,
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['birth_year']);
    }

    public function test_consent_is_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['consent']);

        $this->postJson('/api/v1/militants', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['consent']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'full_name' => 'Ana María Condori',
            'ci' => '1234567 LP',
            'email' => 'Ana@example.com',
            'phone' => '70123456',
            'city' => 'El Alto',
            'department' => 'La Paz',
            'birth_year' => 1990,
            'consent' => true,
            ...$overrides,
        ];
    }
}