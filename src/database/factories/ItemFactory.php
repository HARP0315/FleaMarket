<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word(),
            'brand' => $this->faker->company(),
            'description' => $this->faker->realText(50),
            'img' => 'images/dummy.jpg',
            'condition' => $this->faker->numberBetween(1, 4),
            'price' => $this->faker->numberBetween(100, 10000),
        ];
    }
}
