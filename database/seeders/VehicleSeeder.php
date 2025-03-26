<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\FuelType;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        $vehicleTypeIds = VehicleType::pluck('id')->toArray();
        $fuelTypeIds = FuelType::pluck('id')->toArray();
        $cityCodes = ['B', 'D', 'E', 'F', 'L', 'N', 'T', 'W', 'S'];

        $vehicleModels = ['Pickup', 'Bebek', 'Matic', 'SUV', 'MPV', 'Sport'];
        $brands = ['Honda', 'Toyota', 'Nissan', 'Suzuki', 'Yamaha', 'Kawasaki', 'Mitsubishi', 'Daihatsu'];
        $ownershipTypes = ['Inventaris', 'Pribadi'];

        for ($i = 0; $i < 20; $i++) {
            $vehicleTypeId = $faker->randomElement($vehicleTypeIds);
            $vehicleType = VehicleType::find($vehicleTypeId);
            $brand = $faker->randomElement($brands);

            // Logic untuk memastikan kombinasi brand dan model masuk akal
            $vehicleModel = match ($brand) {
                'Honda', 'Yamaha', 'Kawasaki' => $faker->randomElement(['Bebek', 'Matic', 'Sport']),
                'Toyota', 'Nissan', 'Mitsubishi', 'Daihatsu' => $faker->randomElement(['Pickup', 'SUV', 'MPV']),
                default => $faker->randomElement($vehicleModels),
            };

            // Logic untuk menentukan fuel type berdasarkan jenis kendaraan
            $fuelTypeId = null;
            if (!empty($fuelTypeIds)) {
                // Jika motor, biasanya bensin. Jika mobil bisa bensin atau solar
                if (in_array($vehicleModel, ['Bebek', 'Matic', 'Sport'])) {
                    // Filter untuk fuel type bensin (jika ada dalam seed data)
                    $filtered = FuelType::whereIn('id', $fuelTypeIds)
                        ->where('name', 'like', '%Bensin%')
                        ->orWhere('name', 'like', '%Pertalite%')
                        ->orWhere('name', 'like', '%Pertamax%')
                        ->pluck('id')->toArray();

                    $fuelTypeId = !empty($filtered)
                        ? $faker->randomElement($filtered)
                        : $faker->randomElement($fuelTypeIds);
                } else {
                    $fuelTypeId = $faker->randomElement($fuelTypeIds);
                }
            }

            $vehicleDetails = [
                'Honda' => ['Brio RS CVT', 'CR-V Prestige', 'City Hatchback RS', 'BeAT CBS', 'Vario 160', 'PCX 160'],
                'Toyota' => ['Kijang Innova V 2.4', 'Avanza Veloz', 'Fortuner VRZ', 'Rush TRD Sportivo'],
                'Yamaha' => ['NMAX 155', 'Aerox 155', 'R15 V4', 'MT-15'],
                'Suzuki' => ['Ertiga Sport', 'XL7 Alpha', 'GSX-R150', 'Satria F150'],
                // Add more brand-specific details as needed
            ];

            $detail = isset($vehicleDetails[$brand])
                ? $faker->randomElement($vehicleDetails[$brand])
                : null;

            Vehicle::create([
                // 'name' field removed
                'vehicle_type_id' => $vehicleTypeId,
                'fuel_type_id' => $fuelTypeId,
                'license_plate' => sprintf(
                    '%s %d %s',
                    $faker->randomElement($cityCodes),
                    $faker->numberBetween(1000, 9999),
                    strtoupper($faker->bothify('???'))
                ),
                'owner' => $faker->name,
                'vehicle_model' => $vehicleModel,
                'brand' => $brand,
                'ownership_type' => $faker->randomElement($ownershipTypes),
                'isactive' => $faker->boolean(80),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'detail' => $detail,
            ]);
        }
    }
}
