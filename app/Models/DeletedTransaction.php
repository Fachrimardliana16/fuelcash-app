<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletedTransaction extends Model
{
    protected $fillable = [
        'original_id',
        'transaction_number',
        'user_id',
        'vehicles_id',
        'vehicle_type_id',
        'license_plate',
        'owner',
        'usage_date',
        'fuel_id',
        'fuel_type_id',
        'amount',
        'volume',
        'usage_description',
        'fuel_receipt',
        'invoice',
        'balance_id',
        'deleted_by',
        'deletion_reason',
        'deleted_at'
    ];

    protected $casts = [
        'usage_date' => 'date',
        'deleted_at' => 'datetime',
        'amount' => 'decimal:2',
        'volume' => 'decimal:2',
    ];

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function vehicle()
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

    public function balance()
    {
        return $this->belongsTo(Balance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
