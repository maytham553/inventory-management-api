<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(1),
            'code' => $this->faker->unique()->optional()->numerify('PROD###'),
            'barcode' => $this->faker->unique()->optional()->ean13(),
            'quantity' => $this->faker->numberBetween(0, 100),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'cost' => $this->faker->randomFloat(2, 5, 500),
            'note' => $this->faker->optional()->paragraph(),
        ];
    }
}
