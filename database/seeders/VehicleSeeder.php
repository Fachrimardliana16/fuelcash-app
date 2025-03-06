<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        // Get all vehicle type IDs
        $vehicleTypeIds = VehicleType::pluck('id')->toArray();

        // Common Indonesian city codes for license plates
        $cityCodes = ['B', 'D', 'E', 'F', 'L', 'N', 'T', 'W', 'S'];

        for ($i = 0; $i < 20; $i++) {
            Vehicle::create([
                'vehicle_type_id' => $faker->randomElement($vehicleTypeIds),
                'license_plate' => sprintf(
                    '%s %d %s',
                    $faker->randomElement($cityCodes),
                    $faker->numberBetween(1000, 9999),
                    strtoupper($faker->bothify('???'))
                ),
                'owner' => $faker->name,
                'isactive' => $faker->boolean(80), // 80% chance of being active
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ]);
        }
    }
}
