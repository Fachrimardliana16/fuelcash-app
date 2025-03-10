<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicles_id',
        'vehicle_type_id',
        'license_plate',
        'owner', // Ensure this is included
        'usage_date',
        'fuel_id',
        'fuel_type_id',
        'amount',
        'volume',
        'usage_description',
        'fuel_receipt',
        'invoice',
        'balance_id'
    ];

    protected $casts = [
        'usage_date' => 'date',
        'amount' => 'decimal:2',
        'volume' => 'decimal:2',
    ];

    public function balance()
    {
        return $this->belongsTo(Balance::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicles_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function fuel()
    {
        return $this->belongsTo(Fuel::class);
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class);
    }
}
