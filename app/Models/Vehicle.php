<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id',
        'license_plate',
        'owner',
        'isactive'
    ];

    protected $casts = [
        'isactive' => 'boolean',
    ];

    // Custom attribute mutator
    public function setLicensePlateAttribute($value)
    {
        $this->attributes['license_plate'] = strtoupper($value);
    }

    // Scope for active records
    public function scopeActive($query)
    {
        return $query->where('isactive', true);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }
}
