<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuelTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('fuel_types')->insert([
            [
                'name' => 'Premium',
                'desc' => 'Pertamax dan Pertalite',
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Solar',
                'desc' => 'Dexlite dan Biosolar',
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
