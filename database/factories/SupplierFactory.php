<?php

namespace Database\Factories;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $governorateId = Governorate::inRandomOrder()->first()->id;

        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'governorate_id' => $governorateId,
            'address' => $this->faker->address,
            'balance' => $this->faker->randomFloat(4, 0, 1000), // Adjust the range as needed
            'note' => $this->faker->text,
        ];
    }
}
