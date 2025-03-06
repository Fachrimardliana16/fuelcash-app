<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Fuel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fuel_type_id',
        'name',
        'price',
        'unit',
        'isactive'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'isactive' => 'boolean',
        'deleted_at' => 'datetime'
    ];

    // Scope untuk data aktif
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isactive', true);
    }

    // Relationship dengan eager loading
    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class)->withTrashed();
    }

    // Validasi data
    public static function rules(): array
    {
        return [
            'fuel_type_id' => 'required|exists:fuel_types,id',
            'name' => 'required|string|max:255|unique:fuels,name',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
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
            $model->unit = strip_tags($model->unit);
        });
    }
}
