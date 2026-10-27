<?php

namespace Database\Factories;

use App\Enums\VoteTopicStatus;
use App\Models\User;
use App\Models\VoteTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VoteTopic>
 */
class VoteTopicFactory extends Factory
{
    protected $model = VoteTopic::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'description' => fake()->optional()->paragraph(),
            'author_id' => User::factory(),
            'target_signatures' => 100,
            'signatures_count' => 0,
            'deadline' => now()->addDays(30),
            'status' => VoteTopicStatus::Open,
        ];
    }
}
