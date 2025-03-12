<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashExpenseAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'petty_cash_expense_id',
        'petty_cash_deposit_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the expense that owns this allocation.
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(PettyCashExpense::class, 'petty_cash_expense_id');
    }

    /**
     * Get the deposit that owns this allocation.
     */
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(PettyCashDeposit::class, 'petty_cash_deposit_id');
    }
}
