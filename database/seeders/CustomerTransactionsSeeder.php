<?php

namespace Database\Seeders;

use App\Models\SupplierTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SupplierTransaction::factory()->count(50)->create();
    }
}
