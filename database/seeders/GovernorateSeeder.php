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
            ['name' => 'السليمانية', 'code' => 'SLM'],
            ['name' => 'النجف', 'code' => 'NJF'],
            ['name' => 'كربلاء', 'code' => 'KBL'],
            ['name' => 'واسط', 'code' => 'WAS'],
            ['name' => 'القادسية', 'code' => 'QDS'],
            ['name' => 'بابل', 'code' => 'BBL'],
            ['name' => 'المثنى', 'code' => 'MTH'],
            ['name' => 'ذي قار', 'code' => 'DQD'],
            ['name' => 'الموصل', 'code' => 'MOS'],
            ['name' => 'صلاح الدين', 'code' => 'SDL'],
            ['name' => 'ديالى', 'code' => 'DIL'],
            ['name' => 'كركوك', 'code' => 'KRK'],
            ['name' => 'نينوى', 'code' => 'NNW'],
            ['name' => 'الأنبار', 'code' => 'ANB'],
        ];

        DB::table('governorates')->insert($governorates);
    }
}
