<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
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
        'balance_id',
        'transaction_number',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->transaction_number = static::generateTransactionNumber($transaction->usage_date);
        });
    }

    protected static function generateTransactionNumber($usage_date)
    {
        // Convert usage_date string to Carbon instance if it isn't already
        $date = $usage_date instanceof Carbon ? $usage_date : Carbon::parse($usage_date);

        $month = $date->month;
        $year = $date->year;

        // Get the last transaction number for the specific month and year
        $lastTransaction = static::whereYear('usage_date', $year)
            ->whereMonth('usage_date', $month)
            ->latest()
            ->first();

        // Get the sequence number
        if (!$lastTransaction) {
            $sequenceNumber = 1;
        } else {
            $lastNumber = (int) substr($lastTransaction->transaction_number, 0, 3);
            $sequenceNumber = $lastNumber + 1;
        }

        // Convert month to roman numerals
        $romanMonth = static::numberToRoman($month);

        // Format: 001/KRT-BBM/XII/2024
        return sprintf("%03d/KRT-BBM/%s/%d", $sequenceNumber, $romanMonth, $year);
    }

    protected static function numberToRoman($number)
    {
        $romans = [
            1 => "I",
            2 => "II",
            3 => "III",
            4 => "IV",
            5 => "V",
            6 => "VI",
            7 => "VII",
            8 => "VIII",
            9 => "IX",
            10 => "X",
            11 => "XI",
            12 => "XII"
        ];

        return $romans[$number];
    }
}
