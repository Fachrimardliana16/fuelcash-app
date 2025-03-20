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
                'max_deposit' => 6750000,
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Solar',
                'desc' => 'Dexlite dan Biosolar',
                'max_deposit' => 8450000,
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kekeringan',
                'desc' => 'Bahan bakar untuk kekeringan',
                'max_deposit' => 16000000,
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
