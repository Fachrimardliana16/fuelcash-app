<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Balance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'deposit_amount',
        'remaining_balance'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($balance) {
            $lastBalance = static::latest()->first()?->remaining_balance ?? 0;
            $balance->remaining_balance = $lastBalance + $balance->deposit_amount;
        });
    }
}
