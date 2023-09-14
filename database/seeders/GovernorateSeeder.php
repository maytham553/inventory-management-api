<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            ['name' => 'بغداد', 'code' => 'BGD'],
            ['name' => 'البصرة', 'code' => 'BSR'],
            ['name' => 'أربيل', 'code' => 'EBL'],
            ['name' => 'دهوك', 'code' => 'DHK'],
        ];

        DB::table('governorates')->insert($governorates);
    }
}
