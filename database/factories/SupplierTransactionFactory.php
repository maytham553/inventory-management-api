<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierTransaction>
 */
class SupplierTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $supplierId = Supplier::inRandomOrder()->first()->id;
        $userId = User::inRandomOrder()->first()->id;

        return [
            'supplier_id' => $supplierId,
            'user_id' => $userId,
            'type' => $this->faker->randomElement(['credit', 'debit']),
            'amount' =>$this->faker->randomFloat(4, 1, 10000),
            'note' => $this->faker->sentence,
        ];
    }
}
