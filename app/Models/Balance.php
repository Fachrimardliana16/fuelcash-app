<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'fuel_type_id',
        'date',
        'deposit_amount',
        'remaining_balance'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($balance) {
            // Get the last balance for the specific fuel type
            $lastBalance = static::where('fuel_type_id', $balance->fuel_type_id)
                ->latest()
                ->first()?->remaining_balance ?? 0;

            $balance->remaining_balance = $lastBalance + $balance->deposit_amount;
        });
    }
}
