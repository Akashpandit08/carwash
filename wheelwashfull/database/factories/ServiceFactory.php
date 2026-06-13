<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\ServiceCategory::factory(),
            'name' => $this->faker->word,
            'price' => $this->faker->numberBetween(100, 1000),
            'duration_minutes' => 30,
            'is_active' => true,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
