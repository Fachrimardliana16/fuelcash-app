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
        // Create super admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'id' => 1,
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create super admin role if it doesn't exist
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        // Assign role to user
        $user->assignRole($role);
    }
}
