<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petty_cash_expense_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petty_cash_expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('petty_cash_deposit_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2); // Amount taken from this deposit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_expense_allocations');
    }
};
