<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin role if it doesn't exist
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        // Define super admin users
        $superAdmins = [
            [
                'name' => 'Aulia Alfi Maruf',
                'email' => 'aulia@pdampurbalingga.co.id',
                'password' => Hash::make('pdamadmin891706'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Fachri Mardliana',
                'email' => 'fachri@pdampurbalingga.co.id',
                'password' => Hash::make('Fachri161096'),
                'email_verified_at' => now(),
            ],
        ];

        // Create each super admin user and assign role
        foreach ($superAdmins as $index => $adminData) {
            $user = User::updateOrCreate(
                ['email' => $adminData['email']],
                array_merge(['id' => $index + 1], $adminData)
            );

            // Assign super_admin role to user
            $user->assignRole($role);
        }
    }
}
