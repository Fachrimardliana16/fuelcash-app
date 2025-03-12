<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PettyCashExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'expense_date',
        'amount',
        'category',
        'description',
        'recipient',
        'company_receipt',
        'shop_receipt',
        'item_request_document',
        'user_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            // Generate expense number: EXP-YYYYMMDD-XXXX
            $latestExpense = static::whereDate('created_at', today())->latest()->first();

            $sequence = $latestExpense ? (int)substr($latestExpense->expense_number, -4) + 1 : 1;

            $expense->expense_number = 'EXP-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Get the allocations for this expense.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PettyCashExpenseAllocation::class);
    }

    /**
     * Get the deposits used for this expense.
     */
    public function deposits(): BelongsToMany
    {
        return $this->belongsToMany(
            PettyCashDeposit::class,
            'petty_cash_expense_allocations'
        )->withPivot('amount');
    }

    /**
     * Get the user who recorded this expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
