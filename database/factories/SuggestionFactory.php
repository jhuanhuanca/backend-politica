<?php

namespace Database\Factories;

use App\Enums\SuggestionStatus;
use App\Enums\SuggestionType;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Suggestion>
 */
class SuggestionFactory extends Factory
{
    protected $model = Suggestion::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => SuggestionType::Debate,
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'category' => fake()->optional()->randomElement(['economia', 'politica', 'salud', 'educacion']),
            'target_signatures' => null,
            'status' => SuggestionStatus::Pending,
            'admin_note' => null,
        ];
    }

    public function vote(): static
    {
        return $this->state(fn () => [
            'type' => SuggestionType::Vote,
            'category' => null,
            'target_signatures' => 1000,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => SuggestionStatus::Accepted,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => SuggestionStatus::Rejected,
            'admin_note' => 'No entra en la agenda de este mes.',
        ]);
    }
}
