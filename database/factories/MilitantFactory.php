<?php

namespace Database\Factories;

use App\Enums\MilitantStatus;
use App\Models\Militant;
use App\Support\Bolivia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Militant>
 */
class MilitantFactory extends Factory
{
    protected $model = Militant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'full_name' => fake()->name(),
            'ci' => (string) fake()->unique()->numerify('#######'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '7'.fake()->numerify('#######'),
            'city' => fake()->city(),
            'department' => fake()->randomElement(Bolivia::DEPARTMENTS),
            'birth_year' => fake()->numberBetween(now()->year - 70, now()->year - 18),
            'consent' => true,
            'card_number' => null,
            'status' => MilitantStatus::Pending,
        ];
    }
}
