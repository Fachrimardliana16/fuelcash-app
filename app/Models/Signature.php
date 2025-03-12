<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $fillable = [
        'position',
        'title',
        'name',
        'nip',
        'order',
        'show_stamp'
    ];

    protected $casts = [
        'show_stamp' => 'boolean'
    ];
}
