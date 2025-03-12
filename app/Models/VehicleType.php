<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class VehicleType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'isactive'
    ];

    protected $casts = [
        'isactive' => 'boolean'
    ];

    // Scope untuk data aktif
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isactive', true);
    }

    // Relationship dengan eager loading default
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    // Validasi data
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:vehicle_types,name',
            'desc' => 'nullable|string',
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
            $model->desc = strip_tags($model->desc);
        });
    }
}
