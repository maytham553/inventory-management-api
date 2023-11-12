<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RawMaterial>
 */
class RawMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(4),
            'code' => $this->faker->unique()->optional()->numerify('RM###'),
            'barcode' => $this->faker->unique()->optional()->ean13(),
            'quantity' => $this->faker->numberBetween(0, 100),
            'cost' => $this->faker->numberBetween(0, 1000),
            'note' => $this->faker->optional()->paragraph(),
        ];
    }
}
