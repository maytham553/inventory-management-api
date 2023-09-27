<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            GovernorateSeeder::class,
            SupplierSeeder::class,
            SupplierTransactionSeeder::class,
            CustomerSeeder::class,
            CustomerTransactionsSeeder::class,
            RawMaterialSeeder::class,
            PurchaseSeeder::class,
        ]);
    }
}
