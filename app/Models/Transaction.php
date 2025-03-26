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
            $now = Carbon::now();
            $transaction->transaction_number = static::generateTransactionNumber($now);
        });

        // Hapus event listener yang memaksa force delete
        // static::deleted(function ($transaction) {
        //     if (!$transaction->isForceDeleting()) {
        //         $transaction->forceDelete();
        //     }
        // });
    }

    protected static function generateTransactionNumber($date)
    {
        $month = $date->month;
        $year = $date->year;

        // Get all used transaction numbers for the current month/year (including soft deleted)
        $usedNumbers = static::withTrashed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotNull('transaction_number')
            ->pluck('transaction_number')
            ->toArray();

        // Find the first available number
        $sequenceNumber = 1;
        $romanMonth = static::numberToRoman($month);

        while (true) {
            $proposedNumber = sprintf("%03d/KRT-BBM/%s/%d", $sequenceNumber, $romanMonth, $year);
            if (!in_array($proposedNumber, $usedNumbers)) {
                return $proposedNumber;
            }
            $sequenceNumber++;
        }
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
