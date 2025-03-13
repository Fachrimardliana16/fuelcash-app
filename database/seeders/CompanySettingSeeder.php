<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::create([
            'government_name' => 'PEMERINTAH KABUPATEN PURBALINGGA',
            'company_name' => 'PERUMDA AIR MINUM TIRTA PERWIRA
            KABUPATEN PURBALINGGA',
            'street_address' => 'Jl. Letjend. S. Parman No. 62',
            'village' => 'Kedungmenjangan',
            'district' => 'Purbalingga',
            'regency' => 'Purbalingga',
            'province' => 'Jawa Tengah',
            'postal_code' => '53311',
            'phone_number' => '(0281) 891706',
            'website' => 'www.pdampurbalingga.co.id',
        ]);
    }
}
