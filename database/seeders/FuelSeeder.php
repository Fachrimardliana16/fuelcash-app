<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuelSeeder extends Seeder
{
    public function run()
    {
        // Get fuel type IDs
        $premiumTypeId = DB::table('fuel_types')->where('name', 'Premium')->value('id');
        $solarTypeId = DB::table('fuel_types')->where('name', 'Solar')->value('id');
        $kekeringanTypeId = DB::table('fuel_types')->where('name', 'Kekeringan')->value('id');

        DB::table('fuels')->insert([
            [
                'fuel_type_id' => $premiumTypeId,
                'name' => 'Pertalite',
                'price' => 10000,
                'unit' => 'liter',
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fuel_type_id' => $premiumTypeId,
                'name' => 'Pertamax',
                'price' => 12000,
                'unit' => 'liter',
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fuel_type_id' => $solarTypeId,
                'name' => 'Biosolar',
                'price' => 11000,
                'unit' => 'liter',
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fuel_type_id' => $solarTypeId,
                'name' => 'Dexlite',
                'price' => 13000,
                'unit' => 'liter',
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fuel_type_id' => $kekeringanTypeId,
                'name' => 'BBM Kekeringan',
                'price' => null,
                'unit' => null,
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
