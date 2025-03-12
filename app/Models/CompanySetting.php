<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'government_name',
        'company_type',
        'company_name',
        'company_logo',
        'street_address',
        'village',
        'district',
        'regency',
        'province',
        'postal_code',
        'phone_number',
        'email',
        'website',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'linkedin',
        'description',
        'tax_number'
    ];
}
