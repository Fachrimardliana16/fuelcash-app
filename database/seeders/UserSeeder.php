<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Mega Tyas',
                'email' => 'tyas@pdampurbalingga.co.id',
                'password' => Hash::make('pdam891706'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sugi Astuti',
                'email' => 'tuti@pdampurbalingga.co.id',
                'password' => Hash::make('pdam891706'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Irawan Tri Desi',
                'email' => 'irawan@pdampurbalingga.co.id',
                'password' => Hash::make('pdam891706'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Anjar Iswanto',
                'email' => 'andjar@pdampurbalingga.co.id',
                'password' => Hash::make('pdam891706'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Muh Manshur Kholiq',
                'email' => 'kholiq@pdampurbalingga.co.id',
                'password' => Hash::make('pdam891706'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Andika Anjas',
                'email' => 'andika@pdampurbalingga.co.id',
                'password' => Hash::make('pdam891706'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Anggoro Bayu',
                'email' => 'anggoro@pdampurbalingga.co.id',
                'password' => Hash::make('pdam891706'),
                'email_verified_at' => now(),
            ],


        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
