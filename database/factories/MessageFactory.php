<?php

namespace Database\Factories;

use App\Models\Debate;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'debate_id' => Debate::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
            'parent_id' => null,
        ];
    }
}
