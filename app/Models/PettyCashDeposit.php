<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_number',
        'deposit_date',
        'amount',
        'description',
        'receipt_document',
        'user_id',
        'remaining_amount',
        'is_fully_used',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'is_fully_used' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deposit) {
            // Generate deposit number: DEP-YYYYMMDD-XXXX
            $latestDeposit = static::whereDate('created_at', today())->latest()->first();

            $sequence = $latestDeposit ? (int)substr($latestDeposit->deposit_number, -4) + 1 : 1;

            $deposit->deposit_number = 'DEP-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Set initial remaining amount
            $deposit->remaining_amount = $deposit->amount;
        });
    }

    /**
     * Get the expense allocations for this deposit.
     */
    public function expenseAllocations(): HasMany
    {
        return $this->hasMany(PettyCashExpenseAllocation::class);
    }

    /**
     * Get the user who recorded this deposit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
