<?php

namespace Database\Seeders;

use App\Models\Signature;
use Illuminate\Database\Seeder;

class SignatureSeeder extends Seeder
{
    public function run(): void
    {
        $signatures = [
            [
                'position' => 'Kasubag Umum',
                'title' => 'Diperiksa Oleh',
                'name' => 'Irawan Tridesi WH, S.ST.',
                'nip' => '691200080',
                'order' => 1,
                'show_stamp' => false,
            ],
            [
                'position' => 'Bendahara BBM',
                'title' => 'Bendahara BBM',
                'name' => 'Wahyuningtyas P, S.Kesos.',
                'nip' => '-',
                'order' => 2,
                'show_stamp' => false,
            ],
            [
                'position' => 'Kabag Umum',
                'title' => 'Menyetujui',
                'name' => 'Endah Susilowati, S.H.',
                'nip' => '700100066',
                'order' => 3,
                'show_stamp' => false,
            ],
        ];

        foreach ($signatures as $signature) {
            Signature::create($signature);
        }
    }
}
