<?php

namespace Database\Factories;

use App\Enums\DebateStatus;
use App\Models\Debate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debate>
 */
class DebateFactory extends Factory
{
    protected $model = Debate::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->optional()->paragraph(),
            'category' => fake()->optional()->randomElement(['economia', 'salud', 'educacion']),
            'author_id' => User::factory(),
            'status' => DebateStatus::Open,
            'image_path' => null,
            'video_path' => null,
            'video_url' => null,
        ];
    }
}
