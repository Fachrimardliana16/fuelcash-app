<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Mega Tyas',
                'email' => 'tyas@pdampurbalingga.co.id',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sugi Astuti',
                'email' => 'tuti@pdampurbalingga.co.id',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
