<?php

namespace Database\Factories;

use App\Enums\AdSlot;
use App\Models\Ad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ad>
 */
class AdFactory extends Factory
{
    protected $model = Ad::class;

    public function definition(): array
    {
        return [
            'slot' => fake()->randomElement(AdSlot::cases()),
            'title' => fake()->optional()->words(3, true),
            'image_path' => 'ads/'.fake()->uuid().'.jpg',
            'link_url' => fake()->optional()->url(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
