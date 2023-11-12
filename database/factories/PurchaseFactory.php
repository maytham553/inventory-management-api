<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
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
            'supplier_id' => Supplier::factory(),
            'subtotal_amount' => $this->faker->numberBetween(0, 1000),
            'total_amount' =>$this->faker->numberBetween(0, 1000),
            'discount_amount' => $this->faker->numberBetween(0, 1000),
            'discount_percentage' => $this->faker->numberBetween(0, 1000),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'note' => $this->faker->text,
        ];
    }
}
