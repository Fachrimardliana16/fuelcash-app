<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petty_cash_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_number')->unique(); // Auto-generated deposit number
            $table->date('deposit_date');
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->string('receipt_document')->nullable(); // For receipt document upload
            $table->foreignId('user_id')->constrained(); // Who recorded this deposit
            $table->decimal('remaining_amount', 12, 2); // For FIFO tracking
            $table->boolean('is_fully_used')->default(false); // Whether this deposit is fully used
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_deposits');
    }
};
