<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class FuelType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'desc',
        'isactive'
    ];

    protected $casts = [
        'isactive' => 'boolean',
        'deleted_at' => 'datetime'
    ];

    // Scope untuk data aktif
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isactive', true);
    }

    // Relationship dengan eager loading default
    public function fuels(): HasMany
    {
        return $this->hasMany(Fuel::class)->active();
    }

    // Validasi data
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:fuel_types,name',
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
