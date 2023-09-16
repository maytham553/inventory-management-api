<?php

namespace Database\Seeders;

use App\Models\CustomerTransaction;
use Illuminate\Database\Seeder;

class CustomerTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CustomerTransaction::factory()->count(50)->create();
    }
}
