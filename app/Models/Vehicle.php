<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'license_plate',
        'owner',
        'vehicle_type_id',
        'vehicle_model',
        'brand',
        'detail',
        'ownership_type',
        'isactive',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'isactive' => 'boolean',
        'deleted_at' => 'datetime'
    ];

    // Custom attribute mutator
    public function setLicensePlateAttribute($value)
    {
        $this->attributes['license_plate'] = strtoupper($value);
    }

    // Scope untuk data aktif
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isactive', true);
    }

    // Relationship dengan eager loading
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class)->withTrashed();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'vehicles_id');
    }

    // Validasi data
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'owner' => 'required|string|max:255',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_model' => 'nullable|string|in:Pickup,Bebek,Matic,SUV,MPV,Sport',
            'brand' => 'nullable|string|in:Honda,Toyota,Nissan,Suzuki,Yamaha,Kawasaki,Mitsubishi,Daihatsu,Other',
            'detail' => 'nullable|string|max:255',
            'ownership_type' => 'required|string|in:Inventaris,Pribadi',
            'isactive' => 'boolean'
        ];
    }

    // Boot method untuk setup model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Sanitasi input
            $model->name = strip_tags($model->name);
            $model->owner = strip_tags($model->owner);
            $model->license_plate = strtoupper(strip_tags($model->license_plate));
        });
    }
}
