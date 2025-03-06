<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'desc', 'isactive'];

    protected $casts = [
        'isactive' => 'boolean',
    ];

    // Scope for active records
    public function scopeActive($query)
    {
        return $query->where('isactive', true);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
