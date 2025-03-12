<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petty_cash_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique(); // Auto-generated expense number
            $table->date('expense_date');
            $table->decimal('amount', 12, 2);
            $table->string('category'); // Category of expense
            $table->string('description');
            $table->string('recipient'); // Who received the money
            $table->string('company_receipt')->nullable(); // Company receipt document
            $table->string('shop_receipt')->nullable(); // Shop receipt document
            $table->string('item_request_document')->nullable(); // DPB document
            $table->foreignId('user_id')->constrained(); // Who recorded this expense
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_expenses');
    }
};
