<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerTransaction>
 */
class CustomerTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customerId = Customer::inRandomOrder()->first()->id;
        $userId = User::inRandomOrder()->first()->id;

        return [
            'customer_id' => $customerId,
            'user_id' => $userId,
            'type' => $this->faker->randomElement(['credit', 'debit']),
            'amount' =>$this->faker->numberBetween(0, 1000),
            'note' => $this->faker->sentence,
        ];
    }
}
