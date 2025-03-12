<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('vehicle_types')->insert([
            [
                'name' => 'Roda Dua',
                'description' => 'Motor',
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Roda Empat',
                'description' => 'Mobil, Truck',
                'isactive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
